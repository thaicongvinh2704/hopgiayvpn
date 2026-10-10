
(function(){
  const root=document.getElementById('vpn-box-size-calculator'); if(!root)return;
  const field=n=>root.querySelector('[name="'+n+'"]');
  const out=id=>document.getElementById(id);
  const number=n=>Number(field(n).value);
  const nice=(n,d=1)=>Number.isFinite(n)?n.toFixed(d):'—';
  const ceilDimension=n=>Math.ceil(n-0.000000001);
  function compute(){
    const mmPerUnit=field('unit').value==='in'?25.4:1;
    const lengths=['pL','pW','pH'].map(number);
    const clearance=number('clear'),padding=number('insert'),caliper=number('wall'),mass=number('weight');
    if(lengths.some(v=>!Number.isFinite(v)||v<=0)||[clearance,padding,caliper,mass].some(v=>!Number.isFinite(v)||v<0)){
      out('vpn-calc-id').textContent='Enter positive product dimensions and nonnegative allowances.';
      ['vpn-calc-od','vpn-calc-cube','vpn-calc-metric','vpn-calc-imperial','vpn-calc-compare','vpn-calc-brief'].forEach(id=>out(id).textContent='');out('vpn-calc-brief').value='';return;
    }
    const ID=lengths.map(v=>(v+2*(clearance+padding))*mmPerUnit);
    const OD=ID.map(v=>v+2*caliper*mmPerUnit);
    const unit=field('unit').value;
    const display=a=>a.map(v=>nice(v/mmPerUnit,unit==='in'?3:1)).join(' × ');
    const met=OD.map(v=>ceilDimension(v/10));
    const imp=OD.map(v=>ceilDimension(v/25.4));
    const dkg=met.reduce((a,b)=>a*b,1)/5000;
    const dlb=imp.reduce((a,b)=>a*b,1)/139;
    const cube=OD.reduce((a,b)=>a*b,1)/1000000000;
    out('vpn-calc-id').textContent='Estimated internal (ID): '+display(ID)+' '+unit;
    out('vpn-calc-od').textContent='Estimated closed external (OD): '+display(OD)+' '+unit;
    out('vpn-calc-cube').textContent='External geometric volume: '+nice(cube,5)+' m³ per box (approximate)';
    out('vpn-calc-metric').textContent='Metric DIM illustration: '+met.join(' × ')+' cm / 5,000 = '+nice(dkg,3)+' kg before weight-increment rounding; actual packed weight '+nice(mass,2)+' kg.';
    out('vpn-calc-imperial').textContent='Imperial DIM illustration: '+imp.join(' × ')+' in / 139 = '+nice(dlb,3)+' lb before weight-increment rounding; actual packed weight '+nice(mass*2.20462262,2)+' lb.';
    let compare='Enter all three current external dimensions to compare parcel volume and DIM weight.';
    const old=['oldL','oldW','oldH'].map(n=>field(n).value.trim()===''?NaN:number(n));
    if(old.every(v=>Number.isFinite(v)&&v>0)){
      const oldMM=old.map(v=>v*mmPerUnit);
      const oldCBM=oldMM.reduce((a,b)=>a*b,1)/1000000000;
      const oldMetric=oldMM.map(v=>ceilDimension(v/10)).reduce((a,b)=>a*b,1)/5000;
      const delta=oldCBM>0?100*(oldCBM-cube)/oldCBM:0;
      compare='Compared with current OD: '+nice(delta,1)+'% change in geometric cube (positive means smaller); metric raw DIM '+nice(oldMetric,3)+' → '+nice(dkg,3)+' kg. A lower raw DIM does not necessarily change the carrier-billed weight or price.';
    }
    out('vpn-calc-compare').textContent=compare;
    out('vpn-calc-brief').value='Custom shipping box sizing brief\nPacked product: '+lengths.join(' × ')+' '+unit+'\nFit clearance / side: '+clearance+' '+unit+'; separate insert or pad / side: '+padding+' '+unit+'\nTarget nominal box ID: '+display(ID)+' '+unit+'\nEstimated closed OD (verify sample): '+display(OD)+' '+unit+'\nAssumed board caliper: '+caliper+' '+unit+' per wall\nEstimated complete packed weight: '+mass+' kg\nShipment market/service, carton style, quantity, insert drawing, flute/grade, closure, testing and dimensional tolerance: TO CONFIRM';
  }
  root.querySelectorAll('input').forEach(el=>el.addEventListener('input',compute));
  let previousUnit=field('unit').value;
  field('unit').addEventListener('change',function(){
    const next=field('unit').value;
    const factor=previousUnit==='mm'&&next==='in'?1/25.4:previousUnit==='in'&&next==='mm'?25.4:1;
    ['pL','pW','pH','clear','insert','wall','oldL','oldW','oldH'].forEach(name=>{
      const item=field(name);if(item.value.trim()==='')return;
      item.value=nice(Number(item.value)*factor,next==='in'?4:2);
    });
    previousUnit=next;compute();
  });
  compute();
})();
