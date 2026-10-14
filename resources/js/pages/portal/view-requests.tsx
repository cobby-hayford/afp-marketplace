import { Link, router } from '@inertiajs/react';
import Pagination from '../../components/Pagination';
import PortalLayout from '../../layouts/PortalLayout';

export default function ViewRequests({incoming,outgoing}:{incoming:any;outgoing:any[]}) {
  return <PortalLayout title="View Requests">
    <div className="grid gap-8 xl:grid-cols-2">
      <section><h2 className="editorial mb-4 text-2xl">Requests for My Projects</h2><div className="grid gap-3">
        {incoming.data.length===0?<Empty text="No incoming viewing requests."/>:incoming.data.map((request:any)=><div className="surface p-5" key={request.id}>
          <div className="flex flex-wrap justify-between gap-3"><div><strong>{request.requester?.first_name} {request.requester?.last_name}</strong><div className="text-sm text-[#8e8a82]">wants access to {request.project?.title}</div></div><span className="badge">{request.status}</span></div>
          <dl className="mt-4 grid gap-2 text-sm"><div><span className="text-[#817d75]">Interest:</span> {request.interest_type}</div>{request.territory&&<div><span className="text-[#817d75]">Territory:</span> {request.territory}</div>}<div><span className="text-[#817d75]">Reason:</span> {request.reason}</div>{request.message&&<div><span className="text-[#817d75]">Message:</span> {request.message}</div>}</dl>
          {request.status==='pending'&&<div className="mt-4 flex gap-2"><button className="btn btn-primary" onClick={()=>router.post(`/view-requests/${request.id}/approve`)}>Approve</button><button className="btn btn-secondary" onClick={()=>router.post(`/view-requests/${request.id}/decline`)}>Decline</button></div>}
          {request.status==='approved'&&<button className="btn btn-secondary mt-4" onClick={()=>router.post(`/view-requests/${request.id}/revoke`)}>Revoke Access</button>}
        </div>)}
      </div><Pagination page={incoming}/></section>
      <section><h2 className="editorial mb-4 text-2xl">My Requests</h2><div className="grid gap-3">{outgoing.length===0?<Empty text="You have not requested restricted project access yet."/>:outgoing.map((request:any)=><div className="surface p-5" key={request.id}><div className="flex justify-between gap-3"><Link className="font-semibold hover:text-[#dfc77f]" href={`/projects/${request.project?.slug}`}>{request.project?.title}</Link><span className="badge">{request.status}</span></div>{request.decision_note&&<p className="mt-3 text-sm text-[#99958c]">{request.decision_note}</p>}</div>)}</div></section>
    </div>
  </PortalLayout>;
}
function Empty({text}:{text:string}){return <div className="surface p-7 text-sm text-[#8f8b83]">{text}</div>}
