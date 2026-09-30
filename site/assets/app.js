/* حالت نمایش: خودکار (مطابق گوشی)، روشن یا تیره. انتخاب کاربر در همین مرورگر ذخیره می‌شود. */
(function(){
  var root=document.documentElement, btn=document.getElementById("theme-btn"), ic=document.getElementById("theme-ic");
  var ICONS={auto:'<circle cx="12" cy="12" r="8"/><path d="M12 4a8 8 0 0 0 0 16z" fill="currentColor"/>',
             light:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
             dark:'<path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5z"/>'};
  var NAMES={auto:"خودکار (مطابق گوشی)",light:"روشن",dark:"تیره"}, ORDER=["auto","light","dark"];
  var mode="auto"; try{mode=localStorage.getItem("farvam-theme")||"auto"}catch(e){}
  function apply(m){
    mode=m;
    if(m==="auto")root.removeAttribute("data-theme"); else root.setAttribute("data-theme",m);
    ic.innerHTML=ICONS[m]; btn.title="حالت نمایش: "+NAMES[m];
    try{localStorage.setItem("farvam-theme",m)}catch(e){}
  }
  if(ORDER.indexOf(mode)<0)mode="auto";
  apply(mode);
  btn.addEventListener("click",function(){apply(ORDER[(ORDER.indexOf(mode)+1)%3])});
})();

/* کهکشان ذرات طلایی: هزاران ذره ریز که آرام دور یک مرکز می‌چرخند و چشمک می‌زنند. */
(function(){
  var cv=document.getElementById("galaxy"); if(!cv||!cv.getContext)return;
  var ctx=cv.getContext("2d",{alpha:true}), dpr=1, W=0,H=0, stars=[], dark=false, scrolling=0, scrollT=0, lastDraw=0, raf=0;
  var still=matchMedia("(prefers-reduced-motion: reduce)").matches;
  function isDark(){var t=document.documentElement.getAttribute("data-theme");return t?t==="dark":matchMedia("(prefers-color-scheme: dark)").matches}
  function build(){
    W=cv.clientWidth;H=cv.clientHeight;cv.width=W*dpr;cv.height=H*dpr;ctx.setTransform(dpr,0,0,dpr,0,0);
    var small=W<760, n=Math.round(small?Math.min(260,W*H/1800):Math.min(650,W*H/2200)); stars=[];
    var arms=3, R=Math.hypot(W,H)*.62;
    for(var i=0;i<n;i++){
      var k=Math.random(), arm=i%arms, r=Math.pow(k,.65)*R;
      var a=arm*(Math.PI*2/arms)+r/R*5.2+(Math.random()-.5)*(0.55+0.9*(1-k));
      stars.push({r:r,a:a,s:Math.random()*1.5+.35,w:Math.random()*Math.PI*2,ws:.6+Math.random()*1.8,
        v:(.00045+.0011*(1-k))*(Math.random()<.5?1:.85), h:Math.random()});
    }
  }
  var cx=0,cy=0,t0=performance.now();
  function loop(now){ raf=0; if(document.hidden)return; if(!scrolling&&now-lastDraw>33){lastDraw=now;frame(now)} if(!still)raf=requestAnimationFrame(loop); }
  addEventListener("scroll",function(){scrolling=1;clearTimeout(scrollT);scrollT=setTimeout(function(){scrolling=0},140)},{passive:true});
  document.addEventListener("visibilitychange",function(){if(!document.hidden&&!still&&!raf)raf=requestAnimationFrame(loop)});
  function frame(now){
    var t=(now-t0)/1000; dark=isDark();
    ctx.clearRect(0,0,W,H);
    cx=W*.72; cy=H*.30;
    var g=ctx.createRadialGradient(cx,cy,0,cx,cy,Math.min(W,H)*.45);
    g.addColorStop(0,dark?"rgba(255,205,90,.16)":"rgba(210,150,30,.12)");g.addColorStop(1,"rgba(255,205,90,0)");
    ctx.fillStyle=g;ctx.fillRect(0,0,W,H);
    for(var i=0;i<stars.length;i++){
      var p=stars[i], a=p.a+(still?0:t*p.v*60*.1);
      var x=cx+Math.cos(a)*p.r, y=cy+Math.sin(a)*p.r*.55;
      if(x<-4||x>W+4||y<-4||y>H+4)continue;
      var tw=.45+.55*Math.sin(p.w+t*p.ws);
      var al=(dark?.85:.6)*tw;
      var col=p.h<.7?(dark?"255,207,95":"176,120,14"):(p.h<.9?(dark?"255,236,180":"205,150,40"):(dark?"120,245,225":"10,150,130"));
      ctx.fillStyle="rgba("+col+","+al.toFixed(3)+")";
      ctx.beginPath();ctx.arc(x,y,p.s,0,6.283);ctx.fill();
      if(p.s>1.55&&tw>.85){ctx.fillStyle="rgba("+col+","+(al*.25).toFixed(3)+")";ctx.beginPath();ctx.arc(x,y,p.s*3.2,0,6.283);ctx.fill()}
    }
  }
  build(); addEventListener("resize",function(){build();if(still)frame(performance.now())});
  if(still){frame(performance.now());new MutationObserver(function(){frame(performance.now())}).observe(document.documentElement,{attributes:true,attributeFilter:["data-theme"]})}
  else raf=requestAnimationFrame(loop);
})();

