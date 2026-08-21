import client from './client';

export const getDashboard = () => client.get('/analytique');
export const getStock     = () => client.get('/stock');
export const getProfile   = () => client.get('/profile');
export const getVentes    = () => client.get('/ventes');
export const getDettes    = () => client.get('/dettes');
export const getProduits  = () => client.get('/produits');
export const getClients   = () => client.get('/clients');
export const getMagasins  = () => client.get('/magasins');
export const getArrivages = () => client.get('/arrivages');
