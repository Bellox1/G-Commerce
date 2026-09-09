import React, { useState, useCallback, useRef } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    TextInput, ActivityIndicator, RefreshControl, Modal, ScrollView, StatusBar, Alert,
    KeyboardAvoidingView, Platform
} from 'react-native';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { formatDateFr, formatDateTimeFr } from '../../utils/formatDate';
import TopHeaderNav from '../../components/TopHeaderNav';

const formatMoney = (val) => {
    if (!val && val !== 0) return '0 F';
    return Math.round(Number(val)).toLocaleString('fr-FR') + ' F';
};

const StockScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [activeTab, setActiveTab] = useState('stock'); // 'stock' or 'mouvements'

    // Tab 1: Stock
    const [produits, setProduits] = useState([]);
    const [magasins, setMagasins] = useState([]);
    const [stockParProduit, setStockParProduit] = useState({});
    const [selectedMagasin, setSelectedMagasin] = useState(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [search, setSearch] = useState('');

    // Tab 2: Mouvements
    const [mouvements, setMouvements] = useState([]);
    const [loadingMouv, setLoadingMouv] = useState(false);
    const [loadingMoreMouv, setLoadingMoreMouv] = useState(false);
    const [pageMouv, setPageMouv] = useState(1);
    const [lastPageMouv, setLastPageMouv] = useState(1);
    const loadingMoreRef = useRef(false);
    const [totalMouv, setTotalMouv] = useState(0);
    const [filterType, setFilterType] = useState('tous');

    // Modal ajustement
    const [adjustItem, setAdjustItem] = useState(null);
    const [adjustMagasin, setAdjustMagasin] = useState(null);
    const [adjustType, setAdjustType] = useState('ajustement_positif');
    const [adjustQty, setAdjustQty] = useState('');
    const [adjustRaison, setAdjustRaison] = useState('');
    const [submittingAdjust, setSubmittingAdjust] = useState(false);

    const fetchData = useCallback(async () => {
        try {
            const params = {};
            if (selectedMagasin) params.magasin_id = selectedMagasin;
            const resp = await client.get('/stock', { params });
            if (resp.data) {
                const prodList = Array.isArray(resp.data.data)
                    ? resp.data.data
                    : Array.isArray(resp.data.produits)
                        ? resp.data.produits
                        : Array.isArray(resp.data)
                            ? resp.data
                            : [];

                const magList = Array.isArray(resp.data.magasins)
                    ? resp.data.magasins
                    : Array.isArray(resp.data.data?.magasins)
                        ? resp.data.data.magasins
                        : [];

                setProduits(prodList);
                setMagasins(magList);
                setStockParProduit(resp.data.stock || {});
            }
        } catch (e) {
            console.error('Error fetching stock:', e);
            setProduits([]);
            setMagasins([]);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [selectedMagasin]);

    const fetchMouvements = useCallback(async (pageToLoad = 1, reset = false) => {
        if (reset) {
            setLoadingMouv(true);
        } else {
            setLoadingMoreMouv(true);
        }
        try {
            const params = { per_page: 10, page: pageToLoad };
            if (selectedMagasin) params.magasin_id = selectedMagasin;
            const resp = await client.get('/stock/mouvements', { params });
            const pag = resp.data?.data;
            const list = Array.isArray(pag?.data) ? pag.data : [];
            setMouvements(prev => (reset ? list : [...prev, ...list]));
            setLastPageMouv(pag?.last_page || 1);
            setTotalMouv(pag?.total || 0);
            setPageMouv(pageToLoad);
        } catch (e) {
            console.error('Error fetching mouvements:', e);
            setMouvements(prev => (reset ? [] : prev));
        } finally {
            setLoadingMouv(false);
            setLoadingMoreMouv(false);
            loadingMoreRef.current = false;
        }
    }, [selectedMagasin]);

    const loadMoreMouv = () => {
        if (loadingMoreRef.current || loadingMoreMouv || loadingMouv) return;
        if (pageMouv >= lastPageMouv) return;
        loadingMoreRef.current = true;
        fetchMouvements(pageMouv + 1, false);
    };

    useFocusEffect(
        useCallback(() => {
            if (activeTab === 'stock') {
                fetchData();
            } else {
                fetchMouvements();
            }
        }, [activeTab, fetchData, fetchMouvements])
    );

    const onRefresh = () => {
        setRefreshing(true);
        if (activeTab === 'stock') fetchData();
        else fetchMouvements();
    };

    const handleAdjustSubmit = async () => {
        if (!adjustQty || isNaN(adjustQty) || Number(adjustQty) <= 0) {
            Alert.alert('Erreur', 'Veuillez saisir une quantité valide.');
            return;
        }
        setSubmittingAdjust(true);
        try {
            const resp = await client.post('/stock/ajuster', {
                magasin_id: adjustMagasin || selectedMagasin || magasins[0]?.id,
                produit_id: adjustItem.id,
                type_mouvement: adjustType,
                quantite: Number(adjustQty),
                raison: adjustRaison || 'Ajustement manuel mobile'
            });

            // Mettre à jour le stock dans la liste locale des produits immédiatement
            const delta = Number(adjustQty);
            const isPos = adjustType === 'ajustement_positif';
            setProduits(prev => (Array.isArray(prev) ? prev : []).map(p => {
                if (p.id === adjustItem.id) {
                    const curr = Number(p.quantite_totale ?? p.quantite ?? p.stock ?? 0);
                    const nStk = isPos ? (curr + delta) : Math.max(0, curr - delta);
                    return { ...p, stock: nStk, stock_disponible: nStk, stock_actuel: nStk, quantite: nStk, quantite_totale: nStk };
                }
                return p;
            }));

            Alert.alert('Succès', 'Ajustement de stock enregistré avec succès !');
            setAdjustItem(null);
            setAdjustQty('');
            setAdjustRaison('');
            fetchData();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de l\'ajustement';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmittingAdjust(false);
        }
    };

    const safeProduits = Array.isArray(produits) ? produits : [];
    const filteredProduits = safeProduits
        .filter(p =>
            p && ((p.nom && p.nom.toLowerCase().includes(search.toLowerCase())) ||
            (p.code && p.code.toLowerCase().includes(search.toLowerCase())))
        )
        .sort((a, b) => {
            // Produits en stock d'abord, les 0 en bas
            const qtyA = Number(a.quantite_totale ?? a.quantite ?? 0);
            const qtyB = Number(b.quantite_totale ?? b.quantite ?? 0);
            if (qtyA > 0 && qtyB <= 0) return -1;
            if (qtyA <= 0 && qtyB > 0) return 1;
            return 0;
        });

    const safeMouvements = Array.isArray(mouvements) ? mouvements : [];
    const filteredMouvements = safeMouvements.filter(m => {
        if (filterType === 'tous') return true;
        return m.type === filterType;
    });

    const getTypeBadge = (type) => {
        switch (type) {
            case 'entree_arrivage': return { label: '+ Arrivage', color: Colors.success, bg: Colors.success + '18' };
            case 'ajustement_positif': return { label: '+ Ajout', color: Colors.success, bg: Colors.success + '18' };
            case 'transfert_entree': return { label: '+ Transfert Reçu', color: Colors.info, bg: Colors.info + '18' };
            case 'sortie_vente': return { label: '- Vente', color: Colors.error, bg: Colors.error + '18' };
            case 'ajustement_negatif': return { label: '- Retrait', color: Colors.error, bg: Colors.error + '18' };
            case 'transfert_sortie': return { label: '- Transfert Envoyé', color: Colors.warning, bg: Colors.warning + '18' };
            default: return { label: type || 'Mouvement', color: Colors.textLight, bg: Colors.border };
        }
    };

    return (
        <View style={styles.container}>
            {/* Top Navigation Header replacing Drawer */}
            <TopHeaderNav navigation={navigation} activeCategory="stock" />

            {/* View Title Header */}
            <View style={styles.viewHeaderRow}>
                <Text style={styles.headerTitle}>Gestion & Stock</Text>
                <Text style={styles.headerSub}>Suivez l'état du stock et l'historique complet</Text>
            </View>

                {/* Tabs */}
                <View style={styles.tabRow}>
                    <TouchableOpacity
                        style={[styles.tabBtn, activeTab === 'stock' && styles.tabBtnActive]}
                        onPress={() => setActiveTab('stock')}
                    >
                        <Ionicons name="cube-outline" size={16} color={activeTab === 'stock' ? Colors.primary : Colors.textLight} />
                        <Text style={[styles.tabText, activeTab === 'stock' && styles.tabTextActive]}>Stock Actuel</Text>
                    </TouchableOpacity>
                    <TouchableOpacity
                        style={[styles.tabBtn, activeTab === 'mouvements' && styles.tabBtnActive]}
                        onPress={() => setActiveTab('mouvements')}
                    >
                        <Ionicons name="swap-horizontal-outline" size={16} color={activeTab === 'mouvements' ? Colors.primary : Colors.textLight} />
                        <Text style={[styles.tabText, activeTab === 'mouvements' && styles.tabTextActive]}>Historique Mouvements</Text>
                    </TouchableOpacity>
                </View>

            {/* Filtres & Recherche — style identique à Nos Dettes */}
            <View style={styles.filtersWrap}>
                {/* Search bar */}
                <View style={styles.searchBar}>
                    <Ionicons name="search" size={18} color={Colors.textLight} />
                    <TextInput
                        style={styles.searchInput}
                        placeholder={activeTab === 'stock' ? 'Rechercher par nom ou code...' : 'Rechercher un mouvement...'}
                        placeholderTextColor={Colors.textLight}
                        value={search}
                        onChangeText={setSearch}
                    />
                    {search ? (
                        <TouchableOpacity onPress={() => setSearch('')}>
                            <Ionicons name="close-circle" size={18} color={Colors.textLight} />
                        </TouchableOpacity>
                    ) : null}
                </View>

                {/* Sélecteur de magasin */}
                {magasins.length > 0 && (
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipScroll} contentContainerStyle={styles.chipScrollContent}>
                        <TouchableOpacity
                            style={[styles.chip, selectedMagasin === null && styles.chipActive]}
                            onPress={() => setSelectedMagasin(null)}
                        >
                            <Text style={[styles.chipText, selectedMagasin === null && styles.chipTextActive]}>Tous les dépôts</Text>
                        </TouchableOpacity>
                        {magasins.map((m) => (
                            <TouchableOpacity
                                key={m.id}
                                style={[styles.chip, selectedMagasin === m.id && styles.chipActive]}
                                onPress={() => setSelectedMagasin(m.id)}
                            >
                                <Text style={[styles.chipText, selectedMagasin === m.id && styles.chipTextActive]}>{m.nom}</Text>
                            </TouchableOpacity>
                        ))}
                    </ScrollView>
                )}
            </View>


            {/* Content Tab 1: Stock */}
            {activeTab === 'stock' ? (
                loading ? (
                    <View style={styles.centerLoader}>
                        <ActivityIndicator size="large" color={Colors.primary} />
                        <Text style={styles.loaderText}>Chargement du stock...</Text>
                    </View>
                ) : (
                    <FlatList
                        data={filteredProduits}
                        keyExtractor={(item, index) => (item?.id != null ? `${item.id}-${index}` : `k-${index}`)}
                        contentContainerStyle={[styles.listContent, { paddingBottom: Math.max(insets.bottom + 100, 120) }]}
                        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                        ListEmptyComponent={
                            <View style={styles.emptyBox}>
                                <Ionicons name="cube-outline" size={50} color={Colors.border} />
                                <Text style={styles.emptyTitle}>Aucun produit dans le stock</Text>
                            </View>
                        }
                        renderItem={({ item }) => {
                            const currentStock = stockParProduit[item.id] ?? item.stock_actuel ?? 0;
                            const isLow = item.seuil_alerte ? currentStock <= item.seuil_alerte : currentStock <= 5;

                            return (
                                <View style={styles.card}>
                                    <View style={styles.cardHeader}>
                                        <View style={styles.prodInfo}>
                                            <Text style={styles.prodName}>{item.nom}</Text>
                                            {item.code ? <Text style={styles.prodCode}>Réf: {item.code}</Text> : null}
                                        </View>
                                        <View style={[styles.stockBadge, { backgroundColor: isLow ? Colors.warning + '20' : Colors.success + '20' }]}>
                                            <Text style={[styles.stockBadgeText, { color: isLow ? Colors.warning : Colors.success }]}>
                                                {currentStock} carton(s)
                                            </Text>
                                        </View>
                                    </View>

                                    <TouchableOpacity style={styles.adjustBtn} onPress={() => { setAdjustItem(item); setAdjustMagasin(selectedMagasin); }}>
                                        <Ionicons name="build-outline" size={16} color={Colors.primary} />
                                        <Text style={styles.adjustBtnText}>Ajuster le stock</Text>
                                    </TouchableOpacity>
                                </View>
                            );
                        }}
                    />
                )
            ) : (
                /* Content Tab 2: Mouvements */
                loadingMouv ? (
                    <View style={styles.centerLoader}>
                        <ActivityIndicator size="large" color={Colors.primary} />
                        <Text style={styles.loaderText}>Chargement des mouvements de stock...</Text>
                    </View>
                ) : (
                    <View style={{ flex: 1 }}>
                        <Text style={styles.countText}>
                            {totalMouv} mouvement{totalMouv > 1 ? 's' : ''} au total
                        </Text>
                        <FlatList
                            data={filteredMouvements}
                            keyExtractor={(item, index) => (item?.id != null ? `${item.id}-${index}` : `k-${index}`)}
                            contentContainerStyle={[styles.listContent, { paddingBottom: Math.max(insets.bottom + 100, 120) }]}
                            refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                            onEndReached={loadMoreMouv}
                            onEndReachedThreshold={0.3}
                            ListFooterComponent={
                                loadingMoreMouv ? (
                                    <View style={styles.listFooter}>
                                        <ActivityIndicator color={Colors.primary} />
                                        <Text style={styles.listFooterText}>Chargement...</Text>
                                    </View>
                                ) : pageMouv < lastPageMouv ? (
                                    <View style={styles.listFooter}>
                                        <Text style={styles.listFooterText}>Tirez pour plus de mouvements</Text>
                                    </View>
                                ) : null
                            }
                            ListEmptyComponent={
                                !loadingMouv ? (
                                    <View style={styles.emptyBox}>
                                        <Ionicons name="swap-horizontal-outline" size={50} color={Colors.border} />
                                        <Text style={styles.emptyTitle}>Aucun mouvement enregistré</Text>
                                    </View>
                                ) : null
                            }
                            renderItem={({ item }) => {
                                const badge = getTypeBadge(item.type);
                                const dateStr = item.date_mouvement || item.created_at ? formatDateTimeFr(item.date_mouvement || item.created_at) : '—';

                                return (
                                    <View style={styles.mouvCard}>
                                        <View style={styles.mouvHeader}>
                                            <View style={[styles.badge, { backgroundColor: badge.bg }]}>
                                                <Text style={[styles.badgeText, { color: badge.color }]}>{badge.label}</Text>
                                            </View>
                                            <Text style={styles.mouvDate}>{dateStr}</Text>
                                        </View>

                                        <Text style={styles.mouvProd}>{item.produit?.nom || 'Produit'}</Text>
                                        <Text style={styles.mouvMeta}>Magasin: {item.magasin?.nom || '—'} • Par: {item.user?.name || 'Système'}</Text>

                                        {item.note ? <Text style={styles.mouvNote}>Note: {item.note}</Text> : null}

                                        <View style={styles.mouvFooter}>
                                            <Text style={styles.mouvQtyLabel}>Quantité :</Text>
                                            <Text style={[styles.mouvQtyVal, { color: badge.color }]}>
                                                {item.type?.includes('entree') || item.type?.includes('positif') ? `+${item.quantite}` : `-${item.quantite}`} cartons
                                            </Text>
                                        </View>
                                    </View>
                                );
                            }}
                        />
                    </View>
                )
            )}

            {/* Modal Ajustement */}
            <Modal visible={!!adjustItem} transparent animationType="slide" onRequestClose={() => setAdjustItem(null)}>
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
                <View style={styles.modalOverlay}>
                    <View style={[styles.modalCard, { paddingBottom: Math.max(insets.bottom + 24, 28) }]}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Ajuster : {adjustItem?.nom}</Text>
                            <TouchableOpacity onPress={() => setAdjustItem(null)}>
                                <Ionicons name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>

                        {/* Switch Type */}
                        <View style={styles.typeSwitch}>
                            <TouchableOpacity
                                style={[styles.typeBtn, adjustType === 'ajustement_positif' && styles.typeBtnPos]}
                                onPress={() => setAdjustType('ajustement_positif')}
                            >
                                <Ionicons name="add-circle-outline" size={18} color={adjustType === 'ajustement_positif' ? '#FFF' : Colors.success} />
                                <Text style={[styles.typeText, adjustType === 'ajustement_positif' && styles.typeTextActive]}>+ Ajout Stock</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={[styles.typeBtn, adjustType === 'ajustement_negatif' && styles.typeBtnNeg]}
                                onPress={() => setAdjustType('ajustement_negatif')}
                            >
                                <Ionicons name="remove-circle-outline" size={18} color={adjustType === 'ajustement_negatif' ? '#FFF' : Colors.error} />
                                <Text style={[styles.typeText, adjustType === 'ajustement_negatif' && styles.typeTextActive]}>- Retrait Stock</Text>
                            </TouchableOpacity>
                        </View>

                        <View style={styles.fieldGroup}>
                            <Text style={styles.label}>Magasin concerné</Text>
                            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.magasinScrollContent}>
                                {magasins.map((m) => (
                                    <TouchableOpacity
                                        key={m.id}
                                        style={[styles.magasinChip, adjustMagasin === m.id && styles.magasinChipActive]}
                                        onPress={() => setAdjustMagasin(m.id)}
                                    >
                                        <Ionicons name="storefront-outline" size={13} color={adjustMagasin === m.id ? '#FFF' : Colors.textLight} style={{ marginRight: 4 }} />
                                        <Text style={[styles.magasinChipText, adjustMagasin === m.id && styles.magasinChipTextActive]}>{m.nom}</Text>
                                    </TouchableOpacity>
                                ))}
                            </ScrollView>
                        </View>

                        <View style={styles.fieldGroup}>
                            <Text style={styles.label}>Quantité (cartons)</Text>
                            <TextInput
                                style={styles.input}
                                keyboardType="number-pad"
                                placeholder="Ex: 5"
                                value={adjustQty}
                                onChangeText={setAdjustQty}
                            />
                        </View>

                        <View style={styles.fieldGroup}>
                            <Text style={styles.label}>Raison / Remarque</Text>
                            <TextInput
                                style={styles.input}
                                placeholder="Ex: Inventaire, Casse, Correction..."
                                value={adjustRaison}
                                onChangeText={setAdjustRaison}
                            />
                        </View>

                        <TouchableOpacity style={styles.submitBtn} onPress={handleAdjustSubmit} disabled={submittingAdjust}>
                            {submittingAdjust ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>Valider l'ajustement</Text>}
                        </TouchableOpacity>
                    </View>
                </View>

                </KeyboardAvoidingView>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    viewHeaderRow: {
        paddingHorizontal: 16,
        paddingTop: 14,
        paddingBottom: 8,
        backgroundColor: '#FFFFFF',
    },
    headerTitle: { fontSize: 20, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    headerSub: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    tabRow: { flexDirection: 'row', backgroundColor: '#F8FAFC', borderRadius: 12, padding: 4, marginBottom: 12, marginHorizontal: 16, borderWidth: 1, borderColor: '#E2E8F0' },
    tabBtn: { flex: 1, flexDirection: 'row', justifyContent: 'center', alignItems: 'center', gap: 6, paddingVertical: 8, borderRadius: 8 },
    tabBtnActive: { backgroundColor: '#FFFFFF' },
    tabText: { fontSize: 12, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.textLight },
    tabTextActive: { fontFamily: 'PlusJakartaSans_700Bold', color: Colors.primary },
    searchBar: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 14, paddingHorizontal: 12, height: 44, marginBottom: 8, marginHorizontal: 16, borderWidth: 1, borderColor: '#E2E8F0' },
    searchInput: { flex: 1, marginLeft: 8, fontFamily: 'PlusJakartaSans_400Regular', fontSize: 13, color: Colors.text },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 10 },
    loaderText: { fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    listContent: { padding: 16, paddingBottom: 32 },
    emptyBox: { alignItems: 'center', paddingVertical: 60, gap: 10 },
    emptyTitle: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.text },
    card: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, elevation: 1, borderWidth: 1, borderColor: '#E2E8F0' },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 12 },
    prodInfo: { flex: 1 },
    prodName: { fontSize: 15, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.primary },
    prodCode: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    stockBadge: { paddingHorizontal: 12, paddingVertical: 4, borderRadius: 12 },
    stockBadgeText: { fontSize: 12, fontFamily: 'PlusJakartaSans_700Bold' },
    adjustBtn: { flexDirection: 'row', justifyContent: 'center', alignItems: 'center', gap: 6, borderWidth: 1, borderColor: Colors.primary, borderRadius: 10, paddingVertical: 8 },
    adjustBtnText: { fontSize: 13, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.primary },
    mouvCard: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, borderWidth: 1, borderColor: '#E2E8F0' },
    mouvHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
    badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
    badgeText: { fontSize: 11, fontFamily: 'PlusJakartaSans_700Bold' },
    mouvDate: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    mouvProd: { fontSize: 15, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.primary },
    mouvMeta: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    mouvNote: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.text, fontStyle: 'italic', marginTop: 4 },
    mouvFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 8, marginTop: 8 },
    mouvQtyLabel: { fontSize: 12, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.textLight },
    mouvQtyVal: { fontSize: 15, fontFamily: 'SpaceGrotesk_700Bold' },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalCard: { backgroundColor: Colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    modalTitle: { fontSize: 17, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    typeSwitch: { flexDirection: 'row', gap: 10, marginBottom: 16 },
    typeBtn: { flex: 1, flexDirection: 'row', justifyContent: 'center', alignItems: 'center', gap: 6, paddingVertical: 10, borderRadius: 10, borderWidth: 1, borderColor: Colors.border },
    typeBtnPos: { backgroundColor: Colors.success, borderColor: Colors.success },
    typeBtnNeg: { backgroundColor: Colors.error, borderColor: Colors.error },
    typeText: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    typeTextActive: { color: '#FFF' },
    fieldGroup: { marginBottom: 12 },
    label: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.textLight, marginBottom: 4 },
    input: { backgroundColor: Colors.background, borderRadius: 10, paddingHorizontal: 12, paddingVertical: 10, fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.text, borderWidth: 1, borderColor: Colors.border },
    submitBtn: { backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 14, alignItems: 'center', marginTop: 12 },
    submitBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'Poppins_700Bold' },
    filtersWrap: { paddingHorizontal: 16, paddingBottom: 8 },
    chipScroll: { marginBottom: 6 },
    chipScrollContent: { gap: 8 },
    chip: { paddingHorizontal: 14, paddingVertical: 8, borderRadius: 20, backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border },
    chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    chipText: { fontSize: 13, fontFamily: 'PlusJakartaSans_600SemiBold', color: '#334155' },
    chipTextActive: { color: '#FFFFFF', fontFamily: 'PlusJakartaSans_700Bold' },
    listFooter: { alignItems: 'center', paddingVertical: 16, gap: 6 },
    listFooterText: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    countText: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.textLight, paddingHorizontal: 16, paddingBottom: 8, marginTop: 4 },
    magasinScrollContent: { gap: 8, paddingVertical: 2 },
    magasinChip: {
        flexDirection: 'row', alignItems: 'center',
        paddingHorizontal: 14, paddingVertical: 9, borderRadius: 12,
        backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border,
    },
    magasinChipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    magasinChipText: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    magasinChipTextActive: { color: '#FFF' },
});

export default StockScreen;
