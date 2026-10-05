(() => {
  'use strict';
  document.querySelectorAll('.vpn-chat-avatar-picker').forEach(button=>button.addEventListener('click',()=>{
    const picker=wp.media({title:'Avatar nhân viên chat',library:{type:'image'},multiple:false});
    picker.on('select',()=>{document.getElementById(button.dataset.field).value=picker.state().get('selection').first().id;});
    picker.open();
  }));
})();
