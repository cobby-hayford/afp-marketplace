import { Link, useForm, usePage } from '@inertiajs/react';
import { FormEvent } from 'react';
import AdminLayout from '../../layouts/AdminLayout';
import type { PageProps } from '../../types';

export default function UserShow({ managedUser }: { managedUser: any }) {
    const { auth } = usePage<PageProps>().props;
    const canManageRoles = auth.user?.role === 'super_administrator';
    const note = useForm({ body: '' });
    const addNote = (event: FormEvent) => {
        event.preventDefault();
        note.post(`/admin/users/${managedUser.id}/notes`, {
            preserveScroll: true,
            onSuccess: () => note.reset(),
        });
    };

    return <AdminLayout title="User Review">
        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 className="editorial text-3xl">{managedUser.first_name} {managedUser.last_name}</h2>
                <p className="text-sm text-[#969188]">{managedUser.email} · {managedUser.country?.name || 'Country not set'}</p>
            </div>
            <Link href="/admin/users" className="btn btn-secondary">Back to Users</Link>
        </div>

        <div className="grid gap-5 lg:grid-cols-[1.25fr_.75fr]">
            <div className="grid gap-5">
                <section className="surface p-5">
                    <div className="flex flex-wrap gap-2">
                        <span className="badge">{managedUser.account_type?.replaceAll('_', ' ') || 'No account type'}</span>
                        <span className="badge">{managedUser.role?.replaceAll('_', ' ')}</span>
                        <span className={`badge ${managedUser.suspended_at ? '' : 'badge-gold'}`}>{managedUser.suspended_at ? 'Suspended' : 'Active'}</span>
                    </div>
                    {managedUser.profile ? <div className="mt-5 grid gap-3 text-sm">
                        <h3 className="editorial text-2xl">Professional Profile</h3>
                        <p><strong>Status:</strong> {managedUser.profile.status?.replaceAll('_', ' ')}</p>
                        <p><strong>Professional name:</strong> {managedUser.profile.professional_name || '—'}</p>
                        <p><strong>Country:</strong> {managedUser.profile.country?.name || '—'}</p>
                        <p><strong>Association number:</strong> {managedUser.profile.association_number || '—'}</p>
                        <p><strong>Phone:</strong> {managedUser.profile.phone || '—'}</p>
                        <p><strong>Short bio:</strong> {managedUser.profile.short_bio || '—'}</p>
                        <p className="whitespace-pre-wrap"><strong>Biography:</strong> {managedUser.profile.biography || '—'}</p>
                        <div className="flex flex-wrap gap-2">{managedUser.profile.roles?.map((role: any) => <span className="badge" key={role.id}>{role.role}</span>)}</div>
                    </div> : <p className="mt-4 text-sm text-[#8f8a80]">This user has not created a professional profile.</p>}
                </section>

                <section className="surface p-5">
                    <h3 className="editorial text-2xl">Submissions</h3>
                    <div className="mt-4 grid gap-3">
                        {managedUser.projects?.length ? managedUser.projects.map((project: any) => <div key={project.id} className="rounded-xl border border-white/10 p-3 text-sm">
                            <div className="flex flex-wrap items-center justify-between gap-2"><strong>{project.title}</strong><span className="badge">{project.publication_status.replaceAll('_', ' ')}</span></div>
                            <Link href={`/admin/projects/${project.id}`} className="mt-2 inline-block text-[#d9bd79] hover:underline">Review project</Link>
                        </div>) : <p className="text-sm text-[#8f8a80]">No project submissions.</p>}
                        {managedUser.organisations?.map((organisation: any) => <div key={`org-${organisation.id}`} className="rounded-xl border border-white/10 p-3 text-sm">
                            <div className="flex flex-wrap items-center justify-between gap-2"><strong>{organisation.name}</strong><span className="badge">{organisation.approval_status.replaceAll('_', ' ')}</span></div>
                        </div>)}
                    </div>
                </section>
            </div>

            <div className="grid content-start gap-5">
                <section className="surface p-5">
                    <h3 className="editorial text-2xl">Internal Notes</h3>
                    <p className="mt-1 text-xs text-[#8e8a82]">Visible to administrators only. These notes are never sent to the member.</p>
                    <form onSubmit={addNote} className="mt-4">
                        <textarea className="input min-h-28" value={note.data.body} onChange={e => note.setData('body', e.target.value)} placeholder="Add a factual internal review note…" />
                        {note.errors.body && <p className="mt-1 text-sm text-red-400">{note.errors.body}</p>}
                        <button className="btn btn-primary mt-3" disabled={note.processing}>Add Internal Note</button>
                    </form>
                    <div className="mt-5 grid gap-3">
                        {managedUser.admin_notes?.length ? managedUser.admin_notes.map((item: any) => <div key={item.id} className="rounded-xl border border-white/10 p-3 text-sm">
                            <p className="whitespace-pre-wrap text-[#d1cbc0]">{item.body}</p>
                            <p className="mt-2 text-xs text-[#817d75]">{item.actor ? `${item.actor.first_name} ${item.actor.last_name}` : 'Administrator'} · {new Date(item.created_at).toLocaleString()}</p>
                        </div>) : <p className="text-sm text-[#8e8a82]">No internal notes yet.</p>}
                    </div>
                </section>

                {canManageRoles && <section className="surface p-5 text-sm text-[#aaa59b]">
                    Privileged role changes remain restricted to super administrators from the Users screen.
                </section>}
            </div>
        </div>
    </AdminLayout>;
}
