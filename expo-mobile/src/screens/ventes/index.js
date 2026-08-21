import React, { useState, useCallback, useEffect } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity, Modal,
    TextInput, ActivityIndicator, RefreshControl, ScrollView, StatusBar, Platform
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import DateTimePicker from '@react-native-community/datetimepicker';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { formatDateFr, formatDateTimeFr } from '../../utils/formatDate';
import TopHeaderNav from '../../components/TopHeaderNav';

const toLocalDate = (d) => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
};

const formatMoney = (val) => {
    if (!val && val !== 0) return '0 F';
    return Math.round(Number(val)).toLocaleString('fr-FR') + ' F';
};

const VentesScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [ventes, setVentes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [loadingMore, setLoadingMore] = useState(false);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [searchInput, setSearchInput] = useState('');
    const [search, setSearch] = useState('');
    const [filterStatut, setFilterStatut] = useState('tous');
    const [periode, setPeriode] = useState('tous');
    const [dateDebut, setDateDebut] = useState(null);
    const [dateFin, setDateFin] = useState(null);
    const [showDateModal, setShowDateModal] = useState(false);
    const [dateField, setDateField] = useState(null);
    const [draftCount, setDraftCount] = useState(0);
    const [totalVentes, setTotalVentes] = useState(0);

    useEffect(() => {
        (async () => {
            try {
                const saved = await AsyncStorage.getItem('vente_draft');
                if (saved) {
                    const d = JSON.parse(saved);
                    if (d && Array.isArray(d.sessions) && d.sessions.length > 0) {
                        const valid = d.sessions.filter(s => Array.isArray(s.lines) && s.lines.length > 0).length;
                        setDraftCount(valid);
                        return;
                    }
                }
            } catch (e) { }
            setDraftCount(0);
        })();
    }, [ventes]);

    const fetchVentes = useCallback(async (pageToLoad = 1, reset = false) => {
        try {
            const params = { per_page: 10, periode, page: pageToLoad };
            if (periode === 'perso') {
                if (dateDebut) params.date_debut = dateDebut;
                if (dateFin) params.date_fin = dateFin;
            }
            if (search) params.search = search;
            if (filterStatut && filterStatut !== 'tous') params.statut_paiement = filterStatut;
            const resp = await client.get('/ventes', { params });
            const d = resp.data?.data;
            let list = [];
            let total = 0;
            let lp = 1;
            if (d) {
                list = Array.isArray(d.data) ? d.data : [];
                total = typeof d.total === 'number' ? d.total : list.length;
                lp = d.last_page || 1;
            }
            setVentes(prev => (reset ? list : [...prev, ...list]));
            setTotalVentes(total);
            setLastPage(lp);
            setPage(pageToLoad);
        } catch (e) {
            console.error('Error fetching ventes:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
            setLoadingMore(false);
        }
    }, [periode, dateDebut, dateFin, search, filterStatut]);

    useFocusEffect(
        useCallback(() => {
            fetchVentes(1, true);
        }, [fetchVentes])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchVentes(1, true);
    };

    const loadMore = () => {
        if (loadingMore || refreshing) return;
        if (page >= lastPage) return;
        setLoadingMore(true);
        fetchVentes(page + 1, false);
    };

    // Debounce de la recherche : on ne déclenche le refiltrage serveur qu'après pause.
    useEffect(() => {
        const t = setTimeout(() => setSearch(searchInput), 400);
        return () => clearTimeout(t);
    }, [searchInput]);

    const getStatutBadge = (statut) => {
        switch (statut) {
            case 'paye': return { label: 'Payé', color: Colors.success, bg: Colors.success + '18' };
            case 'partiel': return { label: 'Partiel', color: Colors.warning, bg: Colors.warning + '18' };
            case 'impaye': return { label: 'Impayé', color: Colors.error, bg: Colors.error + '18' };
            case 'credit': return { label: 'Crédit', color: Colors.info, bg: Colors.info + '18' };
            default: return { label: statut || 'Autre', color: Colors.textLight, bg: Colors.border };
        }
    };

    return (
        <View style={styles.container}>
            {/* Top Navigation Header replacing Drawer */}
            <TopHeaderNav navigation={navigation} activeCategory="ventes" />

            {/* View Action Header */}
            <View style={styles.viewHeaderRow}>
                <View style={{ flex: 1 }}>
                    <Text style={styles.headerTitle}>Gestion des Ventes</Text>
                    <Text style={styles.headerSub}>{totalVentes} vente(s) au total</Text>
                </View>

                {/* Stylish Add (+) Button */}
                <TouchableOpacity
                    style={styles.btnAddPill}
                    onPress={() => navigation.navigate('VenteCreate')}
                    activeOpacity={0.88}
                >
                    <Ionicons name="add-circle" size={18} color="#FFFFFF" />
                    <Text style={styles.btnAddPillText}>+ Vente</Text>
                </TouchableOpacity>
            </View>

            {/* Filtres & Recherche style Nos Dettes */}
            <View style={styles.filtersWrap}>
                <View style={styles.searchRow}>
                    <View style={styles.searchBar}>
                        <Ionicons name="search" size={18} color={Colors.textLight} />
                        <TextInput
                            style={styles.searchInput}
                            placeholder="Référence ou client..."
                            placeholderTextColor={Colors.textLight}
                            value={searchInput}
                            onChangeText={setSearchInput}
                            autoFocus
                        />
                        {searchInput ? (
                            <TouchableOpacity onPress={() => { setSearchInput(''); setSearch(''); }}>
                                <Ionicons name="close-circle" size={18} color={Colors.textLight} />
                            </TouchableOpacity>
                        ) : null}
                    </View>
                    <TouchableOpacity
                        style={[styles.filterMenuBtn, (filterStatut && filterStatut !== 'tous') && styles.filterMenuBtnActive]}
                        onPress={() => setShowDateModal(true)}
                    >
                        <Ionicons name="options-outline" size={20} color={(filterStatut && filterStatut !== 'tous') ? '#FFF' : Colors.primary} />
                    </TouchableOpacity>
                </View>

                <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipScroll} contentContainerStyle={styles.chipScrollContent}>
                    {[
                        { key: 'tous', label: 'Toutes les dates' },
                        { key: 'aujourd_hui', label: "Aujourd'hui" },
                        { key: 'hier', label: 'Hier' },
                        { key: 'avant_hier', label: 'Avant-hier' },
                        { key: 'perso', label: 'Personnaliser' },
                    ].map(p => {
                        const active = periode === p.key;
                        return (
                            <TouchableOpacity
                                key={p.key}
                                style={[styles.chip, active && styles.chipActive]}
                                onPress={() => {
                                    if (p.key === 'perso') {
                                        setShowDateModal(true);
                                    } else {
                                        setPeriode(p.key);
                                    }
                                }}
                            >
                                <Text style={[styles.chipText, active && styles.chipTextActive]}>{p.label}</Text>
                            </TouchableOpacity>
                        );
                    })}
                </ScrollView>
            </View>

                {periode === 'perso' && (dateDebut || dateFin) && (
                    <TouchableOpacity style={styles.periodeSummary} onPress={() => setShowDateModal(true)}>
                        <Ionicons name="calendar-outline" size={16} color={Colors.primary} />
                        <Text style={styles.periodeSummaryText}>
                            {dateDebut ? formatDateFr(dateDebut) : '...'} → {dateFin ? formatDateFr(dateFin) : '...'}
                        </Text>
                        <Ionicons name="close-circle" size={16} color={Colors.textLight} onPress={() => { setDateDebut(null); setDateFin(null); setPeriode('aujourd_hui'); }} />
                    </TouchableOpacity>
                )}



            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                    <Text style={styles.loaderText}>Chargement des ventes...</Text>
                </View>
            ) : (
                <FlatList
                    data={ventes}
                    keyExtractor={(item) => item.id.toString()}
                    contentContainerStyle={styles.listContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                    onEndReached={loadMore}
                    onEndReachedThreshold={0.3}
                    ListFooterComponent={
                        loadingMore ? (
                            <View style={styles.footerLoader}>
                                <ActivityIndicator size="small" color={Colors.primary} />
                            </View>
                        ) : null
                    }
                    ListEmptyComponent={
                        <View style={styles.emptyBox}>
                            <Ionicons name="receipt-outline" size={50} color={Colors.border} />
                            <Text style={styles.emptyTitle}>Aucune vente trouvée</Text>
                            <Text style={styles.emptySub}>Aucune vente ne correspond à votre recherche.</Text>
                        </View>
                    }
                    renderItem={({ item }) => {
                        const badge = getStatutBadge(item.statut_paiement);
                        const dateStr = item.created_at ? formatDateTimeFr(item.created_at) : '—';
                        return (
                            <TouchableOpacity style={styles.card} onPress={() => navigation.navigate('VenteShow', { id: item.id })}>
                                <View style={styles.cardTop}>
                                    <View style={styles.refBox}>
                                        <Ionicons name="cart" size={16} color={Colors.primary} />
                                        <Text style={styles.refText}>{item.reference}</Text>
                                    </View>
                                    <View style={[styles.badge, { backgroundColor: badge.bg }]}>
                                        <Text style={[styles.badgeText, { color: badge.color }]}>{badge.label}</Text>
                                    </View>
                                </View>

                                <View style={styles.cardBody}>
                                    <View style={styles.infoRow}>
                                        <Ionicons name="person-outline" size={14} color={Colors.textLight} />
                                        <Text style={styles.clientName}>{item.client?.nom ? `${item.client.nom} ${item.client.prenom || ''}` : 'Client anonyme'}</Text>
                                    </View>
                                    {item.magasin?.nom && (
                                        <View style={styles.infoRow}>
                                            <Ionicons name="business-outline" size={14} color={Colors.textLight} />
                                            <Text style={styles.infoSub}>{item.magasin.nom}</Text>
                                        </View>
                                    )}
                                    <View style={styles.infoRow}>
                                        <Ionicons name="time-outline" size={14} color={Colors.textLight} />
                                        <Text style={styles.infoSub}>{dateStr}</Text>
                                    </View>
                                </View>

                                <View style={styles.cardFooter}>
                                    <Text style={styles.totalLabel}>Total :</Text>
                                    <Text style={styles.totalValue}>{formatMoney(item.montant_total)}</Text>
                                    <Ionicons name="chevron-forward" size={18} color={Colors.textLight} />
                                </View>
                            </TouchableOpacity>
                        );
                    }}
                />
            )}


            {/* Modal période personnalisée */}
            <Modal visible={showDateModal} animationType="slide" transparent>
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Filtrer les ventes</Text>
                            <TouchableOpacity onPress={() => setShowDateModal(false)}>
                                <Ionicons name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>

                        <Text style={styles.label}>Statut de paiement</Text>
                        <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginBottom: 16 }}>
                            {[
                                { key: 'tous', label: 'Tous statuts' },
                                { key: 'paye', label: 'Payées' },
                                { key: 'partiel', label: 'Partielles' },
                                { key: 'impaye', label: 'Impayées' },
                                { key: 'credit', label: 'Crédits' },
                            ].map(s => {
                                const active = (filterStatut || 'tous') === s.key;
                                return (
                                    <TouchableOpacity
                                        key={s.key}
                                        style={[styles.filterChipModal, active && styles.filterChipModalActive]}
                                        onPress={() => setFilterStatut(s.key)}
                                    >
                                        <Text style={[styles.filterChipModalText, active && styles.filterChipModalTextActive]}>{s.label}</Text>
                                    </TouchableOpacity>
                                );
                            })}
                        </View>

                        <Text style={styles.label}>Période personnalisée (Du)</Text>
                        <TouchableOpacity style={styles.dateBtn} onPress={() => { setDateField('debut'); }}>
                            <Text style={styles.dateBtnText}>{dateDebut ? formatDateFr(dateDebut) : 'Choisir une date'}</Text>
                        </TouchableOpacity>

                        <Text style={[styles.label, { marginTop: 12 }]}>Au</Text>
                        <TouchableOpacity style={styles.dateBtn} onPress={() => { setDateField('fin'); }}>
                            <Text style={styles.dateBtnText}>{dateFin ? formatDateFr(dateFin) : 'Choisir une date'}</Text>
                        </TouchableOpacity>

                        {(dateField === 'debut' || dateField === 'fin') && (
                            <View style={{ marginTop: 10, backgroundColor: '#F8FAFC', borderRadius: 12, paddingVertical: 4 }}>
                                <DateTimePicker
                                    value={dateField === 'debut' ? (dateDebut ? new Date(dateDebut) : new Date()) : (dateFin ? new Date(dateFin) : new Date())}
                                    mode="date"
                                    display="spinner"
                                    maximumDate={dateField === 'fin' && dateDebut ? new Date(dateDebut) : undefined}
                                    minimumDate={dateField === 'debut' && dateFin ? new Date(dateFin) : undefined}
                                    onChange={(e, d) => {
                                        if (d) {
                                            const s = toLocalDate(d);
                                            if (dateField === 'debut') setDateDebut(s);
                                            else setDateFin(s);
                                        }
                                        setDateField(null);
                                    }}
                                    style={{ width: '100%' }}
                                />
                            </View>
                        )}

                        <TouchableOpacity
                            style={[styles.submitBtn, { marginTop: 20 }]}
                            onPress={() => {
                                if (dateDebut || dateFin) setPeriode('perso');
                                setShowDateModal(false);
                            }}
                        >
                            <Text style={styles.submitBtnText}>Appliquer le filtre</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </Modal>

        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    viewHeaderRow: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingHorizontal: 16,
        paddingTop: 14,
        paddingBottom: 8,
        backgroundColor: '#FFFFFF',
    },
    headerTitle: { fontSize: 20, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    headerSub: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    btnAddPill: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        backgroundColor: Colors.primary,
        paddingHorizontal: 14,
        paddingVertical: 8,
        borderRadius: 20,
        elevation: 2,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.2,
        shadowRadius: 4,
    },
    btnAddPillText: {
        color: '#FFFFFF',
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
    filtersWrap: { paddingBottom: 8 },
    searchRow: { flexDirection: 'row', alignItems: 'center', marginHorizontal: 16, marginBottom: 8, gap: 8 },
    searchBar: {
        flex: 1, flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 14, paddingHorizontal: 12, height: 44, borderWidth: 1, borderColor: '#E2E8F0'
    },
    filterMenuBtn: { width: 44, height: 44, borderRadius: 14, backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', justifyContent: 'center', alignItems: 'center' },
    filterMenuBtnActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    chipScroll: { marginBottom: 4 },
    chipScrollContent: { paddingHorizontal: 16, gap: 8 },
    chip: { paddingHorizontal: 14, paddingVertical: 8, borderRadius: 20, backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border },
    chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    chipText: { fontSize: 13, fontFamily: 'PlusJakartaSans_600SemiBold', color: '#334155' },
    chipTextActive: { color: '#FFFFFF', fontFamily: 'PlusJakartaSans_700Bold' },
    filterChipModal: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 16, backgroundColor: '#F1F5F9', borderWidth: 1, borderColor: '#E2E8F0' },
    filterChipModalActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    filterChipModalText: { fontSize: 12, fontFamily: 'PlusJakartaSans_500Medium', color: '#334155' },
    filterChipModalTextActive: { color: '#FFF', fontFamily: 'PlusJakartaSans_700Bold' },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 10 },
    loaderText: { fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    listContent: { padding: 16, paddingBottom: 80 },
    emptyBox: { alignItems: 'center', paddingVertical: 60, gap: 10 },
    emptyTitle: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.text },
    emptySub: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight, textAlign: 'center' },
    card: {
        backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, elevation: 1, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.05, shadowRadius: 4
    },
    cardTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10 },
    refBox: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    refText: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
    badgeText: { fontSize: 11, fontFamily: 'Poppins_700Bold' },
    cardBody: { gap: 4, marginBottom: 12 },
    infoRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    clientName: { fontSize: 14, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    infoSub: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    cardFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 10, borderTopWidth: 1, borderTopColor: Colors.border },
    totalLabel: { fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.textLight },
    totalValue: { fontSize: 17, fontFamily: 'Poppins_700Bold', color: Colors.primary, flex: 1, marginLeft: 8 },
    fab: {
        position: 'absolute', bottom: 20, right: 20, width: 56, height: 56, borderRadius: 28, backgroundColor: Colors.secondary, justifyContent: 'center', alignItems: 'center', elevation: 5, shadowColor: Colors.secondary, shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, shadowRadius: 8
    },
    fabBadge: {
        position: 'absolute', top: -4, right: -4, minWidth: 22, height: 22, borderRadius: 11, backgroundColor: Colors.error, justifyContent: 'center', alignItems: 'center', paddingHorizontal: 5, borderWidth: 2, borderColor: Colors.background
    },
    fabBadgeText: { fontSize: 11, fontFamily: 'Poppins_700Bold', color: '#FFF' },
    periodeSummary: { flexDirection: 'row', alignItems: 'center', gap: 8, paddingHorizontal: 20, paddingVertical: 8, backgroundColor: Colors.surface, borderBottomWidth: 1, borderBottomColor: Colors.border },
    periodeSummaryText: { flex: 1, fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.primary },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.4)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: Colors.surface, borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 20, paddingBottom: 30 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    modalTitle: { fontSize: 17, fontFamily: 'Poppins_700Bold', color: Colors.text },
    label: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text, marginBottom: 6 },
    dateBtn: { backgroundColor: '#f1f5f9', borderRadius: 10, paddingVertical: 12, paddingHorizontal: 14 },
    dateBtnText: { fontSize: 14, fontFamily: 'Poppins_500Medium', color: Colors.text },
    submitBtn: { backgroundColor: Colors.primary, paddingVertical: 14, borderRadius: 12, alignItems: 'center' },
    submitBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'Poppins_700Bold' },
});

export default VentesScreen;
