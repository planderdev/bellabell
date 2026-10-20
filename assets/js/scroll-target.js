// Resolve deep links after pin spacing exists, without mutating browser history.
export function scrollTopFor(target) {
  const st=window.ScrollTrigger;
  const journey=target.id==='introduction' && target.closest('.hero-journey.is-pinned');
  if(journey) return st?.getById('hero-journey')?.end??null;
  const brands=target.closest('.brands-section.is-pinned');
  if(brands && target.id.startsWith('brand-')){
    const trigger=st?.getById('brands');
    if(trigger)return target.id==='brand-aouaou'?trigger.end:trigger.start;
  }
  const pinned=target.closest('.is-pinned');
  const trigger=pinned?.id && st?.getById(pinned.id);
  if(trigger)return trigger.start;
  return null;
}
