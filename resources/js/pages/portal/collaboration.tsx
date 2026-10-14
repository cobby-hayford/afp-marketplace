import { Link, router } from '@inertiajs/react';
import Pagination from '../../components/Pagination';
import PortalLayout from '../../layouts/PortalLayout';

export default function Collaboration({posts}:{posts:any}) {
  return <PortalLayout title="Collaboration">
    <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
      <p className="max-w-2xl text-sm leading-6 text-[#9d998f]">Publish moderated collaboration calls and connect privately with suitable industry members.</p>
      <Link href="/collaboration/create" className="btn btn-primary">Create Collaboration Post</Link>
    </div>
    <div className="grid gap-4">
      {posts.data.length === 0 ? <div className="surface p-8 text-center"><h2 className="editorial text-2xl">No collaboration posts yet</h2><p className="mt-2 text-sm text-[#97938a]">Create your first call for a co-producer, director, investor, distributor or production partner.</p></div> : posts.data.map((post:any) => <article key={post.id} className="surface p-5">
        <div className="flex flex-wrap justify-between gap-3">
          <div><h2 className="editorial text-2xl">{post.title}</h2><p className="mt-1 text-sm text-[#969188]">{post.type?.name} · {post.country?.name}</p></div>
          <span className="badge">{post.publication_status.replaceAll('_',' ')}</span>
        </div>
        <p className="mt-4 text-sm leading-6 text-[#aaa59b]">{post.description}</p>
        <div className="mt-4 flex flex-wrap gap-2">
          {post.publication_status === 'published' && <Link className="btn btn-secondary" href={`/collaboration/${post.slug}`}>View Public Post</Link>}
          {['draft','changes_requested','rejected'].includes(post.publication_status) && <Link className="btn btn-secondary" href={`/my-collaboration/${post.slug}/edit`}>Edit</Link>}
          {['draft','changes_requested','rejected'].includes(post.publication_status) && <button className="btn btn-primary" onClick={() => router.post(`/collaboration/${post.slug}/submit`)}>Submit for Review</button>}
        </div>
        {post.responses?.length>0&&<div className="mt-4 border-t border-white/10 pt-4"><div className="text-sm font-semibold">Responses ({post.responses.length})</div><div className="mt-2 grid gap-2">{post.responses.map((r:any)=><div key={r.id} className="rounded-lg border border-white/10 p-3 text-sm"><strong>{r.user?.first_name} {r.user?.last_name}</strong>{r.message&&<p className="mt-1 text-[#99958c]">{r.message}</p>}<Link className="mt-2 inline-block text-[#d9bd79]" href="/messages">Open private conversations →</Link></div>)}</div></div>}
      </article>)}
    </div>
    <Pagination page={posts}/>
  </PortalLayout>;
}
