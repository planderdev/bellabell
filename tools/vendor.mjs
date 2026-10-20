import { copyFile, mkdir } from 'node:fs/promises';
const files = {
  'gsap/dist/gsap.min.js': 'gsap.min.js',
  'gsap/dist/ScrollTrigger.min.js': 'ScrollTrigger.min.js',
  'lenis/dist/lenis.min.js': 'lenis.min.js',
  'lenis/dist/lenis.css': 'lenis.css',
  'swiper/swiper-bundle.min.js': 'swiper-bundle.min.js',
  'swiper/swiper-bundle.min.css': 'swiper-bundle.min.css',
  'lucide/dist/umd/lucide.min.js': 'lucide.min.js'
};
await mkdir('assets/vendor', {recursive:true});
for (const [source, target] of Object.entries(files)) {
  await copyFile(`node_modules/${source}`, `assets/vendor/${target}`);
}
console.log('Browser dependencies copied to assets/vendor.');
