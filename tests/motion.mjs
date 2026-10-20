import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
// Load the browser ES modules without changing the project's package type.
const moduleFrom=async file=>import(`data:text/javascript;base64,${Buffer.from(await readFile(new URL(file,import.meta.url))).toString('base64')}`);
const {chapterAt,initPins}=await moduleFrom('../assets/js/pin-scroll.js');
const {initAnimations}=await moduleFrom('../assets/js/animations.js');
const {createSmoothScroll}=await moduleFrom('../assets/js/smooth-scroll.js');
const {scrollTopFor}=await moduleFrom('../assets/js/scroll-target.js');
const {initMobileMotion,MOBILE_QUERY}=await moduleFrom('../assets/js/mobile-motion.js');
let assertions=0;
const equal=(actual,expected)=>{assert.deepEqual(actual,expected);assertions++;};
for(const boundaries of [[1.28,3.23],[1.1,2.8],[1.3]]){
  const times=[0,.2,...boundaries.flatMap(t=>[t-.0001,t,t+.0001]),5];
  const forward=times.map(time=>chapterAt(time,boundaries));
  equal([...times].reverse().map(time=>chapterAt(time,boundaries)),[...forward].reverse());
  equal(chapterAt(5,boundaries),boundaries.length); // large forward jump
  equal(chapterAt(0,boundaries),0); // immediate jump back to beginning
  boundaries.forEach((time,i)=>equal(chapterAt(time,boundaries),i+1));
}
// Reduced motion must not touch DOM, GSAP, Lenis or create animation side effects.
equal(typeof initPins(true,()=>{throw Error('Unexpected scroll setup');}),'function');
equal(typeof initAnimations(true),'function');
equal(createSmoothScroll(true),null);
equal(typeof initMobileMotion(true),'function');
// Mobile layer is the complement of the desktop pin breakpoint.
equal(MOBILE_QUERY,'(max-width:1023px)');
globalThis.window={ScrollTrigger:{getById:id=>({
  'hero-journey':{start:0,end:1485},brands:{start:10000,end:11620},technology:{start:7000,end:9340}
}[id])}};
const target=(id,selectors)=>({id,closest:selector=>selectors[selector]??null});
equal(scrollTopFor(target('introduction',{'.hero-journey.is-pinned':{}})),1485);
equal(scrollTopFor(target('brand-aouaou',{'.brands-section.is-pinned':{}})),11620);
equal(scrollTopFor(target('brand-fromhalla',{'.brands-section.is-pinned':{}})),10000);
equal(scrollTopFor(target('technology',{'.is-pinned':{id:'technology'}})),7000);
equal(scrollTopFor(target('introduction',{})),null); // mobile/reduced-motion flow
equal(scrollTopFor(target('brand-aouaou',{})),null);
delete globalThis.window;
console.log(`${assertions} motion state, reverse/skip, reduced-motion, mobile-layer and deep-link assertions passed.`);
