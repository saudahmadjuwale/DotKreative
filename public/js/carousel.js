(function(){
  function ready(fn){
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once:true });
    } else {
      fn();
    }
  }

  function initCarousel(root){
    if (root.dataset.carouselInit === '1') return; // don't double-init
    root.dataset.carouselInit = '1';

    const track = root.querySelector('.carousel__track');
    const slides = Array.from(root.querySelectorAll('.carousel__slide'));
    const prev   = root.querySelector('[data-prev]');
    const next   = root.querySelector('[data-next]');
    const dotsCt = root.querySelector('[data-dots]');
    const dots   = dotsCt ? Array.from(dotsCt.querySelectorAll('button')) : [];
    const videos = Array.from(root.querySelectorAll('video'));

    // ensure buttons never submit a form if JS loads late
    if (prev) prev.type = 'button';
    if (next) next.type = 'button';
    dots.forEach(b => b.type = 'button');

    let i = 0, startX = 0, dx = 0, dragging = false;

    function go(to){
      i = (to + slides.length) % slides.length;
      track.style.transform = `translateX(${-i * 100}%)`;
      dots.forEach((b,idx)=> b.classList.toggle('is-active', idx === i));
      videos.forEach((v, idx)=>{ if (idx !== i && !v.paused) v.pause(); });
    }

    function isUI(target){
      return !!(target.closest('.carousel__ctrl') || target.closest('.carousel__dots'));
    }

    // click handlers
    function onPrev(e){ e.preventDefault(); e.stopPropagation(); go(i-1); }
    function onNext(e){ e.preventDefault(); e.stopPropagation(); go(i+1); }
    function onDot(e){
      e.preventDefault(); e.stopPropagation();
      const to = parseInt(this.dataset.dot,10) || 0;
      go(to);
    }

    if (prev) prev.addEventListener('click', onPrev);
    if (next) next.addEventListener('click', onNext);
    dots.forEach(b => b.addEventListener('click', onDot));

    // pointer drag (mouse + touch); left-click only for mouse
    root.addEventListener('pointerdown', (e)=>{
      if (e.pointerType === 'mouse' && e.button !== 0) return;
      if (isUI(e.target)) return;
      dragging = true;
      root.classList.add('is-dragging');
      startX = e.clientX;
      dx = 0;
      try { root.setPointerCapture(e.pointerId); } catch(_){}
    });

    root.addEventListener('pointermove', (e)=>{
      if(!dragging) return;
      dx = e.clientX - startX;
      // preview drag (no transition while dragging)
      const w = root.clientWidth || 1;
      track.style.transition = 'none';
      track.style.transform = `translateX(calc(${-i*100}% + ${dx/w*100}%))`;
    });

    function endDrag(){
      if(!dragging) return;
      dragging = false;
      root.classList.remove('is-dragging');
      track.style.transition = ''; // restore
      if (dx > 50)      go(i-1);
      else if (dx < -50) go(i+1);
      else               go(i);
      dx = 0;
    }
    root.addEventListener('pointerup', endDrag);
    root.addEventListener('pointercancel', endDrag);
    root.addEventListener('mouseleave', endDrag);

    // keyboard nav when carousel focused
    root.addEventListener('keydown', (e)=>{
      if (e.key === 'ArrowLeft')  { e.preventDefault(); go(i-1); }
      if (e.key === 'ArrowRight') { e.preventDefault(); go(i+1); }
    });
    // make it focusable for keyboard users
    if (!root.hasAttribute('tabindex')) root.setAttribute('tabindex','0');

    go(0);
  }

  function initAll(){
    document.querySelectorAll('[data-carousel]').forEach(initCarousel);
  }

  ready(initAll);

  // Optional: if you use Turbo/Laravel Livewire/Alpine swaps, re-init after navigation
  window.addEventListener('pageshow', initAll);
})();
