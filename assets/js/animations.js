export function initAnimations(reduced) {
  if (reduced || !window.gsap || !window.ScrollTrigger) return () => {};
  const {gsap,ScrollTrigger}=window;
  const mediaRadius=getComputedStyle(document.documentElement).getPropertyValue('--radius-xl').trim();
  const lateralOffset=parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--space-12'));
  const context=gsap.context(()=>{
    // Level 1 entrance uses inner text; scroll choreography owns outer wrappers.
    if(document.querySelector('.hero')){
      gsap.timeline({defaults:{ease:'power3.out'}})
        .from('.hero-brand-line>span',{yPercent:115,rotation:3,duration:1.25,stagger:.12},0)
        .from('.hero-title-line>span',{yPercent:110,duration:1,stagger:.12},.25);
    }
    // Level 3 varies by content: typographic wipe vs. image aperture, not fade-up.
    gsap.utils.toArray('[data-reveal]').forEach(element=>{
      if(element.closest('.is-pinned,.hero-journey'))return;
      const image=element.querySelector('.business-image');
      // Keep completed triggers registered. Killing `once` triggers while later
      // timelines measure a restored deep link can mutate GSAP's refresh list.
      const tl=gsap.timeline({scrollTrigger:{trigger:element,start:'top 92%',toggleActions:'play none none none'}});
      if(image){
        tl.from(image,{clipPath:'inset(0% 0% 100% 0%)',duration:1.1,ease:'power3.inOut'},0)
          .from(image.querySelector('img'),{scale:1.13,duration:1.35,ease:'power3.out'},0)
          .from(element.querySelector('.business-meta'),{x:lateralOffset,opacity:0,duration:.7},.4);
      }else if(/^H[1-6]$/.test(element.tagName)){
        tl.from(element,{clipPath:'inset(0% 0% 100% 0%)',y:16,duration:.95,ease:'power3.out',clearProps:'all'});
      }else{
        tl.from(element,{opacity:0,duration:.7,clearProps:'all'});
      }
    });
    const mm=gsap.matchMedia();
    mm.add({desktop:'(min-width:1024px)',compact:'(max-width:1023px)'},media=>{
      const compact=media.conditions.compact;
      // Level 2 ties the landscape opening and its statement to the same playhead.
      const section=document.querySelector('.sustainability-section');
      if(section){
        gsap.timeline({defaults:{ease:'none'},scrollTrigger:{trigger:section,start:'top 80%',end:'bottom 55%',scrub:.6}})
          .fromTo('.sustainability-image',{clipPath:compact?`inset(6% 5% 6% 5% round ${mediaRadius})`:`inset(12% 18% 12% 18% round ${mediaRadius})`},{clipPath:'inset(0% 0% 0% 0% round 0px)',duration:1},0)
          .fromTo('.sustainability-image>img',{scale:compact?1.1:1.18,yPercent:compact?2:5},{scale:1,yPercent:0,duration:1.3},0)
          .from('.sustainability-copy',{y:compact?28:65,duration:.8},.35)
          .to('.sustainability-intro',{y:-30,duration:1},0);
      }
      const global=document.querySelector('.global-section');
      if(global)gsap.timeline({defaults:{ease:'none'},scrollTrigger:{trigger:global,start:'top bottom',end:'bottom top',scrub:.7}})
        .fromTo('.global-background',{yPercent:compact?-3:-8,scale:compact?1.1:1.16},{yPercent:compact?3:8,scale:1.06,duration:1},0)
        .fromTo('.global-content',{y:compact?20:45},{y:compact?-15:-35,duration:1},0);
      const intro=document.querySelector('.introduction');
      if(compact && intro){
        gsap.timeline({defaults:{ease:'none'},scrollTrigger:{trigger:intro,start:'top 90%',end:'bottom 60%',scrub:.4}})
          .fromTo(intro.querySelector('h2'),{y:32},{y:0,duration:1},0)
          .fromTo(intro.querySelectorAll('.intro-lines p'),{opacity:.25,y:18},{opacity:1,y:0,stagger:.25,duration:.7},.15)
          .fromTo(intro.querySelector('.intro-right'),{y:40,rotation:5,scale:1.08},{y:-16,rotation:0,scale:1,duration:1.5},0);
      }
    });
    const events=[...document.querySelectorAll('.history-event')];
    events.forEach(event=>{
      gsap.timeline({scrollTrigger:{trigger:event,start:'top 80%',end:'top 35%',scrub:.4},defaults:{ease:'none'}})
        .fromTo(event,{'--event-progress':0},{'--event-progress':1,duration:1},0)
        .fromTo(event.querySelector('h3'),{x:lateralOffset},{x:0,duration:1},0);
      ScrollTrigger.create({trigger:event,start:'top 65%',end:'bottom 35%',onEnter:()=>activateYear(event),onEnterBack:()=>activateYear(event)});
    });
  });
  return ()=>context.revert();
}
function activateYear(event) {
  const year=document.querySelector('.history-year');
  if(year)year.textContent=event.dataset.year;
  document.querySelectorAll('.history-event').forEach(item=>item.classList.toggle('is-active',item===event));
}

export function initVideo(isReduced) {
  const video = document.querySelector('.hero-video');
  const button = document.querySelector('.video-toggle');
  if (!video || !button) return {refresh(){}};
  let pausedByUser = false;
  let visible = true;
  const videoViewport = matchMedia('(min-width:768px)');
  const allowAutoplay = () => videoViewport.matches && !navigator.connection?.saveData;
  const renderButton = () => {
    const paused = video.paused;
    button.setAttribute('aria-label', paused ? button.dataset.play : button.dataset.pause);
    button.setAttribute('aria-pressed', String(!paused));
    button.innerHTML = `<i data-lucide="${paused ? 'play' : 'pause'}" aria-hidden="true"></i>`;
    window.lucide?.createIcons({root:button,attrs:{'stroke-width':2}});
  };
  const play = async () => {
    if (isReduced() || !visible || document.hidden || pausedByUser) return;
    if (!video.src) video.src = video.dataset.src;
    try { await video.play(); } catch { video.classList.remove('is-playing'); }
    renderButton();
  };
  video.addEventListener('playing', () => {video.classList.add('is-playing');renderButton();});
  video.addEventListener('pause', renderButton);
  video.addEventListener('error', () => {video.classList.remove('is-playing');renderButton();});
  button.addEventListener('click', () => {
    if (video.paused) {pausedByUser=false;play();}
    else {pausedByUser=true;video.pause();}
  });
  new IntersectionObserver(entries => {
    visible = entries[0].isIntersecting;
    if (!visible) video.pause();
    else if (allowAutoplay()) play();
  }).observe(video.closest('.hero'));
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) video.pause();
    else if (visible && allowAutoplay()) play();
  });
  videoViewport.addEventListener('change',()=>{
    if (allowAutoplay()) play();
    else {video.pause();video.classList.remove('is-playing');}
  });
  renderButton();
  return {
    refresh() {
      if (isReduced()) {video.pause();button.hidden=true;}
      else {button.hidden=false;if (allowAutoplay()) play();}
    }
  };
}
