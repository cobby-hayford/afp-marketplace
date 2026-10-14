import { useForm } from '@inertiajs/react';
import AdminLayout from '../../layouts/AdminLayout';

function Card({ type, item }: { type: 'profile' | 'organisation' | 'festival'; item: any }) {
    const form = useForm({ type, id: item.id, status: 'verified', notes: '' });
    const title = type === 'profile'
        ? (item.professional_name || `${item.user?.first_name ?? ''} ${item.user?.last_name ?? ''}`.trim())
        : type === 'organisation' ? item.name : item.festival_name;

    return <div className="surface p-5">
        <div className="flex flex-wrap justify-between gap-3">
            <div>
                <h3 className="editorial text-xl">{title}</h3>
                <p className="text-sm text-[#908b83]">{type === 'festival' ? `Project: ${item.project?.title}` : item.country?.name}</p>
            </div>
            <span className="badge">{item.verification_status.replaceAll('_', ' ')}</span>
        </div>
        {type === 'festival' && <>
            <p className="mt-3 text-sm text-[#aaa59b]">{item.selection_type} {item.award_category && `· ${item.award_category}`} {item.year && `· ${item.year}`}</p>
            <div className="mt-3 flex flex-wrap gap-3 text-sm">
                {item.evidence_url && <a className="text-[#d9bd79]" href={item.evidence_url} target="_blank" rel="noopener noreferrer">Open evidence URL ↗</a>}
                {item.evidence_download_url && <a className="text-[#d9bd79]" href={item.evidence_download_url}>Download private evidence</a>}
                {!item.evidence_url && !item.evidence_download_url && <span className="text-[#8f8b83]">No evidence supplied.</span>}
            </div>
        </>}
        <textarea className="input mt-4 min-h-20" placeholder="Review notes" value={form.data.notes} onChange={e => form.setData('notes', e.target.value)} />
        <div className="mt-3 flex gap-2">
            <select className="input max-w-56" value={form.data.status} onChange={e => form.setData('status', e.target.value)}>
                <option value="verified">Verify</option>
                <option value="pending_verification">Keep Pending</option>
                <option value="rejected">Reject</option>
            </select>
            <button className="btn btn-primary" disabled={form.processing} onClick={() => form.patch('/admin/verifications', { preserveScroll: true })}>Apply</button>
        </div>
    </div>;
}

export default function Verifications({ profiles, organisations, festivals }: { profiles: any[]; organisations: any[]; festivals: any[] }) {
    return <AdminLayout title="Verification Queue">
        <Section title="Association / Member Verification">{profiles.map(x => <Card key={x.id} type="profile" item={x} />)}</Section>
        <Section title="Company Verification">{organisations.map(x => <Card key={x.id} type="organisation" item={x} />)}</Section>
        <Section title="Festival Recognition Verification">{festivals.map(x => <Card key={x.id} type="festival" item={x} />)}</Section>
    </AdminLayout>;
}

function Section({ title, children }: { title: string; children: React.ReactNode }) {
    const rows = Array.isArray(children) ? children : [children];
    return <section className="mb-8">
        <h2 className="editorial mb-4 text-2xl">{title}</h2>
        <div className="grid gap-4 lg:grid-cols-2">{rows.length > 0 ? children : <div className="surface p-5 text-sm text-[#8f8b83]">No records in this queue.</div>}</div>
    </section>;
}
