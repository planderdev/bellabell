export function initSliders(reduced) {
  const section = document.querySelector('.brands-section');
  if (!section || !window.Swiper || reduced) return () => {};
  const media = matchMedia('(max-width:1023px)');
  let swiper = null;
  const destroySlider = () => {
    if (swiper) swiper.destroy(true,true);
    swiper=null;section.classList.remove('has-slider');
    // Desktop GSAP uses aria-disabled; discard Swiper's native disabled state.
    section.querySelectorAll('.brand-navigation button').forEach(button=>{button.disabled=false;});
  };
  const update = () => {
    if (media.matches && !swiper) {
      section.classList.add('has-slider');
      swiper = new window.Swiper(section.querySelector('.brand-viewport'), {
        slidesPerView:1,spaceBetween:0,speed:650,autoHeight:true,initialSlide:location.hash==='#brand-aouaou'?1:0,
        keyboard:{enabled:true,onlyInViewport:true},
        a11y:{enabled:true,prevSlideMessage:section.querySelector('.brand-prev').getAttribute('aria-label'),nextSlideMessage:section.querySelector('.brand-next').getAttribute('aria-label')},
        navigation:{prevEl:section.querySelector('.brand-prev'),nextEl:section.querySelector('.brand-next')},
        on:{
          slideChange(instance) {
            section.querySelector('.brand-current').textContent=String(instance.activeIndex+1).padStart(2,'0');
            instance.slides.forEach((slide,index)=>{slide.inert=index!==instance.activeIndex;slide.setAttribute('aria-hidden',String(index!==instance.activeIndex));});
          },
          init(instance) {
            section.querySelector('.brand-current').textContent=String(instance.activeIndex+1).padStart(2,'0');
            instance.slides.forEach((slide,index)=>{slide.inert=index!==instance.activeIndex;slide.setAttribute('aria-hidden',String(index!==instance.activeIndex));});
          }
        }
      });
    } else if (!media.matches && swiper) {
      destroySlider();
      if (!section.classList.contains('is-pinned')) section.querySelectorAll('.brand-panel').forEach(panel=>{panel.inert=false;panel.removeAttribute('aria-hidden');});
    }
  };
  update();media.addEventListener('change',update);
  return ()=>{
    media.removeEventListener('change',update);
    destroySlider();
    section.querySelectorAll('.brand-panel').forEach(panel=>{panel.inert=false;panel.removeAttribute('aria-hidden');});
  };
}
