import {scrollTopFor} from './scroll-target.js';
export function initNavigation(getScroll, isReduced) {
  const header = document.querySelector('.site-header');
  const hero = document.querySelector('.hero');
  const dialog = document.querySelector('#site-menu');
  const open = document.querySelector('.menu-toggle');
  const close = document.querySelector('.menu-close');
  if (hero && header) {
    const observer = new IntersectionObserver(entries => {
      header.classList.toggle('is-solid', !entries[0].isIntersecting);
    }, {rootMargin:'-80px 0px 0px 0px',threshold:0});
    observer.observe(hero);
  }
  const closeMenu = () => {
    dialog.close();
  };
  open?.addEventListener('click', () => {
    dialog.showModal();
    open.setAttribute('aria-expanded','true');
    document.body.classList.add('menu-open');
    getScroll()?.stop();
    close.focus();
  });
  close?.addEventListener('click', closeMenu);
  dialog?.addEventListener('close', () => {
    open.setAttribute('aria-expanded','false');
    document.body.classList.remove('menu-open');
    getScroll()?.start();
    open.focus();
  });
  // Native dialog handles focus confinement and Escape, including screen reader navigation.
  document.addEventListener('click', event => {
    const details = event.target.closest('.language-switcher');
    document.querySelectorAll('.language-switcher[open]').forEach(item => {
      if (item !== details) item.removeAttribute('open');
    });
    const anchor = event.target.closest('a[href^="#"]');
    if (!anchor) return;
    const target = document.getElementById(anchor.hash.slice(1));
    if (!target) return;
    event.preventDefault();
    if (dialog?.open) closeMenu();
    const smooth = getScroll();
    const pinTop=scrollTopFor(target);
    const focusTarget=()=>{target.setAttribute('tabindex','-1');target.focus({preventScroll:true});};
    if (smooth) smooth.scrollTo(pinTop??target, {offset:pinTop===null?-80:0,duration:isReduced()?0:1,onComplete:focusTarget});
    else if(pinTop!==null) window.scrollTo({top:pinTop,behavior:isReduced()?'instant':'smooth'});
    else target.scrollIntoView({behavior:isReduced()?'instant':'smooth',block:'start'});
    history.pushState(null,'',anchor.hash);
    if(!smooth)focusTarget();
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') document.querySelectorAll('.language-switcher[open]').forEach(item=>item.removeAttribute('open'));
  });
  document.querySelectorAll('.news-filters button').forEach(button => {
    button.addEventListener('click', () => {
      const filter = button.dataset.filter;
      document.querySelectorAll('.news-filters button').forEach(item => {
        const active = item === button;
        item.classList.toggle('is-active',active);
        item.setAttribute('aria-pressed',String(active));
      });
      let count = 0;
      document.querySelectorAll('.news-item').forEach(item => {
        const visible = filter === 'all' || item.dataset.category === filter;
        item.hidden = !visible;
        if (visible) count++;
      });
      const empty = document.querySelector('.news-empty');
      if (empty) empty.hidden = count > 0;
      window.ScrollTrigger?.refresh();
    });
  });
  document.querySelector('.form-errors')?.focus();
  const form = document.querySelector('.contact-form');
  form?.addEventListener('submit', () => {
    if (!form.checkValidity()) return;
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    button.querySelector('span').textContent = button.dataset.sending;
  });
  window.addEventListener('pageshow', () => {
    const button = form?.querySelector('button[type="submit"]');
    if (button && button.dataset.ready === 'true') {
      button.disabled = false;
      button.querySelector('span').textContent = button.dataset.original;
    }
  });
}
