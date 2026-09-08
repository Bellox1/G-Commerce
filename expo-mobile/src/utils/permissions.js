import { useAuth } from '../context/AuthContext';

// Calque exact des permissions web (app/Models/User.php :: peutGerer*)
// À utiliser côté mobile pour filtrer menus/drawer comme sur le layout web.
export const useCan = () => {
    const { hasRole } = useAuth();

    return (perm) => {
        switch (perm) {
            case 'utilisateurs': return hasRole('super_admin') || hasRole('admin') || hasRole('superviseur');
            case 'arrivages':    return hasRole('super_admin') || hasRole('admin') || hasRole('superviseur') || hasRole('magasinier');
            case 'produits':     return hasRole('super_admin') || hasRole('admin') || hasRole('superviseur') || hasRole('magasinier') || hasRole('vendeur');
            case 'magasins':     return hasRole('super_admin') || hasRole('admin') || hasRole('superviseur');
            case 'ventes':       return hasRole('super_admin') || hasRole('admin') || hasRole('superviseur') || hasRole('vendeur');
            case 'clients':      return hasRole('super_admin') || hasRole('admin') || hasRole('superviseur') || hasRole('vendeur') || hasRole('magasinier');
            case 'dettes':       return hasRole('super_admin') || hasRole('admin') || hasRole('superviseur') || hasRole('vendeur');
            case 'tresorerie':   return hasRole('super_admin') || hasRole('admin') || hasRole('superviseur');
            case 'stock':        return hasRole('super_admin') || hasRole('admin') || hasRole('superviseur') || hasRole('magasinier') || hasRole('vendeur');
            case 'transferts':   return hasRole('super_admin') || hasRole('admin') || hasRole('superviseur') || hasRole('magasinier');
            case 'livraisons':   return hasRole('super_admin') || hasRole('admin') || hasRole('superviseur') || hasRole('controleur') || hasRole('magasinier');
            default: return false;
        }
    };
};
