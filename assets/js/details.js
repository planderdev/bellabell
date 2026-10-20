export function initDetails(reduced) {
  const cleanups=[];
  // Native details keeps FAQ answers usable without JavaScript.
  document.querySelectorAll('.faq-section').forEach(section=>{
    const buttons=[...section.querySelectorAll('[data-faq-filter]')];
    const items=[...section.querySelectorAll('.faq-item')];
    buttons.forEach(button=>{
      const filter=()=>{
        buttons.forEach(item=>{
          const active=item===button;
          item.classList.toggle('is-active',active);
          item.setAttribute('aria-pressed',String(active));
        });
        items.forEach(item=>{
          item.hidden=button.dataset.faqFilter!=='all'&&item.dataset.faqCategory!==button.dataset.faqFilter;
          if(item.hidden)item.open=false;
        });
        window.ScrollTrigger?.refresh();
      };
      button.addEventListener('click',filter);
      cleanups.push(()=>button.removeEventListener('click',filter));
    });
    items.forEach(item=>{
      const refresh=()=>window.ScrollTrigger?.refresh();
      item.addEventListener('toggle',refresh);
      cleanups.push(()=>item.removeEventListener('toggle',refresh));
    });
  });
  if(reduced||!window.gsap||!window.ScrollTrigger)return()=>cleanups.forEach(fn=>fn());
  const {gsap}=window;
  const context=gsap.context(()=>{
    const mm=gsap.matchMedia();
    mm.add('(min-width:1024px)',()=>{
      const fan=document.querySelector('.company-fan');
      if(fan){
        const timeline=gsap.timeline({scrollTrigger:{trigger:fan,start:'top 78%',end:'bottom 20%',scrub:1}});
        timeline.fromTo('.fan-left',{rotation:-9,x:24},{rotation:-16,x:-32,ease:'none'},0)
          .fromTo('.fan-right',{rotation:9,x:-24},{rotation:16,x:32,ease:'none'},0)
          .to('.fan-center',{y:-24,ease:'none'},0);
      }
      const section=document.querySelector('.detail-story');
      if(!section)return;
      section.classList.add('is-story-pinned');
      const track=section.querySelector('.detail-story-track');
      const viewport=section.querySelector('.detail-story-viewport');
      const cards=[...section.querySelectorAll('.detail-story-card')];
      const current=section.querySelector('.detail-story-current');
      const distance=()=>Math.max(0,track.scrollWidth-viewport.clientWidth);
      const timeline=gsap.timeline({scrollTrigger:{
        trigger:section,id:'detail-story',start:'top top',end:()=>`+=${Math.round(Math.max(innerHeight*1.6,distance()*1.6))}`,
        pin:true,scrub:.7,anticipatePin:1,invalidateOnRefresh:true
      }});
      timeline.to(track,{x:()=>-distance(),duration:1,ease:'none'},0)
        .to(section.querySelector('.detail-story-line i'),{scaleX:1,duration:1,ease:'none'},0);
      const update=()=>{current.textContent=String(Math.round(timeline.progress()*(cards.length-1))+1).padStart(2,'0');};
      timeline.eventCallback('onUpdate',update);
      window.ScrollTrigger.addEventListener('refresh',update);
      update();
      return()=>{
        window.ScrollTrigger.removeEventListener('refresh',update);
        section.classList.remove('is-story-pinned');
        current.textContent='01';
      };
    });
  });
  return()=>{context.revert();cleanups.forEach(fn=>fn());};
}
