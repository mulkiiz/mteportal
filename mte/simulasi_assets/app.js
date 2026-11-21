(function(){
  'use strict';
  const $=id=>document.getElementById(id),all=(s,base=document)=>Array.from(base.querySelectorAll(s));
  const data=JSON.parse($('matrix-data').textContent),engine=window.MTESimulation;
  const fmt=(n,d=2)=>typeof n==='number'&&Number.isFinite(n)?n.toLocaleString('id-ID',{minimumFractionDigits:d,maximumFractionDigits:d}):'—';
  const percent=n=>n===null?'—':fmt(n*100)+'%';
  const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const storageKey='mte-akreditasi-2025-v1:'+location.pathname;
  let currentTab='resume',lastResult=null,toastTimer;
  const inputs=all('[data-key]'),toggles=all('[data-enable]');
  const searchable=new Map(data.map(it=>[it.id,JSON.stringify(it).toLocaleLowerCase('id-ID')]));
  function toast(message){$('toast').textContent=message;$('toast').hidden=false;clearTimeout(toastTimer);toastTimer=setTimeout(()=>{$('toast').hidden=true;},4200);}
  function showTab(name,updateHash=true){
    if(!['resume','unggul3','unggul5','simulasi','sumber'].includes(name))return;
    currentTab=name;
    all('[data-tab]').forEach(button=>{const active=button.dataset.tab===name;button.setAttribute('aria-selected',String(active));button.tabIndex=active?0:-1;});
    all('.tab-panel').forEach(panel=>panel.hidden=panel.id!=='panel-'+name);
    if(updateHash)history.replaceState(null,'','#'+name);
  }
  all('[data-tab]').forEach(button=>{
    button.addEventListener('click',()=>showTab(button.dataset.tab));
    button.addEventListener('keydown',event=>{
      const tabs=all('[data-tab]'),i=tabs.indexOf(button);let j;
      if(event.key==='ArrowRight')j=(i+1)%tabs.length;
      if(event.key==='ArrowLeft')j=(i+tabs.length-1)%tabs.length;
      if(event.key==='Home')j=0;if(event.key==='End')j=tabs.length-1;
      if(j!==undefined){event.preventDefault();showTab(tabs[j].dataset.tab);tabs[j].focus();}
    });
  });
  all('[data-open-tab]').forEach(button=>button.addEventListener('click',()=>{showTab(button.dataset.openTab);$('tab-'+button.dataset.openTab).scrollIntoView({block:'start'});}));
  function filter(){
    const q=$('search').value.trim().toLocaleLowerCase('id-ID'),source=$('source-filter').value,gate=$('gate-filter').value;
    const idMatch=q.match(/^(?:butir\s*|#\s*)?(\d{1,2})$/);
    let visible=0;
    all('.matrix-item').forEach(card=>{
      const id=Number(card.dataset.id),match=(!q||(idMatch?id===Number(idMatch[1]):searchable.get(id).includes(q)))&&(source==='all'||source===card.dataset.source)&&(gate==='all'||card.dataset.gate==='1');
      card.hidden=!match;if(match)visible++;
    });
    all('.matrix-group').forEach(group=>{
      const shown=all('.matrix-item',group).filter(x=>!x.hidden);group.hidden=!shown.length;
      all('.subgroup',group).forEach(h=>h.hidden=!shown.some(x=>x.dataset.subgroup===h.dataset.subgroup));
    });
    $('result-count').textContent=visible+' dari 55 butir';$('empty-results').hidden=visible!==0;
  }
  function resetFilters(){ $('search').value='';$('source-filter').value='all';$('gate-filter').value='all';filter(); }
  $('search').addEventListener('input',filter);$('source-filter').addEventListener('change',filter);$('gate-filter').addEventListener('change',filter);$('reset-filters').addEventListener('click',resetFilters);
  all('[data-group-link]').forEach(link=>link.addEventListener('click',event=>{event.preventDefault();resetFilters();showTab('resume',false);const id='kelompok-'+link.dataset.groupLink;history.replaceState(null,'','#'+id);$(id).scrollIntoView({block:'start'});}));
  function openItem(id,hash=true){
    const item=$('butir-'+id);if(!item)return;
    resetFilters();showTab('resume',false);item.open=true;if(hash)history.replaceState(null,'','#butir-'+id);item.scrollIntoView({block:'start'});
  }
  all('[data-item-link]').forEach(link=>link.addEventListener('click',event=>{event.preventDefault();openItem(link.dataset.itemLink);}));
  $('toggle-items').addEventListener('click',()=>{
    const cards=all('.matrix-item').filter(x=>!x.hidden),open=cards.some(x=>!x.open);cards.forEach(x=>x.open=open);$('toggle-items').textContent=open?'Tutup semua rincian':'Buka semua rincian';
  });
  function collect(){
    const values={};inputs.forEach(input=>{values[input.dataset.key]=input.value.trim()===''?null:Number(input.value);});
    const enabled={};toggles.forEach(input=>enabled[input.dataset.enable]=input.checked);
    return {schema:'mte-simulasi-2025',version:1,values,enabled};
  }
  function applyScenario(scenario){
    inputs.forEach(input=>{const value=scenario.values[input.dataset.key];input.value=value===null||value===undefined?'':String(value);});
    toggles.forEach(input=>input.checked=!!scenario.enabled[input.dataset.enable]);syncGroups();render(false);
  }
  function syncGroups(){toggles.forEach(toggle=>{
    const card=toggle.closest('.input-card');card.classList.toggle('active',toggle.checked);card.querySelector('fieldset').disabled=!toggle.checked;
    card.querySelector('summary small').textContent=toggle.checked?'Aktif · dihitung dari input':'Tidak aktif · diasumsikan skor 3';
  });}
  function save(){
    try{localStorage.setItem(storageKey,JSON.stringify(collect()));$('save-state').textContent='Asumsi tersimpan di browser ini.';}
    catch(error){$('save-state').textContent='Penyimpanan browser tidak tersedia; unduh skenario untuk menyimpan.';}
  }
  function render(shouldSave=true){
    const scenario=collect(),r=engine.calculate(scenario.values,scenario.enabled,data);lastResult=r;
    const valid=!r.errors.length&&r.na!==null&&r.na!==undefined;
    $('na-value').textContent=valid?fmt(r.na):'—';$('na-value').title=valid?'Nilai sebelum pembulatan: '+r.na:'';
    $('na-bar').style.width=valid?Math.max(0,Math.min(100,r.na/4))+'%':'0%';
    $('computed-count').textContent=(r.computedCount||r.active.length)+' butir';$('assumed-count').textContent=(55-r.active.length)+' butir';$('max-value').textContent=fmt(r.max);
    $('simulation-status').textContent=!valid?'Input belum dapat dihitung':r.pass?'Lolos pada skenario asumsi':r.na>=331?'Skor tercapai; syarat belum lengkap':'Belum mencapai target Unggul';
    const gap=valid?Math.max(0,331-r.na):null;
    $('score-gap').textContent=!valid?'Perbaiki input yang ditandai di bawah.':gap>0?'Masih perlu '+(gap<0.01?'<0,01':fmt(gap))+' poin untuk mencapai 331.':'Nilai telah mencapai 331. Tetap periksa semua syarat perlu dan bukti dokumen.';
    const errors=$('input-errors');errors.hidden=!r.errors.length;errors.innerHTML='<strong>Periksa input</strong><ul>'+r.errors.map(x=>'<li>'+esc(x)+'</li>').join('')+'</ul>';
    const warnings=$('simulation-warnings');warnings.hidden=!r.warnings.length;warnings.innerHTML=r.warnings.map(x=>'<p>'+esc(x)+'</p>').join('');
    if(!valid){$('gate-results').innerHTML='<p class="small-text">Pemeriksaan syarat diperbarui setelah input valid.</p>';$('gate-count').textContent='Menunggu input';}
    else{
      const counted=r.gates.filter(g=>!g.assumed),passed=counted.filter(g=>g.pass).length;
      $('gate-count').textContent=passed+'/4 hitung · 3 asumsi';
      $('gate-results').innerHTML=r.gates.map(g=>{
        const value=g.assumed?fmt(g.value):g.id==='publikasi'?fmt(g.value,3):percent(g.value);
        const target=g.assumed?'rerata ≥3,00':g.id==='publikasi'?'rasio ≥0,05':'≥'+percent(g.target);
        const extra=g.id==='kerja'?' · responden '+percent(r.metrics.response):'';
        return '<div class="gate-row '+(!g.pass?'fail':'')+'"><span class="gate-icon">'+(g.assumed?'◌':g.pass?'✓':'!')+'</span><div>'+esc(g.label)+'<small>'+esc(target+extra)+(g.assumed?' · <span class="assumed">Asumsi skor 3</span>':'')+'</small></div><span class="gate-value">'+esc(value)+'</span></div>';
      }).join('');
    }
    $('max-explanation').textContent=r.max<331?'Dengan kelompok yang aktif, skor maksimum '+fmt(r.max)+' belum mencapai 331 meskipun semua hasil hitungan bernilai 4. Aktifkan kelompok tambahan untuk menguji peluang target.':'Dengan seluruh indikator lain tetap 3, maksimum kelompok aktif adalah '+fmt(r.max)+'. Seluruh kelompok aktif memberi batas 335; target Unggul 5 tahun (361) memerlukan peningkatan indikator lainnya.';
    const byId=new Map((r.rows||[]).map(row=>[row.id,row]));
    $('breakdown-body').innerHTML=data.map(it=>{
      const row=byId.get(it.id),computed=r.active.includes(it.id),score=row?fmt(row.score):'—',contribution=row&&valid?fmt(row.contribution):'—';
      const subs=row&&row.scores.length>1?'<small class="subscores">'+row.scores.map((s,i)=>['I','II','III'][i]+': '+fmt(s)).join(' · ')+'</small>':'';
      return '<tr class="'+(computed?'calculated':'')+'"><td><a href="#butir-'+it.id+'" data-item-link="'+it.id+'">'+String(it.id).padStart(2,'0')+'</a></td><td>'+esc(it.title)+'</td><td><span class="badge '+(computed?'lkps':'neutral')+'">'+(computed?'Dari input':'Asumsi 3')+'</span></td><td>'+score+subs+'</td><td>'+fmt(it.weight)+'</td><td>'+contribution+'</td></tr>';
    }).join('');
    $('breakdown-total').textContent=valid?fmt(r.na):'—';
    if(shouldSave)save();
  }
  $('breakdown-body').addEventListener('click',event=>{const link=event.target.closest('[data-item-link]');if(link){event.preventDefault();openItem(link.dataset.itemLink);}});
  inputs.forEach(input=>input.addEventListener('input',()=>render()));
  toggles.forEach(toggle=>toggle.addEventListener('change',()=>{syncGroups();render();}));
  $('activate-all').addEventListener('click',()=>{toggles.forEach(x=>x.checked=true);syncGroups();render();toast('Seluruh kelompok angka diaktifkan. Indikator lainnya tetap 3.');});
  $('load-target').addEventListener('click',()=>{applyScenario(engine.targetExample());save();toast('Contoh target dimuat. Ini angka ilustrasi, bukan data aktual MTE.');});
  $('reset-simulation').addEventListener('click',()=>{applyScenario({values:engine.DEFAULTS,enabled:{}});save();toast('Kembali ke contoh awal. Kelompok tambahan tidak aktif.');});
  $('export-scenario').addEventListener('click',()=>{
    const scenario=collect();scenario.savedAt=new Date().toISOString();scenario.scope='Instrumen Magister 2025; indikator lainnya diasumsikan 3';
    const blob=new Blob([JSON.stringify(scenario,null,2)],{type:'application/json'}),url=URL.createObjectURL(blob),link=document.createElement('a');
    link.href=url;link.download='skenario-mte-unggul-3-tahun.json';link.click();setTimeout(()=>URL.revokeObjectURL(url),1000);
  });
  function validateScenario(value){
    if(!value||value.schema!=='mte-simulasi-2025'||value.version!==1||!value.values||!value.enabled)throw new Error('Format skenario tidak sesuai versi halaman ini.');
    const clean={values:{},enabled:{}};
    Object.keys(engine.DEFAULTS).forEach(key=>{const v=value.values[key];if(v!==null&&(typeof v!=='number'||!Number.isFinite(v)||v<0))throw new Error('Nilai '+key+' tidak valid.');clean.values[key]=v;});
    toggles.forEach(toggle=>{const key=toggle.dataset.enable;if(typeof value.enabled[key]!=='boolean'&&value.enabled[key]!==undefined)throw new Error('Pilihan kelompok tidak valid.');clean.enabled[key]=value.enabled[key]===true;});
    return clean;
  }
  $('import-scenario').addEventListener('change',async event=>{
    const input=event.target,file=input.files[0];if(!file)return;
    try{if(file.size>100000)throw new Error('Berkas terlalu besar. Maksimum 100 KB.');const scenario=validateScenario(JSON.parse(await file.text()));applyScenario(scenario);save();toast('Skenario berhasil dimuat.');}
    catch(error){toast('Skenario tidak dimuat: '+error.message);}input.value='';
  });
  $('print-page').addEventListener('click',()=>window.print());
  let restored=false;
  try{const saved=localStorage.getItem(storageKey);if(saved){applyScenario(validateScenario(JSON.parse(saved)));restored=true;}}
  catch(error){$('save-state').textContent='Skenario lama tidak terbaca; contoh awal dimuat.';}
  if(!restored)applyScenario({values:engine.DEFAULTS,enabled:{}});
  function route(){const hash=location.hash.slice(1);if(/^butir-\d+$/.test(hash))openItem(Number(hash.slice(6)),false);else if(/^kelompok-\d+$/.test(hash)){showTab('resume',false);const group=$(hash);if(group)group.scrollIntoView({block:'start'});}else showTab(hash||'resume',false);}
  window.addEventListener('hashchange',route);route();
})();
