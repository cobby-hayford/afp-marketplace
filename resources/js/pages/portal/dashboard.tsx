import type { LucideIcon } from 'lucide-react';
import { Link } from '@inertiajs/react'; import { Bell, Bookmark, FileClock, Film, MessageSquare, Plus } from 'lucide-react'; import PortalLayout from '../../layouts/PortalLayout';
export default function Dashboard({stats,recentProjects,recentRequests}:{stats:Record<string,number>;recentProjects:any[];recentRequests:any[]}){const cards: {
    icon: LucideIcon;
    label: string;
    value: number;
}[] = [
    {
        icon: Film,
        label: 'Published Projects',
        value: stats.published,
    },
    {
        icon: FileClock,
        label: 'Pending Projects',
        value: stats.pending,
    },
    {
        icon: Plus,
        label: 'Draft Projects',
        value: stats.draft,
    },
    {
        icon: Bell,
        label: 'View Requests',
        value: stats.view_requests,
    },
    {
        icon: MessageSquare,
        label: 'Unread Messages',
        value: stats.unread_messages,
    },
    {
        icon: Bookmark,
        label: 'Saved Projects',
        value: stats.saved_projects,
    },
];return <PortalLayout title="Dashboard"><div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">{cards.map(({ icon: Icon, label, value }) =><div key={String(label)} className="surface p-4"><Icon size={19} className="text-[#c8a85d]"/><div className="mt-5 text-3xl font-bold">{Number(value)}</div><div className="mt-1 text-xs text-[#8f8b83]">{String(label)}</div></div>)}</div><div className="mt-7 flex flex-wrap gap-2"><Link href="/my-projects/create" className="btn btn-primary"><Plus size={16}/> Add Project</Link><Link href="/profile" className="btn btn-secondary">Edit Profile</Link><Link href="/my-stand" className="btn btn-secondary">View My Stand</Link><Link href="/projects" className="btn btn-secondary">Browse Marketplace</Link></div><div className="mt-9 grid gap-7 lg:grid-cols-2"><Panel title="Recent Projects">{recentProjects.length?recentProjects.map(p=><Link href={`/my-projects/${p.slug}/edit`} key={p.id} className="flex items-center justify-between border-b border-white/10 py-3"><span>{p.title}</span><span className="badge">{p.publication_status.replaceAll('_',' ')}</span></Link>):<Empty text="No projects yet." action="/my-projects/create" label="Add your first project"/>}</Panel><Panel title="Recent View Requests">{recentRequests.length?recentRequests.map(r=><Link href="/view-requests" key={r.id} className="block border-b border-white/10 py-3"><strong>{r.requester?.first_name} {r.requester?.last_name}</strong><div className="mt-1 text-sm text-[#8f8b83]">Requested access to {r.project?.title} · {r.status}</div></Link>):<Empty text="No view requests yet."/>}</Panel></div></PortalLayout>}
function Panel({title,children}:{title:string;children:React.ReactNode}){return <section className="surface p-5"><h2 className="editorial text-2xl">{title}</h2><div className="mt-3">{children}</div></section>} function Empty({text,action,label}:{text:string;action?:string;label?:string}){return <div className="py-8 text-center text-sm text-[#8f8b83]">{text}{action&&<div><Link href={action} className="mt-3 inline-block text-[#d9bd79]">{label} →</Link></div>}</div>}
