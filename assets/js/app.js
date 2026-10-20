(() => {
 'use strict';
 const preview=document.body.dataset.preview==='true';
 const $=(s,root=document)=>root.querySelector(s);
 const $$=(s,root=document)=>Array.from(root.querySelectorAll(s));
 let csrf=$('meta[name="csrf-token"]')?.content || '';
 const menu=$('#mobileMenu'),toggle=$('#menuToggle');
 if(toggle&&menu){toggle.addEventListener('click',()=>{const open=toggle.getAttribute('aria-expanded')==='true';toggle.setAttribute('aria-expanded',String(!open));toggle.setAttribute('aria-label',open?'Open Navigation':'Close Navigation');toggle.innerHTML=open?'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>':'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18"/></svg>';menu.hidden=open;});}
 if('IntersectionObserver' in window){document.documentElement.classList.add('js-reveal');let io=new IntersectionObserver(entries=>{for(const en of entries){if(en.isIntersecting){en.target.classList.add('is-visible');io.unobserve(en.target);}}},{threshold:.04,rootMargin:'0px 0px 40px 0px'});$$('.reveal').forEach(el=>io.observe(el));}
 const queryService=new URLSearchParams(window.location.search).get('service');
 if(queryService&&$('select[name="service"]'))$('select[name="service"]').value=queryService;
 function feedback(form,text,error=false){const area=$('.form-message',form);if(area){area.textContent=text;area.style.color=error?'#b13755':'#3c6b3b';}}
 async function api(action,options={}){
  const res=await fetch('/api.php?task='+encodeURIComponent(action),{credentials:'same-origin',...options,headers:{Accept:'application/json',...(options.headers||{})}});
  let data;try{data=await res.json();}catch{throw new Error('Server Response Was Not JSON. Check PHP Hosting Configuration.');}
  if(!res.ok||data.ok===false)throw new Error(data.message||'Please Try Again Later.');return data;
 }
 async function submitForm(form){
  const action=form.dataset.form,btn=$('button[type="submit"]',form);
  if(preview){feedback(form,'Design Preview Only. Submissions Activate After PHP Hosting And Database Setup.',false);return;}
  const formData=new FormData(form);
  btn&& (btn.disabled=true);
  feedback(form,'Sending Your Request...');
  try{
   const result=await api(action,{method:'POST',body:formData});
   feedback(form,result.message||'Successfully Saved.');
   if(['contact','newsletter','admin_client','admin_post','admin_upload'].includes(action)){
    form.reset();if(action==='admin_post'&&$('#postId'))$('#postId').value='';
   }
   if(action==='login'){if(result.csrf){csrf=result.csrf;$('meta[name="csrf-token"]').content=csrf;$$('input[name="csrf"]').forEach(i=>i.value=csrf);}await refreshSession();}
   if(action==='profile')await refreshSession();
   if(action.startsWith('admin_')){await refreshAdminData();}
  }catch(err){feedback(form,err.message,true);}
  finally{btn&&(btn.disabled=false);}
 }
 $$('form[data-form]').forEach(form=>form.addEventListener('submit',event=>{event.preventDefault();submitForm(form);}));
 const portalPanel=$('#portalDashboard'),portalLogin=$('#portalLogin'),adminDashboard=$('#adminDashboard'),adminAuth=$('#adminAuth');
 let sessionUser=null;
 function el(tag,text,cls=''){let e=document.createElement(tag);if(cls)e.className=cls;e.textContent=text;return e;}
 function row(area,title,details,rightText,url='',deleteCB){
  if(!area)return;
  const r=el('div','','admin-data-row'),wrap=el('div','');wrap.appendChild(el('strong',title));if(details)wrap.appendChild(el('small',details));r.appendChild(wrap);
  const controls=el('div','');if(url){const a=el('a',rightText||'Open');a.href=url;a.style.color='#6850c5';controls.appendChild(a);}else if(rightText){controls.appendChild(el('span',rightText));}
  if(deleteCB){const b=el('button','Delete');b.type='button';b.addEventListener('click',deleteCB);controls.appendChild(b);}r.appendChild(controls);area.appendChild(r);
 }
 async function refreshSession(){
  if(preview)return;
  try{const data=await api('session');sessionUser=data.user||null;}catch{sessionUser=null;}
  if(portalPanel&&portalLogin){portalPanel.hidden=!sessionUser;portalLogin.hidden=!!sessionUser;if(sessionUser){$('#portalGreeting').textContent='Hello, '+sessionUser.name+'.';$('#profileName').value=sessionUser.name||'';$('#profileEmail').value=sessionUser.email||'';$('#profilePhone').value=sessionUser.phone||'';loadMyDocuments();}}
  if(adminDashboard&&adminAuth){const administrator=sessionUser&&sessionUser.role==='admin';adminDashboard.hidden=!administrator;adminAuth.hidden=!!administrator;if(administrator)await refreshAdminData();else if(sessionUser){const feedbackArea=$('.form-message',adminAuth);if(feedbackArea)feedbackArea.textContent='This Account Does Not Have Administrator Access.';}}
 }
 async function loadMyDocuments(){
  const list=$('#documentList');if(!list)return;list.textContent='';
  try{const data=await api('documents');if(!data.documents.length)list.appendChild(el('p','No Documents Have Been Shared With You Yet.'));
  data.documents.forEach(doc=>{let r=el('article',''),a=el('a',doc.title+' ↗');a.href='/api.php?task=download&id='+encodeURIComponent(doc.id);r.appendChild(a);r.appendChild(el('span',doc.created_at));list.appendChild(r);});}catch(err){list.appendChild(el('p',err.message));}
 }
 $$('.portal-tabs button').forEach(btn=>btn.addEventListener('click',()=>{ $$('.portal-tabs button').forEach(b=>b.classList.toggle('active',b===btn));$('#tabDocuments').hidden=btn.dataset.tab!=='documents';$('#tabProfile').hidden=btn.dataset.tab!=='profile'; }));
 async function doLogout(){if(preview)return;try{await api('logout',{method:'POST',body:new URLSearchParams({csrf})});location.href='/portal/';}catch(err){alert(err.message);}}
 $('#logoutButton')?.addEventListener('click',doLogout);$('#adminLogout')?.addEventListener('click',doLogout);
 $$('.admin-tabs button[data-admin-tab]').forEach(btn=>btn.addEventListener('click',()=>{$$('.admin-tabs button').forEach(b=>b.classList.toggle('active',b===btn));['posts','clients','documents','inquiries','newsletter'].forEach(s=>{const el=$('#admin-'+s);if(el)el.hidden=s!==btn.dataset.adminTab;});}));
 async function refreshAdminData(){
  if(!adminDashboard||preview||!sessionUser||sessionUser.role!=='admin')return;
  try{
   let data=await api('admin_list');
   let ps=$('#adminPostsList'),cl=$('#adminClientsList'),inq=$('#adminInquiriesList'),newsl=$('#adminNewsletterList'),docs=$('#adminDocumentsList'),sel=$('#uploadClient');
   [ps,cl,inq,newsl,docs].forEach(item=>{if(item)item.textContent='';});
   if(sel){sel.textContent='';const placeholder=document.createElement('option');placeholder.value='';placeholder.textContent='Select A Client';sel.appendChild(placeholder);}
   data.posts.forEach(p=>{row(ps,p.title,p.status+' · '+p.slug,'Edit','',async()=>{if(!confirm('Delete This Blog Post?'))return;await adminDelete('post',p.id);});const last=ps.lastElementChild;const edit=el('button','Edit');edit.type='button';edit.style.background='#ded5fb';edit.style.color='#31274f';edit.addEventListener('click',()=>{const f=$('form[data-form="admin_post"]');const vals=['title','slug','category','excerpt','body','status','seo_title','seo_description','ai_summary'];vals.forEach(k=>{if(f.elements[k])f.elements[k].value=p[k]||'';});if($('#postId'))$('#postId').value=p.id;window.scrollTo({top:f.getBoundingClientRect().top+window.scrollY-140,behavior:'smooth'});});last?.lastElementChild?.prepend(edit);});
   data.clients.forEach(c=>{row(cl,c.name,c.email,'Client');if(sel){const option=document.createElement('option');option.value=c.id;option.textContent=c.name+' — '+c.email;sel.appendChild(option);}});
   data.inquiries.forEach(i=>row(inq,i.name,i.email+' · '+i.service+' · '+i.message,'Received'));
   data.subscribers.forEach(s=>row(newsl,s.email,s.created_at,'Subscribed'));
   data.documents.forEach(d=>row(docs,d.title,d.client_name+' · '+d.created_at,'','',async()=>{if(!confirm('Delete This Document?'))return;await adminDelete('document',d.id);}));
  }catch(err){const area=$('#adminPostsList');if(area)area.textContent=err.message;}
 }
 async function adminDelete(type,id){try{await api('admin_delete',{method:'POST',body:new URLSearchParams({csrf,type,id:String(id)})});await refreshAdminData();}catch(err){alert(err.message);}}
 if((portalPanel||adminDashboard)&&!preview)refreshSession();
})();
