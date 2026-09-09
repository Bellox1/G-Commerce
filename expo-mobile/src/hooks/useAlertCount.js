import { useEffect, useState } from 'react';
import client from '../api/client';
import { getCache } from '../utils/offlineSync';

const useAlertCount = () => {
    const [alertCount, setAlertCount] = useState(0);

    useEffect(() => {
        let cancelled = false;
        let lastKnownCount = 0; // Garde la dernière valeur connue pour ne pas effacer le badge offline

        const fetchCount = async () => {
            try {
                const res = await client.get('/notifications');
                if (cancelled) return;
                const body = res.data;

                // Réponse vide forcée hors-ligne → conserver le dernier badge connu
                if (res.isOfflineEmpty) {
                    // Essayer le cache produits avant de garder l'ancienne valeur
                    const cachedProduitsRes = (await getCache('/produits?per_page=1000')) || (await getCache('/produits'));
                    const produitsList = Array.isArray(cachedProduitsRes?.data?.data)
                        ? cachedProduitsRes.data.data
                        : (Array.isArray(cachedProduitsRes?.data)
                            ? cachedProduitsRes.data
                            : (Array.isArray(cachedProduitsRes) ? cachedProduitsRes : []));
                    if (produitsList.length > 0) {
                        const cnt = produitsList.filter(p => {
                            const stk = p.stock ?? p.stock_disponible ?? p.stock_actuel ?? 0;
                            const seuil = p.seuil_alerte ?? 5;
                            return stk <= seuil;
                        }).length;
                        lastKnownCount = cnt;
                        setAlertCount(cnt);
                    } else {
                        // Aucune donnée → conserver le badge actuel (ne pas remettre à zéro)
                        setAlertCount(prev => prev > 0 ? prev : lastKnownCount);
                    }
                    return;
                }
                
                let stockAlertsCount = Array.isArray(body?.stockAlertes) ? body.stockAlertes.length : -1;
                let unreadNotifsCount = Array.isArray(body?.items) ? body.items.filter(n => !n.read_at).length : 0;

                if (stockAlertsCount === -1) {
                    // Cache partiel -> consulter le cache des notifications ou des produits
                    const cachedNotifs = await getCache('/notifications');
                    if (cachedNotifs && Array.isArray(cachedNotifs.stockAlertes)) {
                        stockAlertsCount = cachedNotifs.stockAlertes.length;
                        if (Array.isArray(cachedNotifs.items)) {
                            unreadNotifsCount = cachedNotifs.items.filter(n => !n.read_at).length;
                        }
                    } else {
                        const cachedProduitsRes = (await getCache('/produits?per_page=1000')) || (await getCache('/produits'));
                        const produitsList = Array.isArray(cachedProduitsRes?.data?.data)
                            ? cachedProduitsRes.data.data
                            : (Array.isArray(cachedProduitsRes?.data)
                                ? cachedProduitsRes.data
                                : (Array.isArray(cachedProduitsRes) ? cachedProduitsRes : []));
                        
                        if (produitsList.length > 0) {
                            stockAlertsCount = produitsList.filter(p => {
                                const stk = p.stock ?? p.stock_disponible ?? p.stock_actuel ?? 0;
                                const seuil = p.seuil_alerte ?? 5;
                                return stk <= seuil;
                            }).length;
                        } else {
                            stockAlertsCount = 0;
                        }
                    }
                }

                const total = Math.max(0, stockAlertsCount) + unreadNotifsCount;
                lastKnownCount = total;
                setAlertCount(total);
            } catch (e) {
                // ignore — conserver la dernière valeur affichée
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
