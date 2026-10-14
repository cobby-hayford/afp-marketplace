import type { ReactNode } from 'react';
export default function Field({label,error,children,hint}:{label:string;error?:string;children:ReactNode;hint?:string}){return <label className="block"><span className="label">{label}</span>{children}{hint&&<span className="mt-1 block text-xs text-[#858179]">{hint}</span>}{error&&<span className="error">{error}</span>}</label>}
