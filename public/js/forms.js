/* Forms behaviors: toggle password and on-hover hints */
(function(w){
  w.Constroi = w.Constroi || {};

  function togglePass(id, btn){
    var i = document.getElementById(id);
    if(!i) return;
    var is = i.getAttribute('type') === 'password';
    i.setAttribute('type', is ? 'text' : 'password');
    if(btn) btn.textContent = is ? 'Ocultar' : 'Mostrar';
  }
  w.Constroi.togglePass = togglePass;

  function getAnchor(el){
    // if inside input-group, anchor the hint after the group; else after the input
    var grp = el.closest ? el.closest('.input-group') : null;
    return grp || el;
  }

  // Required fields (auth pages only): show "Obrigatório" under field on hover when empty
  document.addEventListener('DOMContentLoaded', function(){
    if (!document.body.classList.contains('auth-page')) return;
    var reqInputs = document.querySelectorAll('.auth-page form [required].required-hover');

    function getReqHint(anchor){
      var next = anchor.nextElementSibling;
      if (!next || !next.classList || !next.classList.contains('req-hint')) {
        var hint = document.createElement('div');
        hint.className = 'req-hint text-danger small';
        hint.textContent = 'Obrigatório';
        hint.style.display = 'none';
        anchor.parentNode.insertBefore(hint, anchor.nextSibling);
        return hint;
      }
      return next;
    }

    reqInputs.forEach(function(inp){
      var anchor = getAnchor(inp);
      var hint = null;
      inp.addEventListener('mouseenter', function(){
        if (inp.value) return;
        if (!hint) hint = getReqHint(anchor);
        hint.style.display = 'block';
      });
      inp.addEventListener('mouseleave', function(){ if (hint) hint.style.display = 'none'; });
      inp.addEventListener('input', function(){ if (inp.value && hint) hint.style.display = 'none'; });
      inp.addEventListener('focus', function(){ if (!inp.value && hint) hint.style.display = 'block'; });
      inp.addEventListener('blur', function(){ if (hint) hint.style.display = 'none'; });
    });
  });

  // Parameter hints (auth pages only): show rules (pattern/min/max/minlength)
  document.addEventListener('DOMContentLoaded', function(){
  if (!document.body.classList.contains('auth-page')) return;
  var optionalInputs = document.querySelectorAll('.auth-page form .form-control');

    function buildParamText(inp){
      var parts = [];
      var name = (inp.getAttribute('name') || '').toLowerCase();
      // For these fields, always show a simple 'Opcional'
      if (name === 'localidade' || name === 'andar' || name === 'numero' || name === 'porta') {
        return 'Opcional';
      }
      var title = inp.getAttribute('title');
      // We no longer use placeholder as hint content; prefer a clean 'Opcional'
      var type = (inp.getAttribute('type') || '').toLowerCase();
      var pattern = inp.getAttribute('pattern');
      var min = inp.getAttribute('min');
      var max = inp.getAttribute('max');
      var minlength = inp.getAttribute('minlength');
      var maxlength = inp.getAttribute('maxlength');

      if (title) parts.push(title);
      if (type === 'number') {
        if (min) parts.push('Mín: ' + min);
        if (max) parts.push('Máx: ' + max);
      }
      if (minlength && !title) {
        parts.push('Comprimento mínimo: ' + minlength);
      }
      if (pattern) {
        parts.push(title ? '' : ('Formato: ' + pattern));
      }
      if (!parts.length) {
        if (inp.hasAttribute('required')) {
          // For required inputs, don't show 'Opcional' fallback
          return '';
        }
        parts.push('Opcional');
      }
      return parts.filter(Boolean).join(' · ');
    }

    function getParamHint(anchor){
      var next = anchor.nextElementSibling;
      if (!next || !next.classList || !next.classList.contains('param-hint')) {
        var hint = document.createElement('div');
        hint.className = 'param-hint text-muted small';
        hint.style.display = 'none';
        anchor.parentNode.insertBefore(hint, anchor.nextSibling);
        return hint;
      }
      return next;
    }

    optionalInputs.forEach(function(inp){
      // allow explicit opt-out via attribute or class
      var name = (inp.getAttribute('name') || '').toLowerCase();
  if (inp.classList.contains('no-hint') || inp.getAttribute('data-hint') === 'off') return;
  // Avoid duplicate info: if it's a password with pw-live, let the dedicated helper handle hints
  if (inp.type === 'password' && inp.classList.contains('pw-live')) return;
      var anchor = getAnchor(inp);
      var hint = null;
      var text = buildParamText(inp);

      inp.addEventListener('mouseenter', function(){
        if (!hint) hint = getParamHint(anchor);
        var t = text || '';
        if (t) {
          hint.textContent = t;
          hint.style.display = 'block';
        }
      });
      inp.addEventListener('mouseleave', function(){ if (hint) hint.style.display = 'none'; });
      inp.addEventListener('focus', function(){ if (hint && (text || '').length) hint.style.display = 'block'; });
      inp.addEventListener('blur', function(){ if (hint) hint.style.display = 'none'; });
      inp.addEventListener('input', function(){ if (hint && document.activeElement === inp && (text || '').length) hint.style.display = 'block'; });
    });
  });

  // Password strength meter + live criteria (Fraca/Média/Forte) — register page only
  document.addEventListener('DOMContentLoaded', function(){
    if (!document.body.classList.contains('register-page')) return;
    var pwInputs = document.querySelectorAll('.auth-page input[type="password"].pw-live');

    function computePw(pw){
      if (pw.length < 6) {
        return {len:false, upper:false, digit:false, symbol:false, score:0, pct:10, label:'Fraca', cls:'is-weak'};
      }
      var len = pw.length >= 8;
      var upper = /[A-Z]/.test(pw);
      var digit = /\d/.test(pw);
      var symbol = /[@$!%*?&\-_^#()+=]/.test(pw);
      var score = 0;
      if (len) score++;
      if (upper) score++;
      if (digit) score++;
      if (symbol) score++;
      if (pw.length >= 12) score++; // bónus por comprimento
      var pct = Math.min(100, Math.round((score/5)*100));
      var label = 'Fraca';
      var cls = 'is-weak';
      if (score >= 3 && score <= 4) { label = 'Média'; cls = 'is-medium'; }
      if (score >= 5) { label = 'Forte'; cls = 'is-strong'; }
      return {len:len, upper:upper, digit:digit, symbol:symbol, score:score, pct:pct, label:label, cls:cls};
    }

    function buildPwHelper(anchor){
      var next = anchor.nextElementSibling;
      if (next && next.classList && next.classList.contains('pw-helper')) return next;
      var wrap = document.createElement('div');
      wrap.className = 'pw-helper';
      wrap.style.display = 'none';
      wrap.innerHTML = ''+
        '<div class="d-flex align-items-center justify-content-between gap-2">'+
          '<div class="pw-meter"><div class="pw-meter-fill"></div></div>'+
          '<div class="pw-meter-label small text-muted">Fraca</div>'+
        '</div>'+
        '<div class="pw-criteria mt-1">'+
          '<span class="crit crit-len">• Mín. 8</span>'+
          '<span class="crit crit-upper">• 1 maiúscula</span>'+
          '<span class="crit crit-digit">• 1 número</span>'+
          '<span class="crit crit-symbol">• 1 símbolo</span>'+
        '</div>';
      anchor.parentNode.insertBefore(wrap, anchor.nextSibling);
      return wrap;
    }

    function updateHelper(helper, state){
      var fill = helper.querySelector('.pw-meter-fill');
      var label = helper.querySelector('.pw-meter-label');
      if (fill){
        fill.style.width = state.pct + '%';
        fill.classList.remove('is-weak','is-medium','is-strong');
        fill.classList.add(state.cls);
      }
      if (label){ label.textContent = state.label; }

      function mark(sel, ok, base){
        var el = helper.querySelector(sel);
        if (!el) return;
        el.classList.toggle('ok', !!ok);
        el.textContent = (ok ? '✓ ' : '• ') + base;
      }
      mark('.crit-len', state.len, 'Mín. 8');
      mark('.crit-upper', state.upper, '1 maiúscula');
      mark('.crit-digit', state.digit, '1 número');
      mark('.crit-symbol', state.symbol, '1 símbolo');
    }

    pwInputs.forEach(function(inp){
      var anchor = getAnchor(inp);
      var helper = null;
      function ensure(){ if (!helper) helper = buildPwHelper(anchor); }
      function show(){ ensure(); helper.style.display = 'block'; }
      function hide(){ if (helper) helper.style.display = 'none'; }
      function refresh(){ ensure(); var st = computePw(inp.value || ''); updateHelper(helper, st); }

      inp.addEventListener('focus', function(){ show(); refresh(); });
      inp.addEventListener('input', function(){ show(); refresh(); });
      inp.addEventListener('blur', function(){ if (!(inp.value||'').length) hide(); });
      // also show on mouseenter for discoverability
      inp.addEventListener('mouseenter', function(){ show(); refresh(); });
      inp.addEventListener('mouseleave', function(){ if (document.activeElement !== inp && !(inp.value||'').length) hide(); });
    });
  });

})(window);
