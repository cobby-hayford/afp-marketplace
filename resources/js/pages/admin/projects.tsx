import { Link, router } from '@inertiajs/react';
import AdminModerationActions from '../../components/AdminModerationActions';
import Pagination from '../../components/Pagination';
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

export default function Projects({ projects, filters }: { projects: any; filters: any }) {
    return <AdminLayout title="Project Moderation">
        <div className="mb-5 flex flex-wrap gap-2">
            {['submitted', 'under_review', 'approved', 'published', 'changes_requested', 'rejected', 'suspended', 'archived'].map(status =>
                <button key={status} className={`badge ${filters.status === status ? 'badge-gold' : ''}`} onClick={() => router.get('/admin/projects', { status })}>
                    {status.replaceAll('_', ' ')}
                </button>
            )}
        </div>
        <div className="grid gap-4">
            {projects.data.map((project: any) => {
                const canPublish = ['submitted', 'under_review', 'approved'].includes(project.publication_status);
                const canReview = ['submitted', 'under_review'].includes(project.publication_status);
                const isPublished = project.publication_status === 'published';
                return <article key={project.id} className="surface p-5">
                    <div className="flex flex-wrap justify-between gap-4">
                        <div>
                            <h2 className="editorial text-2xl">{project.title}</h2>
                            <p className="text-sm text-[#918d84]">{project.owner?.profile?.professional_name || `${project.owner?.first_name} ${project.owner?.last_name}`} · {project.country?.name} · {project.content_type?.name} · {project.stage?.name}</p>
                        </div>
                        <div className="flex gap-2"><span className="badge">{project.publication_status.replaceAll('_', ' ')}</span>{project.featured && <span className="badge badge-gold">Featured</span>}</div>
                    </div>
                    <p className="mt-4 text-sm text-[#aaa59b]">{project.logline}</p>
                    <div className="mt-4 flex flex-wrap gap-2">
                        <Link href={`/admin/projects/${project.id}`} className="btn btn-secondary">Preview Listing</Link>
                        {isPublished && <button className="btn btn-secondary" onClick={() => router.patch(`/admin/projects/${project.id}/feature`)}>{project.featured ? 'Unfeature' : 'Feature'}</button>}
                        {isPublished && <button className="btn btn-secondary text-amber-200" onClick={() => router.patch(`/admin/projects/${project.id}/unpublish`)}>Unpublish</button>}
                    </div>
                    {(canPublish || canReview) && <AdminModerationActions
                        approveUrl={canPublish ? `/admin/projects/${project.id}/approve` : undefined}
                        transitionUrl={`/admin/projects/${project.id}/transition`}
                        statuses={canReview ? reviewStatuses : removalStatuses}
                    />}
                    {isPublished && <AdminModerationActions
                        transitionUrl={`/admin/projects/${project.id}/transition`}
                        statuses={removalStatuses}
                    />}
                </article>;
            })}
        </div>
        <Pagination page={projects} />
    </AdminLayout>;
}
