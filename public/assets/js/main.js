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
 form?.addEventListener('submit',async e=>{
  e.preventDefault();const status=document.querySelector('#form-status');const send=form.querySelector('[type="submit"]');
  if(!form.reportValidity())return;
  const data=Object.fromEntries(new FormData(form).entries());
  if(data.consent!=='yes'){status.textContent='Please confirm you have read the privacy notice.';status.className='form-status visible error';return}
  status.className='form-status visible';status.textContent='Sending your enquiry…';send.disabled=true;send.setAttribute('aria-busy','true');
  try{
   const response=await fetch('/api/inquiry.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
   const body=await response.json().catch(()=>({error:'Email delivery is unavailable in this local preview.'}));
   if(!response.ok)throw new Error(body.error||'Unable to send enquiry.');
   status.className='form-status visible success';status.textContent=body.delivery==='preview' ? 'Local preview: enquiry saved for testing. No email was sent.' : 'Thanks. Your enquiry has been received by our email service.';form.reset()
  }catch(error){status.className='form-status visible error';status.textContent=error.message||'Unable to send this enquiry. Please try again later.'}
  finally{send.disabled=false;send.removeAttribute('aria-busy')}
 });
})();
