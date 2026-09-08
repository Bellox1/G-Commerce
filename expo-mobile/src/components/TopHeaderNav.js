import React, { useRef, useEffect, useState } from 'react';
import {
    View, Text, StyleSheet, TouchableOpacity,
    ScrollView, Platform, StatusBar, Image, Modal, Linking
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import Colors from '../theme/Colors';
import { useAuth } from '../context/AuthContext';
import { useCan } from '../utils/permissions';
import useAlertCount from '../hooks/useAlertCount';
import { BASE_URL } from '../api/client';

const ALL_CATEGORIES = [
    { key: 'dashboard',      label: 'Aperçu',      icon: 'grid-outline',             iconActive: 'grid',              always: true,         screen: 'MainTabs' },
    { key: 'produits',       label: 'Produits',     icon: 'cube-outline',             iconActive: 'cube',              perm: 'produits',     screen: 'DrawerProduits' },
    { key: 'arrivages',      label: 'Arrivages',    icon: 'download-outline',         iconActive: 'download',          perm: 'arrivages',    screen: 'DrawerArrivages' },
    { key: 'ventes',         label: 'Ventes',       icon: 'cart-outline',             iconActive: 'cart',              perm: 'ventes',       screen: 'Ventes' },
    { key: 'livraisons',     label: 'Livraisons',   icon: 'checkmark-done-outline',   iconActive: 'checkmark-done',    perm: 'livraisons',   screen: 'DrawerLivraisons' },
    { key: 'clients',        label: 'Clients',      icon: 'people-outline',           iconActive: 'people',            perm: 'clients',      screen: 'DrawerClients' },
    { key: 'dettes',         label: 'Dettes',       icon: 'wallet-outline',           iconActive: 'wallet',            perm: 'dettes',       screen: 'DrawerDettes' },
    { key: 'transferts',     label: 'Transferts',   icon: 'swap-horizontal-outline',  iconActive: 'swap-horizontal',   perm: 'transferts',   screen: 'DrawerTransferts' },
    { key: 'dettes-societe', label: 'Nos dettes',   icon: 'briefcase-outline',        iconActive: 'briefcase',         perm: 'dettes',       screen: 'DrawerDettesSociete' },
    { key: 'tresorerie',     label: 'Trésorerie',   icon: 'cash-outline',             iconActive: 'cash',              perm: 'tresorerie',   screen: 'Tresorerie' },
    { key: 'stock',          label: 'Stock',        icon: 'layers-outline',           iconActive: 'layers',            perm: 'stock',        screen: 'Stock' },
    { key: 'magasins',       label: 'Magasins',     icon: 'storefront-outline',       iconActive: 'storefront',        perm: 'magasins',     screen: 'DrawerMagasins' },
    { key: 'employes',       label: 'Personnel',    icon: 'people-circle-outline',    iconActive: 'people-circle',     perm: 'utilisateurs', screen: 'DrawerEmployes' },
    { key: 'analytique',     label: 'Analytique',   icon: 'bar-chart-outline',        iconActive: 'bar-chart',         always: true,         screen: 'DrawerAnalytique' },
    { key: 'offre',          label: 'Offre',        icon: 'star-outline',             iconActive: 'star',              always: true,         screen: 'Offre' },
    { key: 'faq',            label: 'FAQ & Aide',   icon: 'help-circle-outline',      iconActive: 'help-circle',       always: true,         screen: 'FAQ' },
];

const TopHeaderNav = ({ navigation, activeCategory = 'dashboard' }) => {
    const { user } = useAuth();
    const can = useCan();
    const scrollRef = useRef(null);
    const insets = useSafeAreaInsets();
    const [menuModalVisible, setMenuModalVisible] = useState(false);

    // Single shared source of truth for the notification badge
    const alertCount = useAlertCount();

    const visibleCategories = ALL_CATEGORIES.filter((c) => c.always || can(c.perm));

    // Auto-scroll to active category
    useEffect(() => {
        if (!scrollRef.current) return;
        const activeIndex = visibleCategories.findIndex(c => c.key === activeCategory);
        if (activeIndex <= 0) return;
        setTimeout(() => {
            scrollRef.current?.scrollTo?.({ x: Math.max(0, activeIndex * 72 - 36), animated: true });
        }, 200);
    }, [activeCategory, visibleCategories.length]);

    const handleCategoryPress = (cat) => {
        if (!cat?.screen || !navigation) return;
        if (cat.screen === 'MainTabs') {
            try { navigation.navigate('MainTabs', { screen: 'Home' }); }
            catch (e) { navigation.navigate('MainTabs'); }
        } else {
            navigation.navigate(cat.screen);
        }
    };

    const handleOpenLink = (url) => {
        setMenuModalVisible(false);
        Linking.openURL(url).catch(() => {});
    };

    const handleSendSuggestion = () => {
        setMenuModalVisible(false);
        Linking.openURL('mailto:pilotixcontact@gmail.com?subject=Suggestion%20sur%20PILOTIX').catch(() => {});
    };

    const initials = user?.name
        ? user.name.split(' ').map((n) => n[0]).slice(0, 2).join('').toUpperCase()
        : 'P';

    return (
        <View style={styles.headerRoot}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top + 6, Platform.OS === 'ios' ? 50 : 40) }]}>
                <Image
                    source={require('../../assets/pilotix-logo.png')}
                    style={styles.logoImage}
                    resizeMode="contain"
                    fadeDuration={0}
                />

                {/* Search Pill */}
                <TouchableOpacity
                    style={styles.searchPillBar}
                    onPress={() => navigation.navigate('DrawerProduits')}
                    activeOpacity={0.88}
                >
                    <Ionicons name="search-outline" size={18} color={Colors.primary} style={{ marginRight: 8 }} />
                    <View style={{ flex: 1 }}>
                        <Text style={styles.searchPillTitle} numberOfLines={1}>Rechercher dans PILOTIX</Text>
                        <Text style={styles.searchPillSub} numberOfLines={1}>Produits · Clients · Ventes</Text>
                    </View>
                    <TouchableOpacity
                        style={styles.searchFilterCircle}
                        onPress={(e) => {
                            e.stopPropagation();
                            setMenuModalVisible(true);
                        }}
                        activeOpacity={0.7}
                    >
                        <Ionicons name="options-outline" size={16} color={Colors.text} />
                    </TouchableOpacity>
                </TouchableOpacity>

                {/* Notification Bell */}
                <TouchableOpacity style={styles.bellButton} onPress={() => navigation.navigate('Alertes')} activeOpacity={0.7}>
                    <Ionicons name="notifications-outline" size={21} color={Colors.text} />
                    {alertCount > 0 && (
                        <View style={styles.notificationBadge}>
                            <Text style={styles.notificationBadgeText}>{alertCount > 99 ? '99+' : alertCount}</Text>
                        </View>
                    )}
                </TouchableOpacity>

                {/* Profile Avatar */}
                <TouchableOpacity style={styles.avatarButton} onPress={() => navigation.navigate('Profile')} activeOpacity={0.8}>
                    <Text style={styles.avatarText}>{initials}</Text>
                </TouchableOpacity>
            </View>

            {/* Horizontal Category Bar with auto-scroll */}
            <View style={styles.categoriesBar}>
                <ScrollView
                    ref={scrollRef}
                    horizontal
                    showsHorizontalScrollIndicator={false}
                    contentContainerStyle={styles.categoriesScroll}
                >
                    {visibleCategories.map((cat) => {
                        const active = activeCategory === cat.key;
                        return (
                            <TouchableOpacity
                                key={cat.key}
                                style={styles.categoryItem}
                                onPress={() => handleCategoryPress(cat)}
                                activeOpacity={0.7}
                            >
                                <Ionicons
                                    name={active ? cat.iconActive : cat.icon}
                                    size={22}
                                    color={active ? Colors.primary : Colors.textLight}
                                />
                                <Text style={[styles.categoryLabel, active && styles.categoryLabelActive]}>
                                    {cat.label}
                                </Text>
                                {active && <View style={styles.categoryActiveLine} />}
                            </TouchableOpacity>
                        );
                    })}
                </ScrollView>
            </View>

            {/* Modal Menu Options (Conditions, Confidentialité, Suggestions) */}
            <Modal visible={menuModalVisible} transparent animationType="fade" onRequestClose={() => setMenuModalVisible(false)}>
                <TouchableOpacity
                    style={styles.modalOverlay}
                    activeOpacity={1}
                    onPress={() => setMenuModalVisible(false)}
                >
                    <TouchableOpacity activeOpacity={1} style={styles.menuModalCard}>
                        <View style={styles.menuHeader}>
                            <Text style={styles.menuTitle}>Informations & Support</Text>
                            <TouchableOpacity onPress={() => setMenuModalVisible(false)}>
                                <Ionicons name="close" size={22} color={Colors.text} />
                            </TouchableOpacity>
                        </View>

                        <TouchableOpacity
                            style={styles.menuItem}
                            onPress={() => handleOpenLink(`${BASE_URL}/conditions`)}
                            activeOpacity={0.7}
                        >
                            <View style={[styles.menuIconBox, { backgroundColor: '#EFF6FF' }]}>
                                <Ionicons name="document-text-outline" size={20} color={Colors.primary} />
                            </View>
                            <Text style={styles.menuItemText}>Conditions d'utilisation</Text>
                            <Ionicons name="chevron-forward" size={16} color={Colors.textLight} />
                        </TouchableOpacity>

                        <TouchableOpacity
                            style={styles.menuItem}
                            onPress={() => handleOpenLink(`${BASE_URL}/confidentialite`)}
                            activeOpacity={0.7}
                        >
                            <View style={[styles.menuIconBox, { backgroundColor: '#F0FDF4' }]}>
                                <Ionicons name="shield-checkmark-outline" size={20} color={Colors.success} />
                            </View>
                            <Text style={styles.menuItemText}>Politique de confidentialité</Text>
                            <Ionicons name="chevron-forward" size={16} color={Colors.textLight} />
                        </TouchableOpacity>

                        <View style={styles.menuDivider} />

                        <TouchableOpacity
                            style={[styles.menuItem, styles.suggestionItem]}
                            onPress={handleSendSuggestion}
                            activeOpacity={0.7}
                        >
                            <View style={[styles.menuIconBox, { backgroundColor: '#FEF3C7' }]}>
                                <Ionicons name="bulb" size={20} color="#D97706" />
                            </View>
                            <View style={{ flex: 1 }}>
                                <Text style={[styles.menuItemText, { color: '#92400E', fontFamily: 'PlusJakartaSans_700Bold' }]}>Faire une suggestion</Text>
                                <Text style={{ fontSize: 11, color: Colors.textLight }}>Envoyer un e-mail à l'équipe PILOTIX</Text>
                            </View>
                            <Ionicons name="mail" size={18} color="#D97706" />
                        </TouchableOpacity>
                    </TouchableOpacity>
                </TouchableOpacity>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    headerRoot: {
        backgroundColor: '#FFFFFF',
        borderBottomWidth: 1,
        borderBottomColor: '#F1F5F9',
    },
    topBar: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 14,
        paddingTop: Platform.OS === 'ios' ? 52 : (StatusBar.currentHeight ? StatusBar.currentHeight + 8 : 44),
        paddingBottom: 10,
        backgroundColor: '#FFFFFF',
        gap: 8,
    },
    logoImage: { width: 40, height: 40 },
    searchPillBar: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#FFFFFF',
        borderRadius: 24,
        paddingHorizontal: 12,
        paddingVertical: 7,
        borderWidth: 1,
        borderColor: '#E2E8F0',
        elevation: 3,
        shadowColor: '#0F172A',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.08,
        shadowRadius: 6,
    },
    searchPillTitle: { fontSize: 12.5, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    searchPillSub: { fontSize: 10.5, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    searchFilterCircle: {
        width: 28, height: 28, borderRadius: 14,
        backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center',
    },
    bellButton: {
        width: 38, height: 38, borderRadius: 19,
        backgroundColor: '#F8FAFC', justifyContent: 'center', alignItems: 'center',
        borderWidth: 1, borderColor: '#E2E8F0', position: 'relative',
    },
    notificationBadge: {
        position: 'absolute', top: 1, right: 1,
        minWidth: 16, height: 16, borderRadius: 8,
        backgroundColor: Colors.error, justifyContent: 'center', alignItems: 'center', paddingHorizontal: 3,
    },
    notificationBadgeText: { fontSize: 9.5, fontFamily: 'PlusJakartaSans_700Bold', color: '#FFFFFF' },
    avatarButton: {
        width: 38, height: 38, borderRadius: 19,
        backgroundColor: Colors.primary, justifyContent: 'center', alignItems: 'center',
    },
    avatarText: { color: '#FFFFFF', fontSize: 14, fontFamily: 'SpaceGrotesk_700Bold' },
    categoriesBar: { borderTopWidth: 1, borderTopColor: '#F8FAFC', backgroundColor: '#FFFFFF' },
    categoriesScroll: { paddingHorizontal: 14, paddingVertical: 10, gap: 20 },
    categoryItem: {
        alignItems: 'center', justifyContent: 'center',
        position: 'relative', paddingBottom: 6,
    },
    categoryLabel: { fontSize: 11.5, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.textLight, marginTop: 4 },
    categoryLabelActive: { color: Colors.primary, fontFamily: 'PlusJakartaSans_700Bold' },
    categoryActiveLine: {
        position: 'absolute', bottom: 0, left: 0, right: 0,
        height: 2.5, backgroundColor: Colors.primary, borderRadius: 1.5,
    },
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(15, 23, 42, 0.45)',
        justifyContent: 'flex-end',
    },
    menuModalCard: {
        backgroundColor: '#FFFFFF',
        borderTopLeftRadius: 24,
        borderTopRightRadius: 24,
        padding: 20,
        paddingBottom: 34,
        gap: 8,
    },
    menuHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 12,
        paddingBottom: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#F1F5F9',
    },
    menuTitle: { fontSize: 16, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    menuItem: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingVertical: 12,
        paddingHorizontal: 12,
        borderRadius: 12,
        backgroundColor: '#F8FAFC',
        gap: 12,
    },
    menuIconBox: {
        width: 36, height: 36, borderRadius: 10,
        justifyContent: 'center', alignItems: 'center',
    },
    menuItemText: { flex: 1, fontSize: 14, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.text },
    menuDivider: { height: 1, backgroundColor: '#E2E8F0', marginVertical: 4 },
    suggestionItem: { backgroundColor: '#FFFBEB', borderWidth: 1, borderColor: '#FCD34D' },
});

export default TopHeaderNav;
