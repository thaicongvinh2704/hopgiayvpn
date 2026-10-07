(() => {
  'use strict';
  const cfg=window.VPNChatAdmin; if(!cfg)return;
  const $=id=>document.getElementById(id);
  let selected=null,cursor=0,page=1,timer,controller,active=false,failures=0,pending=null,dirty=false,notifications=false,baseline=false,audio;
  const known=new Map(),seen=new Set();
  const drafts=new Map();let refreshRequested=false,sending=false,readActive=false,searchTimer,deleting=false,syncEpoch=0,deleteNotice='';
  let listSignature='',cannedSignature='',customerSignature='',infoSignature='';
  const date=value=>new Date(value.replace(' ','T')+'Z');
  const hasEmail=contact=>typeof contact.email==='string'&&contact.email.trim().length>0;
  function node(tag,className,text){const el=document.createElement(tag);el.className=className;if(text!==undefined)el.textContent=text;return el;}
  function empty(){ $('vpn-detail').hidden=true;$('vpn-empty').hidden=false;$('vpn-chat-admin').classList.remove('vpn-chat-selected'); }
  function choose(row){
    if(deleting)return;
    deleteNotice='';
    if(sending||pending&&pending.id!==row.id){state('Tin hiện tại chưa được xác nhận. Gửi lại trước khi chuyển hội thoại.');return;}
    if(selected?.public_id===row.id){$('vpn-chat-admin').classList.add('vpn-chat-selected');sync(true,true);return;}
    if(selected)drafts.set(selected.public_id,{text:$('vpn-message').value,note:$('vpn-note').checked});
    selected={public_id:row.id};cursor=0;seen.clear();dirty=false;customerSignature='';infoSignature='';$('vpn-transcript').replaceChildren();
    const draft=drafts.get(row.id);$('vpn-message').value=draft?.text||'';$('vpn-note').checked=!!draft?.note;$('vpn-send-status').textContent='';$('vpn-conversation-info').open=false;$('vpn-info-toggle').setAttribute('aria-expanded','false');
    $('vpn-detail').hidden=false;$('vpn-empty').hidden=true;$('vpn-chat-admin').classList.add('vpn-chat-selected');$('vpn-reply').hidden=true;$('vpn-customer').replaceChildren(node('h2','',row.name));
    highlightRows();state('Đang tải hội thoại…');sync(true,true);
  }
  const state=text=>{$('vpn-admin-error').textContent=text;};
  async function api(path,data,signal) {
    const url=new URL(cfg.api);if(url.searchParams.has('rest_route'))url.searchParams.set('rest_route',url.searchParams.get('rest_route')+path);else url.pathname+=path;
    const abort=new AbortController();const cancel=()=>abort.abort();signal?.addEventListener('abort',cancel,{once:true});const timeout=setTimeout(cancel,12000);
    if(signal?.aborted)cancel();
    try {
      const res=await fetch(url,{method:data===undefined?'GET':'POST',credentials:'same-origin',cache:'no-store',headers:{'Content-Type':'application/json','X-WP-Nonce':cfg.nonce},body:data===undefined?undefined:JSON.stringify(data),signal:abort.signal});
      const json=await res.json();if(!res.ok){const e=new Error(json.code||'Lỗi API');e.status=res.status;e.retry=Number(res.headers.get('Retry-After'))||0;throw e;}return json;
    }finally{clearTimeout(timeout);signal?.removeEventListener('abort',cancel);}
  }
  function schedule(delay=(selected?2000:5000)+Math.random()*400){clearTimeout(timer);if(!document.hidden)timer=setTimeout(sync,delay);}
  function notify(row){
    if(!notifications)return;
    if(window.Notification?.permission==='granted')new Notification('VPN Live Chat',{body:'Có tin nhắn mới trong inbox.',tag:`vpn-${row.id}`});
    if(audio){const oscillator=audio.createOscillator(),gain=audio.createGain();gain.gain.value=.08;oscillator.connect(gain).connect(audio.destination);oscillator.start();oscillator.stop(audio.currentTime+.12);}
  }
  function list(rows){
    const signature=JSON.stringify(rows);
    if(signature===listSignature){highlightRows();return;}
    listSignature=signature;const container=$('vpn-list');container.replaceChildren();
    for(const row of rows){
      const last=known.get(row.id);if(baseline && row.unread && (last===undefined || Number(row.guest_seq)>last))notify(row);known.set(row.id,Number(row.guest_seq));
      const button=document.createElement('button');button.type='button';button.className=`vpn-row${row.unread?' unread':''}${row.overdue?' overdue':''}`;
      button.dataset.id=row.id;button.dataset.customer=row.customer_id;
      const current=selected?.public_id===row.id||!!selected?.customer_id&&Number(selected.customer_id)===Number(row.customer_id);button.classList.toggle('selected',current);button.setAttribute('aria-pressed',String(current));
      const contactable=hasEmail(row),name=row.name?.trim()||row.customer_code||'Khách ẩn danh';button.classList.toggle('has-email',contactable);button.classList.toggle('no-email',!contactable);
      button.append(node('span','vpn-contact-avatar',name.split(/\s+/).map(w=>w[0]).slice(0,2).join('').toUpperCase()));
      const body=node('span','vpn-contact-body'),top=node('span','vpn-contact-top');top.append(node('strong','vpn-contact-name',name),node('time','vpn-contact-time',date(row.updated_at).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})));
      const identity=node('span','vpn-contact-identity');const emailBadge=node('span','vpn-email-badge',row.verified_email?'Đã xác minh':contactable?'Có email':'Chưa có email');emailBadge.title=contactable?'Khách đã cung cấp email liên hệ; chưa xác minh địa chỉ.':'Khách chưa cung cấp email liên hệ.';identity.append(emailBadge);
      if(contactable){const email=node('span','vpn-contact-email',row.email);email.title=row.email;identity.append(email);}
      const bottom=node('span','vpn-contact-bottom');bottom.append(node('span','vpn-contact-preview',row.preview||'Chưa có tin nhắn'));if(row.unread)bottom.append(node('span','vpn-unread-dot','Chưa đọc'));
      if(Number(row.conversation_count)>1)identity.append(node('span','vpn-history-count',`${row.conversation_count} hội thoại`));body.append(top,identity,bottom);button.append(body);button.addEventListener('click',()=>choose(row));container.append(button);
    }baseline=true;
    if(!rows.length)container.append(node('p','vpn-list-empty','Chưa có hội thoại trong mục này.'));
  }
  function highlightRows(){
    for(const button of $('vpn-list').querySelectorAll('.vpn-row')){
      const current=button.dataset.id===selected?.public_id||!!selected?.customer_id&&Number(button.dataset.customer)===Number(selected.customer_id);
      button.classList.toggle('selected',current);button.setAttribute('aria-pressed',String(current));
    }
  }
  function appendMessage(message){
    if(seen.has(Number(message.seq)))return;seen.add(Number(message.seq));
    const item=node('div','vpn-msg');item.dataset.sender=message.sender;item.dataset.seq=message.seq;
    const who=node('strong','',message.sender==='note'?'Ghi chú nội bộ':message.sender==='guest'?'Khách':(message.profile?.name||'Sales'));
    const content=node('div','',message.body),time=node('small','',date(message.created_at).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}));time.title=date(message.created_at).toLocaleString();
    item.append(who,content,time);const transcript=$('vpn-transcript');
    const last=transcript.lastElementChild;
    const next=last&&Number(last.dataset.seq)>Number(message.seq)?[...transcript.children].find(el=>Number(el.dataset.seq)>Number(message.seq)):null;
    transcript.insertBefore(item,next||null);
  }
  function detail(c,delta){
    const changed=selected?.public_id!==c.public_id || !selected?.id;selected=c;$('vpn-detail').hidden=false;
    const signature=JSON.stringify([c.public_id,c.name,c.email,c.customer_code,c.verified_email,c.history]);
    if(signature!==customerSignature){customerSignature=signature;const customer=$('vpn-customer');customer.replaceChildren();
    customer.append(node('h2','',c.name?.trim()||c.customer_code||'Khách ẩn danh'));const contact=node('p','vpn-customer-email');contact.classList.toggle('has-email',hasEmail(c));const badge=node('span','vpn-email-badge',c.verified_email?'Email đã xác minh':hasEmail(c)?'Có email liên hệ':'Chưa có email');badge.title=hasEmail(c)?'Email khách tự cung cấp, chưa xác minh.':'Chưa có địa chỉ liên hệ.';contact.append(badge);if(hasEmail(c))contact.append(node('span','vpn-customer-address',c.email));customer.append(contact);
    if(c.history?.length>1){const label=node('label','vpn-history-label','Hội thoại của khách ');const select=node('select','vpn-customer-history');select.setAttribute('aria-label','Lịch sử hội thoại của khách');for(const h of c.history){const o=node('option','',`${date(h.updated_at).toLocaleString()} · ${h.status}${h.unread?' · Chưa đọc':''}`);o.value=h.id;o.selected=h.id===c.public_id;select.append(o);}select.addEventListener('change',()=>choose({id:select.value,name:c.name}));label.append(select);customer.append(label);}
    }
    $('vpn-empty').hidden=true;highlightRows();
    const infoKey=JSON.stringify([c.public_id,c.source_path,c.created_at]);
    if(infoKey!==infoSignature){infoSignature=infoKey;
    const info=$('vpn-customer-info');info.replaceChildren(node('p','',`${c.source_path||'Không có trang nguồn'} · ${date(c.created_at).toLocaleString()}`));
    if(c.source_path){const link=document.createElement('a');link.textContent='Xem trang nguồn';link.href=c.source_path;link.target='_blank';link.rel='noopener';info.append(link);}
    }
    const writable=cfg.manager||Number(c.owner_id)===cfg.user;
    if(cfg.manager)$('vpn-delete').disabled=deleting||sending||!!pending;
    const canReply=(writable||c.status==='unassigned'&&Number(c.owner_id)===0)&&!['closed','spam'].includes(c.status);
    $('vpn-claim').hidden=c.status!=='unassigned'||Number(c.owner_id)!==0;
    for(const id of ['vpn-save','vpn-message','vpn-note','vpn-status','vpn-owner','vpn-label','vpn-follow','vpn-needs','vpn-read'])$(id).disabled=!writable;
    $('vpn-message').disabled=!canReply;$('vpn-note').disabled=!canReply;$('vpn-reply').hidden=!canReply;$('vpn-readonly').hidden=canReply;
    if(changed||!dirty){$('vpn-owner').value=c.owner_id;$('vpn-status').value=c.status;$('vpn-label').value=c.label;$('vpn-needs').value=c.needs;$('vpn-follow').value=c.follow_up_at?c.follow_up_at.replace(' ','T').slice(0,16):'';}
    const transcript=$('vpn-transcript'),follow=changed||transcript.scrollHeight-transcript.scrollTop-transcript.clientHeight<48;
    for(const message of delta.messages){
      if(seen.has(Number(message.seq)))continue;
      if(!changed && message.sender==='guest' && Number(message.seq)>(known.get(c.public_id)||0)){notify({id:c.public_id});known.set(c.public_id,Number(message.seq));}
      appendMessage(message);
    }
    cursor=delta.cursor;$('vpn-more').hidden=!delta.more;
    if(delta.messages.length&&follow)transcript.scrollTop=transcript.scrollHeight;
  }
  async function markRead(){
    const c=selected,t=$('vpn-transcript');if(deleting||readActive||sending||!c?.id||document.hidden||!$('vpn-detail').getClientRects().length||(!cfg.manager&&Number(c.owner_id)!==cfg.user)||Number(c.read_seq)>=cursor||t.scrollHeight-t.scrollTop-t.clientHeight>=48)return;
    readActive=true;const seq=cursor;try{const result=await api('agent/update',{id:c.public_id,version:Number(c.version),read_seq:seq});if(selected?.public_id===c.public_id){selected={...selected,version:Math.max(Number(selected.version),Number(result.version)),read_seq:Math.max(Number(selected.read_seq),Number(result.read_seq))};const row=document.querySelector('.vpn-row.selected');if(!c.history?.some(h=>h.id!==c.public_id&&h.unread)){row?.classList.remove('unread');row?.querySelector('.vpn-unread-dot')?.remove();}}}catch(e){if(e.status===409)refreshRequested=true;}finally{readActive=false;}
  }
  async function sync(foreground=false,detailOnly=false){
    foreground=foreground===true;detailOnly=detailOnly===true&&!!selected?.public_id;
    if(document.hidden||deleting)return;if(active&&!foreground){refreshRequested=true;return;}
    if(foreground){syncEpoch++;controller?.abort();refreshRequested=false;clearTimeout(timer);}
    active=true;controller=new AbortController();const requestedId=selected?.public_id,epoch=syncEpoch;
    try{
      const result=await api('agent/sync',{filter:$('vpn-filter').value,search:$('vpn-search').value,page,presence:$('vpn-presence').value,id:selected?.public_id,cursor,...(detailOnly?{detail_only:true}:{})},controller.signal);
      if(epoch!==syncEpoch)return;
      if(result.list){list(result.list);$('vpn-page').textContent=` ${page} `;$('vpn-prev').disabled=page===1;$('vpn-next').disabled=!result.has_more;}
      if(result.canned&&JSON.stringify(result.canned)!==cannedSignature){cannedSignature=JSON.stringify(result.canned);const canned=$('vpn-canned'),value=canned.value;canned.replaceChildren(new Option('Chọn câu trả lời',''));
      for(const reply of result.canned){const option=new Option(reply.title,reply.id);option.dataset.body=reply.body;canned.append(option);}canned.value=value;}
      if(result.selected&&result.selected.public_id===selected?.public_id){if(Number(result.selected.version)>=Number(selected.version||0)){detail(result.selected,result.delta);void markRead();}else refreshRequested=true;}failures=0;state(deleteNotice||'Đã kết nối');schedule(result.delta?.more?100:undefined);
    }catch(e){
      if(epoch!==syncEpoch)return;
      if(e.name==='AbortError'){schedule(500);return;}
      if(selected?.public_id===requestedId && [403,404].includes(e.status)){selected=null;cursor=0;empty();}
      failures++;state(`Chưa đồng bộ: ${e.message}. Đăng nhập lại nếu phiên WP hết hạn.`);schedule(Math.max(e.retry*1000,Math.min(60000,5000*2**Math.min(4,failures))));
    }finally{if(epoch===syncEpoch){active=false;if(refreshRequested){refreshRequested=false;schedule(0);}}}
  }
  async function action(fn){try{await fn();state('Đã lưu');await sync(true);}catch(e){state(e.status===409?'Hội thoại đã thay đổi hoặc được người khác nhận. Đang cập nhật; kiểm tra lại trước khi lưu.':`Không lưu được: ${e.message}`);await sync(true);}}
  async function update(data){while(readActive)await new Promise(resolve=>setTimeout(resolve,30));const id=selected.public_id;const result=await api('agent/update',{id,version:Number(selected.version),...data});if(selected?.public_id===id)selected=result;return result;}
  function options(id,items){for(const [value,label]of items)$(id).append(new Option(label,value));}
  options('vpn-owner',[[0,'Chưa nhận'],...cfg.agents.map(a=>[a.ID,a.display_name])]);
  options('vpn-status',[['unassigned','Chưa nhận'],['assigned','Đã nhận'],['waiting_customer','Chờ khách'],['follow_up','Cần theo dõi'],['closed','Đã đóng'],['spam','Spam']]);
  options('vpn-label',[['','Chưa phân loại'],['needs_sample','Cần mẫu'],['needs_quote','Cần báo giá'],['qualified','Đủ điều kiện'],['quote_sent','Đã báo giá'],['won','Thành công'],['lost','Không thành công']]);
  for(const id of ['vpn-owner','vpn-status','vpn-label','vpn-needs','vpn-follow'])$(id).addEventListener('input',()=>{dirty=true;});
  $('vpn-owner').addEventListener('change',()=>{$('vpn-status').value=Number($('vpn-owner').value)?'assigned':'unassigned';});
  $('vpn-claim').addEventListener('click',()=>action(()=>update({action:'claim'})));
  $('vpn-save').addEventListener('click',()=>action(async()=>{await update({owner_id:Number($('vpn-owner').value),status:$('vpn-status').value,label:$('vpn-label').value,needs:$('vpn-needs').value,follow_up_at:$('vpn-follow').value?$('vpn-follow').value.replace('T',' ')+':00':null});dirty=false;}));
  $('vpn-read').addEventListener('click',()=>action(()=>update({read_seq:cursor})));
  $('vpn-more').addEventListener('click',()=>sync(true,true));
  $('vpn-canned').addEventListener('change',()=>{$('vpn-message').value=$('vpn-canned').selectedOptions[0]?.dataset.body||'';});
  $('vpn-reply').addEventListener('submit',async event=>{
    event.preventDefault();if(deleting||sending||!selected?.id)return;const button=event.target.querySelector('button');button.disabled=true;sending=true;
    try{
      if(!pending)pending={id:selected.public_id,message:$('vpn-message').value,note:$('vpn-note').checked,client_message_id:crypto.randomUUID().replaceAll('-',''),claim_if_unassigned:selected.status==='unassigned'&&Number(selected.owner_id)===0};
      $('vpn-send-status').textContent='Đang gửi…';const result=await api('agent/send',pending);
      if(result.message&&result.conversation)detail({...selected,...result.conversation},{messages:[result.message],cursor,more:!$('vpn-more').hidden});
      if($('vpn-message').value===pending.message)$('vpn-message').value='';drafts.delete(pending.id);pending=null;$('vpn-send-status').textContent='Đã gửi';void sync(true,true);$('vpn-transcript').scrollTop=$('vpn-transcript').scrollHeight;
    }catch(e){if(e.message==='invalid_text')pending=null;$('vpn-send-status').textContent=`Chưa xác nhận đã lưu: ${e.message}. Bấm gửi để thử lại cùng tin.`;if(e.status===409)await sync();}
    finally{sending=false;button.disabled=false;if(cfg.manager)$('vpn-delete').disabled=!!pending;markRead();}
  });
  for(const id of ['vpn-filter','vpn-presence'])$(id).addEventListener('change',()=>{page=1;sync(true);});
  $('vpn-open-next').addEventListener('click',()=>{const row=document.querySelector('#vpn-list .vpn-row.unread')||document.querySelector('#vpn-list .vpn-row');if(row)row.click();else{$('vpn-search').focus();state('Chưa có hội thoại trong mục này.');}});
  $('vpn-search-button').addEventListener('click',()=>{page=1;sync(true);});
  $('vpn-search').addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>{page=1;sync(true);},250);});
  $('vpn-search').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();clearTimeout(searchTimer);page=1;sync(true);}});
  $('vpn-info-toggle').addEventListener('click',()=>{const info=$('vpn-conversation-info');info.open=!info.open;$('vpn-info-toggle').setAttribute('aria-expanded',String(info.open));});
  $('vpn-conversation-info').addEventListener('toggle',()=>{$('vpn-info-toggle').setAttribute('aria-expanded',String($('vpn-conversation-info').open));});
  $('vpn-back').addEventListener('click',()=>{$('vpn-chat-admin').classList.remove('vpn-chat-selected');});
  $('vpn-transcript').addEventListener('scroll',()=>{if(!active)markRead();},{passive:true});
  let composing=false;const message=$('vpn-message');message.addEventListener('compositionstart',()=>{composing=true;});message.addEventListener('compositionend',()=>setTimeout(()=>{composing=false;},0));
  message.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey&&!e.isComposing&&e.keyCode!==229&&!composing){e.preventDefault();message.form.requestSubmit();}});
  message.addEventListener('input',()=>{message.style.height='auto';message.style.height=`${Math.min(120,Math.max(44,message.scrollHeight))}px`;});
  function foldNotices(){
    const notices=[...document.querySelectorAll('#wpbody-content .notice,#wpbody-content .update-nag')].filter(n=>!n.closest('.vpn-system-notices'));
    if(!notices.length)return;let fold=document.querySelector('.vpn-system-notices');if(!fold){fold=node('details','vpn-system-notices');fold.append(node('summary','','Thông báo hệ thống'));$('vpn-chat-admin').after(fold);}notices.forEach(n=>fold.append(n));
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',()=>setTimeout(foldNotices,300),{once:true});else setTimeout(foldNotices,300);
  window.addEventListener('load',()=>setTimeout(foldNotices,300),{once:true});
  $('vpn-next').addEventListener('click',()=>{page++;sync(true);});$('vpn-prev').addEventListener('click',()=>{page=Math.max(1,page-1);sync(true);});
  $('vpn-notify').addEventListener('click',async()=>{notifications=true;if(window.Notification && Notification.permission==='default')await Notification.requestPermission();const Audio=window.AudioContext||window.webkitAudioContext;if(Audio){audio??=new Audio();await audio.resume();}state('Đã cho phép thông báo khi inbox đang mở.');});
  if(cfg.manager){
    $('vpn-canned-form').addEventListener('submit',e=>{e.preventDefault();action(()=>api('agent/canned',{id:Number($('vpn-canned-id').value)||undefined,title:$('vpn-canned-title').value,body:$('vpn-canned-body').value}));});
    $('vpn-canned-delete').addEventListener('click',()=>action(()=>api('agent/canned',{action:'delete',id:Number($('vpn-canned-id').value)})));

    $('vpn-block').addEventListener('click',()=>action(()=>api('manager/block',{id:selected.public_id,reason:$('vpn-block-reason').value,hours:24})));
    $('vpn-export').addEventListener('click',()=>action(async()=>{
      let after=0,all=[],c,more;
      do{const result=await api('manager/privacy',{id:selected.public_id,action:'export',cursor:after});c=result.conversation;all.push(...result.messages);after=result.cursor;more=result.more;}while(more);
      const url=URL.createObjectURL(new Blob([JSON.stringify({conversation:c,messages:all},null,2)],{type:'application/json'}));const link=document.createElement('a');link.href=url;link.download='vpn-chat-private-export.json';link.click();setTimeout(()=>URL.revokeObjectURL(url),1000);
    }));
    $('vpn-delete').addEventListener('click',async()=>{
      const c=selected;if(deleting||!c?.id)return;
      if(sending||pending){state('Tin hiện tại chưa được xác nhận. Gửi lại trước khi xóa hội thoại.');return;}
      if(!confirm(`Xóa vĩnh viễn hội thoại của ${c.name?.trim()||c.customer_code||'khách này'}? Toàn bộ tin nhắn và ghi chú trong hội thoại sẽ bị xóa. Không thể hoàn tác.`))return;
      deleting=true;deleteNotice='';$('vpn-delete').disabled=true;clearTimeout(timer);syncEpoch++;controller?.abort();active=false;refreshRequested=false;
      try{
        await api('manager/privacy',{id:c.public_id,action:'delete',confirmed_delete:true});
        drafts.delete(c.public_id);known.delete(c.public_id);selected=null;cursor=0;seen.clear();dirty=false;
        $('vpn-transcript').replaceChildren();$('vpn-message').value='';$('vpn-note').checked=false;$('vpn-send-status').textContent='';
        $('vpn-conversation-info').open=false;empty();page=1;$('vpn-search').focus();deleteNotice='Đã xóa hội thoại.';state(deleteNotice);
      }catch(e){deleteNotice=`Không xóa được: ${e.message}. Có thể thử lại.`;state(deleteNotice);}
      finally{deleting=false;$('vpn-delete').disabled=false;await sync();}
    });
    $('vpn-health-refresh').addEventListener('click',()=>action(async()=>{
      $('vpn-health').textContent=JSON.stringify(await api('manager/health'),null,2);const result=await api('manager/block',{action:'list'});$('vpn-blocks').replaceChildren();
      for(const block of result.blocks){const div=document.createElement('div');div.textContent=`#${block.id} Phiên #${block.session_id}: ${block.reason} · hết hạn ${block.expires_at} UTC · ${block.revoked==='1'?'Đã bỏ chặn':'Đang chặn'}`;const button=document.createElement('button');button.textContent='Bỏ chặn';button.type='button';button.addEventListener('click',()=>action(()=>api('manager/block',{action:'unban',block_id:Number(block.id)})));div.append(button);$('vpn-blocks').append(div);}
    }));
  }
  document.addEventListener('visibilitychange',()=>{if(document.hidden){clearTimeout(timer);controller?.abort();}else sync();});
  window.addEventListener('focus',sync);window.addEventListener('pagehide',()=>{clearTimeout(timer);controller?.abort();});
  sync();
})();
