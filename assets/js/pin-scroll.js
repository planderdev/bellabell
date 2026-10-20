// Every chapter derives its state from playhead time, never scroll direction/events.
export function chapterAt(time, boundaries) {
  return boundaries.reduce((chapter, boundary) => chapter + Number(time >= boundary), 0);
}
function activeScene(panels, index, counter) {
  panels.forEach((panel,i) => {
    const active = i === index;
    if(panel.dataset.active!==String(active)){
      panel.inert = !active;
      panel.setAttribute('aria-hidden',String(!active));
      panel.dataset.active = String(active);
    }
  });
  const number=String(index+1).padStart(2,'0');
  if (counter && counter.textContent!==number) counter.textContent=number;
}
function resetPanels(panels) {
  panels.forEach(panel => {panel.inert=false;panel.removeAttribute('aria-hidden');delete panel.dataset.active;});
}
export function initPins(reduced, getScroll) {
  if (reduced || !window.gsap || !window.ScrollTrigger) return () => {};
  const {gsap,ScrollTrigger} = window;
  const mediaRadius=getComputedStyle(document.documentElement).getPropertyValue('--radius-lg').trim();
  const mm = gsap.matchMedia();
  mm.add({desktop:'(min-width:1024px)',compact:'(max-width:1023px) and (orientation:portrait), (max-width:1023px) and (min-height:600px)'}, context => {
    const compact=context.conditions.compact;
    const cleanups = [], refreshStates = [];
    const pin = (section,id,distance,scrub=.55) => {
      section.classList.add('is-pinned');
      cleanups.push(()=>section.classList.remove('is-pinned','is-motion-active'));
      return gsap.timeline({defaults:{ease:'none'},scrollTrigger:{
        id,trigger:section,start:'top top',end:()=>`+=${Math.round(distance())}`,
        pin:true,pinSpacing:true,scrub:compact?.35:scrub,anticipatePin:1,invalidateOnRefresh:true,
        onToggle:self=>section.classList.toggle('is-motion-active',self.isActive)
      }});
    };
    const observeState = (timeline, update) => {
      timeline.eventCallback('onUpdate',update);refreshStates.push(update);update();
    };
    const seek = (timeline,progress,immediate=false) => {
      const st=timeline.scrollTrigger, top=st.start+progress*(st.end-st.start);
      const smooth=getScroll();
      if(smooth) smooth.scrollTo(top,{immediate,duration:.85});
      else window.scrollTo({top,behavior:immediate?'instant':'smooth'});
    };

    // 01 — the opening landscape becomes the image frame of the introduction.
    const journey=document.querySelector('.hero-journey');
    if(journey && compact){
      // Let the introduction keep its full reading height on a narrow screen.
      const hero=journey.querySelector('.hero');
      const tl=pin(hero,'hero-journey',()=>hero.offsetHeight*.9);
      tl.to(hero.querySelector('.hero-poster'),{scale:1.16,yPercent:4,duration:1.5},0)
        .to(hero.querySelector('.hero-brand-line:nth-child(1)'),{xPercent:-18,y:-45,duration:1.5},0)
        .to(hero.querySelector('.hero-brand-line:nth-child(2)'),{xPercent:20,y:-20,duration:1.5},0)
        .to(hero.querySelector('.hero-brand'),{autoAlpha:0,duration:.6},.6)
        .to(hero.querySelector('h1'),{y:-35,duration:1.5},0)
        .to(hero.querySelector('.hero-media'),{clipPath:`inset(9% 4% 4% 4% round ${mediaRadius})`,duration:1.1,ease:'power2.inOut'},.4)
        .to(hero.querySelector('.hero-scroll-progress>span'),{scaleX:1,duration:1.5},0);
    }else if(journey){
      const intro=journey.querySelector('.introduction');
      const introParts=[...intro.querySelectorAll('.section-label,h2,.intro-lines p,.body-copy,.text-link')];
      const heroParts=[journey.querySelector('.hero-content'),journey.querySelector('.hero-bottom')];
      const tl=pin(journey,'hero-journey',()=>journey.offsetHeight*1.65,.55);
      gsap.set(journey.querySelector('.hero-media'),{clipPath:'inset(0% 0% 0% 0% round 0px)'});
      gsap.set(journey.querySelector('h1'),{clipPath:'inset(0% 0% 0% 0%)'});
      gsap.set(introParts,{y:50,autoAlpha:0});
      gsap.set(intro.querySelector('.intro-right'),{y:80,rotation:6,autoAlpha:0});
      tl.addLabel('landscape',0)
        .to('.hero-poster',{scale:1.17,xPercent:-3,duration:2},0)
        .to('.hero-brand-line:nth-child(1)',{xPercent:-16,y:-90,duration:1.15},.1)
        .to('.hero-brand-line:nth-child(2)',{xPercent:22,y:-45,duration:1.15},.1)
        .to('.hero h1',{y:-85,clipPath:'inset(0% 0% 100% 0%)',duration:.65},.25)
        .to('.hero-description,.hero .button',{y:-30,autoAlpha:0,duration:.45},.2)
        .to('.hero-brand',{autoAlpha:0,duration:.35},.7)
        .to('.hero-media',{clipPath:`inset(18% 62% 14% 5% round ${mediaRadius})`,duration:1.2,ease:'power2.inOut'},.35)
        .to('.hero-shade',{opacity:.05,duration:.8},.5)
        .to('.hero-bottom',{autoAlpha:0,y:16,duration:.3},.5)
        .addLabel('introduction',1.2)
        .to(introParts,{autoAlpha:1,y:0,stagger:.09,duration:.55,ease:'power2.out'},1.2)
        .to(intro.querySelector('.intro-right'),{autoAlpha:1,y:0,rotation:-5,duration:.8,ease:'power2.out'},1.2)
        .to('.hero-scroll-progress>span',{scaleX:1,duration:2.4},0)
        .to({}, {duration:.4});
      observeState(tl,()=>{
        const inIntro=tl.time()>=1.2;
        intro.inert=!inIntro;intro.setAttribute('aria-hidden',String(!inIntro));
        heroParts.forEach(el=>{el.inert=inIntro;el.setAttribute('aria-hidden',String(inIntro));});
      });
      cleanups.push(()=>resetPanels([intro,...heroParts]));
    }

    // 02 — continuous full-bleed canvas. Incoming apertures reveal the next place
    // while the previous landscape continues to move underneath them.
    const philosophy=document.querySelector('.philosophy-section');
    if(philosophy){
      const scenes=[...philosophy.querySelectorAll('.philosophy-scene')];
      const tl=pin(philosophy,'philosophy',()=>philosophy.offsetHeight*(compact?2.1:3.2),.6);
      const aperture=compact
        ?['inset(0% 0% 0% 0%)','inset(28% 6% 32% 48%)','inset(55% 38% 9% 6%)']
        :['inset(0% 0% 0% 0%)','inset(23% 7% 23% 69%)','inset(65% 56% 8% 7%)'];
      scenes.forEach((scene,i)=>{
        const image=scene.querySelector('.philosophy-image');
        const word=scene.querySelector('.philosophy-word');
        const copy=scene.querySelector('.philosophy-text');
        gsap.set(image,{clipPath:i?'inset(50% 50% 50% 50%)':'inset(0% 0% 0% 0%)'});
        if(i)gsap.set([word,copy],{yPercent:110,autoAlpha:0});
        if(!i){tl.to(image.querySelector('img'),{scale:1.1,xPercent:-2,duration:1.9},0);return;}
        const time=i===1?.6:2.55,previous=scenes[i-1];
        tl.addLabel(i===1?'research-aperture':'wellness-aperture',time)
          .to(image,{clipPath:aperture[i],duration:.42,ease:'power2.out'},time)
          .to(image,{clipPath:'inset(0% 0% 0% 0%)',duration:1.1,ease:'power2.inOut'},time+.42)
          .fromTo(image.querySelector('img'),{scale:1.2,xPercent:i===1?5:-5},{scale:1,xPercent:0,duration:1.65},time)
          .to(previous.querySelector('.philosophy-image'),{xPercent:i===1?-4:4,scale:1.08,duration:1.5},time)
          .to(previous.querySelector('.philosophy-word'),{yPercent:-100,xPercent:-6,autoAlpha:0,duration:.65},time+.16)
          .to(previous.querySelector('.philosophy-text'),{y:-70,autoAlpha:0,duration:.5},time+.16)
          .to(word,{yPercent:0,autoAlpha:1,duration:.8,ease:'power2.out'},time+.64)
          .to(copy,{yPercent:0,autoAlpha:1,duration:.8,ease:'power2.out'},time+.76);
      });
      tl.addLabel('wellness',4.15).to(scenes[2].querySelector('img'),{scale:1.04,duration:.6},4.15)
        .to(philosophy.querySelector('.chapter-track span'),{scaleX:1,duration:4.75},0);
      observeState(tl,()=>activeScene(scenes,chapterAt(tl.time(),[1.28,3.23]),philosophy.querySelector('.chapter-current')));
      cleanups.push(()=>resetPanels(scenes));
    }

    // 03 — measured horizontal travel and per-frame image depth share one playhead.
    const ingredients=document.querySelector('.ingredients-section');
    if(ingredients){
      const track=ingredients.querySelector('.ingredients-track');
      const cards=[...track.children];
      const distance=()=>Math.max(0,track.scrollWidth-ingredients.clientWidth);
      const tl=pin(ingredients,'ingredients',()=>Math.max(ingredients.offsetHeight*(compact?1.25:1.4),distance()*(compact?1.2:1.05)),.6);
      tl.to(track,{x:()=>-distance(),duration:3},0)
        .to(ingredients.querySelector('.gallery-track span'),{scaleX:1,duration:3},0);
      cards.forEach((card,i)=>{
        const start=Math.max(0,i*.85-.1);
        tl.fromTo(card.querySelector('img'),{scale:1.17,xPercent:-3},{scale:1.08,xPercent:3,duration:1.3},start)
          .fromTo(card.querySelector('.ingredient-number'),{x:24},{x:-12,duration:1.3},start);
      });
      const focus=event=>{
        const card=event.target.closest('.ingredient-card');if(!card)return;
        const progress=Math.min(1,Math.max(0,(card.offsetLeft-parseFloat(getComputedStyle(track).paddingLeft))/Math.max(1,distance())));
        seek(tl,progress,true);
      };
      track.addEventListener('focusin',focus);
      cleanups.push(()=>track.removeEventListener('focusin',focus));
    }

    // 04 — fixed editorial copy on the left and a single layered media viewport.
    const technology=document.querySelector('.technology-section');
    if(technology){
      const scenes=[...technology.querySelectorAll('.research-scene')];
      const tl=pin(technology,'technology',()=>technology.offsetHeight*(compact?1.8:2.6),.55);
      scenes.forEach((scene,i)=>{
        const media=scene.querySelector('.research-image'),copy=scene.querySelector('.research-copy');
        gsap.set(media,{clipPath:i?'inset(100% 0% 0% 0%)':'inset(0% 0% 0% 0%)'});
        if(i)gsap.set(copy,{y:55,autoAlpha:0,clipPath:'inset(0% 0% 100% 0%)'});
        if(!i){tl.to(media.querySelector('img'),{scale:1.08,duration:1.8},0);return;}
        const time=i===1?.65:2.35,prev=scenes[i-1];
        tl.addLabel(i===1?'collaboration':'products',time)
          .to(media,{clipPath:'inset(0% 0% 0% 0%)',duration:1.1,ease:'power2.inOut'},time)
          .fromTo(media.querySelector('img'),{yPercent:12,scale:1.12},{yPercent:0,scale:1,duration:1.25},time)
          .to(prev.querySelector('.research-image img'),{yPercent:-8,scale:1.08,duration:1.1},time)
          .to(prev.querySelector('.research-copy'),{y:-45,autoAlpha:0,clipPath:'inset(100% 0% 0% 0%)',duration:.45},time+.1)
          .to(copy,{y:0,autoAlpha:1,clipPath:'inset(0% 0% 0% 0%)',duration:.7,ease:'power2.out'},time+.45);
      });
      tl.to({}, {duration:.5});
      observeState(tl,()=>{
        const index=chapterAt(tl.time(),[1.1,2.8]);
        activeScene(scenes,index,technology.querySelector('.research-current'));
        technology.querySelectorAll('.research-step').forEach((step,i)=>step.classList.toggle('is-active',i<=index));
      });
      cleanups.push(()=>{resetPanels(scenes);technology.querySelectorAll('.research-step').forEach(el=>el.classList.remove('is-active'));});
    }

    // 05 — portfolio stays full-screen; the incoming brand grows from an aperture.
    const brands=document.querySelector('.brands-section');
    if(brands){
      const panels=[...brands.querySelectorAll('.brand-panel')];
      const [first,second]=panels;
      const tl=pin(brands,'brands',()=>brands.offsetHeight*(compact?1.25:1.8),.6);
      gsap.set(second,{clipPath:'inset(100% 0% 0% 0%)'});
      tl.addLabel('from-halla',0)
        .to(first.querySelector('.brand-art img'),{scale:1.1,duration:1.8},0)
        .to(first.querySelector('.brand-display'),{xPercent:-12,y:-60,autoAlpha:0,duration:1.15},.4)
        .to(first.querySelector('.brand-copy'),{y:-70,autoAlpha:0,duration:.65},.55)
        .to(second,{clipPath:compact?`inset(32% 8% 38% 8% round ${mediaRadius})`:`inset(45% 42% 12% 7% round ${mediaRadius})`,duration:.45,ease:'power2.out'},.5)
        .to(second,{clipPath:'inset(0% 0% 0% 0% round 0px)',duration:1.15,ease:'power2.inOut'},.95)
        .fromTo(second.querySelector('.brand-art'),{scale:1.13,y:60},{scale:1,y:0,duration:1.4},.65)
        .fromTo(second.querySelector('.brand-display'),{xPercent:32,y:45},{xPercent:0,y:0,duration:1.25,ease:'power2.out'},.95)
        .fromTo(second.querySelector('.brand-copy'),{y:80,autoAlpha:0},{y:0,autoAlpha:1,duration:.85,ease:'power2.out'},1.15)
        .addLabel('aouaou',2.1).to({}, {duration:.45})
        .to(brands.querySelector('.brand-progress>span'),{scaleX:1,duration:2.55},0);
      const buttons=[brands.querySelector('.brand-prev'),brands.querySelector('.brand-next')];
      observeState(tl,()=>{
        const index=chapterAt(tl.time(),[1.3]);
        activeScene(panels,index,brands.querySelector('.brand-current'));
        // Keep controls focusable while their availability changes during scroll.
        buttons.forEach((button,i)=>button.setAttribute('aria-disabled',String(i===index)));
      });
      const prev=()=>{if(buttons[0].getAttribute('aria-disabled')!=='true')seek(tl,0);};
      const next=()=>{if(buttons[1].getAttribute('aria-disabled')!=='true')seek(tl,1);};
      buttons[0].addEventListener('click',prev);buttons[1].addEventListener('click',next);
      cleanups.push(()=>{resetPanels(panels);buttons[0].removeEventListener('click',prev);buttons[1].removeEventListener('click',next);buttons.forEach(el=>el.removeAttribute('aria-disabled'));});
    }
    // Refresh suppresses timeline callbacks; reconcile semantic state afterwards.
    const syncStates=()=>refreshStates.forEach(update=>update());
    ScrollTrigger.addEventListener('refresh',syncStates);syncStates();
    return ()=>{ScrollTrigger.removeEventListener('refresh',syncStates);cleanups.forEach(fn=>fn());};
  });
  return ()=>mm.revert();
}
