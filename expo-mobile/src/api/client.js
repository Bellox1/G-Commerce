import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Alert } from 'react-native';
import { setCache, getCache, queueOfflineAction, syncOfflineQueue, saveOfflineVente } from '../utils/offlineSync';

// Évite de réafficher plusieurs fois la même alerte de blocage d'offre
let offerBlockAlertShown = false;

// Callback pour notifier la fin de synchronisation
let onSyncCompleteCallback = null;

export function setOnSyncComplete(callback) {
    onSyncCompleteCallback = callback;
}

// Callback pour notifier la déconnexion sur 401 (session expirée)
let onUnauthorizedCallback = null;

export function setOnUnauthorized(callback) {
    onUnauthorizedCallback = callback;
}

export const BASE_URL = 'https://pilotix.alwaysdata.net'; // 'http://192.168.1.13:8000' //
const API_URL = `${BASE_URL}/api`;

const client = axios.create({
    baseURL: API_URL,
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

client.interceptors.request.use(async (config) => {
    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

function makeCacheKey(config) {
    const url = config?.url || '';
    if (!config?.params || typeof config.params !== 'object' || Object.keys(config.params).length === 0) {
        return url;
    }
    const paramsStr = Object.keys(config.params)
        .sort()
        .map(k => `${encodeURIComponent(k)}=${encodeURIComponent(config.params[k])}`)
        .join('&');
    return `${url}?${paramsStr}`;
}

client.interceptors.response.use(
    async (response) => {
        // Mise à jour transparente du cache local pour les GET (hors profile)
        const url = response.config?.url || '';
        const shouldCache = response.config?.method?.toLowerCase() === 'get'
            && url
            && !url.includes('/profile');

        if (shouldCache) {
            const cacheKey = makeCacheKey(response.config);
            setCache(cacheKey, response.data);
            if (cacheKey !== url) {
                setCache(url, response.data);
            }
        }

        // Si le réseau refonctionne, tenter de vider la file d'attente hors-ligne (hors requête de synchro)
        if (!response.config?.isSyncRequest) {
            try {
                const syncRes = await syncOfflineQueue();
                if (syncRes && syncRes.synced > 0 && onSyncCompleteCallback) {
                    onSyncCompleteCallback();
                }
            } catch (e) {
                // Ignore sync errors silencieusement
            }
        }

        return response;
    },
    async (error) => {
        const config = error.config || {};
        const method = (config.method || 'get').toLowerCase();
        const requestUrl = config.url || '';
        const isLoginRequest = requestUrl.includes('/login');

        // ─── MODE HORS-LIGNE : pas de réponse serveur ───
        if (!error.response && !isLoginRequest) {

            if (method === 'get') {
                // 1. Essayer le cache avec paramètres, puis le cache de base
                const cacheKey = makeCacheKey(config);
                let cachedData = await getCache(cacheKey);
                if (!cachedData && cacheKey !== requestUrl) {
                    cachedData = await getCache(requestUrl);
                }

                if (cachedData) {
                    if (__DEV__) console.log(`[HORS-LIGNE] Cache utilisé: ${cacheKey}`);
                    return Promise.resolve({
                        data: cachedData,
                        status: 200,
                        statusText: 'OK (Cache local)',
                        headers: {},
                        config,
                        isOfflineCache: true,
                    });
                }
                // 2. Pas de cache → réponse vide silencieuse
                if (__DEV__) console.log(`[HORS-LIGNE] Aucun cache pour ${requestUrl}, liste vide renvoyée.`);
                return Promise.resolve({
                    data: { data: [], total: 0, last_page: 1, message: 'Hors-ligne' },
                    status: 200,
                    statusText: 'OK (Hors-ligne)',
                    headers: {},
                    config,
                    isOfflineEmpty: true,
                });
            }

            if (['post', 'put', 'delete'].includes(method)) {
                // Mise en file d'attente hors-ligne
                let payload = {};
                try {
                    payload = typeof config.data === 'string'
                        ? JSON.parse(config.data)
                        : (config.data || {});
                } catch (_) {
                    payload = config.data || {};
                }

                if (__DEV__) console.log(`[HORS-LIGNE] ${method.toUpperCase()} ${requestUrl} mis en file.`);
                await queueOfflineAction({
                    type: `${method.toUpperCase()}_OFFLINE`,
                    endpoint: requestUrl,
                    method: method.toUpperCase(),
                    payload,
                });

                // Vente hors-ligne → affichage immédiat dans la liste avec détails complets
                if (method === 'post' && requestUrl.includes('/ventes')) {
                    try {
                        const v = payload.ventes?.[0] || {};
                        const cachedClientsRes = await getCache('/clients');
                        const cachedMagasinsRes = await getCache('/magasins');
                        const cachedProduitsRes = (await getCache('/produits?per_page=1000')) || (await getCache('/produits'));

                        const clientsList = Array.isArray(cachedClientsRes?.data) ? cachedClientsRes.data : (Array.isArray(cachedClientsRes) ? cachedClientsRes : []);
                        const magasinsList = Array.isArray(cachedMagasinsRes?.data) ? cachedMagasinsRes.data : (Array.isArray(cachedMagasinsRes) ? cachedMagasinsRes : []);
                        const produitsList = Array.isArray(cachedProduitsRes?.data?.data) ? cachedProduitsRes.data.data : (Array.isArray(cachedProduitsRes?.data) ? cachedProduitsRes.data : (Array.isArray(cachedProduitsRes) ? cachedProduitsRes : []));

                        const clientObj = clientsList.find(c => c.id === v.client_id);
                        const magasinObj = magasinsList.find(m => m.id === payload.magasin_id);

                        const totalMontant = (v.lignes || []).reduce(
                            (sum, l) => sum + (Number(l.prix_vente || 0) * Number(l.quantite || 0)) + (Number(l.prix_cartouche || 0) * Number(l.quantite_cartouche || 0)),
                            0
                        );

                        const totalRemis = v.a_credit ? (parseFloat(v.montant_paye) || 0) : (parseFloat(v.montant_remis) || totalMontant);

                        const mappedLignes = (v.lignes || []).map(l => {
                            const pObj = produitsList.find(p => p.id === l.produit_id);
                            return {
                                id: 'line_off_' + Math.random().toString(36).substr(2, 5),
                                produit_id: l.produit_id,
                                quantite: Number(l.quantite || 0),
                                prix_vente: Number(l.prix_vente || 0),
                                quantite_cartouche: Number(l.quantite_cartouche || 0),
                                prix_cartouche: Number(l.prix_cartouche || 0),
                                sous_total: (Number(l.prix_vente || 0) * Number(l.quantite || 0)) + (Number(l.prix_cartouche || 0) * Number(l.quantite_cartouche || 0)),
                                produit: pObj ? {
                                    id: pObj.id,
                                    nom: pObj.nom,
                                    image: pObj.image,
                                    code_barres: pObj.code_barres
                                } : { id: l.produit_id, nom: l.nom || ('Produit #' + l.produit_id) }
                            };
                        });

                        const userRaw = await AsyncStorage.getItem('user');
                        const userObj = userRaw ? JSON.parse(userRaw) : null;
                        const nowIso = new Date().toISOString();

                        const offlineVente = {
                            ...payload,
                            id: 'OFF-' + Date.now(),
                            reference: 'VNT-OFF-' + Date.now().toString().slice(-6),
                            isOffline: true,
                            _offline: true,
                            montant_total: totalMontant,
                            montant_paye: totalRemis,
                            montant_reste: Math.max(0, totalMontant - totalRemis),
                            statut_paiement: v.a_credit ? (totalRemis > 0 ? 'partiel' : 'credit') : 'paye',
                            client: clientObj || (v.client_id ? { id: v.client_id, nom: 'Client #' + v.client_id } : null),
                            magasin: magasinObj || { nom: 'Magasin Local' },
                            user: userObj || { name: 'Vendeur' },
                            lignes: mappedLignes,
                            produits: mappedLignes,
                            date_vente: nowIso,
                            created_at: nowIso,
                        };

                        await saveOfflineVente(offlineVente);

                        // Déduire le stock des produits en cache localement
                        if (produitsList.length > 0) {
                            let updated = false;
                            const updatedProduits = produitsList.map(p => {
                                const lineMatch = (v.lignes || []).find(l => l.produit_id === p.id);
                                if (lineMatch) {
                                    updated = true;
                                    const currentStock = p.stock ?? p.stock_disponible ?? p.stock_actuel ?? 0;
                                    const newStock = Math.max(0, currentStock - Number(lineMatch.quantite || 0));
                                    return { ...p, stock: newStock, stock_disponible: newStock, stock_actuel: newStock };
                                }
                                return p;
                            });
                            if (updated) {
                                await setCache('/produits?per_page=1000', { data: updatedProduits });
                                await setCache('/produits', { data: updatedProduits });
                            }
                        }
                    } catch (errOfflineSave) {
                        console.error('Erreur enrichissement vente hors-ligne:', errOfflineSave);
                    }
                }

                // Ajustement de stock hors-ligne → mise à jour immédiate du stock local en cache
                if (method === 'post' && requestUrl.includes('/stock/ajuster')) {
                    try {
                        const cachedProduitsRes = (await getCache('/produits?per_page=1000')) || (await getCache('/produits'));
                        const produitsList = Array.isArray(cachedProduitsRes?.data?.data) ? cachedProduitsRes.data.data : (Array.isArray(cachedProduitsRes?.data) ? cachedProduitsRes.data : (Array.isArray(cachedProduitsRes) ? cachedProduitsRes : []));
                        const pId = payload.produit_id;
                        const qty = Number(payload.quantite || 0);
                        const typeMvt = payload.type_mouvement;

                        if (pId && produitsList.length > 0) {
                            const updatedProduits = produitsList.map(p => {
                                if (p.id === pId) {
                                    const current = Number(p.stock ?? p.stock_disponible ?? p.stock_actuel ?? 0);
                                    const newStk = typeMvt === 'ajustement_positif' ? (current + qty) : Math.max(0, current - qty);
                                    return { ...p, stock: newStk, stock_disponible: newStk, stock_actuel: newStk };
                                }
                                return p;
                            });
                            await setCache('/produits?per_page=1000', { data: updatedProduits });
                            await setCache('/produits', { data: updatedProduits });
                        }
                    } catch (errAdj) {
                        console.error('Erreur mise à jour stock ajusté hors-ligne:', errAdj);
                    }
                }

                return Promise.resolve({
                    data: {
                        success: true,
                        offline: true,
                        message: 'Opération enregistrée hors-ligne. Elle sera synchronisée dès le retour de la connexion.',
                    },
                    status: 200,
                    statusText: 'OK (Mode hors-ligne)',
                    headers: {},
                    config,
                    isOfflineQueue: true,
                });
            }
        }

        // ─── Session expirée ───
        if (error.response && error.response.status === 401 && !isLoginRequest) {
            if (__DEV__) console.log('[Auth] Session expirée (401), nettoyage token.');
            await AsyncStorage.multiRemove(['auth_token', 'user']);
            if (onUnauthorizedCallback) {
                onUnauthorizedCallback();
            }
        }

        // ─── Offre en pause / expirée ───
        if (error.response && error.response.status === 403) {
            const code = error.response.data?.code;
            if ((code === 'offer_paused' || code === 'offer_expired') && !offerBlockAlertShown) {
                offerBlockAlertShown = true;
                const msg = error.response.data?.message || 'Votre offre est suspendue.';
                Alert.alert('Accès bloqué', msg, [
                    { text: 'OK', onPress: () => { offerBlockAlertShown = false; } },
                ]);
            }
        }

        error.userMessage = apiErrorMessage(error, { isLogin: isLoginRequest });
        return Promise.reject(error);
    }
);

export function apiErrorMessage(error, { isLogin = false } = {}) {
    if (!error.response) {
        return "Serveur injoignable. Vous êtes en mode hors-ligne.";
    }

    const status = error.response.status;

    if (status >= 500) {
        return `Erreur serveur (${status}). Réessayez plus tard.`;
    }

    if (status === 401) {
        return isLogin
            ? "Email ou mot de passe incorrect."
            : "Session expirée, veuillez vous reconnecter.";
    }

    if (status === 422) {
        const errors = error.response.data?.errors;
        if (errors && Object.keys(errors).length) {
            return errors[Object.keys(errors)[0]][0];
        }
    }

    if (error.response.data?.message) {
        return error.response.data.message;
    }

    return "Une erreur est survenue. Réessayez plus tard.";
}

export default client;
