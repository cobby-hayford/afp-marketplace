import { Link, router, useForm, usePage } from '@inertiajs/react';
import { FormEvent, useEffect } from 'react';
import PortalLayout from '../../../layouts/PortalLayout';
import type { PageProps } from '../../../types';

export default function Conversation({conversation,messages}:{conversation:any;messages:any}) {
  const {auth}=usePage<PageProps>().props;
  const form=useForm({body:''});
  useEffect(()=>{
    if(!window.Echo)return;
    const channel=window.Echo.private(`conversation.${conversation.id}`);
    channel.listen('.message.created',()=>router.reload({only:['messages','notifications']}));
    return ()=>{window.Echo?.leave(`conversation.${conversation.id}`)};
  },[conversation.id]);
  const submit=(e:FormEvent)=>{e.preventDefault();form.post(`/messages/${conversation.id}`,{preserveScroll:true,onSuccess:()=>form.reset()})};
  return <PortalLayout title="Private Conversation">
    <div className="mb-4 flex flex-wrap items-center justify-between gap-3 surface p-4"><div><div className="text-sm text-[#8e8a82]">Participants</div><strong>{conversation.participants.map((p:any)=>`${p.first_name} ${p.last_name}`).join(', ')}</strong></div><Link href="/messages" className="btn btn-secondary">All Conversations</Link></div>
    <section className="surface overflow-hidden">
      <div className="max-h-[58vh] space-y-3 overflow-y-auto p-4 md:p-6">
        {messages.data.map((message:any)=><div key={message.id} className={`flex ${message.sender_id===auth.user?.id?'justify-end':'justify-start'}`}><div className={`max-w-[85%] rounded-2xl px-4 py-3 ${message.sender_id===auth.user?.id?'bg-[#6e592d] text-white':'bg-white/5'}`}><div className="mb-1 text-xs opacity-70">{message.sender?.first_name} {message.sender?.last_name}</div><p className="whitespace-pre-wrap text-sm leading-6">{message.body}</p><time className="mt-1 block text-[11px] opacity-60">{new Date(message.created_at).toLocaleString()}</time></div></div>)}
      </div>
      <form onSubmit={submit} className="border-t border-white/10 p-4"><label htmlFor="message-body" className="sr-only">Message</label><textarea id="message-body" className="input min-h-24" maxLength={5000} value={form.data.body} onChange={e=>form.setData('body',e.target.value)} placeholder="Write a private message…"/><div className="mt-3 flex items-center justify-between gap-3"><p className="text-xs text-[#79756e]">Keep commercial negotiations and due diligence professional. Deals are concluded outside the platform.</p><button className="btn btn-primary" disabled={form.processing||!form.data.body.trim()}>Send</button></div>{form.errors.body&&<p className="mt-2 text-sm text-red-400">{form.errors.body}</p>}</form>
    </section>
  </PortalLayout>;
}
