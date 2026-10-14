import { useForm } from '@inertiajs/react';
import Field from '../../components/Field';
import PortalLayout from '../../layouts/PortalLayout';

export default function Stand({ stand, countries, organisations }: { stand: any; countries: any[]; organisations: any[] }) {
    const form = useForm({
        display_name: stand?.display_name || '',
        stand_type: stand?.stand_type || 'Producer Stand',
        country_id: String(stand?.country_id || ''),
        organisation_id: String(stand?.organisation_id || ''),
        summary: stand?.summary || '',
        description: stand?.description || '',
        offerings: stand?.offerings || '',
        looking_for: stand?.looking_for || '',
        website: stand?.website || '',
        social_links: {
            linkedin: stand?.social_links?.linkedin || '',
            instagram: stand?.social_links?.instagram || '',
            x: stand?.social_links?.x || '',
            facebook: stand?.social_links?.facebook || '',
        },
        logo: null as File | null,
        cover: null as File | null,
    });
    const setSocial = (key: keyof typeof form.data.social_links, value: string) => form.setData('social_links', { ...form.data.social_links, [key]: value });

    return <PortalLayout title="My Digital Stand">
        <p className="mb-6 max-w-3xl text-[#9d998f]">Your Digital Stand is your presence in the online trade hall. A profile-owned stand becomes public when your professional profile is approved; organisation-owned stands require the organisation to be approved.</p>
        <form onSubmit={e => { e.preventDefault(); form.post('/my-stand', { forceFormData: true }); }} className="surface grid gap-4 p-5 md:grid-cols-2">
            <Field label="Display name" error={form.errors.display_name}><input className="input" value={form.data.display_name} onChange={e => form.setData('display_name', e.target.value)} /></Field>
            <Field label="Stand type" error={form.errors.stand_type}><select className="input" value={form.data.stand_type} onChange={e => form.setData('stand_type', e.target.value)}>{['Producer Stand', 'Production Company Stand', 'Distributor Stand', 'Broadcaster Stand', 'Streaming Platform Stand', 'Music Company Stand', 'Investor Stand', 'Sales Agent Stand'].map(x => <option key={x}>{x}</option>)}</select></Field>
            <Field label="Country" error={form.errors.country_id}><select className="input" value={form.data.country_id} onChange={e => form.setData('country_id', e.target.value)}><option value="">Select</option>{countries.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}</select></Field>
            <Field label="Organisation (optional)"><select className="input" value={form.data.organisation_id} onChange={e => form.setData('organisation_id', e.target.value)}><option value="">Use my professional profile</option>{organisations.map(o => <option key={o.id} value={o.id}>{o.name} ({o.approval_status})</option>)}</select></Field>
            <div className="md:col-span-2"><Field label="Summary" error={form.errors.summary}><textarea className="input min-h-24" value={form.data.summary} onChange={e => form.setData('summary', e.target.value)} /></Field></div>
            <div className="md:col-span-2"><Field label="Description" error={form.errors.description}><textarea className="input min-h-40" value={form.data.description} onChange={e => form.setData('description', e.target.value)} /></Field></div>
            <Field label="Offerings"><textarea className="input min-h-28" value={form.data.offerings} onChange={e => form.setData('offerings', e.target.value)} /></Field>
            <Field label="What we are looking for"><textarea className="input min-h-28" value={form.data.looking_for} onChange={e => form.setData('looking_for', e.target.value)} /></Field>
            <Field label="Website"><input className="input" value={form.data.website} onChange={e => form.setData('website', e.target.value)} /></Field>
            <Field label="LinkedIn"><input className="input" value={form.data.social_links.linkedin} onChange={e => setSocial('linkedin', e.target.value)} /></Field>
            <Field label="Instagram"><input className="input" value={form.data.social_links.instagram} onChange={e => setSocial('instagram', e.target.value)} /></Field>
            <Field label="X / Twitter"><input className="input" value={form.data.social_links.x} onChange={e => setSocial('x', e.target.value)} /></Field>
            <Field label="Facebook"><input className="input" value={form.data.social_links.facebook} onChange={e => setSocial('facebook', e.target.value)} /></Field>
            <Field label="Logo / photo"><input type="file" accept="image/png,image/jpeg,image/webp" className="input" onChange={e => form.setData('logo', e.target.files?.[0] || null)} /></Field>
            <Field label="Cover image"><input type="file" accept="image/png,image/jpeg,image/webp" className="input" onChange={e => form.setData('cover', e.target.files?.[0] || null)} /></Field>
            <div className="md:col-span-2"><button disabled={form.processing} className="btn btn-primary">{form.processing ? 'Saving…' : 'Save Digital Stand'}</button></div>
        </form>
    </PortalLayout>;
}
