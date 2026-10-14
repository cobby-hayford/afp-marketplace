import { Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AdminModerationActions from '../../components/AdminModerationActions';
import Pagination from '../../components/Pagination';
import AdminLayout from '../../layouts/AdminLayout';

function InternalNote({ user }: { user: any }) {
    const form = useForm({ body: '' });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(`/admin/users/${user.id}/notes`, { preserveScroll: true, onSuccess: () => form.reset() });
    };
    return <div className="mt-4 rounded-xl border border-white/10 p-3">
        <div className="text-xs uppercase tracking-[.14em] text-[#8f8b83]">Internal review notes</div>
        {user.admin_notes?.length > 0 && <div className="mt-3 grid gap-2">{user.admin_notes.slice(0, 3).map((note: any) => <div key={note.id} className="text-sm text-[#aaa59b]"><span className="text-[#d4cec2]">{note.actor ? `${note.actor.first_name} ${note.actor.last_name}` : 'Admin'}:</span> {note.body}</div>)}</div>}
        <form onSubmit={submit} className="mt-3 flex flex-col gap-2 sm:flex-row"><input className="input" value={form.data.body} onChange={e => form.setData('body', e.target.value)} placeholder="Add private admin note" /><button className="btn btn-secondary" disabled={form.processing}>Add Note</button></form>
        {form.errors.body && <p className="mt-1 text-sm text-red-400">{form.errors.body}</p>}
    </div>;
}

export default function Memberships({ profiles, filters }: { profiles: any; filters: any }) {
    return <AdminLayout title="Member Applications">
        <div className="mb-5 flex flex-wrap gap-2">{['pending_review', 'changes_requested', 'rejected', 'approved'].map(status => <button key={status} className={`badge ${filters.status === status ? 'badge-gold' : ''}`} onClick={() => router.get('/admin/memberships', { status }, { preserveState: true })}>{status.replaceAll('_', ' ')}</button>)}</div>
        <div className="grid gap-4">{profiles.data.length === 0 ? <Empty /> : profiles.data.map((profile: any) => <article className="surface p-5" key={profile.id}>
            <div className="flex flex-wrap justify-between gap-4"><div><h2 className="editorial text-2xl">{profile.professional_name || `${profile.user.first_name} ${profile.user.last_name}`}</h2><p className="text-sm text-[#928e85]">{profile.user.email} · {profile.country?.name || profile.user.country?.name} · {profile.user.account_type?.replaceAll('_', ' ')}</p></div><span className="badge">{profile.status.replaceAll('_', ' ')}</span></div>
            <p className="mt-4 text-sm leading-6 text-[#aaa59b]">{profile.biography || profile.short_bio || 'No biography submitted.'}</p>
            <div className="mt-3 flex flex-wrap gap-2">{profile.roles?.map((item: any) => <span className="badge" key={item.id}>{item.role}</span>)}{profile.content_types?.map((item: any) => <span className="badge" key={item.id}>{item.name}</span>)}</div>
            {profile.documents?.length > 0 && <div className="mt-4 rounded-xl border border-white/10 p-3"><div className="mb-2 text-xs uppercase tracking-[.14em] text-[#8f8b83]">Supporting documents</div><div className="flex flex-wrap gap-3">{profile.documents.map((document: any) => <a key={document.id} href={document.download_url} className="text-sm text-[#d9bd79] underline-offset-4 hover:underline">{document.title} · {document.type.replaceAll('_', ' ')}</a>)}</div></div>}
            <InternalNote user={profile.user} />
            <div className="mt-4"><Link href={`/admin/users/${profile.user.id}`} className="text-sm text-[#d9bd79] hover:underline">Open full member review</Link></div>
            {['submitted', 'pending_review'].includes(profile.status) && <AdminModerationActions approveUrl={`/admin/memberships/${profile.id}/approve`} transitionUrl={`/admin/memberships/${profile.id}/transition`} />}
        </article>)}</div>
        <Pagination page={profiles} />
    </AdminLayout>;
}

function Empty() {
    return <div className="surface p-8 text-center text-[#8e8a82]">No member applications match this queue.</div>;
}