/* جلوه سه‌بعدی: کارت‌های تصویر با حرکت ماوس می‌چرخند (فقط روی دستگاه‌های دارای ماوس). */
(function(){
  if(!matchMedia("(hover:hover) and (pointer:fine)").matches||matchMedia("(prefers-reduced-motion: reduce)").matches)return;
  [].forEach.call(document.querySelectorAll(".shot"),function(el){
    el.classList.add("tilt"); if(el.parentElement)el.parentElement.classList.add("stage");
    var base=el.closest(".hero")?{x:5,y:-10}:{x:0,y:0};
    el.addEventListener("mousemove",function(e){
      var r=el.getBoundingClientRect(),px=(e.clientX-r.left)/r.width,py=(e.clientY-r.top)/r.height;
      el.classList.add("live");
      el.style.transform="rotateX("+((.5-py)*10).toFixed(2)+"deg) rotateY("+((px-.5)*12).toFixed(2)+"deg) translateZ(10px)";
      el.style.setProperty("--mx",(px*100).toFixed(1)+"%"); el.style.setProperty("--my",(py*100).toFixed(1)+"%");
    });
    el.addEventListener("mouseleave",function(){el.classList.remove("live");el.style.transform="rotateX("+base.x+"deg) rotateY("+base.y+"deg)"});
  });
})();

/* داده‌ها از سرور (پنل مدیریت) در window.FARVAM قرار می‌گیرند. */
var F = window.FARVAM || {};
var IMG_DIR = (F.base||"") + "images/", IMG_EXT = ".webp";
var WHATSAPP = F.whatsapp || "";
var SOCIAL = F.social || {};
var ROLES = F.roles || {};
var LABELS = F.labels || {};
/* کانال تبلیغ از لینک (مثلاً ?utm_source=instagram) خوانده و در پیام دمو فرستاده می‌شود. */
var UTM = ""; try{var q=new URLSearchParams(location.search);UTM=[q.get("utm_source"),q.get("utm_campaign")].filter(Boolean).join(" / ")}catch(e){}

