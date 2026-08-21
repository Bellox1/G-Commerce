import React from 'react';
import {
    View,
    Text,
    StyleSheet,
    TouchableOpacity,
    Image,
} from 'react-native';
import { DrawerContentScrollView } from '@react-navigation/drawer';
import { DrawerActions } from '@react-navigation/native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useAuth } from '../context/AuthContext';
import Colors from '../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import { useCan } from '../utils/permissions';

const ROLE_LABELS = {
    super_admin: 'Super Admin',
    admin: 'Administrateur',
    superviseur: 'Superviseur',
    vendeur: 'Vendeur',
    controleur: 'Contrôleur',
    magasinier: 'Magasinier',
    prestataire: 'Prestataire',
};

const CustomDrawerContent = (props) => {
    const { user, logout, hasCapability, hasRole } = useAuth();
    const insets = useSafeAreaInsets();
    const can = useCan();

    const state = props.state;
    const activeRoute = state?.routes?.[state.index]?.name;

    const userRole = user?.role || user?.roles?.[0]?.name || null;
    const roleLabel = ROLE_LABELS[userRole] || userRole || 'Utilisateur';

    const mainItems = [
        { key: 'accueil',        label: 'Tableau de bord',     icon: 'grid-outline',            screen: 'MainTabs',         always: true },
        { key: 'produits',       label: 'Produits',            icon: 'cube-outline',            screen: 'DrawerProduits',   perm: 'produits' },
        { key: 'arrivages',      label: 'Arrivages',           icon: 'download-outline',        screen: 'DrawerArrivages',  perm: 'arrivages', cap: 'import' },
        { key: 'ventes',         label: 'Ventes',              icon: 'cart-outline',            screen: 'DrawerVentes',     perm: 'ventes' },
        { key: 'livraisons',     label: 'Livraisons',          icon: 'bicycle-outline',          screen: 'DrawerLivraisons', perm: 'livraisons' },
        { key: 'clients',        label: 'Clients',             icon: 'people-outline',          screen: 'DrawerClients',    perm: 'clients' },
        { key: 'dettes',         label: 'Dettes',              icon: 'wallet-outline',           screen: 'DrawerDettes',     perm: 'dettes' },
        { key: 'transferts',     label: 'Transferts',          icon: 'swap-horizontal-outline', screen: 'DrawerTransferts', perm: 'transferts', cap: 'multi_magasin' },
    ];

    const moreItems = [
        { key: 'dettes-societe', label: 'Nos dettes',          icon: 'briefcase-outline',    screen: 'DrawerDettesSociete', perm: 'dettes' },
        { key: 'stock',          label: 'Stock',               icon: 'layers-outline',       screen: 'DrawerStock',         perm: 'stock' },
        { key: 'magasins',       label: 'Magasins & Dépôts',   icon: 'storefront-outline',   screen: 'DrawerMagasins',      perm: 'magasins' },
        { key: 'employes',       label: 'Personnel',           icon: 'people-circle-outline',screen: 'DrawerEmployes',      perm: 'utilisateurs', cap: 'multi_user' },
    ];

    const secondaryItems = [
        { key: 'analytique', label: 'Analytique & Rapports', icon: 'bar-chart-outline',  screen: 'DrawerAnalytique', always: true },
        { key: 'offre',      label: 'Mon offre',             icon: 'star-outline',        screen: 'Offre',            requiresTenant: true },
        { key: 'depenses',   label: 'Dépenses',              icon: 'receipt-outline',     screen: 'Depenses',         requiresTenant: true },
        { key: 'faq',        label: 'FAQ & Aide',           icon: 'help-circle-outline',screen: 'FAQ',              always: true },
    ];

    const isSuper = hasRole('super_admin') || hasRole('prestataire');
    const visible = (items) => items.filter((i) => (i.always || can(i.perm)) && (!i.cap || isSuper || hasCapability(i.cap)) && (!i.requiresTenant || !!user?.tenant));
    const main = visible(mainItems);
    const more = visible(moreItems);
    const secondary = visible(secondaryItems);

    const initials = user?.name
        ? user.name.split(' ').map((n) => n[0]).slice(0, 2).join('').toUpperCase()
        : 'P';

    const renderItem = (item) => {
        const active = activeRoute === item.screen;
        return (
            <TouchableOpacity
                key={item.key}
                style={[styles.item, active && styles.itemActive]}
                onPress={() => {
                    props.navigation.navigate(item.screen);
                    props.navigation.dispatch(DrawerActions.closeDrawer());
                }}
                activeOpacity={0.7}
            >
                {active && <View style={styles.activeBar} />}
                <Ionicons
                    name={item.icon}
                    size={21}
                    color={active ? Colors.primary : Colors.textLight}
                    style={styles.itemIcon}
                />
                <Text style={[styles.itemLabel, active && styles.itemLabelActive]}>
                    {item.label}
                </Text>
            </TouchableOpacity>
        );
    };

    return (
        <View style={[styles.root, { paddingTop: insets.top }]}>
            {/* Header: Logo & User Card */}
            <View style={styles.header}>
                <View style={styles.brandRow}>
                    <Image
                        source={require('../../assets/pilotix.png')}
                        style={styles.brandLogo}
                        resizeMode="contain"
                    />
                </View>

                <View style={styles.userCard}>
                    <View style={styles.avatar}>
                        <Text style={styles.avatarText}>{initials}</Text>
                    </View>
                    <View style={styles.userInfo}>
                        <Text style={styles.userName} numberOfLines={1}>{user?.name || 'Utilisateur'}</Text>
                        <View style={styles.roleBadge}>
                            <Text style={styles.roleText}>{roleLabel}</Text>
                        </View>
                        {user?.magasin?.nom ? (
                            <Text style={styles.magasinText} numberOfLines={1}>📍 {user.magasin.nom}</Text>
                        ) : null}
                    </View>
                </View>
            </View>

            {/* Navigation List */}
            <DrawerContentScrollView
                {...props}
                style={{ flex: 1 }}
                contentContainerStyle={styles.scrollContent}
                showsVerticalScrollIndicator={false}
            >
                <Text style={styles.sectionHeader}>PRINCIPAUX</Text>
                {main.map(renderItem)}

                {more.length > 0 && (
                    <View style={styles.groupSection}>
                        <Text style={styles.sectionHeader}>GESTION</Text>
                        {more.map(renderItem)}
                    </View>
                )}

                {secondary.length > 0 && (
                    <View style={styles.groupSection}>
                        <Text style={styles.sectionHeader}>AUTRES</Text>
                        {secondary.map(renderItem)}
                    </View>
                )}
            </DrawerContentScrollView>

            {/* Footer */}
            <View style={[styles.footer, { paddingBottom: Math.max(insets.bottom, 16) }]}>
                <TouchableOpacity
                    style={styles.footerItem}
                    onPress={() => { props.navigation.navigate('Profile'); props.navigation.dispatch(DrawerActions.closeDrawer()); }}
                    activeOpacity={0.7}
                >
                    <Ionicons name="person-outline" size={20} color={Colors.text} />
                    <Text style={styles.footerLabel}>Mon Profil</Text>
                </TouchableOpacity>

                <TouchableOpacity style={styles.logoutBtn} onPress={logout} activeOpacity={0.8}>
                    <Ionicons name="log-out-outline" size={18} color={Colors.error} />
                    <Text style={styles.logoutText}>Déconnexion</Text>
                </TouchableOpacity>
            </View>
        </View>
    );
};

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#FFFFFF' },
    header: {
        paddingHorizontal: 16,
        paddingTop: 14,
        paddingBottom: 16,
        borderBottomWidth: 1,
        borderBottomColor: '#F1F5F9',
        backgroundColor: '#FFFFFF',
    },
    brandRow: {
        marginBottom: 12,
        alignItems: 'flex-start',
    },
    brandLogo: {
        width: 140,
        height: 42,
    },
    userCard: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#F8FAFC',
        padding: 12,
        borderRadius: 16,
        borderWidth: 1,
        borderColor: '#E2E8F0',
    },
    avatar: {
        width: 40,
        height: 40,
        borderRadius: 20,
        backgroundColor: Colors.primary,
        justifyContent: 'center',
        alignItems: 'center',
    },
    avatarText: { color: '#FFFFFF', fontSize: 15, fontFamily: 'SpaceGrotesk_700Bold' },
    userInfo: { marginLeft: 12, flex: 1 },
    userName: { fontSize: 14, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    roleBadge: {
        alignSelf: 'flex-start',
        backgroundColor: Colors.primaryLight,
        paddingHorizontal: 8,
        paddingVertical: 2,
        borderRadius: 6,
        marginTop: 2,
    },
    roleText: { fontSize: 11, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.primary },
    magasinText: { fontSize: 11, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 3 },

    scrollContent: { paddingTop: 14, paddingHorizontal: 12, paddingBottom: 16 },
    sectionHeader: {
        fontSize: 10,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: '#94A3B8',
        letterSpacing: 1,
        marginLeft: 12,
        marginBottom: 6,
        marginTop: 6,
    },
    groupSection: { marginTop: 14, paddingTop: 12, borderTopWidth: 1, borderTopColor: '#F1F5F9' },
    item: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingVertical: 10,
        paddingHorizontal: 14,
        borderRadius: 12,
        marginBottom: 2,
        position: 'relative',
    },
    activeBar: {
        position: 'absolute',
        left: 0,
        top: 6,
        bottom: 6,
        width: 3.5,
        borderRadius: 2,
        backgroundColor: Colors.primary,
    },
    itemActive: { backgroundColor: Colors.primaryLight },
    itemIcon: { marginRight: 12, width: 22, textAlign: 'center' },
    itemLabel: { fontSize: 14, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.text },
    itemLabelActive: { color: Colors.primary, fontFamily: 'PlusJakartaSans_700Bold' },

    footer: {
        borderTopWidth: 1,
        borderTopColor: '#F1F5F9',
        paddingTop: 10,
        paddingHorizontal: 16,
        gap: 8,
    },
    footerItem: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingVertical: 10,
        paddingHorizontal: 12,
        borderRadius: 12,
        gap: 10,
    },
    footerLabel: { fontSize: 14, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.text },
    logoutBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: '#FEF2F2',
        paddingVertical: 11,
        borderRadius: 20,
        gap: 8,
    },
    logoutText: { fontSize: 13.5, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.error },
});

export default CustomDrawerContent;
