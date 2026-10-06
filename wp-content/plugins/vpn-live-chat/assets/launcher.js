/* Only an already-started chat resumes in the background; a passive greeting opens no session. */
(() => {
  'use strict';
  const cfg=window.VPNChatLauncher;
  if(!cfg || document.getElementById('vpn-chat-launch'))return;
  const svg=(path,kind)=>`<svg class="vpn-chat-icon-${kind}" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">${path}</svg>`;
  const button=document.createElement('button');button.id='vpn-chat-launch';button.className='vpn-chat-launch';button.type='button';
  button.innerHTML=svg('<path d="M21 11.5a8.5 8.5 0 0 1-12.3 7.6L3 21l1.9-5.7A8.5 8.5 0 1 1 21 11.5Z"/><path d="M8 11h8M8 7.5h5"/>','open')+svg('<path d="m6 6 12 12M18 6 6 18"/>','close')+'<span class="vpn-chat-launch-label">Chat with Sales</span><span id="vpn-chat-unread" class="vpn-chat-unread" hidden></span>';
  button.setAttribute('aria-label','Open packaging chat');button.setAttribute('aria-haspopup','dialog');button.setAttribute('aria-expanded','false');button.setAttribute('aria-controls','vpn-chat-panel');
  const greeting=document.createElement('aside');greeting.id='vpn-chat-greeting';greeting.className='vpn-chat-greeting';greeting.setAttribute('aria-label','Chat with our Sale Manager');
  const identity=document.createElement('div');identity.className='vpn-chat-invite-identity';
  const avatar=document.createElement('span');avatar.className='vpn-chat-invite-avatar';avatar.textContent=cfg.support?.initials||'TN';
  if(cfg.support?.avatar){const img=document.createElement('img');img.src=cfg.support.avatar;img.alt='';img.width=44;img.height=44;img.decoding='async';img.addEventListener('error',()=>img.remove(),{once:true});avatar.append(img);}
  const profile=document.createElement('span');const name=document.createElement('strong');name.textContent=cfg.support?.name||'Tho Nguyen';const role=document.createElement('span');role.textContent='Sale Manager · VPN Packaging';profile.append(name,role);identity.append(avatar,profile);
  const eyebrow=document.createElement('span');eyebrow.className='vpn-chat-invite-eyebrow';eyebrow.textContent='LET’S TALK PACKAGING';
  const text=document.createElement('p');text.textContent='Need a quote or help choosing packaging?';
  const help=document.createElement('span');help.className='vpn-chat-invite-help';help.textContent='Ask our Sale Manager directly. No sign-up needed.';
  const action=document.createElement('span');action.className='vpn-chat-invite-action';action.textContent=`Chat with ${cfg.support?.name||'Tho Nguyen'} \u2192`;
  const open=document.createElement('button');open.id='vpn-chat-greeting-open';open.className='vpn-chat-greeting-open';open.type='button';open.append(eyebrow,identity,text,help,action);
  const dismiss=document.createElement('button');dismiss.type='button';dismiss.className='vpn-chat-greeting-dismiss';dismiss.textContent='×';dismiss.setAttribute('aria-label','Dismiss welcome message');greeting.append(open,dismiss);
  let dismissed=false,inviteTimer,attentionTimer;
  try{dismissed=sessionStorage.getItem('vpn-chat-invite-dismissed-v160')==='1';}catch{}
  greeting.hidden=true;
  function revealInvitation(){
    if(!cfg.greeting_enabled||dismissed||document.hidden||button.dataset.open==='true')return;
    greeting.hidden=false;greeting.classList.add('vpn-chat-invite-visible');button.classList.add('vpn-chat-attention');
    clearTimeout(attentionTimer);attentionTimer=setTimeout(()=>button.classList.remove('vpn-chat-attention'),9500);
  }
  function queueInvitation(){clearTimeout(inviteTimer);if(!document.hidden&&!dismissed&&cfg.greeting_enabled)inviteTimer=setTimeout(revealInvitation,2500);}
  function dismissGreeting(){dismissed=true;clearTimeout(inviteTimer);clearTimeout(attentionTimer);button.classList.remove('vpn-chat-attention');greeting.hidden=true;try{sessionStorage.setItem('vpn-chat-invite-dismissed-v160','1');}catch{}}
  document.addEventListener('visibilitychange',()=>{if(document.hidden)clearTimeout(inviteTimer);else if(greeting.hidden)queueInvitation();});
  window.addEventListener('pagehide',()=>{clearTimeout(inviteTimer);clearTimeout(attentionTimer);});
  dismiss.addEventListener('click',()=>{dismissGreeting();button.focus();});open.addEventListener('click',()=>button.click());
  const obstacleSelector='.mobile-conversion-bar,.cky-consent-container,.cmplz-cookiebanner,#cookie-law-info-bar,#cookie-notice,[data-cookie-banner],.floating-whatsapp,.whatsapp-float,.contact-floating';
  cfg.layout=()=>{
    const vp=window.visualViewport,width=vp?.width??document.documentElement.clientWidth,height=vp?.height??innerHeight;
    const left=vp?.offsetLeft??0,top=vp?.offsetTop??0;
    const insetBottom=Math.max(0,innerHeight-height-top),edge=Math.max(0,innerWidth-width-left);
    let bottom=Number(cfg.bottom)||100;
    document.querySelectorAll(obstacleSelector).forEach(el=>{
      const style=getComputedStyle(el),r=el.getBoundingClientRect();
      if(!['fixed','sticky'].includes(style.position)||style.visibility==='hidden'||style.display==='none'||!r.height||r.top<top+height*.35)return;
      if(r.right>left+width-90 && r.left<left+width && r.top<top+height && r.bottom>top+height-bottom-58)bottom=Math.max(bottom,top+height-r.top+12);
    });
    return {width,height,edge,insetBottom,bottom:Math.min(bottom,Math.max(16,height-80)),keyboard:insetBottom>120,margin:width<=600?12:20};
  };
  let previous='';const observed=new WeakSet();let frame;
  function align(){
    const l=cfg.layout();button.style.right=greeting.style.right=`${l.edge+l.margin}px`;
    button.style.bottom=`calc(${l.insetBottom+l.bottom}px + env(safe-area-inset-bottom))`;
    greeting.style.bottom=`calc(${l.insetBottom+l.bottom+72}px + env(safe-area-inset-bottom))`;
    greeting.classList.toggle('vpn-chat-greeting-suppressed',l.height-l.bottom<190);
    document.querySelectorAll(obstacleSelector).forEach(el=>{if(!observed.has(el)){observed.add(el);observer.observe(el,{attributes:true,attributeFilter:['class','style','hidden']});}});
    const key=JSON.stringify(l);if(key!==previous){previous=key;document.dispatchEvent(new CustomEvent('vpn-chat-layout'));}
  }
  function scheduleAlign(){cancelAnimationFrame(frame);frame=requestAnimationFrame(align);}
  const observer=new MutationObserver(scheduleAlign);observer.observe(document.body,{childList:true,attributes:true,attributeFilter:['class']});
  window.addEventListener('resize',scheduleAlign);window.visualViewport?.addEventListener('resize',scheduleAlign);window.visualViewport?.addEventListener('scroll',scheduleAlign);
  let loading;
  function load(){
    if(!loading)loading=Promise.all([
      new Promise((resolve,reject)=>{const link=document.createElement('link');link.rel='stylesheet';link.href=cfg.css;link.onload=resolve;link.onerror=()=>{link.remove();reject(Error('style'));};document.head.append(link);}),
      new Promise((resolve,reject)=>{const script=document.createElement('script');script.src=cfg.bundle;script.onload=resolve;script.onerror=()=>{script.remove();reject(Error('script'));};document.head.append(script);})
    ]).catch(e=>{loading=null;throw e;});
    return loading;
  }
  button.addEventListener('click',async()=>{
    dismissGreeting();button.disabled=true;
    try{await load();await window.VPNChatOpen(cfg,button);}catch{button.setAttribute('aria-label','Chat could not load. Click to retry.');}finally{button.disabled=false;}
  });
  document.body.append(greeting,button);align();queueInvitation();
  try{if(sessionStorage.getItem('vpn-chat-active')==='1')load().then(()=>window.VPNChatResume(cfg,button)).catch(()=>{});}catch{}
})();