(function(){
  function esc(s){return String(s).replace(/[&<>"]/g,function(c){return{"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;"}[c]})}

  /* gallery tabs only show panels that have at least one image */
  var gsec=document.getElementById("panels");
  var gtabs=gsec?[document.getElementById("g-acc"),document.getElementById("g-ops"),document.getElementById("g-mgmt")]:[];
  function refresh(){
    var any=false, sel=null;
    gtabs.forEach(function(t){
      var pan=document.getElementById(t.getAttribute("aria-controls"));
      var shots=[].slice.call(pan.querySelectorAll(".shot:not(.empty)"));
      shots.forEach(function(f,i){f.classList.toggle("first",i===0)});
      t.hidden=!shots.length; if(shots.length)any=true;
      if(t.getAttribute("aria-selected")==="true"&&!t.hidden)sel=t;
    });
    if(gsec)gsec.hidden=!any;
    if(!sel){var v=gtabs.filter(function(t){return!t.hidden})[0];if(v)v.click()}
  }

  /* image slots */
  function bindShot(fig){
    var key=fig.dataset.img, img=fig.querySelector("img"), ph=fig.querySelector(".ph");
    var label=fig.dataset.label||LABELS[key]||key;
    ph.innerHTML='<span class="neon t"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 16l-5-5-9 9"/></svg></span><b>تصویر: '+esc(label)+'</b><code>'+esc(IMG_DIR+key+IMG_EXT)+'</code>';
    fig.classList.add("empty");
    img.onload=function(){fig.classList.remove("empty");refresh()};
    img.onerror=function(){fig.classList.add("empty");refresh()};
    img.src=IMG_DIR+key+IMG_EXT;
  }
  [].forEach.call(document.querySelectorAll(".shot[data-img]"),function(f){if(f.id!=="role-shot")bindShot(f)});

  /* tabs helper */
  function tabs(list,onSelect){
    list.forEach(function(t,i){
      t.addEventListener("click",function(){select(t)});
      t.addEventListener("keydown",function(e){var d=e.key==="ArrowLeft"?1:e.key==="ArrowRight"?-1:0;if(!d)return;var n=list[(i+d+list.length)%list.length];n.focus();select(n)});
    });
    function select(t){list.forEach(function(x){x.setAttribute("aria-selected",x===t?"true":"false");x.tabIndex=x===t?0:-1});onSelect(t)}
    select(list.filter(function(t){return t.getAttribute("aria-selected")==="true"})[0]||list[0]);
  }

  /* gallery */
  if(gtabs.length)tabs(gtabs,function(t){gtabs.forEach(function(x){document.getElementById(x.getAttribute("aria-controls")).hidden=x!==t})});

  /* roles */
  var panel=document.getElementById("p-role"), shot=document.getElementById("role-shot");
  if(panel)tabs([].slice.call(document.querySelectorAll("#roles .tab")),function(t){
    var k=t.dataset.k, r=ROLES[k];
    panel.setAttribute("aria-labelledby","t-"+k);
    panel.querySelector('[data-f="role"]').textContent=r.role;
    panel.querySelector('[data-f="title"]').textContent=r.title;
    panel.querySelector('[data-f="intro"]').textContent=r.intro;
    panel.querySelector('[data-f="list"]').innerHTML=r.list.map(function(i){return"<li><b>"+esc(i[0])+"</b> "+esc(i[1])+"</li>"}).join("");
    shot.dataset.img=r.img; shot.dataset.label=LABELS[r.img]; shot.querySelector("img").alt=LABELS[r.img]+" در فَروَم";
    bindShot(shot);
  });

  /* socials */
  var ICON={instagram:'<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/>',telegram:'<path d="M21 4 3 11l6 2 2 6 3-4 5 4z"/><path d="m9 13 8-6"/>',bale:'<circle cx="12" cy="12" r="9"/><path d="M8 12h8M12 8v8"/>',eitaa:'<circle cx="12" cy="12" r="9"/><path d="M9 9h6M9 12h6M9 15h4"/>'};
  var SURL={instagram:"https://instagram.com/",telegram:"https://t.me/",bale:"https://ble.ir/",eitaa:"https://eitaa.com/"};
  var NAME={instagram:"اینستاگرام",telegram:"تلگرام",bale:"بله",eitaa:"ایتا"};
  var socEl=document.getElementById("socials"); if(socEl)socEl.innerHTML=Object.keys(SOCIAL).filter(function(k){return SOCIAL[k]}).map(function(k){return '<a href="'+SURL[k]+esc(SOCIAL[k])+'" target="_blank" rel="noopener" aria-label="'+NAME[k]+'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">'+ICON[k]+'</svg></a>'}).join("");

  /* loss calculator */
  var fa=function(n,d){return Number(n).toLocaleString("fa-IR",{maximumFractionDigits:d})};
  function calc(){
    var sout=parseFloat(document.getElementById("c-sout").value)||0, days=parseFloat(document.getElementById("c-days").value)||0, price=parseFloat(document.getElementById("c-price").value)||0;
    var mg=sout*days/1000, yg=mg*12;
    document.getElementById("c-month-g").textContent=fa(mg,2)+" گرم";
    document.getElementById("c-year-g").textContent=fa(yg,2);
    document.getElementById("c-year-t").textContent=price?fa(Math.round(yg*price),0)+" تومان":"قیمت را وارد کنید";
  }
  if(document.getElementById("calc")){["c-sout","c-days","c-price"].forEach(function(id){document.getElementById(id).addEventListener("input",calc)});calc();}

  /* demo form */
  var demoForm=document.getElementById("demo-form"); if(demoForm)demoForm.addEventListener("submit",function(e){
    e.preventDefault();
    var v=function(id){return document.getElementById(id).value.trim()};
    var err=document.getElementById("f-error");
    if(!v("f-name")||!v("f-phone")){err.hidden=false;return}
    err.hidden=true;
    var text=(F.leadTitle||"درخواست دمو فَروَم")+"\nنام: "+v("f-name")+"\nموبایل: "+v("f-phone")+"\nصنف: "+v("f-role")+(v("f-city")?"\nشهر: "+v("f-city"):"")+(v("f-note")?"\nدغدغه: "+v("f-note"):"")+(v("f-src")?"\nآشنایی از: "+v("f-src"):"")+(UTM?"\nکانال تبلیغ: "+UTM:"")+(window.FARVAM_AB?"\n"+window.FARVAM_AB:"");
    document.getElementById("f-text").textContent=text;
    if(F.leadUrl){try{fetch(F.leadUrl,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({name:v("f-name"),phone:v("f-phone"),role:v("f-role"),city:v("f-city"),src:v("f-src"),note:v("f-note"),utm:UTM+(window.FARVAM_AB?" | "+window.FARVAM_AB:""),page:location.pathname,website:(document.getElementById("f-web")||{}).value||""})}).then(function(r){var ok=document.getElementById("f-saved");if(ok&&r.ok)ok.hidden=false;if(r.ok){try{if(window.gtag)gtag("event","generate_lead",{method:"farvam_form"});(window.dataLayer=window.dataLayer||[]).push({event:"farvam_lead"});if(F.conversion){var sc=document.createElement("div");sc.innerHTML=F.conversion;[].forEach.call(sc.querySelectorAll("script"),function(o){var n=document.createElement("script");if(o.src)n.src=o.src;else n.textContent=o.textContent;document.body.appendChild(n)})}}catch(e){}}}).catch(function(){})}catch(x){}}
    var acts=document.getElementById("f-acts"); acts.innerHTML="";
    var wa=document.createElement("a");wa.className="btn btn-gold";wa.target="_blank";wa.rel="noopener";wa.href="https://wa.me/"+WHATSAPP+"?text="+encodeURIComponent(text);wa.textContent="ارسال در واتساپ";acts.appendChild(wa);
    var copy=document.createElement("button");copy.type="button";copy.className="btn btn-glass";copy.textContent="کپی متن";
    copy.addEventListener("click",function(){
      function sel(){var r=document.createRange();r.selectNodeContents(document.getElementById("f-text"));var s=getSelection();s.removeAllRanges();s.addRange(r);copy.textContent="متن انتخاب شد؛ کپی کنید"}
      try{navigator.clipboard.writeText(text).then(function(){copy.textContent="کپی شد"},sel)}catch(x){sel()}
    });
    acts.appendChild(copy);
    document.getElementById("f-out").hidden=false;
  });
})();


/* چرخش نام صنف در تیتر اصلی */
(function(){
  var el=document.getElementById("rot"); if(!el)return;
  var words=(F.rotator||[]); if(words.length<2)return;
  if(matchMedia("(prefers-reduced-motion: reduce)").matches)return;
  var i=0; setInterval(function(){el.classList.add("out");setTimeout(function(){i=(i+1)%words.length;el.textContent=words[i];el.classList.remove("out")},320)},2600);
})();

/* موتور توضیحات پویا: متن معرفی را با فرمول‌های فروش (PAS، AIDA، BAB، FAB، 4P) و لحن‌های مختلف برای هر صنف می‌سازد. */
(function(){
  var box=document.getElementById("pitch"); if(!box||!F.engine)return;
  var E=F.engine, st={role:Object.keys(E.roles)[0],tone:Object.keys(E.tones)[0],formula:"auto"}, last={};
  function esc(s){return String(s).replace(/[&<>"]/g,function(c){return{"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;"}[c]})}
  function pick(a,key){ if(!a||!a.length)return ""; var j,n=0; do{j=Math.floor(Math.random()*a.length);n++}while(a.length>1&&last[key]===j&&n<6); last[key]=j; return a[j]; }
  function fill(t,r){ return t.replace(/\{(\w+)\}/g,function(_,k){ if(k==="role")return r.name; if(k==="roles")return r.plural||r.name; if(k==="brand")return E.brand||"فَروَم"; if(k==="offer")return E.offer||""; return pick(r[k],"r_"+k); }); }
  function chips(id,obj,cur,key){
    var el=document.getElementById(id);
    el.innerHTML=Object.keys(obj).map(function(k){return '<button type="button" class="tab" data-k="'+k+'" aria-pressed="'+(k===cur)+'">'+esc(obj[k].name)+'</button>'}).join("");
    el.addEventListener("click",function(e){var b=e.target.closest("button");if(!b)return;st[key]=b.dataset.k;[].forEach.call(el.children,function(x){x.setAttribute("aria-pressed",x===b)});render()});
  }
  var forms=Object.assign({auto:{name:"انتخاب خودکار"}},E.formulas);
  chips("p-roles",E.roles,st.role,"role"); chips("p-tones",E.tones,st.tone,"tone"); chips("p-forms",forms,"auto","formula");
  var out=document.getElementById("p-out"), lab=document.getElementById("p-label"), txt="";
  function render(){
    var r=E.roles[st.role], tone=E.tones[st.tone], fk=st.formula==="auto"?pick(Object.keys(E.formulas),"fk"):st.formula, f=E.formulas[fk];
    var parts=f.steps.map(function(s){ var tpl=pick((tone.blocks||{})[s]||E.tones[Object.keys(E.tones)[0]].blocks[s],"t_"+st.tone+s); return {step:(E.stepNames||{})[s]||s, text:fill(tpl,r)}; });
    lab.textContent=f.name+" · "+tone.name;
    out.innerHTML=parts.map(function(p){return '<p><span class="st">'+esc(p.step)+'</span>'+esc(p.text)+'</p>'}).join("");
    txt=parts.map(function(p){return p.text}).join("\n\n");
    out.classList.remove("flash");void out.offsetWidth;out.classList.add("flash");
  }
  document.getElementById("p-again").addEventListener("click",render);
  var cp=document.getElementById("p-copy");
  cp.addEventListener("click",function(){try{navigator.clipboard.writeText(txt).then(function(){cp.textContent="کپی شد";setTimeout(function(){cp.textContent="کپی متن"},1500)},function(){})}catch(e){}});
  document.getElementById("p-wa").addEventListener("click",function(){ this.href="https://wa.me/"+WHATSAPP+"?text="+encodeURIComponent(txt+"\n\n— از سایت فَروَم"); });
  render();
})();

/* لوگوی سه‌بعدی: روی کامپیوتر با حرکت ماوس به سمت نشانگر می‌چرخد. */
(function(){
  var el=document.getElementById("logo3d"); if(!el)return;
  if(!matchMedia("(hover:hover) and (pointer:fine)").matches||matchMedia("(prefers-reduced-motion: reduce)").matches)return;
  var hero=el.closest(".hero")||document.body;
  hero.addEventListener("mousemove",function(e){var r=el.getBoundingClientRect(),dx=(e.clientX-(r.left+r.width/2))/innerWidth,dy=(e.clientY-(r.top+r.height/2))/innerHeight;
    el.style.transform="rotateY("+(dx*40).toFixed(1)+"deg) rotateX("+(-dy*30).toFixed(1)+"deg)"});
  hero.addEventListener("mouseleave",function(){el.style.transform=""});
})();

/* v5: A/B headline, calculator → form, conversion code, offer countdown, video modal */
(function(){
  var F=window.FARVAM||{};
  // A/B: one headline variant per visitor, kept in this browser and sent with the lead
  var h=document.getElementById("h1ab");
  if(h){ try{ var v=JSON.parse(h.dataset.variants||"[]"); if(v.length>1){ var k=null; try{k=localStorage.getItem("farvam-ab")}catch(e){}
      if(k===null||!v[+k]){k=String(Math.floor(Math.random()*v.length)); try{localStorage.setItem("farvam-ab",k)}catch(e){}}
      h.innerHTML=v[+k]; window.FARVAM_AB="تیتر "+(+k+1); } }catch(e){} }
  // calculator result goes into the form note
  var cb=document.getElementById("c-to-form");
  if(cb) cb.addEventListener("click",function(){
    var y=(document.getElementById("c-year-g")||{}).textContent||"", t=(document.getElementById("c-year-t")||{}).textContent||"";
    var note=document.getElementById("f-note"); if(note){ note.value="برآورد نشتی سالانه من: "+y+" گرم"+(/\d|[۰-۹]/.test(t)&&t.indexOf("تومان")>-1?" (حدود "+t+")":"")+". می‌خواهم با کارشناس بررسی کنم."; }
    var d=document.getElementById("demo"); if(d) d.scrollIntoView({behavior:"smooth"}); setTimeout(function(){var n=document.getElementById("f-name"); if(n) n.focus({preventScroll:true})},600);
  });
  // countdown to offer deadline
  [].forEach.call(document.querySelectorAll(".deadline"),function(el){
    var end=Date.parse(el.dataset.deadline), out=el.querySelector(".cd"); if(!end||!out)return;
    var fa=function(n){return Number(n).toLocaleString("fa-IR")};
    function tick(){var s=Math.max(0,Math.floor((end-Date.now())/1000)); if(!s){el.hidden=true;return}
      out.textContent=fa(Math.floor(s/86400))+" روز و "+fa(Math.floor(s%86400/3600))+" ساعت مانده"; }
    tick(); setInterval(tick,60000);
  });
  // hero video modal (loads the player only when opened)
  var vb=document.getElementById("vopen"), vm=document.getElementById("vmodal");
  if(vb&&vm){ var fr=vm.querySelector(".vframe");
    vb.addEventListener("click",function(){ if(!fr.innerHTML) fr.innerHTML=fr.dataset.embed; if(vm.showModal) vm.showModal(); else vm.setAttribute("open",""); });
    vm.addEventListener("close",function(){ fr.innerHTML=""; });
    vm.addEventListener("click",function(e){ if(e.target===vm) vm.close(); }); }
})();
