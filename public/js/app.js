/* App JS - Constrói Spa & Pool
   Use este ficheiro para scripts do site. */
(function(){
  // Pequeno no-op para validar que o ficheiro carrega
  if (location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
    console.log('Constrói Spa & Pool: app.js carregado');
  }

  // Sombra suave no header ao fazer scroll com debounce
  var scrollTimer;
  window.addEventListener('scroll', function(){
    clearTimeout(scrollTimer);
    scrollTimer = setTimeout(function(){
      document.body.classList.toggle('scrolled', window.scrollY > 8);
    }, 80);
  });

  // Ajuste dinâmico da compensação do body conforme a altura real do header
  function ajustarPaddingTopo(){
    var header = document.querySelector('.site-header');
    if(!header) return;
    var h = header.getBoundingClientRect().height;
    document.body.style.paddingTop = h + 'px';
  }
  window.addEventListener('load', ajustarPaddingTopo);
  window.addEventListener('resize', function(){
    // Debounce simples
    clearTimeout(window.__hdrRsz);
    window.__hdrRsz = setTimeout(ajustarPaddingTopo, 100);
  });
  // Também ao trocar o estado do menu (mobile), pois a altura do header pode mudar
  document.addEventListener('shown.bs.collapse', ajustarPaddingTopo);
  document.addEventListener('hidden.bs.collapse', ajustarPaddingTopo);

  // UX: Dropdown "Serviços" — desktop clica e navega; mobile 1º toque abre, 2º toque navega
  document.addEventListener('DOMContentLoaded', function(){
    // Ensure data-bs-toggle="dropdown" only on desktop (>=992px)
    function syncDropdownToggleAttr(){
      var isDesktop = window.innerWidth >= 992;
      document.querySelectorAll('.nav-item.dropdown > a.dropdown-toggle').forEach(function(a){
        if (isDesktop) {
          a.setAttribute('data-bs-toggle','dropdown');
        } else {
          a.removeAttribute('data-bs-toggle');
        }
      });
    }
    syncDropdownToggleAttr();
    window.addEventListener('resize', function(){
      clearTimeout(window.__ddAttrRsz);
      window.__ddAttrRsz = setTimeout(syncDropdownToggleAttr, 150);
    });
    var dropdownLinks = document.querySelectorAll('.nav-item.dropdown > a.dropdown-toggle');
    dropdownLinks.forEach(function(link){
      link.addEventListener('click', function(e){
        var isMobile = window.innerWidth < 992; // breakpoint Bootstrap lg
        var href = link.getAttribute('href');
        if (isMobile) {
          // Mobile: primeiro toque abre, segundo navega
          if (link.classList.contains('clicked-once')) {
            link.classList.remove('clicked-once');
            if (href && href !== '#') window.location.href = href;
          } else {
            e.preventDefault();
            link.classList.add('clicked-once');
            setTimeout(function(){ link.classList.remove('clicked-once'); }, 1200);
          }
        } else {
          // Desktop: clicar navega imediatamente
          if (href && href !== '#') {
            // Não impedir o default; força navegação direta
            window.location.href = href;
          }
        }
      });
    });

    // Hover suave (desktop): abre/fecha com ligeiro delay para evitar flicker
    var mqDesktop = window.matchMedia('(min-width: 992px)');
    var items = document.querySelectorAll('.nav-item.dropdown.hover-dropdown');
  items.forEach(function(item){
      var toggle = item.querySelector('a.dropdown-toggle');
      if (!toggle) return;
      var dd = null;
      var openTimer = null, closeTimer = null;
      var ensureDropdown = function(){
        if (dd) return dd;
        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
          dd = bootstrap.Dropdown.getOrCreateInstance(toggle, { autoClose: true });
        }
        return dd;
      };
      item.addEventListener('mouseenter', function(){
        if (!mqDesktop.matches) return; // só em desktop
        clearTimeout(closeTimer);
        openTimer = setTimeout(function(){ var inst = ensureDropdown(); inst && inst.show(); }, 220);
      });
      item.addEventListener('mouseleave', function(){
        if (!mqDesktop.matches) return;
        clearTimeout(openTimer);
        closeTimer = setTimeout(function(){ var inst = ensureDropdown(); inst && inst.hide(); }, 250);
      });
    });

    // Mobile: fechar dropdown e o menu colapsado ao selecionar um item
    document.querySelectorAll('.navbar .dropdown-menu .dropdown-item').forEach(function(it){
      it.addEventListener('click', function(){
        // Fecha o dropdown atual
        var parentDropdown = it.closest('.dropdown');
        if (parentDropdown) {
          var toggle = parentDropdown.querySelector('a.dropdown-toggle');
          if (toggle && typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
            var inst = bootstrap.Dropdown.getOrCreateInstance(toggle);
            inst && inst.hide();
          }
        }
        // Fecha o menu colapsado se estiver aberto (modo mobile)
        var collapse = document.getElementById('menuPrincipal');
        if (collapse && collapse.classList.contains('show') && typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
          bootstrap.Collapse.getOrCreateInstance(collapse).hide();
        }
      });
    });
  });

  // Fechar o menu ao clicar fora (mobile)
  document.addEventListener('click', function (e) {
    var menu = document.getElementById('menuPrincipal');
    var toggle = document.querySelector('.navbar-toggler');
    if (!menu || !toggle) return;
    if (!menu.contains(e.target) && !toggle.contains(e.target)) {
      if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
        var inst = bootstrap.Collapse.getOrCreateInstance(menu, {toggle:false});
        if (menu.classList.contains('show')) inst.hide();
      }
    }
  });
})();

/* Tema: toggle unificado vive no footer (footer.php) e usa a chave csp_theme. */
