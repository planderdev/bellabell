export function createSmoothScroll(reduced) {
  if (reduced || !window.Lenis || !window.gsap || !window.ScrollTrigger || !matchMedia('(min-width:1024px)').matches) return null;
  const lenis = new window.Lenis({autoRaf:false, duration:1.05, smoothWheel:true, syncTouch:false, anchors:false});
  const tick = time => lenis.raf(time * 1000);
  lenis.on('scroll', window.ScrollTrigger.update);
  const resize=()=>lenis.resize();
  window.ScrollTrigger.addEventListener('refresh',resize);
  window.gsap.ticker.add(tick);
  window.gsap.ticker.lagSmoothing(0);
  return {
    scrollTo: (target, options = {}) => lenis.scrollTo(target, options),
    stop: () => lenis.stop(),
    start: () => lenis.start(),
    destroy() {
      window.gsap.ticker.remove(tick);
      lenis.off('scroll', window.ScrollTrigger.update);
      window.ScrollTrigger.removeEventListener('refresh',resize);
      lenis.destroy();
    }
  };
}
