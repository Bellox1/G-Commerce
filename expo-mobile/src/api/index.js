import client from './client';

// Dashboard & Analytique
export const getDashboard   = (params) => client.get('/dashboard', { params });
export const getAnalytique  = (params) => client.get('/analytique', { params });
export const storeDepense   = (data)   => client.post('/dashboard/depense', data);

// Produits
export const getProduits    = ()       => client.get('/produits');
export const storeProduit   = (data)   => client.post('/produits', data);
export const updateProduit  = (id, data) => client.put(`/produits/${id}`, data);
export const deleteProduit  = (id)     => client.delete(`/produits/${id}`);

// Clients
export const getClients     = ()       => client.get('/clients');
export const storeClient    = (data)   => client.post('/clients', data);
export const updateClient   = (id, data) => client.put(`/clients/${id}`, data);
export const deleteClient   = (id)     => client.delete(`/clients/${id}`);

// Ventes
export const getVentes      = ()       => client.get('/ventes');
export const getVente       = (id)     => client.get(`/ventes/${id}`);
export const storeVente     = (data)   => client.post('/ventes', data);
export const updateVente    = (id, data) => client.put(`/ventes/${id}`, data);
export const deleteVente    = (id)     => client.delete(`/ventes/${id}`);

// Stock
export const getStock       = ()       => client.get('/stock');
export const getMouvements  = ()       => client.get('/stock/mouvements');
export const ajusterStock   = (data)   => client.post('/stock/ajuster', data);

// Arrivages
export const getArrivages   = ()       => client.get('/arrivages');
export const getArrivage    = (id)     => client.get(`/arrivages/${id}`);
export const storeArrivage  = (data)   => client.post('/arrivages', data);
export const updateArrivage = (id, data) => client.put(`/arrivages/${id}`, data);
export const deleteArrivage = (id)     => client.delete(`/arrivages/${id}`);
export const validerArrivage= (id)     => client.post(`/arrivages/${id}/valider`);

// Dettes
export const getDettes      = ()       => client.get('/dettes');
export const getDette       = (id)     => client.get(`/dettes/${id}`);
export const storeDette     = (data)   => client.post('/dettes', data);
export const updateDette    = (id, data) => client.put(`/dettes/${id}`, data);
export const deleteDette    = (id)     => client.delete(`/dettes/${id}`);
export const payerDette     = (id, data) => client.post(`/dettes/${id}/payer`, data);

// Dettes Société
export const getDettesSociete   = ()       => client.get('/dettes-societe');
export const storeDetteSociete  = (data)   => client.post('/dettes-societe', data);
export const payerDetteSociete  = (id, data) => client.post(`/dettes-societe/${id}/payer`, data);
export const deleteDetteSociete = (id)     => client.delete(`/dettes-societe/${id}`);

// Employés
export const getEmployes    = ()       => client.get('/employes');
export const storeEmploye   = (data)   => client.post('/employes', data);
export const updateEmploye  = (id, data) => client.put(`/employes/${id}`, data);
export const deleteEmploye  = (id)     => client.delete(`/employes/${id}`);
export const toggleEmployeActive = (id) => client.post(`/employes/${id}/toggle-active`);

// Magasins
export const getMagasins    = ()       => client.get('/magasins');
export const storeMagasin   = (data)   => client.post('/magasins', data);
export const updateMagasin  = (id, data) => client.put(`/magasins/${id}`, data);

// Transferts
export const getTransferts  = ()       => client.get('/transferts');
export const getTransfert   = (id)     => client.get(`/transferts/${id}`);
export const storeTransfert = (data)   => client.post('/transferts', data);

// Livraisons
export const getLivraisons  = ()       => client.get('/livraisons');
export const updateLivraisonStatut = (id, data) => client.put(`/livraisons/${id}/statut`, data);

// Profil
export const getProfile     = ()       => client.get('/profile');
export const updateProfile  = (data)   => client.put('/profile', data);
export const updatePassword = (data)   => client.put('/profile/password', data);

// Fournisseurs
export const getFournisseurs  = ()       => client.get('/fournisseurs');
export const storeFournisseur = (data)   => client.post('/fournisseurs', data);
export const updateFournisseur = (id, data) => client.put(`/fournisseurs/${id}`, data);
export const deleteFournisseur = (id)   => client.delete(`/fournisseurs/${id}`);
