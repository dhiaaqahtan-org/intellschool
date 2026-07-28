import '../app.js';

const root=document.documentElement;
const toggle=document.querySelector('[data-sidebar-toggle]');
const sidebar=document.querySelector('[data-platform-sidebar]');
const backdrop=document.querySelector('[data-sidebar-backdrop]');
let returnFocus=null;
const setSidebar=(open)=>{
 root.classList.toggle('sidebar-open',open);
 toggle?.setAttribute('aria-expanded',String(open));
 backdrop?.setAttribute('aria-hidden',String(!open));
 if(open){returnFocus=document.activeElement;sidebar?.querySelector('a,button')?.focus();}
 else if(returnFocus instanceof HTMLElement){returnFocus.focus();returnFocus=null;}
};
toggle?.addEventListener('click',()=>setSidebar(!root.classList.contains('sidebar-open')));
backdrop?.addEventListener('click',()=>setSidebar(false));
sidebar?.addEventListener('click',(event)=>{if(event.target.closest('a')&&matchMedia('(max-width: 62rem)').matches)setSidebar(false);});
document.addEventListener('keydown',(event)=>{if(event.key==='Escape'&&root.classList.contains('sidebar-open'))setSidebar(false);});
document.querySelectorAll('form').forEach((form)=>form.addEventListener('submit',(event)=>{
 if(event.defaultPrevented||!form.checkValidity())return;
 const button=event.submitter;
 if(!(button instanceof HTMLButtonElement))return;
 button.disabled=true;button.setAttribute('aria-busy','true');
 if(button.dataset.submittingLabel)button.textContent=button.dataset.submittingLabel;
}));
document.querySelector('[data-error-summary]')?.focus();
