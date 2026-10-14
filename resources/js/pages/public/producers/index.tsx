import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import Pagination from '../../../components/Pagination';
import Seo from '../../../components/Seo';
import PublicLayout from '../../../layouts/PublicLayout';
import type { Paginator } from '../../../types';

type Option={id:number;name:string};
type Options={countries:Option[];contentTypes:Option[];genres:Option[];languages:Option[];roles:string[]};

export default function Producers({producers,filters,options,seo,basePath='/producers',pageTitle='Producers Across Africa',pageIntro}:{producers:Paginator<any>;filters:Record<string,string>;options:Options;seo:any;basePath?:string;pageTitle?:string;pageIntro?:string}){
  const [form,setForm]=useState<Record<string,string>>({...filters});
  const submit=(event:FormEvent)=>{event.preventDefault();router.get(basePath,Object.fromEntries(Object.entries(form).filter(([,value])=>value)),{preserveState:true,replace:true})};
  return <PublicLayout><Seo seo={seo}/><section className="container-afp py-12">
    <div className="max-w-3xl"><div className="text-xs uppercase tracking-[.2em] text-[#8e7b50]">Professional Directory</div><h1 className="editorial mt-1 text-5xl">{pageTitle}</h1><p className="mt-3 text-[#9f9a90]">{pageIntro}</p></div>
    <form onSubmit={submit} className="surface mt-8 grid gap-3 p-4 md:grid-cols-4 xl:grid-cols-8">
      <input className="input md:col-span-2" placeholder="Search producers" value={form.q||''} onChange={e=>setForm({...form,q:e.target.value})}/>
      {([['Country','country_id',options.countries],['Content Type','content_type_id',options.contentTypes],['Genre','genre_id',options.genres],['Language','language_id',options.languages]] as const).map(([label,key,items])=><select key={key} className="input" aria-label={label} value={form[key]||''} onChange={e=>setForm({...form,[key]:e.target.value})}><option value="">{label}</option>{items.map(option=><option key={option.id} value={option.id}>{option.name}</option>)}</select>)}
      <select className="input" aria-label="Professional role" value={form.role||''} onChange={e=>setForm({...form,role:e.target.value})}><option value="">Role</option>{options.roles.map(role=><option key={role} value={role}>{role}</option>)}</select>
      <label className="flex items-center gap-2 px-2 text-sm"><input type="checkbox" checked={form.verified==='1'} onChange={e=>setForm({...form,verified:e.target.checked?'1':''})}/> Verified</label>
      <button className="btn btn-primary">Filter</button>
      <button type="button" className="btn btn-secondary" onClick={()=>{setForm({});router.get(basePath)}}>Clear</button>
    </form>
    <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">{producers.data.map(profile=><Link key={profile.id} href={`/producers/${profile.slug}`} className="surface flex gap-4 p-4"><div className="h-24 w-20 shrink-0 overflow-hidden rounded-lg bg-[#202226]">{profile.photo_url&&<img src={profile.photo_url} alt={profile.name} className="h-full w-full object-cover"/>}</div><div><div className="flex flex-wrap gap-2"><span className="text-xs text-[#9b978f]">{profile.country}</span>{profile.verified&&<span className="badge badge-gold">Association verified</span>}</div><h2 className="editorial mt-1 text-2xl">{profile.name}</h2><p className="mt-1 text-sm text-[#9b978f]">{profile.roles?.slice(0,2).join(' · ')}</p><p className="mt-2 text-xs text-[#77736c]">{profile.project_count} published project{profile.project_count===1?'':'s'}</p></div></Link>)}</div>
    {producers.data.length===0&&<div className="surface mt-8 p-10 text-center text-[#8f8b83]">No approved producers match these filters.</div>}
    <Pagination page={producers}/>
  </section></PublicLayout>;
}
