(()=>{
 document.documentElement.classList.add('js');
 const header=document.querySelector('.header');
 const onScroll=()=>header?.classList.toggle('stuck',scrollY>10);
 addEventListener('scroll',onScroll,{passive:true});onScroll();
 const menu=document.querySelector('.menu-toggle');
 const close=()=>{document.body.classList.remove('menu-open');menu?.setAttribute('aria-expanded','false');menu?.setAttribute('aria-label','Open navigation menu')};
 menu?.addEventListener('click',()=>{const open=document.body.classList.toggle('menu-open');menu.setAttribute('aria-expanded',String(open));menu.setAttribute('aria-label',open?'Close navigation menu':'Open navigation menu')});
 document.querySelectorAll('#primary-nav a').forEach(a=>a.addEventListener('click',close));
 document.addEventListener('keydown',e=>{if(e.key==='Escape')close()});
 addEventListener('resize',()=>{if(innerWidth>760)close()});
 const reveal=[...document.querySelectorAll('[data-reveal]')];
 if(matchMedia('(prefers-reduced-motion:reduce)').matches||!('IntersectionObserver' in window))reveal.forEach(el=>el.classList.add('in-view'));
 else {const io=new IntersectionObserver(entries=>entries.forEach(x=>{if(x.isIntersecting){x.target.classList.add('in-view');io.unobserve(x.target)}}),{threshold:.05,rootMargin:'0px 0px 45px 0px'});reveal.forEach(el=>io.observe(el))}
 document.querySelectorAll('.faq-q').forEach(btn=>btn.addEventListener('click',()=>{const target=btn.closest('.faq-item');const isOpen=target.classList.toggle('open');btn.setAttribute('aria-expanded',String(isOpen))}));
 const form=document.querySelector('#inquiry-form');
 form?.addEventListener('submit',e=>{
  e.preventDefault();
  if(!form.reportValidity())return;
  const status=document.querySelector('#form-status');
  status.className='form-status visible';
  status.textContent='Preview only: this form has not sent or saved your enquiry. Real email delivery will be enabled on the final PHP site.';
 });
})();
