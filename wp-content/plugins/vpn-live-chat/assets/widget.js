(() => {
 'use strict';
 let panel,launch,cfg,csrf='',conversation=null,cursor=0,timer,controller,opened=false,busy=false,config={},idle=0,failures=0,pending=null,token='',turnstileId;
 let challengeScript,bootstrapPromise,syncPromise,readPromise,readCursor=0,readTimer,resizePanel,unread=0,conversationClosed=false;
 let welcomeTimer,welcomeShown=false,contactDirty=false,contactBusy=false,identityRequest='',identityBusy=false,contactEmail='',contactExpanded=false,contactDismissed=false;
 const seen=new Set(),channel=typeof BroadcastChannel==='function'?new BroadcastChannel('vpn-chat-refresh'):null;
 const $=id=>panel.querySelector(`#${id}`);
 function state(text){$('vpn-chat-state').textContent=['Saved.','Connected.','Ready to send.'].includes(text)?'':text;}
 function activeFlag(value){try{if(value)sessionStorage.setItem('vpn-chat-active','1');else sessionStorage.removeItem('vpn-chat-active');}catch{}}
 async function request(path,data,signal){
  const abort=new AbortController(),onAbort=()=>abort.abort();signal?.addEventListener('abort',onAbort,{once:true});const timeout=setTimeout(()=>abort.abort(),12000);
  try{
   const url=new URL(cfg.api),route=path.split('?')[0];if(url.searchParams.has('rest_route'))url.searchParams.set('rest_route',url.searchParams.get('rest_route')+route);else url.pathname+=route;
   const query=path.split('?')[1];if(query)for(const [k,v]of new URLSearchParams(query))url.searchParams.set(k,v);
   const response=await fetch(url,{method:data===undefined?'GET':'POST',credentials:'same-origin',cache:'no-store',headers:{'Content-Type':'application/json','X-VPN-CSRF':csrf},body:data===undefined?undefined:JSON.stringify(data),signal:abort.signal});
   const json=await response.json();if(!response.ok){const e=Error(json.code||'request_failed');e.retry=Number(response.headers.get('Retry-After'))||0;throw e;}return json;
  }finally{clearTimeout(timeout);signal?.removeEventListener('abort',onAbort);}
 }
 function avatar(profile,className='vpn-chat-avatar'){
  const el=document.createElement('span');el.className=className;el.setAttribute('aria-hidden','true');const initials=document.createElement('span');initials.textContent=profile?.initials||'VP';el.append(initials);
  if(profile?.avatar){try{const url=new URL(profile.avatar,location.href);if(['http:','https:'].includes(url.protocol)){const img=document.createElement('img');img.src=url.href;img.alt='';img.width=40;img.height=40;img.loading='lazy';img.decoding='async';img.addEventListener('error',()=>img.remove(),{once:true});el.append(img);}}catch{}}
  return el;
 }
 let headerProfile='';
 function paintConfig(c){
  config=c;const profile=c.support||cfg.support||{name:'VPN Packaging',initials:'VP'};const key=JSON.stringify(profile);
  if(key!==headerProfile){headerProfile=key;$('vpn-chat-header-avatar').replaceChildren(avatar(profile));$('vpn-chat-title').textContent=profile.name;$('vpn-chat-welcome-avatar').replaceChildren(avatar(profile));$('vpn-chat-welcome-name').textContent=profile.name;}
  const presence=['online','away','offline'].includes(c.presence)?c.presence:(c.online?'online':'offline');panel.dataset.presence=presence;
  $('vpn-chat-presence').textContent={online:'Online',away:'Away',offline:'Offline'}[presence];
  $('vpn-chat-copy').textContent=c.offline_copy||'Please leave your email address so we can get back to you as soon as possible.';
  $('vpn-chat-away-name').textContent=profile.name;emailReminder();
  const fallback=$('vpn-chat-fallback');fallback.hidden=!c.fallback_url;if(c.fallback_url){fallback.href=c.fallback_url;fallback.textContent='Contact us another way';}
  $('vpn-chat-message').maxLength=$('vpn-chat-first').maxLength=c.max_chars||2000;
  $('vpn-chat-start-button').disabled=!c.accepting;if(!c.accepting&&!conversation)state('Chat is temporarily unavailable. Please use our contact link.');
 }
 function badge(count){unread=Math.max(0,Number(count)||0);const el=document.getElementById('vpn-chat-unread');el.hidden=!unread;el.textContent=unread>99?'99+':String(unread);launch.setAttribute('aria-label',`${opened?'Minimize':'Open'} packaging chat${unread?`, ${unread} unread messages`:''}`);}
 function emailReminder(){
  // One optional follow-up card, after a real conversation starts or on explicit request.
  $('vpn-chat-away-message').hidden=true;
  $('vpn-chat-contact').hidden=!contactExpanded&&(!conversation||!!contactEmail||conversationClosed||contactDismissed);
  $('vpn-chat-add-email').textContent=contactEmail?'Edit email':'Add email (optional)';
  $('vpn-chat-add-email').setAttribute('aria-expanded',String(!$('vpn-chat-contact').hidden));
  $('vpn-chat-contact-title').textContent=conversation?'Want our quote by email?':'Where can we send our reply?';
 }
 function openContact(){contactExpanded=true;emailReminder();resizePanel();$('vpn-chat-contact').scrollIntoView({block:'nearest'});$('vpn-chat-contact-email').focus({preventScroll:true});}
 function summary(c){
  contactEmail=(c.email||'').trim();emailReminder();
  if(!contactDirty&&!contactBusy)$('vpn-chat-contact-email').value=c.email||'';
  if(Number(c.guest_read_seq)>=readCursor){readCursor=Number(c.guest_read_seq);badge(c.unread);}
  panel.querySelectorAll('.vpn-chat-message[data-sender=guest]').forEach(el=>{el.querySelector('.vpn-chat-delivery').textContent=Number(el.dataset.seq)<=Number(c.agent_read_seq)?'Read':'Saved';});
 }
 async function challenge(){
  if(!config.site_key)return;$('vpn-chat-challenge').hidden=false;
  if(!challengeScript)challengeScript=new Promise((resolve,reject)=>{if(window.turnstile)return resolve();const script=document.createElement('script');script.src='https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';script.async=true;script.onload=resolve;script.onerror=()=>{challengeScript=null;script.remove();reject(Error('challenge_unavailable'));};document.head.append(script);});
  await challengeScript;if(turnstileId!==undefined){window.turnstile.reset(turnstileId);return;}
  turnstileId=window.turnstile.render($('vpn-chat-challenge'),{sitekey:config.site_key,action:'vpn_chat_start',callback:value=>{token=value;state('Ready to send.');},'expired-callback':()=>{token='';state('Verification expired. Your draft is still here.');},'error-callback':()=>{token='';state('Verification failed. Your draft is still here.');}});
 }
 function atBottom(){const el=$('vpn-chat-content');return el.scrollHeight-el.scrollTop-el.clientHeight<36;}
 function scrollBottom(){const el=$('vpn-chat-content');el.scrollTop=el.scrollHeight;}
 function append(rows){
  const log=$('vpn-chat-log'),follow=atBottom();
  for(const message of rows){
   if(seen.has(Number(message.seq)))continue;seen.add(Number(message.seq));
   const item=document.createElement('div');item.className='vpn-chat-message';item.dataset.sender=message.sender;item.dataset.seq=message.seq;
   const profile=message.profile||{name:'Packaging team',initials:'VP'};
   if(message.sender==='agent')item.append(avatar(profile));
   const group=document.createElement('div');group.className='vpn-chat-message-group';const who=document.createElement('strong');who.className='vpn-chat-message-name';who.textContent=message.sender==='agent'?profile.name:'You';
   const content=document.createElement('div');content.className='vpn-chat-bubble';content.textContent=message.body;
   const meta=document.createElement('div');meta.className='vpn-chat-message-meta';const time=document.createElement('time');const date=new Date(message.created_at.replace(' ','T')+'Z');time.dateTime=date.toISOString();time.textContent=date.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});time.title=date.toLocaleString();
   meta.append(time);if(message.sender==='guest'){const delivery=document.createElement('span');delivery.className='vpn-chat-delivery';delivery.textContent='Saved';meta.append(delivery);}
   group.append(who,content,meta);item.append(group);log.append(item);
  }
  if(rows.length&&follow)scrollBottom();
 }
 function stop(){clearTimeout(timer);controller?.abort();}
 function schedule(delay){clearTimeout(timer);if(!document.hidden&&conversation)timer=setTimeout(sync,delay??(opened?Math.min(15000,[3000,5000,10000,15000][Math.min(idle,3)]+Math.random()*350):15000));}
 async function acknowledge(){
  if(!opened||document.hidden||!conversation||!cursor||cursor<=readCursor||!atBottom()||readPromise)return;
  const id=conversation,seq=cursor;
  readPromise=request('guest/read',{id,cursor:seq}).then(result=>{if(conversation===id){summary(result);channel?.postMessage('read');}}).catch(()=>{}).finally(()=>{readPromise=null;});
  return readPromise;
 }
 async function sync(){
  if(document.hidden||!conversation)return;if(syncPromise)return syncPromise;
  syncPromise=(async()=>{
   controller=new AbortController();
   try{
    const result=await request(`guest/sync?id=${encodeURIComponent(conversation)}&cursor=${cursor}`,undefined,controller.signal);
    append(result.messages);cursor=result.cursor;paintConfig(result.config);summary(result.conversation);$('vpn-chat-loading').hidden=true;resizePanel();
    if($('vpn-chat-state').textContent==='Connecting…'||$('vpn-chat-state').textContent.startsWith('Connection interrupted'))state('Connected.');
    idle=result.messages.length?0:Math.min(3,idle+1);failures=0;const closed=result.conversation.status==='closed';conversationClosed=closed;emailReminder();$('vpn-chat-send-button').disabled=closed||busy;$('vpn-chat-new').hidden=!closed;
    if(closed)state('This conversation is closed. You can start a new chat.');
    await acknowledge();schedule(result.more?100:undefined);
   }catch(e){
    if(e.name==='AbortError'){schedule(500);return;}
    if(e.message==='session_expired'){state('Reconnecting to your saved conversation…');csrf='';setTimeout(()=>bootstrap().catch(()=>state('Please reopen chat to reconnect.')),100);return;}
    failures++;state('Connection interrupted. Retrying shortly.');schedule(Math.max(e.retry*1000,Math.min(60000,3000*2**Math.min(failures,4))));
   }
  })().finally(()=>{syncPromise=null;});return syncPromise;
 }
 async function bootstrap(){
  if(bootstrapPromise)return bootstrapPromise;
  bootstrapPromise=(async()=>{
   const result=await request('bootstrap',{});csrf=result.csrf;
   if(result.conversation!==conversation){seen.clear();cursor=0;readCursor=0;$('vpn-chat-log').replaceChildren();}
   paintIdentity(result);conversationClosed=false;contactEmail='';conversation=result.conversation;emailReminder();activeFlag(!!conversation);paintConfig(result.config);
   $('vpn-chat-loading').hidden=!conversation;
   $('vpn-chat-welcome').hidden=!!conversation||!welcomeShown;$('vpn-chat-start').hidden=!!conversation;$('vpn-chat-first-composer').hidden=!!conversation;$('vpn-chat-prompts').hidden=!!conversation;$('vpn-chat-reply').hidden=!conversation;$('vpn-chat-log').hidden=!conversation;
   emailReminder();arriveWelcome();
   if(conversation)await sync();else if(opened&&config.accepting)await challenge();
  })().finally(()=>{bootstrapPromise=null;});return bootstrapPromise;
 }
 function paintIdentity(result){
  $('vpn-chat-customer-code').textContent=result.customer?.verified_email?'Email verified':result.customer?.code||'';
  const select=$('vpn-chat-history'),chosen=result.conversation;select.replaceChildren();for(const h of result.history||[]){const o=document.createElement('option');o.value=h.id;o.textContent=`${new Date(h.updated_at.replace(' ','T')+'Z').toLocaleString([],{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'})} · ${h.status==='closed'?'Closed':'Conversation'}${h.unread?' · New message':''}`;o.selected=h.id===chosen;select.append(o);}select.hidden=(result.history?.length||0)<2;
 }
 function arriveWelcome(){
  clearTimeout(welcomeTimer);if(!opened||conversation||welcomeShown)return;
  welcomeTimer=setTimeout(()=>{if(!opened||document.hidden||conversation)return;welcomeShown=true;$('vpn-chat-welcome').hidden=false;resizePanel();},850);
 }
 function grow(textarea){textarea.style.height='auto';textarea.style.height=`${Math.min(120,Math.max(44,textarea.scrollHeight))}px`;if(resizePanel)resizePanel();}
 async function send(event){
  event.preventDefault();if(busy)return;busy=true;const first=pending?Object.hasOwn(pending,'name'):!conversation;const textarea=$(first?'vpn-chat-first':'vpn-chat-message'),button=$(first?'vpn-chat-start-button':'vpn-chat-send-button');button.disabled=true;$('vpn-chat-send-button').disabled=true;
  try{
   if(!pending){pending={message:textarea.value,client_message_id:crypto.randomUUID().replaceAll('-','')};if(first){const metadata={},query=new URLSearchParams(location.search);for(const key of ['utm_source','utm_medium','utm_campaign'])if(query.has(key))metadata[key]=query.get(key).slice(0,100);Object.assign(pending,{name:$('vpn-chat-name').value,email:$('vpn-chat-email').value,source_path:location.pathname,metadata});}else pending.id=conversation;}
   state('Sending…');const result=await request(first?'guest/start':'guest/send',{...pending,challenge:token});conversation=result.id;activeFlag(true);
   if(textarea.value===pending.message)textarea.value='';grow(textarea);pending=null;token='';state('Saved.');idle=0;
   clearTimeout(welcomeTimer);$('vpn-chat-welcome').hidden=true;$('vpn-chat-start').hidden=true;$('vpn-chat-first-composer').hidden=true;$('vpn-chat-prompts').hidden=true;$('vpn-chat-reply').hidden=false;$('vpn-chat-log').hidden=false;$('vpn-chat-challenge').hidden=true;
   channel?.postMessage('saved');await sync();scrollBottom();await acknowledge();
  }catch(e){
   token='';if(['challenge_required','challenge_unavailable'].includes(e.message)||first)try{await challenge();}catch{}
   if(['invalid_text','invalid_email'].includes(e.message))pending=null;
   if(e.message==='session_expired'){csrf='';state('Session expired. Close and reopen chat; your draft is kept.');}
   else state(e.message==='rate_limited'?'Please wait before retrying. Your draft is kept.':'Message was not confirmed. Click send to retry the same message. Your draft is kept.');
  }finally{busy=false;button.disabled=first&&!config.accepting;$('vpn-chat-send-button').disabled=conversationClosed;}
 }
 function close(){clearTimeout(welcomeTimer);opened=false;panel.hidden=true;$('vpn-chat-options').setAttribute('aria-expanded','false');panel.querySelector('.vpn-chat-identity').hidden=true;launch.disabled=false;launch.dataset.open='false';launch.setAttribute('aria-expanded','false');launch.style.visibility='';badge(unread);schedule(15000);launch.focus();}
 function build(){
  panel=document.createElement('section');panel.id='vpn-chat-panel';panel.className='vpn-chat-panel';panel.hidden=true;panel.tabIndex=-1;panel.setAttribute('role','dialog');panel.setAttribute('aria-labelledby','vpn-chat-title');
  panel.innerHTML=`<header class="vpn-chat-header"><span id="vpn-chat-header-avatar"></span><div class="vpn-chat-heading"><h2 id="vpn-chat-title"></h2><span class="vpn-chat-company">Sale Manager</span><span id="vpn-chat-presence" class="vpn-chat-presence">Connecting…</span></div><button id="vpn-chat-options" type="button" aria-label="Conversation options" aria-expanded="false" aria-controls="vpn-chat-identity-menu">⋮</button><button id="vpn-chat-close" type="button" aria-label="Minimize chat"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 12h12"/></svg></button></header>
  <div id="vpn-chat-identity-menu" class="vpn-chat-identity" hidden><span id="vpn-chat-customer-code"></span><select id="vpn-chat-history" aria-label="Your conversation history" hidden></select><details id="vpn-chat-restore"><summary>Continue a previous conversation</summary><form id="vpn-chat-identity-form"><label>Email address<input id="vpn-chat-identity-email" type="email" autocomplete="email" maxlength="254" required placeholder="you@company.com"></label><button id="vpn-chat-code-request" type="submit">Send verification code</button></form><form id="vpn-chat-code-form" hidden><label>6-digit code<input id="vpn-chat-code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required></label><button id="vpn-chat-code-verify" type="submit">Verify and restore</button></form><p id="vpn-chat-identity-status" role="status" aria-live="polite">Use the same email to restore your chats on another browser. Email is optional for chatting.</p></details></div><div id="vpn-chat-content" class="vpn-chat-content"><div id="vpn-chat-away-message" class="vpn-chat-welcome vpn-chat-away-message" role="status" aria-live="polite" hidden><div class="vpn-chat-message-group"><strong id="vpn-chat-away-name" class="vpn-chat-message-name"></strong><div class="vpn-chat-bubble"><p id="vpn-chat-copy"></p><button id="vpn-chat-leave-email" type="button">Leave your email</button></div><span class="vpn-chat-auto-label">Automatic message</span></div></div><div id="vpn-chat-welcome" class="vpn-chat-welcome" role="status" aria-live="polite" hidden><span id="vpn-chat-welcome-avatar"></span><div class="vpn-chat-message-group"><strong id="vpn-chat-welcome-name" class="vpn-chat-message-name"></strong><div class="vpn-chat-bubble"><p id="vpn-chat-welcome-text"></p></div><span class="vpn-chat-auto-label">Automatic welcome</span></div></div>
  <div id="vpn-chat-prompts" hidden></div><form id="vpn-chat-start" class="vpn-chat-metadata"><input id="vpn-chat-name" type="hidden" value=""><input id="vpn-chat-email" type="hidden" value=""></form>
  <p id="vpn-chat-loading" class="vpn-chat-form-help" hidden>Loading your conversation…</p><div id="vpn-chat-log" class="vpn-chat-log" role="log" aria-live="polite" aria-label="Conversation" tabindex="0" hidden></div><form id="vpn-chat-contact" class="vpn-chat-contact" hidden><span class="vpn-chat-followup-label">✉ Follow-up</span><button id="vpn-chat-contact-dismiss" type="button" aria-label="Dismiss email invitation">×</button><h3 id="vpn-chat-contact-title">Want our quote by email?</h3><label class="vpn-chat-composer-label" for="vpn-chat-contact-email">Email for follow-up (optional)</label><div><input id="vpn-chat-contact-email" type="email" autocomplete="email" placeholder="Your email" maxlength="254"><button id="vpn-chat-contact-save" type="submit">Save</button></div><p class="vpn-chat-followup-help">Optional — keep chatting here</p><p id="vpn-chat-contact-status" role="status" aria-live="polite"></p></form><div id="vpn-chat-challenge" class="vpn-chat-challenge" hidden></div></div>
  <div class="vpn-chat-composer"><div id="vpn-chat-first-composer"><label class="vpn-chat-composer-label" for="vpn-chat-first">Message</label><textarea id="vpn-chat-first" form="vpn-chat-start" placeholder="Type your message…" maxlength="2000" required rows="1"></textarea><button id="vpn-chat-start-button" form="vpn-chat-start" type="submit" aria-label="Start conversation"><svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="m22 2-11 11"/></svg></button></div><form id="vpn-chat-reply" hidden><label class="vpn-chat-composer-label" for="vpn-chat-message">Message</label><textarea id="vpn-chat-message" placeholder="Type your message…" maxlength="2000" required rows="1"></textarea><button id="vpn-chat-send-button" type="submit" aria-label="Send message"><svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="m22 2-11 11"/></svg></button></form><p id="vpn-chat-state" role="status" aria-live="polite"></p><a id="vpn-chat-fallback" hidden></a><button id="vpn-chat-new" type="button" hidden>Start a new chat</button><div class="vpn-chat-composer-footer"><button id="vpn-chat-add-email" type="button" aria-expanded="false" aria-controls="vpn-chat-contact">Add email (optional)</button><span class="vpn-chat-footer">VPN Packaging</span></div></div>`;
  document.body.append(panel);const profile=cfg.support||{name:'VPN Packaging',initials:'VP'};$('vpn-chat-header-avatar').append(avatar(profile));$('vpn-chat-title').textContent=profile.name;
  $('vpn-chat-welcome-text').textContent=cfg.greeting_text||'Hi! How can we help with your packaging?';
  $('vpn-chat-prompts').addEventListener('click',e=>{const button=e.target.closest('button[data-message]');if(!button)return;$('vpn-chat-first').value=button.dataset.message;grow($('vpn-chat-first'));$('vpn-chat-first').focus({preventScroll:true});});
  $('vpn-chat-close').addEventListener('click',close);
  $('vpn-chat-options').addEventListener('click',()=>{const menu=panel.querySelector('.vpn-chat-identity');menu.hidden=!menu.hidden;$('vpn-chat-options').setAttribute('aria-expanded',String(!menu.hidden));if(!menu.hidden)menu.querySelector('summary').focus();});
  document.addEventListener('keydown',e=>{if(opened&&e.key==='Escape'){e.preventDefault();const menu=panel.querySelector('.vpn-chat-identity');if(!menu.hidden){menu.hidden=true;$('vpn-chat-options').setAttribute('aria-expanded','false');$('vpn-chat-options').focus();}else close();}});
  $('vpn-chat-leave-email').addEventListener('click',openContact);
  $('vpn-chat-add-email').addEventListener('click',openContact);
  $('vpn-chat-contact-dismiss').addEventListener('click',()=>{contactExpanded=false;contactDismissed=true;emailReminder();resizePanel();$('vpn-chat-add-email').focus();});
  $('vpn-chat-contact-email').addEventListener('input',()=>{contactDirty=true;$('vpn-chat-contact-status').textContent='';});
  $('vpn-chat-contact').addEventListener('submit',async e=>{e.preventDefault();if(contactBusy)return;const email=$('vpn-chat-contact-email').value.trim();
   if(!conversation){$('vpn-chat-email').value=email;contactEmail=email;contactDirty=false;contactExpanded=false;contactDismissed=!!email;emailReminder();resizePanel();state(email?'Email added. It will be saved with your first message.':'Email removed. You can keep chatting.');return;}
   contactBusy=true;$('vpn-chat-contact-save').disabled=true;try{const result=await request('guest/contact',{id:conversation,email});if($('vpn-chat-contact-email').value.trim()===email){contactDirty=false;$('vpn-chat-contact-email').value=result.email;}contactEmail=result.email.trim();contactExpanded=false;contactDismissed=!!contactEmail;emailReminder();state(result.email?'Email saved. Thank you!':'Email removed. You can keep chatting.');channel?.postMessage('contact');}catch{$('vpn-chat-contact-status').textContent='Could not save email. Please check it and try again.';}finally{contactBusy=false;$('vpn-chat-contact-save').disabled=false;resizePanel();}});
  $('vpn-chat-start').addEventListener('submit',send);$('vpn-chat-reply').addEventListener('submit',send);
  $('vpn-chat-new').addEventListener('click',()=>{stop();conversation=null;conversationClosed=false;contactEmail='';contactExpanded=false;contactDismissed=false;contactDirty=false;$('vpn-chat-email').value='';$('vpn-chat-contact-email').value='';emailReminder();pending=null;seen.clear();cursor=readCursor=0;badge(0);activeFlag(false);$('vpn-chat-log').replaceChildren();$('vpn-chat-log').hidden=true;$('vpn-chat-start').hidden=false;$('vpn-chat-first-composer').hidden=false;$('vpn-chat-reply').hidden=true;$('vpn-chat-new').hidden=true;$('vpn-chat-prompts').hidden=false;state('Start a new conversation.');challenge().catch(()=>state('Could not load verification. Please retry.'));resizePanel();});
  $('vpn-chat-history').addEventListener('change',async()=>{if(busy||pending){state('Please finish sending your message first.');return;}stop();if(syncPromise)await syncPromise;conversation=$('vpn-chat-history').value;seen.clear();cursor=readCursor=0;$('vpn-chat-log').replaceChildren();$('vpn-chat-start').hidden=true;$('vpn-chat-first-composer').hidden=true;$('vpn-chat-prompts').hidden=true;$('vpn-chat-reply').hidden=false;$('vpn-chat-log').hidden=false;contactExpanded=false;contactDismissed=false;emailReminder();activeFlag(true);await sync();scrollBottom();});
  $('vpn-chat-identity-form').addEventListener('submit',async e=>{e.preventDefault();if(identityBusy)return;identityBusy=true;$('vpn-chat-code-request').disabled=true;try{if(!csrf)await bootstrap();const result=await request('guest/identity/request',{email:$('vpn-chat-identity-email').value.trim()});identityRequest=result.request_id;$('vpn-chat-code-form').hidden=false;$('vpn-chat-identity-status').textContent='Code sent. Check your email; the code expires in 10 minutes.';$('vpn-chat-code').focus();}catch(e){$('vpn-chat-identity-status').textContent=e.message==='rate_limited'?'Please wait before requesting another code.':'Could not send the code. Check your email address and try again.';}finally{identityBusy=false;$('vpn-chat-code-request').disabled=false;resizePanel();}});
  $('vpn-chat-restore').addEventListener('toggle',()=>resizePanel());
  $('vpn-chat-code-form').addEventListener('submit',async e=>{e.preventDefault();if(identityBusy||busy||pending)return;identityBusy=true;$('vpn-chat-code-verify').disabled=true;try{stop();if(syncPromise)await syncPromise;await request('guest/identity/verify',{request_id:identityRequest,code:$('vpn-chat-code').value.trim()});contactDirty=false;await bootstrap();$('vpn-chat-code').value='';$('vpn-chat-code-form').hidden=true;$('vpn-chat-restore').open=false;$('vpn-chat-identity-status').textContent='Email verified. Your conversations are restored.';state('Email verified. Your conversations are restored.');channel?.postMessage('identity');}catch(e){$('vpn-chat-identity-status').textContent=e.message==='verification_expired'?'Code expired or too many attempts. Request a new code.':e.message==='different_verified_identity'?'This browser is already linked to a different verified email.':'Code not accepted. Check the code and try again.';schedule();}finally{identityBusy=false;$('vpn-chat-code-verify').disabled=false;resizePanel();}});

  $('vpn-chat-content').addEventListener('scroll',()=>{clearTimeout(readTimer);readTimer=setTimeout(acknowledge,200);},{passive:true});
  document.addEventListener('visibilitychange',()=>{if(document.hidden){stop();clearTimeout(welcomeTimer);}else{sync();arriveWelcome();}});window.addEventListener('focus',()=>{if(conversation)sync();});window.addEventListener('pagehide',stop);
  channel?.addEventListener('message',e=>{if(e.data==='identity'){stop();bootstrap().catch(()=>state('Please reopen chat to refresh your identity.'));return;}if(conversation)sync();else if(opened)bootstrap().catch(()=>state('Could not refresh chat. Your draft is kept.'));});
  for(const id of ['vpn-chat-first','vpn-chat-message']){const textarea=$(id);let composing=false;textarea.addEventListener('compositionstart',()=>{composing=true;});textarea.addEventListener('compositionend',()=>setTimeout(()=>{composing=false;},0));textarea.addEventListener('input',()=>{grow(textarea);idle=0;if(conversation)schedule(3000);});textarea.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey&&!e.isComposing&&e.keyCode!==229&&!composing){e.preventDefault();textarea.form.requestSubmit();}});}
  resizePanel=()=>{const l=cfg.layout();const clearance=l.keyboard?16:l.bottom+72;panel.style.width=`${Math.min(400,l.width-(l.width<=600?16:40))}px`;panel.style.right=`${l.edge+(l.width<=600?8:20)}px`;panel.style.bottom=`calc(${l.insetBottom+clearance}px + env(safe-area-inset-bottom))`;const natural=conversation ? Math.max(360,panel.querySelector('.vpn-chat-header').offsetHeight+panel.querySelector('.vpn-chat-composer').offsetHeight+$('vpn-chat-log').offsetHeight+($('vpn-chat-contact').hidden?0:$('vpn-chat-contact').offsetHeight+16)+32) : 500;panel.style.height=`${Math.min(natural,l.width<=600?660:580,Math.max(150,l.height-clearance-16))}px`;launch.style.visibility=opened&&l.keyboard?'hidden':'';};
  document.addEventListener('vpn-chat-layout',resizePanel);window.addEventListener('resize',resizePanel);window.visualViewport?.addEventListener('resize',resizePanel);window.visualViewport?.addEventListener('scroll',resizePanel);resizePanel();
 }
 function initialize(configuration,button){cfg=configuration;launch=button;if(!panel)build();}
 window.VPNChatResume=async(configuration,button)=>{initialize(configuration,button);try{await bootstrap();}catch{activeFlag(false);}};
 window.VPNChatOpen=async(configuration,button)=>{
  initialize(configuration,button);if(opened){close();return;}opened=true;panel.hidden=false;launch.dataset.open='true';launch.setAttribute('aria-expanded','true');badge(unread);resizePanel();state('Connecting…');
  try{if(!csrf)await bootstrap();else if(conversation)await sync();else{arriveWelcome();if(config.accepting)await challenge();}if(!opened)return;if(!conversation&&config.accepting&&!config.site_key&&$('vpn-chat-state').textContent==='Connecting…')state('');if(conversation){scrollBottom();await acknowledge();}if(conversation&&cfg.layout().width>600)$('vpn-chat-message').focus({preventScroll:true});else panel.focus({preventScroll:true});}
  catch{state('Chat cannot connect right now. Close and reopen to retry.');}
 };
})();
