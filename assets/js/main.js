import {createSmoothScroll} from './smooth-scroll.js';
import {initNavigation} from './navigation.js';
import {initAnimations,initVideo} from './animations.js';
import {initPins} from './pin-scroll.js';
import {initSliders} from './sliders.js';
import {initDetails} from './details.js';
import {scrollTopFor} from './scroll-target.js';

document.documentElement.classList.add('js');
window.lucide?.createIcons({attrs:{'stroke-width':2}});
if (window.gsap && window.ScrollTrigger) window.gsap.registerPlugin(window.ScrollTrigger);
const motionPreference = matchMedia('(prefers-reduced-motion:reduce)');
const isReduced = () => motionPreference.matches;
let smooth=null;
let cleanups=[];
const video=initVideo(isReduced);
function refreshMotion() {
  cleanups.forEach(cleanup=>cleanup());cleanups=[];
  smooth?.destroy();smooth=null;
  const reduced=isReduced();
  document.documentElement.dataset.motion=reduced?'reduced':'full';
  smooth=createSmoothScroll(reduced);
  cleanups.push(initPins(reduced,()=>smooth));
  cleanups.push(initAnimations(reduced));
  cleanups.push(initSliders(reduced));
  cleanups.push(initDetails(reduced));
  video.refresh();
  window.ScrollTrigger?.refresh();
}
initNavigation(()=>smooth,isReduced);
motionPreference.addEventListener('change',refreshMotion);
matchMedia('(min-width:1024px)').addEventListener('change',()=>{
  smooth?.destroy();
  smooth=createSmoothScroll(isReduced());
});
refreshMotion();
document.fonts.ready.then(()=>window.ScrollTrigger?.refresh());
window.addEventListener('load',()=>window.ScrollTrigger?.refresh(),{once:true});
window.addEventListener('pageshow',event=>{
  if(event.persisted){
    window.ScrollTrigger?.refresh();
    smooth?.scrollTo(window.scrollY,{immediate:true});
  }
});
// Media boxes reserve their dimensions. Refreshing on every lazy image would rewind
// scrubbed accessibility states during unrelated chapters; load/fonts handle layout changes.
// Resize handling belongs to GSAP matchMedia / ScrollTrigger, not an extra animation loop.
// Resolve initial and history/hash navigation after pin spacing is measured.
function followLocationAnchor() {
  if(!location.hash)return;
  let anchorId=location.hash.slice(1);
  try{anchorId=decodeURIComponent(anchorId);}catch{/* Keep controls usable for malformed URLs. */}
  const target=document.getElementById(anchorId);
  if(!target)return;
  const top=scrollTopFor(target);
  if(top!==null){
    if(smooth)smooth.scrollTo(top,{immediate:true});
    else window.scrollTo({top,behavior:'instant'});
  }else target.scrollIntoView({behavior:'instant',block:'start'});
}
const restoringHistory=performance.getEntriesByType('navigation')[0]?.type==='back_forward';
window.addEventListener('load',()=>{
  if(!restoringHistory)requestAnimationFrame(followLocationAnchor);
},{once:true});
window.addEventListener('hashchange',followLocationAnchor);
