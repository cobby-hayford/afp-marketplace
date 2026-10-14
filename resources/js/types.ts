export type PageProps = {
  auth: { user: null | { id:number; first_name:string; last_name:string; email:string; role:string; account_type?:string }; profile_status?: string | null };
  notifications: { unread_count:number; recent:Array<{id:string;data:{title?:string;message?:string;url?:string;kind?:string};read_at?:string|null;created_at?:string}> };
  flash: { success?: string | null; error?: string | null };
  site: { name:string; subtitle:string; tagline:string; disclaimer?:string|null; contact_email?:string|null; contact_phone?:string|null; social:Record<string,string|null> };
  [key:string]: unknown;
};
export type ProjectCardData = { id:number; title:string; slug:string; country?:string|null; content_type?:string|null; stage?:string|null; genres:string[]; tags:string[]; budget?:string|null; currency?:string|null; logline?:string|null; poster_url?:string|null; featured:boolean; listing_approved:boolean; festival_verified:boolean };
export type Paginator<T> = { data:T[]; current_page:number; last_page:number; total:number; from:number|null; to:number|null; prev_page_url?:string|null; next_page_url?:string|null; links:Array<{url:string|null;label:string;active:boolean}> };
