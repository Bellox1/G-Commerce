import { useEffect, useState } from 'react';
import client from '../api/client';

const useAlertCount = () => {
    const [alertCount, setAlertCount] = useState(0);

    useEffect(() => {
        let cancelled = false;
        let stock = 0;
        let notif = 0;
        const combine = () => { if (!cancelled) setAlertCount(stock + notif); };

        // Stock alerts (same server source as the Alertes screen)
        client.get('/analytique')
            .then(res => {
                if (cancelled) return;
                const body = res.data;
                const payload = body && body.data !== undefined ? body.data : body;
                stock = (payload?.stockAlertes || []).length;
                combine();
            })
            .catch(() => {});

        // Unread subscription/abonnement notifications
        client.get('/notifications')
            .then(res => {
                if (cancelled) return;
                const body = res.data;
                notif = body?.count || (body?.items ? body.items.filter(n => !n.read_at).length : 0);
                combine();
            })
            .catch(() => {});

        return () => { cancelled = true; };
    }, []);

    return alertCount;
};

export default useAlertCount;
