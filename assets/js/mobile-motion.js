// Mobile motion layer (<1024px). No pins, no smooth scroll: reveals, clip apertures,
// light parallax and chapter counters driven by ScrollTrigger. Desktop keeps the
// pinned sequences in pin-scroll.js; both respect prefers-reduced-motion.
export const MOBILE_QUERY='(max-width:1023px)';
export function initMobileMotion(reduced) {
  if (reduced || !window.gsap || !window.ScrollTrigger) return () => {};
  const {gsap,ScrollTrigger}=window;
  const mm=gsap.matchMedia();
  mm.add(MOBILE_QUERY,()=>{
    const cleanups=[];
    const radius=getComputedStyle(document.documentElement).getPropertyValue('--radius-xl').trim();
    const once=(trigger,start='top 80%')=>({trigger,start,toggleActions:'play none none none'});
    const scrub=(trigger,start,end,amount=.5)=>({trigger,start,end,scrub:amount,invalidateOnRefresh:true});
    const open=(target,duration=1.1)=>({clipPath:'inset(0% 0% 0% 0%)',duration,ease:'power3.inOut'});

    // 01 — hero departs under the introduction; intro copy and the floating photo rise in.
    const hero=document.querySelector('.hero');
    if(hero){
      gsap.timeline({defaults:{ease:'none'},scrollTrigger:scrub(hero,'top top','bottom top',.4)})
        .fromTo('.hero-poster',{scale:1.12,yPercent:0},{scale:1.12,yPercent:7,duration:1},0)
        .to('.hero-content',{y:-48,autoAlpha:.25,duration:1},0)
        .to('.hero-bottom',{autoAlpha:0,duration:.3},0);
    }
    const intro=document.querySelector('.introduction');
    if(intro){
      const parts=[...intro.querySelectorAll('.section-label,h2,.intro-lines p,.body-copy,.text-link')];
      gsap.timeline({scrollTrigger:once(intro,'top 78%')})
        .from(parts,{y:40,autoAlpha:0,duration:.75,stagger:.1,ease:'power2.out'},0)
        .fromTo(intro.querySelector('.intro-right'),{y:60,rotation:6,autoAlpha:0},{y:0,rotation:-4,autoAlpha:1,duration:.9,ease:'power2.out'},.25);
    }

    // 02 — each philosophy scene opens from the bottom, its word and copy follow;
    // a sticky counter under the stage reports the active chapter.
    const philosophy=document.querySelector('.philosophy-section');
    if(philosophy){
      const scenes=[...philosophy.querySelectorAll('.philosophy-scene')];
      const counter=philosophy.querySelector('.chapter-current');
      const bar=philosophy.querySelector('.chapter-track span');
      scenes.forEach((scene,i)=>{
        const image=scene.querySelector('.philosophy-image'),img=image.querySelector('img');
        gsap.timeline({scrollTrigger:once(scene,'top 75%')})
          .fromTo(image,{clipPath:'inset(0% 0% 100% 0%)'},open(image),0)
          .fromTo(img,{scale:1.18},{scale:1.08,duration:1.5,ease:'power2.out'},0)
          .from(scene.querySelector('.philosophy-word'),{y:40,autoAlpha:0,duration:.8,ease:'power2.out'},.35)
          .from(scene.querySelectorAll('.philosophy-text h2,.philosophy-text p'),{y:36,autoAlpha:0,duration:.7,stagger:.12,ease:'power2.out'},.5);
        gsap.fromTo(img,{yPercent:-4},{yPercent:4,ease:'none',scrollTrigger:scrub(scene,'top bottom','bottom top',.4)});
        ScrollTrigger.create({trigger:scene,start:'top 55%',end:'bottom 55%',
          onEnter:()=>setChapter(i),onEnterBack:()=>setChapter(i)});
      });
      const setChapter=index=>{
        const label=String(index+1).padStart(2,'0');
        if(counter&&counter.textContent!==label)counter.textContent=label;
      };
      if(bar)gsap.fromTo(bar,{scaleX:0},{scaleX:1,ease:'none',scrollTrigger:scrub(philosophy.querySelector('.philosophy-stage'),'top 55%','bottom 55%',.3)});
      cleanups.push(()=>{if(counter)counter.textContent='01';});
    }

    // 03 — ingredient cards open in sequence with their number sliding into place.
    document.querySelectorAll('.ingredient-card').forEach(card=>{
      const image=card.querySelector('.ingredient-image'),img=image.querySelector('img');
      gsap.timeline({scrollTrigger:once(card,'top 82%')})
        .fromTo(image,{clipPath:'inset(0% 0% 100% 0%)'},open(image,1),0)
        .fromTo(img,{scale:1.15},{scale:1.06,duration:1.3,ease:'power2.out'},0)
        .from(card.querySelector('.ingredient-number'),{x:24,autoAlpha:0,duration:.7,ease:'power2.out'},.3)
        .from(card.querySelector('.ingredient-category'),{y:12,autoAlpha:0,duration:.5},.4)
        .from(card.querySelectorAll('.ingredient-copy>*'),{y:24,autoAlpha:0,duration:.6,stagger:.08,ease:'power2.out'},.45);
    });

    // 04 — research scenes reveal upward; the step track fills as each stage enters.
    const technology=document.querySelector('.technology-section');
    if(technology){
      const scenes=[...technology.querySelectorAll('.research-scene')];
      const steps=[...technology.querySelectorAll('.research-step')];
      const activate=count=>steps.forEach((step,i)=>step.classList.toggle('is-active',i<count));
      scenes.forEach((scene,i)=>{
        const image=scene.querySelector('.research-image'),img=image.querySelector('img');
        gsap.timeline({scrollTrigger:once(scene,'top 80%')})
          .fromTo(image,{clipPath:'inset(100% 0% 0% 0%)'},open(image,1),i*.12)
          .fromTo(img,{yPercent:10,scale:1.1},{yPercent:0,scale:1,duration:1.2,ease:'power2.out'},i*.12)
          .from(scene.querySelector('.research-copy'),{y:32,autoAlpha:0,duration:.7,ease:'power2.out'},i*.12+.35);
        ScrollTrigger.create({trigger:scene,start:'top 70%',onEnter:()=>activate(i+1),onLeaveBack:()=>activate(i)});
      });
      cleanups.push(()=>activate(0));
    }

    // 05 — brand panels (Swiper on mobile) open their artwork and copy on first entry.
    const brands=document.querySelector('.brands-section');
    if(brands){
      brands.querySelectorAll('.brand-panel').forEach((panel,i)=>{
        const art=panel.querySelector('.brand-art');
        gsap.timeline({scrollTrigger:once(brands,'top 70%')})
          .fromTo(art,{clipPath:`inset(0% 0% 100% 0% round ${radius})`},{clipPath:'inset(0% 0% 0% 0% round 0px)',duration:1.1,ease:'power3.inOut'},i*.1)
          .fromTo(art.querySelector('img'),{scale:1.12,y:30},{scale:1,y:0,duration:1.3,ease:'power2.out'},i*.1)
          .from(panel.querySelectorAll('.brand-copy>*'),{y:24,autoAlpha:0,duration:.6,stagger:.08,ease:'power2.out'},i*.1+.4);
      });
    }

    // 06 — narrative sections keep a light parallax tied to scroll position.
    const sustainability=document.querySelector('.sustainability-section');
    if(sustainability){
      gsap.timeline({defaults:{ease:'none'},scrollTrigger:scrub('.sustainability-image','top 85%','top 30%',.5)})
        .fromTo('.sustainability-image',{clipPath:`inset(6% 6% 6% 6% round ${radius})`},{clipPath:'inset(0% 0% 0% 0% round 0px)',duration:1},0)
        .fromTo('.sustainability-image>img',{scale:1.15,yPercent:4},{scale:1,yPercent:0,duration:1},0);
      gsap.from('.sustainability-copy',{y:40,autoAlpha:0,duration:.8,ease:'power2.out',scrollTrigger:once('.sustainability-image','top 55%')});
    }
    const global=document.querySelector('.global-section');
    if(global){
      gsap.timeline({defaults:{ease:'none'},scrollTrigger:scrub(global,'top bottom','bottom top',.6)})
        .fromTo('.global-background img',{yPercent:-8,scale:1.12},{yPercent:8,scale:1.12,duration:1},0)
        .fromTo('.global-content',{y:36},{y:-24,duration:1},0);
    }
    return ()=>cleanups.forEach(fn=>fn());
  });
  return ()=>mm.revert();
}
