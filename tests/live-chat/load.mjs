import { readFile, writeFile, mkdir } from 'node:fs/promises';
import { performance } from 'node:perf_hooks';
const fixtures=JSON.parse(await readFile(new URL('./runtime/load-fixtures.json',import.meta.url),'utf8'));
const base='http://127.0.0.1:8091';const wait=ms=>new Promise(resolve=>setTimeout(resolve,ms));
const reports=[];
for(const count of [5,10,20,30]){
  const started=performance.now(),duration=15000,samples=[],errors=[];
  async function loop(kind,interval,request){
    while(performance.now()-started<duration){
      const tick=performance.now();
      try{const res=await request();await res.text();const elapsed=performance.now()-tick;samples.push({kind,ms:elapsed,status:res.status});if(!res.ok)errors.push(res.status);}catch(e){errors.push(e.message);}
      await wait(Math.max(0,interval-(performance.now()-tick)));
    }
  }
  const jobs=fixtures.guests.slice(0,count).map(g=>loop('guest',3000,()=>fetch(`${base}/wp-json/vpn-chat/v1/guest/sync?id=${g.id}&cursor=0`,{headers:{Cookie:g.cookie},signal:AbortSignal.timeout(12000)})));
  for(const agent of fixtures.sales)jobs.push(loop('sales',5000,()=>fetch(`${base}/wp-json/vpn-chat/v1/agent/sync`,{method:'POST',headers:{Cookie:agent.cookie,'X-WP-Nonce':agent.nonce,Origin:base,'Content-Type':'application/json'},body:JSON.stringify({filter:'unassigned',presence:'available'}),signal:AbortSignal.timeout(12000)})));
  jobs.push(loop('website',2000,()=>fetch(base,{signal:AbortSignal.timeout(12000)})));
  await Promise.all(jobs);const times=samples.filter(s=>s.kind!=='website').map(s=>s.ms).sort((a,b)=>a-b);const p95=times[Math.max(0,Math.ceil(times.length*.95)-1)];
  const report={guests:count,sales:3,guest_interval_ms:3000,sales_interval_ms:5000,duration_ms:Math.round(performance.now()-started),api_requests:times.length,p95_ms:Math.round(p95),max_ms:Math.round(times.at(-1)),errors};reports.push(report);console.log(JSON.stringify(report));
}
await mkdir(new URL('./results/',import.meta.url),{recursive:true});await writeFile(new URL('./results/load-report.json',import.meta.url),JSON.stringify({environment:'Local Windows PHP built-in single-worker server; not representative of shared hosting. No production requests.',reports},null,2));
if(reports.some(r=>r.errors.length))process.exitCode=1;
