import { Link, router } from '@inertiajs/react';
import AdminModerationActions from '../../components/AdminModerationActions';
import AdminLayout from '../../layouts/AdminLayout';

const reviewStatuses = [
    { value: 'changes_requested', label: 'Request Changes' },
    { value: 'rejected', label: 'Reject' },
    { value: 'suspended', label: 'Suspend' },
    { value: 'archived', label: 'Archive' },
];
const removalStatuses = [
    { value: 'suspended', label: 'Suspend' },
    { value: 'archived', label: 'Archive' },
];

export default function ProjectPreview({ project }: { project: any }) {
    const canPublish = ['submitted', 'under_review', 'approved'].includes(project.publication_status);
    const canReview = ['submitted', 'under_review'].includes(project.publication_status);
    const isPublished = project.publication_status === 'published';

    return <AdminLayout title="Project Preview">
        <article className="surface overflow-hidden">
            <div className="grid gap-6 p-5 md:grid-cols-[220px_1fr]">
                {project.poster_url ? <img src={project.poster_url} alt={`${project.title} poster`} className="w-full rounded-xl" /> : <div className="aspect-[2/3] rounded-xl bg-white/5" />}
                <div>
                    <div className="flex flex-wrap gap-2"><span className="badge">{project.content_type}</span><span className="badge">{project.stage}</span><span className="badge">{project.country}</span><span className="badge">{project.publication_status?.replaceAll('_', ' ')}</span></div>
                    <h2 className="editorial mt-4 text-4xl">{project.title}</h2>
                    <p className="mt-3 text-lg text-[#d0c9bb]">{project.logline}</p>
                    <p className="mt-4 whitespace-pre-wrap text-sm leading-7 text-[#aaa59b]">{project.description}</p>
                    {project.restricted?.screener_url && <div className="mt-5 rounded-xl border border-[#806e42]/50 bg-[#2d2719]/30 p-4"><div className="text-xs uppercase tracking-[.15em] text-[#c9ad68]">Restricted — administrator view</div><a className="mt-2 block break-all text-sm underline" href={project.restricted.screener_url} target="_blank" rel="noreferrer">{project.restricted.screener_url}</a></div>}
                </div>
            </div>
        </article>
        {(canPublish || canReview) && <AdminModerationActions approveUrl={canPublish ? `/admin/projects/${project.id}/approve` : undefined} transitionUrl={`/admin/projects/${project.id}/transition`} statuses={canReview ? reviewStatuses : removalStatuses} />}
        {isPublished && <div className="mt-4 grid gap-4">
            <div className="flex flex-wrap gap-2"><button className="btn btn-secondary" onClick={() => router.patch(`/admin/projects/${project.id}/feature`)}>{project.featured ? 'Unfeature' : 'Feature'}</button><button className="btn btn-secondary text-amber-200" onClick={() => router.patch(`/admin/projects/${project.id}/unpublish`)}>Unpublish</button></div>
            <AdminModerationActions transitionUrl={`/admin/projects/${project.id}/transition`} statuses={removalStatuses} />
        </div>}
        <Link href="/admin/projects" className="btn btn-secondary mt-4">Back to Queue</Link>
    </AdminLayout>;
}
