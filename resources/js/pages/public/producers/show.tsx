import { Link, router } from '@inertiajs/react';
import { Bookmark, CheckCircle2 } from 'lucide-react';
import ProjectCard from '../../../components/ProjectCard';
import Seo from '../../../components/Seo';
import PublicLayout from '../../../layouts/PublicLayout';

export default function ProducerShow({ profile, seo }: { profile: any; seo: any }) {
    const social = Object.entries(profile.social_links || {}).filter(([, url]) => Boolean(url));
    return <PublicLayout><Seo seo={seo} /><section className="container-afp py-12">
        <div className="grid gap-8 lg:grid-cols-[280px_1fr]">
            <aside>
                <div className="surface overflow-hidden"><div className="aspect-[4/5] bg-[#202226]">{profile.photo_url && <img src={profile.photo_url} alt={profile.name} className="h-full w-full object-cover" />}</div></div>
                <button onClick={() => router.post('/saved/toggle', { type: 'profile', id: profile.id })} className="btn btn-secondary mt-3 w-full"><Bookmark size={16} /> Save Producer</button>
            </aside>
            <div>
                <div className="flex flex-wrap items-center gap-2 text-sm text-[#98948b]"><span>{profile.country}</span>{profile.verified && <span className="badge badge-gold"><CheckCircle2 size={12} /> Association Verified</span>}</div>
                <h1 className="editorial mt-2 text-5xl md:text-7xl">{profile.name}</h1>
                <p className="mt-3 text-lg text-[#b5b0a6]">{profile.roles?.join(' · ')}</p>
                <p className="mt-6 max-w-3xl whitespace-pre-line leading-8 text-[#b2ada3]">{profile.biography}</p>
                <div className="mt-8 grid gap-6 sm:grid-cols-2">
                    <Info title="Content Specialties" values={profile.specialties} />
                    <Info title="Genres" values={profile.genres} />
                    <Info title="Languages" values={profile.languages} />
                    <Info title="Contact" values={[profile.email, profile.phone, profile.website].filter(Boolean)} />
                </div>
                {profile.association_number && <p className="mt-6 text-sm text-[#918c82]"><strong className="text-[#c9c3b7]">Association/member number:</strong> {profile.association_number}</p>}
                {profile.awards && <div className="mt-8"><div className="text-xs uppercase tracking-[.17em] text-[#77736c]">Awards & Recognition</div><p className="mt-2 whitespace-pre-line text-[#aaa59b]">{profile.awards}</p></div>}
                {social.length > 0 && <div className="mt-6 flex flex-wrap gap-3">{social.map(([network, url]) => <a key={network} href={String(url)} target="_blank" rel="noopener noreferrer" className="btn btn-secondary capitalize">{network === 'x' ? 'X' : network} ↗</a>)}</div>}
            </div>
        </div>
        {profile.projects?.length > 0 && <div className="mt-16"><div className="mb-6 flex items-center justify-between"><h2 className="editorial text-4xl">Published Projects</h2><Link href="/projects" className="text-sm text-[#d9bd79]">Marketplace →</Link></div><div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">{profile.projects.map((project: any) => <ProjectCard key={project.id} project={project} />)}</div></div>}
    </section></PublicLayout>;
}

function Info({ title, values }: { title: string; values: any[] }) {
    return <div><div className="text-xs uppercase tracking-[.17em] text-[#77736c]">{title}</div><div className="mt-2 flex flex-wrap gap-2">{values?.length ? values.map((value, i) => <span key={i} className="badge">{value}</span>) : <span className="text-sm text-[#77736c]">Not publicly listed</span>}</div></div>;
}
