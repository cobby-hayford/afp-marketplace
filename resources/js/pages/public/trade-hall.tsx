import type { LucideIcon } from 'lucide-react';
import { Link } from '@inertiajs/react'; import { Building2, Clapperboard, Handshake, Search, Sparkles } from 'lucide-react'; import PublicLayout from '../../layouts/PublicLayout'; import Seo from '../../components/Seo';
export default function TradeHall({counts,seo}:{counts:Record<string,number>;seo:any}){const areas: {
    icon: LucideIcon;
    title: string;
    description: string;
    url: string;
    count: number;
}[] = [
    {
        icon: Clapperboard,
        title: 'Projects',
        description: 'Approved films, series, music and digital projects.',
        url: '/projects',
        count: counts.projects,
    },
    {
        icon: Building2,
        title: 'Digital Stands',
        description: 'Professional market presence for producers and organisations.',
        url: '/stands',
        count: counts.stands,
    },
    {
        icon: Search,
        title: 'Buying Requests',
        description: 'What broadcasters, distributors and platforms are seeking.',
        url: '/buying-requests',
        count: counts.buying_requests,
    },
    {
        icon: Handshake,
        title: 'Collaboration',
        description: 'Co-production, creative and commercial partnership calls.',
        url: '/collaboration',
        count: counts.collaboration,
    },
    {
        icon: Sparkles,
        title: 'Opportunities',
        description: 'Grants, labs, festivals, markets and industry calls.',
        url: '/opportunities',
        count: counts.opportunities,
    },
];return <PublicLayout><Seo seo={seo}/><section className="container-afp py-20"><div className="max-w-4xl"><div className="text-xs uppercase tracking-[.26em] text-[#c8a85d]">B2B discovery & connection</div><h1 className="editorial mt-3 text-5xl md:text-7xl">The African Content Trade Hall</h1><p className="mt-6 text-xl leading-8 text-[#aaa59c]">Discover content, meet producers, find buyers and build partnerships. The platform opens conversations; commercial transactions are concluded directly between the parties.</p></div><div className="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">{areas.map(({ icon: Icon, title, description, url, count }) =><Link href={String(url)} key={String(title)} className="surface min-h-56 p-6 transition hover:-translate-y-1 hover:border-[#625738]"><Icon className="text-[#c8a85d]"/><div className="mt-8 text-xs uppercase tracking-[.18em] text-[#817b70]">{Number(count)} active</div><h2 className="editorial mt-2 text-3xl">{String(title)}</h2><p className="mt-3 leading-7 text-[#99958c]">{description}</p></Link>)}</div></section></PublicLayout>}
