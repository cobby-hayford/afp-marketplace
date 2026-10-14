import { Link } from '@inertiajs/react';
import Pagination from '../../../components/Pagination';
import PortalLayout from '../../../layouts/PortalLayout';

export default function Messages({conversations}:{conversations:any}) {
  return <PortalLayout title="Messages">
    <div className="grid gap-3">
      {conversations.data.length === 0 ? <div className="surface p-8 text-center"><h2 className="editorial text-2xl">No conversations yet</h2><p className="mt-2 text-sm text-[#918d84]">Private conversations begin from approved view requests, buying-request responses and collaboration connections.</p></div> : conversations.data.map((conversation:any) => {
        const last = conversation.messages?.[0];
        return <Link key={conversation.id} href={`/messages/${conversation.id}`} className="surface block p-5 hover:border-[#8e7848]">
          <div className="flex flex-wrap items-center justify-between gap-3"><div><div className="font-semibold">{conversation.participants.map((p:any)=>`${p.first_name} ${p.last_name}`).join(', ')}</div><div className="mt-1 text-xs uppercase tracking-[.13em] text-[#877b5d]">{conversation.type.replaceAll('_',' ')}</div></div>{conversation.last_message_at&&<time className="text-xs text-[#7e7a72]">{new Date(conversation.last_message_at).toLocaleString()}</time>}</div>
          <p className="mt-3 truncate text-sm text-[#9f9a91]">{last?.body || 'Conversation opened — send the first message.'}</p>
        </Link>;
      })}
    </div>
    <Pagination page={conversations}/>
  </PortalLayout>;
}
