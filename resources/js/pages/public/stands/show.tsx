import { Link, router } from '@inertiajs/react';
import { Bookmark } from 'lucide-react';
import { ReactNode, useState } from 'react';
import ProjectCard from '../../../components/ProjectCard';
import Seo from '../../../components/Seo';
import PublicLayout from '../../../layouts/PublicLayout';

type TabName = 'Overview' | 'Catalogue' | 'Looking For' | 'Collaboration' | 'Team' | 'About';
const tabs: TabName[] = ['Overview', 'Catalogue', 'Looking For', 'Collaboration', 'Team', 'About'];

export default function StandShow({ stand, seo }: { stand: any; seo: any }) {
    const [active, setActive] = useState<TabName>('Overview');
    const social = Object.entries(stand.social_links || {}).filter(([, url]) => Boolean(url));

    return <PublicLayout><Seo seo={seo} /><section>
        <div className="h-64 bg-[#191b1f] md:h-80">{stand.cover_url && <img src={stand.cover_url} alt="" className="h-full w-full object-cover" />}</div>
        <div className="container-afp -mt-12 relative">
            <div className="surface bg-[#111316]/95 p-6 md:p-8">
                <div className="flex flex-wrap items-start justify-between gap-5">
                    <div><div className="text-xs uppercase tracking-[.2em] text-[#8f7b50]">{stand.stand_type} · {stand.country}</div><h1 className="editorial mt-2 text-4xl md:text-6xl">{stand.display_name}</h1><p className="mt-3 max-w-3xl text-lg text-[#aaa59c]">{stand.summary}</p></div>
                    <button onClick={() => router.post('/saved/toggle', { type: 'stand', id: stand.id })} className="btn btn-secondary"><Bookmark size={17} /> Save Stand</button>
                </div>
            </div>

            <div className="mt-6 overflow-x-auto" role="tablist" aria-label="Digital Stand sections">
                <div className="flex min-w-max gap-2">{tabs.map(tab => <button key={tab} role="tab" aria-selected={active === tab} onClick={() => setActive(tab)} className={`btn ${active === tab ? 'btn-primary' : 'btn-secondary'}`}>{tab}</button>)}</div>
            </div>

            <div className="mt-8 grid gap-8 lg:grid-cols-[1fr_320px]">
                <div>
                    {active === 'Overview' && <Tab title="Overview"><p className="whitespace-pre-line leading-8 text-[#aaa59c]">{stand.description}</p><div className="mt-8 grid gap-4 sm:grid-cols-2"><Panel title="Offerings" text={stand.offerings} /><Panel title="Looking For" text={stand.looking_for} /></div></Tab>}
                    {active === 'Catalogue' && <Tab title="Catalogue">{stand.projects?.length ? <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">{stand.projects.map((project: any) => <ProjectCard key={project.id} project={project} />)}</div> : <Empty text="No published catalogue projects yet." />}</Tab>}
                    {active === 'Looking For' && <Tab title="Looking For"><p className="whitespace-pre-line leading-8 text-[#aaa59c]">{stand.looking_for || 'This stand has not published its current partnership needs.'}</p></Tab>}
                    {active === 'Collaboration' && <Tab title="Collaboration">{stand.collaboration?.length ? <div className="grid gap-3">{stand.collaboration.map((post: any) => <Link key={post.id} href={`/collaboration/${post.slug}`} className="surface p-4"><div className="text-xs uppercase tracking-[.16em] text-[#9d8a58]">{post.type?.name} · {post.country?.name}</div><h3 className="editorial mt-2 text-2xl">{post.title}</h3><p className="mt-2 line-clamp-3 text-sm leading-6 text-[#99958c]">{post.description}</p></Link>)}</div> : <Empty text="No active collaboration posts from this stand." />}</Tab>}
                    {active === 'Team' && <Tab title="Team">{stand.team?.length ? <div className="grid gap-3 sm:grid-cols-2">{stand.team.map((member: any) => <Link href={`/producers/${member.slug}`} key={member.slug} className="surface p-4"><strong>{member.name}</strong><div className="mt-1 text-sm text-[#8f8b83]">{member.role}</div></Link>)}</div> : <Empty text="No public team profiles are currently listed." />}</Tab>}
                    {active === 'About' && <Tab title="About"><p className="whitespace-pre-line leading-8 text-[#aaa59c]">{stand.description}</p></Tab>}
                </div>

                <aside className="grid h-fit content-start gap-4">
                    <Panel title="Offerings" text={stand.offerings} />
                    <Panel title="Looking For" text={stand.looking_for} />
                    {social.map(([network, url]) => <a key={network} href={String(url)} target="_blank" rel="noopener noreferrer" className="btn btn-secondary capitalize">{network === 'x' ? 'X' : network} ↗</a>)}
                    {stand.website && <a href={stand.website} target="_blank" rel="noopener noreferrer" className="btn btn-secondary">Visit Website</a>}
                </aside>
            </div>
        </div>
    </section></PublicLayout>;
}

function Tab({ title, children }: { title: string; children: ReactNode }) { return <section className="mb-10"><h2 className="editorial mb-4 text-3xl">{title}</h2>{children}</section>; }
function Panel({ title, text }: { title: string; text?: string | null }) { return <div className="surface p-5"><h2 className="font-bold">{title}</h2><p className="mt-2 whitespace-pre-line text-sm leading-6 text-[#99958c]">{text || 'Not specified.'}</p></div>; }
function Empty({ text }: { text: string }) { return <div className="surface p-7 text-center text-sm text-[#8f8b83]">{text}</div>; }
