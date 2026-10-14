import { Link, router, usePage } from '@inertiajs/react';
import { Menu, Search, X } from 'lucide-react';
import { FormEvent, ReactNode, useState } from 'react';
import NotificationBell from '../components/NotificationBell';
import Flash from '../components/Flash';
import type { PageProps } from '../types';

const mainNav = [
    ['Home', '/'],
    ['Projects', '/projects'],
    ['Producers', '/producers'],
    ['Collaboration', '/collaboration'],
    ['Opportunities', '/opportunities'],
    ['News', '/news'],
    ['About', '/about'],
    ['Contact', '/contact'],
];
const tradeHallNav = [
    ['Projects', '/projects'],
    ['Digital Stands', '/stands'],
    ['Buying Requests', '/buying-requests'],
    ['Collaboration', '/collaboration'],
    ['Opportunities', '/opportunities'],
];

export default function PublicLayout({ children }: { children: ReactNode }) {
    const { auth, site } = usePage<PageProps>().props;
    const [mobile, setMobile] = useState(false);
    const [q, setQ] = useState('');
    const search = (event: FormEvent) => {
        event.preventDefault();
        if (q.trim()) router.get('/search', { q: q.trim() });
    };
    const social = Object.entries(site.social || {}).filter(([, url]) => Boolean(url));

    return <div className="min-h-screen">
        <header className="sticky top-0 z-40 border-b border-white/10 bg-[#0d0e10]/95 backdrop-blur">
            <div className="container-afp flex min-h-18 items-center justify-between gap-3 py-2">
                <Link href="/" className="flex shrink-0 items-center gap-3">
                    <span className="grid h-9 w-9 place-items-center rounded-full border border-[#6f5f34] bg-[#211d13] editorial text-lg text-[#d9bd79]">AF</span>
                    <span className="hidden 2xl:block"><strong className="block text-sm tracking-wide">{site.name}</strong><span className="block text-[10px] uppercase tracking-[.2em] text-[#8f8b82]">{site.subtitle}</span></span>
                </Link>

                <nav className="hidden xl:flex items-center gap-3 text-xs text-[#c7c2b8] 2xl:gap-4 2xl:text-sm" aria-label="Primary navigation">
                    <Link href="/" className="hover:text-white">Home</Link>
                    <details className="group relative">
                        <summary className="cursor-pointer list-none hover:text-white">Trade Hall</summary>
                        <div className="surface absolute left-0 top-8 z-50 grid min-w-52 gap-1 bg-[#111316] p-2 shadow-2xl">
                            <Link href="/trade-hall" className="rounded-lg px-3 py-2 font-semibold text-[#d9bd79] hover:bg-white/5">Trade Hall Overview</Link>
                            {tradeHallNav.map(([label, url]) => <Link key={url} href={url} className="rounded-lg px-3 py-2 hover:bg-white/5">{label}</Link>)}
                        </div>
                    </details>
                    {mainNav.slice(1).map(([label, url]) => <Link key={url} href={url} className="hover:text-white">{label}</Link>)}
                </nav>

                <div className="flex shrink-0 items-center gap-1.5">
                    <form onSubmit={search} className="hidden lg:flex items-center rounded-lg border border-white/10 bg-white/[.025] px-2">
                        <Search size={16} className="text-[#817d75]" />
                        <input value={q} onChange={e => setQ(e.target.value)} className="w-28 bg-transparent px-2 py-2 text-sm outline-none 2xl:w-36" aria-label="Global search" placeholder="Search..." />
                    </form>
                    {auth.user ? <><NotificationBell /><Link href="/dashboard" className="btn btn-secondary hidden sm:inline-flex">Dashboard</Link></> : <><Link href="/login" className="hidden sm:block px-2 text-sm">Sign In</Link><Link href="/register" className="btn btn-primary hidden sm:inline-flex">Join Marketplace</Link></>}
                    <button onClick={() => setMobile(!mobile)} className="xl:hidden rounded-lg p-2" aria-label="Toggle navigation" aria-expanded={mobile}>{mobile ? <X /> : <Menu />}</button>
                </div>
            </div>

            {mobile && <div className="container-afp border-t border-white/10 py-4 xl:hidden">
                <form onSubmit={search} className="mb-3 flex gap-2"><input value={q} onChange={e => setQ(e.target.value)} className="input" placeholder="Search projects, producers, companies..." /><button className="btn btn-secondary" aria-label="Search"><Search size={18} /></button></form>
                <nav className="grid grid-cols-2 gap-2" aria-label="Mobile navigation">
                    <Link href="/trade-hall" onClick={() => setMobile(false)} className="rounded-lg px-3 py-2 text-[#d9bd79] hover:bg-white/5">Trade Hall</Link>
                    <Link href="/buying-requests" onClick={() => setMobile(false)} className="rounded-lg px-3 py-2 hover:bg-white/5">Buying Requests</Link>
                    <Link href="/stands" onClick={() => setMobile(false)} className="rounded-lg px-3 py-2 hover:bg-white/5">Digital Stands</Link>
                    {mainNav.map(([label, url]) => <Link key={url} href={url} onClick={() => setMobile(false)} className="rounded-lg px-3 py-2 hover:bg-white/5">{label}</Link>)}
                </nav>
                {!auth.user && <div className="mt-3 flex gap-2"><Link href="/login" className="btn btn-secondary flex-1">Sign In</Link><Link href="/register" className="btn btn-primary flex-1">Join</Link></div>}
            </div>}
        </header>

        <main><div className="container-afp pt-4"><Flash /></div>{children}</main>

        <footer className="mt-20 border-t border-white/10 bg-[#090a0b]">
            <div className="container-afp grid gap-10 py-12 md:grid-cols-4">
                <div className="md:col-span-2">
                    <div className="editorial text-2xl">{site.name}</div>
                    <p className="mt-3 max-w-xl text-sm leading-6 text-[#8f8b82]">{site.tagline} for discovery, professional introductions and private business conversations. Commercial parties remain responsible for due diligence and their contractual arrangements.</p>
                    {(site.contact_email || site.contact_phone) && <div className="mt-4 grid gap-1 text-sm text-[#aaa59c]">{site.contact_email && <a href={`mailto:${site.contact_email}`}>{site.contact_email}</a>}{site.contact_phone && <span>{site.contact_phone}</span>}</div>}
                    {social.length > 0 && <div className="mt-4 flex flex-wrap gap-3">{social.map(([network, url]) => <a key={network} href={String(url)} target="_blank" rel="noopener noreferrer" className="text-sm capitalize text-[#d9bd79]">{network === 'x' ? 'X' : network}</a>)}</div>}
                </div>
                <div><h3 className="text-sm font-bold">Marketplace</h3><div className="mt-3 grid gap-2 text-sm text-[#9f9a90]"><Link href="/projects">Projects</Link><Link href="/stands">Digital Stands</Link><Link href="/buying-requests">Buying Requests</Link><Link href="/collaboration">Collaboration</Link></div></div>
                <div><h3 className="text-sm font-bold">Legal & Resources</h3><div className="mt-3 grid gap-2 text-sm text-[#9f9a90]"><Link href="/marketplace-guidelines">Marketplace Guidelines</Link><Link href="/verification-policy">Verification Policy</Link><Link href="/copyright-policy">Copyright Policy</Link><Link href="/privacy">Privacy</Link><Link href="/terms">Terms</Link></div></div>
            </div>
            <div className="container-afp border-t border-white/10 py-5 text-xs text-[#6f6c66]">© {new Date().getFullYear()} {site.name}. {site.disclaimer}</div>
        </footer>
    </div>;
}
