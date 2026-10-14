import { router, useForm } from '@inertiajs/react';
import Field from '../../components/Field';
import PortalLayout from '../../layouts/PortalLayout';

type ChoiceKey = 'genres' | 'languages' | 'content_types';

export default function Profile({ profile, options }: { profile: any; options: any }) {
    const form = useForm({
        professional_name: profile?.professional_name || '',
        country_id: String(profile?.country_id || ''),
        city: profile?.city || '',
        short_bio: profile?.short_bio || '',
        biography: profile?.biography || '',
        website: profile?.website || '',
        association_number: profile?.association_number || '',
        awards: profile?.awards || '',
        social_links: {
            linkedin: profile?.social_links?.linkedin || '',
            instagram: profile?.social_links?.instagram || '',
            x: profile?.social_links?.x || '',
            facebook: profile?.social_links?.facebook || '',
        },
        phone: profile?.phone || '',
        contact_preference: profile?.contact_preference || 'platform',
        show_email: Boolean(profile?.show_email),
        show_phone: Boolean(profile?.show_phone),
        roles: profile?.roles?.map((r: any) => r.role) || ['Producer'],
        genres: profile?.genres?.map((x: any) => x.id) || [],
        languages: profile?.languages?.map((x: any) => x.id) || [],
        content_types: profile?.content_types?.map((x: any) => x.id) || [],
        photo: null as File | null,
    });
    const documents = useForm({ type: 'professional_document', document: null as File | null });

    const toggle = (key: ChoiceKey, id: number) => {
        const values = form.data[key] as number[];
        form.setData(key, values.includes(id) ? values.filter(x => x !== id) : [...values, id]);
    };
    const setSocial = (key: keyof typeof form.data.social_links, value: string) => {
        form.setData('social_links', { ...form.data.social_links, [key]: value });
    };

    return <PortalLayout title="Professional Profile">
        <div className="mb-5 surface p-4 text-sm">
            <strong>Status:</strong> <span className="capitalize text-[#d9bd79]">{profile?.status?.replaceAll('_', ' ') || 'draft'}</span>
            {profile?.status === 'changes_requested' && <p className="mt-2 text-[#d5ae7a]">Update the requested information and resubmit your profile.</p>}
        </div>

        <form onSubmit={e => {
            e.preventDefault();
            form.transform((data: any) => ({
    ...data,
    _method: 'put',
}));

form.post('/profile', {
    forceFormData: true,
});
        }} className="grid gap-6">
            <section className="surface grid gap-4 p-5 md:grid-cols-2">
                <Field label="Professional display name" error={form.errors.professional_name}><input className="input" value={form.data.professional_name} onChange={e => form.setData('professional_name', e.target.value)} /></Field>
                <Field label="Country" error={form.errors.country_id}><select className="input" value={form.data.country_id} onChange={e => form.setData('country_id', e.target.value)}><option value="">Select country</option>{options.countries.map((x: any) => <option key={x.id} value={x.id}>{x.name}</option>)}</select></Field>
                <Field label="City (optional)" error={form.errors.city}><input className="input" value={form.data.city} onChange={e => form.setData('city', e.target.value)} /></Field>
                <Field label="Association / member number" error={form.errors.association_number}><input className="input" value={form.data.association_number} onChange={e => form.setData('association_number', e.target.value)} /></Field>
                <Field label="Website" error={form.errors.website}><input className="input" value={form.data.website} onChange={e => form.setData('website', e.target.value)} /></Field>
                <Field label="Professional photo" error={form.errors.photo}><input type="file" accept="image/png,image/jpeg,image/webp" className="input" onChange={e => form.setData('photo', e.target.files?.[0] || null)} /></Field>
                <div className="md:col-span-2"><Field label="Short bio" error={form.errors.short_bio}><textarea className="input min-h-24" value={form.data.short_bio} onChange={e => form.setData('short_bio', e.target.value)} /></Field></div>
                <div className="md:col-span-2"><Field label="Biography" error={form.errors.biography}><textarea className="input min-h-44" value={form.data.biography} onChange={e => form.setData('biography', e.target.value)} /></Field></div>
                <div className="md:col-span-2"><Field label="Awards / selected recognition" error={form.errors.awards}><textarea className="input min-h-28" placeholder="List awards or professional recognition you want shown on your public profile." value={form.data.awards} onChange={e => form.setData('awards', e.target.value)} /></Field></div>
            </section>

            <section className="surface p-5">
                <h2 className="editorial text-2xl">Professional Roles</h2>
                <Field label="Roles (comma separated)" error={form.errors.roles}><input className="input mt-3" value={(form.data.roles as string[]).join(', ')} onChange={e => form.setData('roles', e.target.value.split(',').map(x => x.trim()).filter(Boolean))} /></Field>
            </section>

            <Choice title="Content specialties" rows={options.contentTypes} selected={form.data.content_types} onToggle={id => toggle('content_types', id)} />
            <Choice title="Genres" rows={options.genres} selected={form.data.genres} onToggle={id => toggle('genres', id)} />
            <Choice title="Languages" rows={options.languages} selected={form.data.languages} onToggle={id => toggle('languages', id)} />

            <section className="surface grid gap-4 p-5 md:grid-cols-2">
                <h2 className="editorial md:col-span-2 text-2xl">Public Links & Contact Preferences</h2>
                <Field label="LinkedIn"><input className="input" value={form.data.social_links.linkedin} onChange={e => setSocial('linkedin', e.target.value)} /></Field>
                <Field label="Instagram"><input className="input" value={form.data.social_links.instagram} onChange={e => setSocial('instagram', e.target.value)} /></Field>
                <Field label="X / Twitter"><input className="input" value={form.data.social_links.x} onChange={e => setSocial('x', e.target.value)} /></Field>
                <Field label="Facebook"><input className="input" value={form.data.social_links.facebook} onChange={e => setSocial('facebook', e.target.value)} /></Field>
                <Field label="Private phone (optional)" error={form.errors.phone}><input className="input" value={form.data.phone} onChange={e => form.setData('phone', e.target.value)} /></Field>
                <Field label="Preferred contact method"><select className="input" value={form.data.contact_preference} onChange={e => form.setData('contact_preference', e.target.value)}><option value="platform">Platform messaging</option><option value="email">Email</option><option value="phone">Phone</option></select></Field>
                <label className="flex gap-2 text-sm"><input type="checkbox" checked={form.data.show_email} onChange={e => form.setData('show_email', e.target.checked)} /> Show my account email publicly</label>
                <label className="flex gap-2 text-sm"><input type="checkbox" checked={form.data.show_phone} onChange={e => form.setData('show_phone', e.target.checked)} /> Show my phone publicly</label>
            </section>

            <section className="surface p-5">
                <h2 className="editorial text-2xl">Supporting Documents</h2>
                <p className="mt-1 text-sm text-[#908c83]">Optional private evidence for membership review. These files are available only to you and authorized administrators.</p>
                <div className="mt-4 grid gap-3 md:grid-cols-[220px_1fr_auto]">
                    <Field label="Document type"><select className="input" value={documents.data.type} onChange={e => documents.setData('type', e.target.value)}><option value="association_proof">Association proof</option><option value="identity_document">Identity document</option><option value="professional_document">Professional document</option><option value="company_document">Company document</option><option value="other">Other</option></select></Field>
                    <Field label="File" error={documents.errors.document}><input className="input" type="file" accept=".pdf,.doc,.docx,image/jpeg,image/png,image/webp" onChange={e => documents.setData('document', e.target.files?.[0] || null)} /></Field>
                    <button type="button" className="btn btn-secondary self-end" disabled={documents.processing || !documents.data.document} onClick={() => documents.post('/profile/documents', { forceFormData: true, onSuccess: () => documents.reset('document') })}>Upload</button>
                </div>
                {profile?.documents?.length > 0 && <div className="mt-4 grid gap-2">{profile.documents.map((document: any) => <div key={document.id} className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-white/10 p-3 text-sm"><span><strong>{document.title}</strong> · {document.type.replaceAll('_', ' ')}</span><div className="flex gap-2"><a className="text-[#d9bd79]" href={document.download_url}>Download</a>{profile.status !== 'approved' && <button type="button" className="text-red-300" onClick={() => router.delete(`/profile-documents/${document.id}`)}>Remove</button>}</div></div>)}</div>}
            </section>

            <div className="flex flex-wrap gap-3">
                <button disabled={form.processing} className="btn btn-primary">{form.processing ? 'Saving…' : 'Save Profile'}</button>
                {profile && ['draft', 'changes_requested', 'rejected'].includes(profile.status) && <button type="button" className="btn btn-secondary" onClick={() => router.post('/profile/submit')}>Submit for Approval</button>}
            </div>
        </form>
    </PortalLayout>;
}

function Choice({ title, rows, selected, onToggle }: { title: string; rows: any[]; selected: number[]; onToggle: (id: number) => void }) {
    return <section className="surface p-5"><h2 className="editorial text-2xl">{title}</h2><div className="mt-4 flex flex-wrap gap-2">{rows.map(x => <label key={x.id} className={`badge cursor-pointer ${selected.includes(x.id) ? 'badge-gold' : ''}`}><input className="sr-only" type="checkbox" checked={selected.includes(x.id)} onChange={() => onToggle(x.id)} />{x.name}</label>)}</div></section>;
}
