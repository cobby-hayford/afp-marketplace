import { Link, router, usePage } from '@inertiajs/react';
import { Bell } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { PageProps } from '../types';

type EchoLike = { private: (channel: string) => { notification: (callback: () => void) => { stopListening?: (event: string) => void }; leave?: () => void } };

export default function NotificationBell() {
  const { notifications, auth } = usePage<PageProps>().props;
  const [open, setOpen] = useState(false);

useEffect(() => {
    const echo = window.Echo as EchoLike | undefined;
    const userId = auth.user?.id;

    if (!echo || !userId) return;

    const channel = echo.private(`user.${userId}`);

    channel.notification(() =>
        router.reload({ only: ['notifications', 'adminCounts'] })
    );

    return () => {
        window.Echo?.leave?.(`user.${userId}`);
    };
}, [auth.user?.id]);

  return <div className="relative">
    <button className="relative rounded-lg p-2 hover:bg-white/5" onClick={() => setOpen(!open)} aria-label="Notifications">
      <Bell size={20}/>
      {notifications.unread_count > 0 && <span className="absolute -right-1 -top-1 min-w-5 rounded-full bg-red-600 px-1 text-center text-[11px] font-bold text-white">{notifications.unread_count}</span>}
    </button>
    {open && <div className="absolute right-0 z-50 mt-2 w-[min(22rem,90vw)] surface bg-[#15171a] p-2 shadow-2xl">
      <div className="flex items-center justify-between px-2 py-2">
        <strong>Notifications</strong>
        {notifications.unread_count > 0 && <button className="text-xs text-[#d9bd79]" onClick={() => router.patch('/notifications/read-all')}>Mark all read</button>}
      </div>
      <div className="max-h-80 overflow-y-auto">
        {notifications.recent.length === 0 ? <p className="px-2 py-5 text-sm text-[#9f9b92]">No notifications yet.</p> : notifications.recent.map(n =>
          <Link key={n.id} href={n.data.url || '/notifications'} onClick={() => { if (!n.read_at) router.patch(`/notifications/${n.id}/read`); setOpen(false); }} className="block rounded-lg px-2 py-2 hover:bg-white/5">
            <div className="text-sm font-semibold">{n.data.title}</div>
            <div className="text-xs text-[#a7a49d]">{n.data.message}</div>
          </Link>)}
      </div>
      <Link href="/notifications" className="mt-1 block border-t border-white/10 px-2 py-2 text-sm text-[#d9bd79]">View all notifications</Link>
    </div>}
  </div>;
}
