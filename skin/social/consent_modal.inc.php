<!-- HTML -->
<?php if (!defined('_GNUBOARD_')) exit; ?>
<dialog id="consentDialog" aria-labelledby="consentDialogTitle" aria-describedby="consentDialogBody">
  <form method="dialog" class="cd-card">
    <header class="cd-head">
      <h3 id="consentDialogTitle" class="cd-title">안내</h3>
    </header>
    <div id="consentDialogBody" class="cd-body"></div>
    <footer class="cd-actions">
      <button type="button" class="cd-agree">동의합니다</button>
      <button value="close" class="cd-close">닫기</button>
    </footer>
  </form>
</dialog>

<!-- 스타일 -->
<style>
#consentDialog { padding:0; border:none; border-radius:12px; }
#consentDialog::backdrop { background: rgba(0,0,0,.45); backdrop-filter: blur(5px);}
.cd-card { min-width: 320px; max-width: 560px; background:#fff; border-radius:12px; }
.cd-head { display:flex; align-items:center; justify-content:space-between; padding:16px; }
.cd-title { margin:0; font-size:18px; font-weight:bold; }
.cd-body { max-height:500px; overflow-y:auto; padding:16px; border-top:1px solid #e6e6e9; border-bottom:1px solid #e6e6e9; line-height:1.6; font-size:14px; color:#222; }
.cd-actions { display:flex; gap:8px; justify-content:flex-end; padding:12px 16px 16px; }
.cd-actions .cd-agree { padding:8px 14px; border:1px solid #3a8afd; background:#3a8afd; color:#fff; border-radius:8px; }
.cd-actions .cd-close { padding:8px 14px; border:1px solid #ccc; background:#fff; color:#111; border-radius:8px; }
</style>

<!-- JS -->
<script>
(function(){
  const dlg = document.getElementById('consentDialog');
  if (!dlg) return;

  const body   = document.getElementById('consentDialogBody');
  const titleE = document.getElementById('consentDialogTitle');
  let opener   = null;

  const openFrom = (btn) => {
    opener = btn;
    const tplSel = btn.getAttribute('data-template');
    const title  = btn.getAttribute('data-title') || '안내';
    const tpl    = tplSel ? document.querySelector(tplSel) : null;

    titleE.textContent = title;
    body.innerHTML     = tpl ? tpl.innerHTML : '';

    dlg.dataset.check      = btn.getAttribute('data-check') || '';
    dlg.dataset.checkGroup = btn.getAttribute('data-check-group') || '';

    if (dlg.showModal) dlg.showModal(); else dlg.setAttribute('open','');
  };

  const closeDialog = () => {
    if (dlg.close) dlg.close(); else dlg.removeAttribute('open');
    if (opener) opener.focus();
  };

  document.addEventListener('click', (e)=>{
    const trigger = e.target.closest('.js-open-consent');
    if (trigger) { openFrom(trigger); return; }

    if (e.target.classList.contains('cd-agree')) {
      const sel      = dlg.dataset.check;
      const groupSel = dlg.dataset.checkGroup;

      if (groupSel) {
        document.querySelectorAll(groupSel).forEach(cb => {
          cb.checked = true;
          cb.dispatchEvent(new Event('change', {bubbles:true}));
        });
      }
      if (sel) {
        const cb = document.querySelector(sel);
        if (cb) { cb.checked = true; cb.dispatchEvent(new Event('change', {bubbles:true})); }
      }
      closeDialog();
      e.preventDefault();
      return;
    }
  });

  dlg.addEventListener('cancel', (e)=>{ e.preventDefault(); closeDialog(); });
})();
</script>