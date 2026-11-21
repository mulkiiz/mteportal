/* Pure scoring engine. Percentages in formulas use proportions (40% = 0.4). */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) module.exports = factory();
  else root.MTESimulation = factory();
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
  'use strict';
  const DEFAULTS = {
    ndtps:10, ndt:10, ndtt:0, gb:1, lk:3, nkdtps:3, nm:40,
    d_na1:2,d_na2:12,d_na3:2,d_na4:4,d_nb1:2,d_nb2:2,d_nb3:1,
    m_na1:2,m_na2:6,m_na3:1,m_na4:2,m_nb1:2,m_nb2:1,m_nb3:1,firstpub:2,
    graduates:30,respondents:12,aligned:6,
    bop:21000000,dpd:15000000,dpkm:3750000,
    k_edu:10,k_research:8,k_pkm:5,k_int:1,k_nat:4,k_local:5,
    r_int:1,r_nat:10,r_local:19,r_students:15,r_thesis:6,
    p_int:1,p_nat:4,p_local:5,p_students:4,
    rbk:16,d_patent:1,d_ttg:2,d_book:2,d_hki:4,cited:3,recognition:5,
    foreign:0,ipk:3.4,ms:2.7,cohort:20,ontime:9,ach_int:0,ach_nat:1,ach_local:2,
    m_patent:0,m_ttg:4,m_book:4,m_hki:4
  };
  const GROUPS={core:[25,26,31,33,43,46],finance:[9,10,11],research:[6,21,22,24,29,30],staff:[28,32,34,35],students:[38,39,40,41,42,44]};
  const optional=['finance','research','staff','students'];
  const fraction=x=>x;
  const ratio=(a,b)=>b>0?a/b:null;
  // The source uses strict > for the score-4 branch. At equality the composite formula applies.
  function composite(ri,rn,rw,a,b,c){
    if(ri===null||rn===null||rw===null)return null;
    if(ri>a&&rn>b)return 4;
    const A=Math.min(1,ri/a),B=Math.min(1,rn/b),C=Math.min(1,rw/c);
    return 3.75*(A+B+C/2-A*B-A*C/2-B*C/2+A*B*C/2);
  }
  function pub(x,prefix,denom,a,b,c){
    return composite(ratio(x[prefix+'na4']+x[prefix+'nb3'],denom),ratio(x[prefix+'na2']+x[prefix+'na3']+x[prefix+'nb2'],denom),ratio(x[prefix+'na1']+x[prefix+'nb1'],denom),a,b,c);
  }
  function calculate(input, enabled, items){
    const x={...input},groups={finance:false,research:false,staff:false,students:false,...enabled};
    const errors=[], warnings=[];
    const required=['ndtps','ndt','ndtt','gb','lk','nkdtps','nm','graduates','respondents','aligned','firstpub',...Object.keys(DEFAULTS).filter(k=>/^[dm]_n[ab]/.test(k))];
    const groupKeys={finance:['bop','dpd','dpkm'],research:['k_edu','k_research','k_pkm','k_int','k_nat','k_local','r_int','r_nat','r_local','r_students','r_thesis','p_int','p_nat','p_local','p_students'],staff:['rbk','d_patent','d_ttg','d_book','d_hki','cited','recognition'],students:['foreign','ipk','ms','cohort','ontime','ach_int','ach_nat','ach_local','m_patent','m_ttg','m_book','m_hki']};
    optional.forEach(g=>{if(groups[g])required.push(...groupKeys[g]);});
    const decimals=new Set(['bop','dpd','dpkm','rbk','ipk','ms']);
    required.forEach(k=>{if(typeof x[k]!=='number'||!Number.isFinite(x[k])||x[k]<0||(!decimals.has(k)&&!Number.isSafeInteger(x[k])))errors.push('Periksa input '+k+': gunakan angka '+(decimals.has(k)?'':'bulat ')+'nol atau lebih.');});
    if(x.ndtps<1||x.ndt<1)errors.push('NDTPS dan NDT harus lebih dari nol.');
    if(x.nm<1)errors.push('Jumlah mahasiswa pada TS harus lebih dari nol.');
    if(x.ndtps>x.ndt)errors.push('NDTPS tidak boleh melebihi jumlah dosen tetap pengampu (NDT).');
    if(x.gb+x.lk>x.ndtps)errors.push('Jumlah Guru Besar + Lektor Kepala tidak boleh melebihi DTPS.');
    if(x.nkdtps>x.ndtps)errors.push('DTPS penulis pertama/korespondensi tidak boleh melebihi DTPS.');
    if(x.graduates<1||x.respondents<1)errors.push('Jumlah lulusan dan responden tracer harus lebih dari nol.');
    if(x.respondents>x.graduates)errors.push('Responden tracer tidak boleh melebihi jumlah lulusan.');
    if(x.aligned>x.respondents)errors.push('Lulusan bekerja sesuai bidang tidak boleh melebihi responden tracer.');
    const international=x.m_na3+x.m_na4+x.m_nb2+x.m_nb3;
    if(x.firstpub>international)errors.push('Publikasi internasional mahasiswa sebagai penulis pertama tidak boleh melebihi total kategori internasional mahasiswa (NA3 + NA4 + NB2 + NB3).');
    const nr=x.r_int+x.r_nat+x.r_local,np=x.p_int+x.p_nat+x.p_local;
    if(groups.research){
      if(nr<1||np<1)errors.push('Jumlah penelitian dan PkM harus lebih dari nol untuk menghitung persentase keterlibatan mahasiswa.');
      if(x.r_students>nr||x.r_thesis>nr)errors.push('Judul penelitian yang melibatkan mahasiswa/merujuk tesis tidak boleh melebihi total penelitian.');
      if(x.p_students>np)errors.push('PkM yang melibatkan mahasiswa tidak boleh melebihi total PkM.');
    }
    if(groups.students){
      if(x.foreign>x.nm)errors.push('Mahasiswa asing tidak boleh melebihi total mahasiswa pada TS.');
      if(x.ipk>4)errors.push('IPK harus pada rentang 0–4.');
      if(x.cohort<1||x.ontime>x.cohort)errors.push('Kohort harus lebih dari nol dan lulusan tepat waktu tidak boleh melebihi kohort.');
    }
    const active=new Set(GROUPS.core);
    optional.forEach(g=>{if(groups[g])GROUPS[g].forEach(id=>active.add(id));});
    const raw={};items.forEach(item=>{raw[item.id]=item.weights.map(()=>3);});
    const metrics={pdtt:ratio(x.ndtt,x.ndt+x.ndtt),pgblk:ratio(x.gb+x.lk,x.ndtps),pkdtps:ratio(x.nkdtps,x.ndtps),pubFirstRatio:ratio(x.firstpub,x.nm),kbk:ratio(x.aligned,x.respondents),response:ratio(x.respondents,x.graduates)};
    const put=(id,value)=>raw[id]=Array.isArray(value)?value:[value];
    if(errors.length)return {errors,warnings,active:[...active],max:300+items.filter(it=>active.has(it.id)).reduce((s,it)=>s+it.weight,0),metrics};
    // Literal A denominator 7 from matrix p.18. B: first definition for 5–7 DTPS;
    // second definition for >=8 DTPS with PDTT >10%, matching the two branches.
    let s25;
    if(x.ndtps<5)s25=0;
    else if(metrics.pdtt>0.4)s25=2;
    else if(x.ndtps>=8&&metrics.pdtt<=0.1)s25=4;
    else if(x.ndtps<8)s25=2+2*((x.ndtps-5)/7)*((0.4-metrics.pdtt)/0.4);
    else s25=2+2*((0.4-metrics.pdtt)/0.3);
    put(25,s25);put(26,metrics.pgblk>=0.7?4:2+20*metrics.pgblk/7);
    put(33,metrics.pkdtps>=0.5?4:1+6*metrics.pkdtps);
    put(31,pub(x,'d_',x.ndtps,0.5,4,4));put(43,pub(x,'m_',x.nm,0.1,0.3,0.9));
    put(46,metrics.kbk>=0.6?4:20*metrics.kbk/3);
    if(x.ndtps<8)warnings.push('Butir 25 memakai A = (NDTPS − 5) / 7 persis seperti PDF. Definisi B bertumpang tindih pada sumber; konvensi cabang dijelaskan di Sumber & metode.');
    if(metrics.response<0.3)warnings.push('Responden tracer kurang dari 30% lulusan: bukti butir 46 belum memenuhi cakupan minimum. Skor numerik tetap ditampilkan, tetapi syarat Unggul ditandai belum lolos.');
    if(groups.finance){put(9,Math.min(4,x.bop/7000000));put(10,Math.min(4,x.dpd/5000000));put(11,Math.min(4,x.dpkm/1250000));}
    if(groups.research){
      put(6,[Math.min(4,(x.k_edu+x.k_research+x.k_pkm)/x.ndtps),composite(x.k_int,x.k_nat,x.k_local,2,6,8)]);
      put(21,Math.min(4,1+4*x.r_students/nr));put(22,Math.min(4,1+12*x.r_thesis/nr));put(24,Math.min(4,1+6*x.p_students/np));
      put(29,composite(x.r_int/3/x.ndtps,x.r_nat/3/x.ndtps,x.r_local/3/x.ndtps,0.07,0.5,1.5));
      put(30,composite(x.p_int/3/x.ndtps,x.p_nat/3/x.ndtps,x.p_local/3/x.ndtps,0.07,0.5,1.5));
    }
    if(groups.staff){
      put(28,x.rbk>=12&&x.rbk<=16?4:x.rbk>16&&x.rbk<=20?(64-3*x.rbk)/4:null);
      if(raw[28][0]===null)errors.push('RBK di luar 12–20 SKS tidak diberi cabang rumus pada PDF; skor total belum dapat ditetapkan.');
      const rlp=(3*x.d_patent+2*(x.d_ttg+x.d_book)+x.d_hki)/x.ndtps;
      put(32,Math.min(4,2+2*rlp/3));put(34,Math.min(4,2+4*x.cited/x.ndtps));put(35,Math.min(4,2+2*x.recognition/x.ndtps));
    }
    if(groups.students){
      put(38,Math.min(4,2+200*x.foreign/x.nm));put(39,x.ipk>=3.5?4:x.ipk>=3?4*x.ipk-10:null);
      if(raw[39][0]===null)errors.push('RIPK <3,00 tidak diberi cabang rumus pada PDF; skor total belum dapat ditetapkan.');
      put(40,composite(x.ach_int/x.nm,x.ach_nat/x.nm,x.ach_local/x.nm,0.002,0.02,0.04));
      put(41,x.ms<1.5?0:x.ms>1.5&&x.ms<=2.5?4:x.ms>2.5&&x.ms<=4?9-2*x.ms:null);
      if(raw[41][0]===null)errors.push('MS =1,5 atau MS >4 tidak diberi cabang rumus pada PDF; skor total belum dapat ditetapkan.');
      const p=x.ontime/x.cohort;put(42,p>=0.6?4:p>=0.2?1+(30*p-6)/4:null);
      if(raw[42][0]===null)errors.push('PTW <20% tidak diberi rumus eksplisit pada PDF; skor total belum dapat ditetapkan.');
      const nlp=(3*x.m_patent+2*(x.m_ttg+x.m_book)+x.m_hki)/x.nm;put(44,Math.min(4,2+nlp));
    }
    const rows=items.map(item=>{
      const scores=raw[item.id];
      const contribution=scores.some(s=>s===null)?null:scores.reduce((sum,s,i)=>sum+s*item.weights[i],0);
      let score=scores.some(s=>s===null)?null:scores.reduce((a,b)=>a+b,0)/scores.length;
      if(item.id===6)score=(scores[0]+2*scores[1])/3;
      return {id:item.id,score,scores,weight:item.weight,contribution,computed:active.has(item.id)};
    });
    const na=errors.length?null:rows.reduce((s,r)=>s+r.contribution,0);
    // Syarat perlu averages are over the raw indicators in tables 5.1–5.3,
    // not the weighted overall NA. All qualitative indicators remain assumption 3.
    const gates=[
      {id:'pamong',label:'Sistem tata pamong',value:3,target:3,pass:true,assumed:true,items:[4,5]},
      {id:'kurikulum',label:'Kurikulum',value:3,target:3,pass:true,assumed:true,items:[12,13,17]},
      {id:'jabatan',label:'Guru Besar + Lektor Kepala',value:metrics.pgblk,target:0.4,pass:metrics.pgblk>=0.4,items:[26]},
      {id:'kinerja',label:'DTPS penulis pertama/korespondensi',value:metrics.pkdtps,target:0.25,pass:metrics.pkdtps>=0.25,items:[33]},
      {id:'publikasi',label:'Publikasi internasional mahasiswa penulis pertama / NM',value:metrics.pubFirstRatio,target:0.05,pass:metrics.pubFirstRatio>=0.05,items:[43]},
      {id:'kerja',label:'Kesesuaian bidang kerja',value:metrics.kbk,target:0.5,pass:metrics.kbk>=0.5&&metrics.response>=0.3,items:[46]},
      {id:'mutu',label:'Penjaminan mutu',value:3,target:3,pass:true,assumed:true,items:[48,49,50,51,52]}
    ];
    const pass=na!==null&&na>=331&&gates.every(g=>g.pass);
    const max=300+items.filter(it=>active.has(it.id)).reduce((s,it)=>s+it.weight,0);
    return {errors,warnings,rows,na,max,pass,gates,metrics,active:[...active],computedCount:active.size,assumedCount:55-active.size};
  }
  function targetExample(){return {values:{...DEFAULTS,gb:2,lk:5,nkdtps:5,
    d_na4:6,d_nb3:0,d_na2:41,d_na3:0,d_nb2:0,m_na4:5,m_nb3:0,m_na2:13,m_na3:0,m_nb2:0,firstpub:4,aligned:8,
    bop:28000000,dpd:20000000,dpkm:5000000,k_edu:15,k_research:15,k_pkm:10,k_int:3,k_nat:7,
    r_int:3,r_nat:16,r_local:11,r_students:23,r_thesis:8,p_int:3,p_nat:16,p_local:11,p_students:15,
    rbk:14,d_patent:10,cited:5,recognition:10,foreign:1,ipk:3.6,ms:2,ontime:12,ach_int:1,ach_nat:1,
    m_patent:20,m_ttg:10,m_book:0,m_hki:0},enabled:{finance:true,research:true,staff:true,students:true}};}
  return {DEFAULTS,GROUPS,calculate,composite,targetExample};
});
