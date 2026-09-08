import { useEffect, useState } from 'react';
import client from '../api/client';

const useAlertCount = () => {
    const [alertCount, setAlertCount] = useState(0);

    useEffect(() => {
        let cancelled = false;

        const fetchCount = async () => {
            try {
                const res = await client.get('/notifications');
                if (cancelled) return;
                const body = res.data;
                const stockAlertsCount = body?.stockAlertes ? body.stockAlertes.length : 0;
                const unreadNotifsCount = body?.items ? body.items.filter(n => !n.read_at).length : 0;
                const total = stockAlertsCount + unreadNotifsCount;
                setAlertCount(total);
            } catch (e) {
                // ignore
            }
        };

        fetchCount();
        const interval = setInterval(fetchCount, 25000);

        return () => {
            cancelled = true;
            clearInterval(interval);
        };
    }, []);

    return alertCount;
};

export default useAlertCount;
