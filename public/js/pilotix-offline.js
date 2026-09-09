/**
 * PILOTIX — Gestionnaire Hors Ligne
 * Stocke les opérations créées sans connexion (ventes, arrivages)
 * dans IndexedDB et les synchronise dès la reconnexion.
 * Version: 1.0.0
 */
const PilotixOffline = (function () {
    'use strict';

    const DB_NAME    = 'pilotix-offline-v1';
    const DB_VERSION = 1;
    const S_VENTES   = 'pending_ventes';
    const S_ARRIVAGES= 'pending_arrivages';
    const S_STOCK    = 'stock_snapshots';

    let _db = null;

    /* ── Ouvrir / créer la base IndexedDB ── */
    function openDB() {
        if (_db) return Promise.resolve(_db);
        return new Promise(function (resolve, reject) {
            const req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = function (e) {
                const db = e.target.result;
                if (!db.objectStoreNames.contains(S_VENTES)) {
                    db.createObjectStore(S_VENTES,    { keyPath: 'offline_id', autoIncrement: true });
                }
                if (!db.objectStoreNames.contains(S_ARRIVAGES)) {
                    db.createObjectStore(S_ARRIVAGES, { keyPath: 'offline_id', autoIncrement: true });
                }
                if (!db.objectStoreNames.contains(S_STOCK)) {
                    db.createObjectStore(S_STOCK, { keyPath: 'magasin_id' });
                }
            };
            req.onsuccess = function (e) { _db = e.target.result; resolve(_db); };
            req.onerror   = function (e) { reject(e.target.error); };
        });
    }

    /* ── Helpers génériques ── */
    function _get(store, key) {
        return openDB().then(function (db) {
            return new Promise(function (resolve, reject) {
                const req = db.transaction(store, 'readonly').objectStore(store).get(key);
                req.onsuccess = function (e) { resolve(e.target.result); };
                req.onerror   = function (e) { reject(e.target.error); };
            });
        });
    }

    function _getAll(store) {
        return openDB().then(function (db) {
            return new Promise(function (resolve, reject) {
                const req = db.transaction(store, 'readonly').objectStore(store).getAll();
                req.onsuccess = function (e) { resolve(e.target.result || []); };
                req.onerror   = function (e) { reject(e.target.error); };
            });
        });
    }

    function _add(store, data) {
        return openDB().then(function (db) {
            return new Promise(function (resolve, reject) {
                const item = Object.assign({ _status: 'pending', _created_at: new Date().toISOString() }, data);
                const req  = db.transaction(store, 'readwrite').objectStore(store).add(item);
                req.onsuccess = function (e) { resolve(e.target.result); };
                req.onerror   = function (e) { reject(e.target.error); };
            });
        });
    }

    function _put(store, data) {
        return openDB().then(function (db) {
            return new Promise(function (resolve, reject) {
                const req = db.transaction(store, 'readwrite').objectStore(store).put(data);
                req.onsuccess = function (e) { resolve(e.target.result); };
                req.onerror   = function (e) { reject(e.target.error); };
            });
        });
    }

    function _delete(store, key) {
        return openDB().then(function (db) {
            return new Promise(function (resolve, reject) {
                const req = db.transaction(store, 'readwrite').objectStore(store).delete(key);
                req.onsuccess = function () { resolve(); };
                req.onerror   = function (e) { reject(e.target.error); };
            });
        });
    }

    /* ── Snapshots stock ── */
    function saveStockSnapshot(magasinId, produits) {
        return _put(S_STOCK, {
            magasin_id:  String(magasinId),
            produits:    produits,
            snapshot_at: new Date().toISOString(),
        });
    }

    function getStockSnapshot(magasinId) {
        return _get(S_STOCK, String(magasinId));
    }

    /* Déduire les quantités vendues du snapshot local (pour validation offline chaînée) */
    async function deductFromSnapshot(magasinId, lignes) {
        const snap = await getStockSnapshot(magasinId);
        if (!snap) return;
        lignes.forEach(function (ligne) {
            const p = snap.produits.find(function (x) { return String(x.id) === String(ligne.produit_id); });
            if (p) {
                p.stock = Math.max(0, (p.stock || 0) - (ligne.quantite || 0));
            }
        });
        await saveStockSnapshot(magasinId, snap.produits);
    }

    /* ── Ventes en attente ── */
    function storeVente(formBody, display, url) {
        return _add(S_VENTES, { _form_data: formBody, _display: display, _url: url || '/ventes' });
    }
    function getPendingVentes()         { return _getAll(S_VENTES); }
    function deletePendingVente(id)     { return _delete(S_VENTES, id); }

    /* ── Arrivages en attente ── */
    function storeArrivage(formBody, display, url) {
        return _add(S_ARRIVAGES, { _form_data: formBody, _display: display, _url: url });
    }
    function getPendingArrivages()      { return _getAll(S_ARRIVAGES); }
    function deletePendingArrivage(id)  { return _delete(S_ARRIVAGES, id); }

    /* ── Nombre total d'opérations en attente ── */
    async function countPending() {
        const v = await getPendingVentes();
        const a = await getPendingArrivages();
        return v.length + a.length;
    }

    /* ── Synchronisation ── */
    let _syncLock = false;

    async function syncAll() {
        if (_syncLock || !navigator.onLine) return { ventes: 0, arrivages: 0, errors: 0 };
        _syncLock = true;

        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        let synced_v = 0, synced_a = 0, errors = 0;

        async function postItem(item) {
            const params = new URLSearchParams(item._form_data);
            params.delete('_token');
            const resp = await fetch(item._url, {
                method:   'POST',
                headers:  { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token, 'X-Offline-Sync': '1' },
                body:     params.toString(),
                redirect: 'follow',
            });
            return resp.ok || resp.redirected || resp.status === 302;
        }

        try {
            /* Ventes */
            for (const item of await getPendingVentes()) {
                try {
                    if (await postItem(item)) {
                        await deletePendingVente(item.offline_id);
                        synced_v++;
                    } else {
                        errors++;
                    }
                } catch (_) { errors++; }
            }

            /* Arrivages */
            for (const item of await getPendingArrivages()) {
                try {
                    if (await postItem(item)) {
                        await deletePendingArrivage(item.offline_id);
                        synced_a++;
                    } else {
                        errors++;
                    }
                } catch (_) { errors++; }
            }
        } finally {
            _syncLock = false;
        }

        return { ventes: synced_v, arrivages: synced_a, errors };
    }

    /* ── API publique ── */
    return {
        openDB,
        saveStockSnapshot, getStockSnapshot, deductFromSnapshot,
        storeVente,    getPendingVentes,    deletePendingVente,
        storeArrivage, getPendingArrivages, deletePendingArrivage,
        countPending,  syncAll,
    };
})();
