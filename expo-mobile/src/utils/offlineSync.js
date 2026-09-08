import AsyncStorage from '@react-native-async-storage/async-storage';

const QUEUE_KEY = '@offline_action_queue';
const CACHE_PREFIX = '@cache_';
const OFFLINE_VENTES_KEY = '@offline_ventes';

/**
 * Sauvegarde des données en cache local pour la consultation hors-ligne.
 */
export async function setCache(key, data) {
    try {
        await AsyncStorage.setItem(CACHE_PREFIX + key, JSON.stringify({
            timestamp: Date.now(),
            data
        }));
    } catch (e) {
        console.warn('Erreur mise en cache locale:', e);
    }
}

/**
 * Récupération des données du cache local si hors-ligne.
 */
export async function getCache(key) {
    try {
        const item = await AsyncStorage.getItem(CACHE_PREFIX + key);
        if (!item) return null;
        const parsed = JSON.parse(item);
        return parsed.data;
    } catch (e) {
        console.warn('Erreur lecture cache local:', e);
        return null;
    }
}

/**
 * Récupère la file d'attente des actions hors-ligne.
 */
export async function getOfflineQueue() {
    try {
        const raw = await AsyncStorage.getItem(QUEUE_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch (e) {
        return [];
    }
}

/**
 * Ajoute une opération (vente, produit, client) à la file hors-ligne.
 */
export async function queueOfflineAction(action) {
    try {
        const queue = await getOfflineQueue();
        const newAction = {
            id: 'offline_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            createdAt: new Date().toISOString(),
            ...action
        };
        queue.push(newAction);
        await AsyncStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
        return newAction;
    } catch (e) {
        console.error('Erreur mise en file hors-ligne:', e);
        throw e;
    }
}

/**
 * Traite et synchronise la file d'attente hors-ligne avec le serveur Laravel sans conflit.
 */
export async function syncOfflineQueue(onProgress) {
    const queue = await getOfflineQueue();
    if (!queue || queue.length === 0) {
        return { synced: 0, failed: 0, remaining: 0 };
    }

    // Require dynamique pour éviter les require cycles avec client.js
    const client = require('../api/client').default;
    const { removeOfflineVente, getOfflineVentes, clearOfflineVentes } = require('./offlineSync');

    let synced = 0;
    let failed = 0;
    const remainingQueue = [];
    let hasSyncedVente = false;

    for (let i = 0; i < queue.length; i++) {
        const item = queue[i];
        if (onProgress) onProgress(i + 1, queue.length, item);

        try {
            if (item.method === 'POST' || !item.method) {
                const response = await client.post(item.endpoint, item.payload);
                
                // Si c'était une création de vente hors-ligne, marquer pour nettoyage
                if (item.endpoint.includes('/ventes')) {
                    hasSyncedVente = true;
                }
            } else if (item.method === 'PUT') {
                await client.put(item.endpoint, item.payload);
            } else if (item.method === 'DELETE') {
                await client.delete(item.endpoint);
            }
            synced++;
        } catch (e) {
            const status = e.response?.status;
            if (status === 422 || status === 409 || status === 404) {
                console.warn(`Action hors-ligne ${item.id} ignorée/résolue (${status}):`, e.response?.data?.message);
                synced++;
            } else {
                console.warn(`Échec synchro pour ${item.id}, conservation dans la file:`, e.message);
                remainingQueue.push(item);
                failed++;
            }
        }
    }

    // Si au moins une vente a été synchronisée, vider toutes les ventes hors-ligne
    // (le serveur a maintenant les vraies ventes avec références VNT-...)
    if (hasSyncedVente) {
        await clearOfflineVentes();
    }

    await AsyncStorage.setItem(QUEUE_KEY, JSON.stringify(remainingQueue));
    return { synced, failed, remaining: remainingQueue.length };
}

/**
 * Retourne le nombre d'opérations en attente de synchronisation.
 */
export async function getPendingCount() {
    const queue = await getOfflineQueue();
    return queue.length;
}

/**
 * Vide la file d'attente.
 */
export async function clearOfflineQueue() {
    await AsyncStorage.removeItem(QUEUE_KEY);
}

/**
 * Sauvegarde une vente créée hors-ligne pour affichage immédiat.
 */
export async function saveOfflineVente(vente) {
    try {
        const existing = await getOfflineVentes();
        const offlineVente = {
            ...vente,
            id: vente.id || 'offline_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            isOffline: true,
            createdAt: new Date().toISOString(),
        };
        existing.unshift(offlineVente); // Ajouter au début (plus récent en premier)
        await AsyncStorage.setItem(OFFLINE_VENTES_KEY, JSON.stringify(existing));
        return offlineVente;
    } catch (e) {
        console.error('Erreur sauvegarde vente hors-ligne:', e);
    }
}

/**
 * Récupère les ventes créées hors-ligne.
 */
export async function getOfflineVentes() {
    try {
        const raw = await AsyncStorage.getItem(OFFLINE_VENTES_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch (e) {
        console.error('Erreur lecture ventes hors-ligne:', e);
        return [];
    }
}

/**
 * Supprime une vente hors-ligne (après synchronisation réussie).
 */
export async function removeOfflineVente(venteId) {
    try {
        const existing = await getOfflineVentes();
        const filtered = existing.filter(v => v.id !== venteId);
        await AsyncStorage.setItem(OFFLINE_VENTES_KEY, JSON.stringify(filtered));
    } catch (e) {
        console.error('Erreur suppression vente hors-ligne:', e);
    }
}

/**
 * Vide toutes les ventes hors-ligne.
 */
export async function clearOfflineVentes() {
    await AsyncStorage.removeItem(OFFLINE_VENTES_KEY);
}
