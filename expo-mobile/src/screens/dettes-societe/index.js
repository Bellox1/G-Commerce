import React, { useState, useCallback, useRef } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    ActivityIndicator, RefreshControl, Modal, ScrollView, StatusBar, Alert, TextInput,
    KeyboardAvoidingView, Platform
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { formatDateFr, todayWAT } from '../../utils/formatDate';
import TopHeaderNav from '../../components/TopHeaderNav';

const formatMoney = (val) => {
    const n = Number(val);
    if (!isFinite(n)) return '0 F';
    return n.toLocaleString('fr-FR') + ' F';
};
const MODES = [
    { key: 'especes', label: 'Espèces' },
    { key: 'virement', label: 'Virement' },
    { key: 'mobile_money', label: 'Mobile Money' },
    { key: 'cheque', label: 'Chèque' },
];
const STATUTS = [
    { key: '', label: 'Tous' },
    { key: 'en_cours', label: 'En cours' },
    { key: 'partiel', label: 'Partiel' },
    { key: 'solde', label: 'Soldé' },
];

const DettesSocieteScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [dettes, setDettes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [loadingMore, setLoadingMore] = useState(false);
    const loadingMoreRef = useRef(false);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);

    const [fournisseurs, setFournisseurs] = useState([]);
    const [totalDettes, setTotalDettes] = useState(0);
    const [totalSolde, setTotalSolde] = useState(0);

    const [statutFilter, setStatutFilter] = useState('');
    const [fournisseurFilter, setFournisseurFilter] = useState(null);
    const [search, setSearch] = useState('');

    // Modal paiement
    const [selectedDette, setSelectedDette] = useState(null);
    const [montantPaiement, setMontantPaiement] = useState('');
    const [modePaiement, setModePaiement] = useState('especes');
    const [datePaiement, setDatePaiement] = useState(todayWAT());
    const [note, setNote] = useState('');
    const [submitting, setSubmitting] = useState(false);

    const fetchData = useCallback(async (pageToLoad = 1, reset = true) => {
        try {
            const params = { page: pageToLoad };
            if (statutFilter) params.statut = statutFilter;
            if (fournisseurFilter) params.fournisseur_id = fournisseurFilter;
            const resp = await client.get('/dettes-societe', { params });
            const pag = resp.data?.data;
            const list = Array.isArray(pag?.data) ? pag.data : [];
            setDettes(reset ? list : (prev) => [...prev, ...list]);
            setLastPage(pag?.last_page || 1);
            setPage(pageToLoad);
            setFournisseurs(resp.data?.fournisseurs || []);
            setTotalDettes(resp.data?.totalDettes || 0);
            setTotalSolde(resp.data?.totalSolde || 0);
        } catch (e) {
            console.error('Error fetching dettes-societe:', e);
            if (reset) setDettes([]);
        } finally {
            setLoading(false);
            setRefreshing(false);
            setLoadingMore(false);
            loadingMoreRef.current = false;
        }
    }, [statutFilter, fournisseurFilter]);

    useFocusEffect(
        useCallback(() => {
            fetchData(1, true);
        }, [fetchData])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchData(1, true);
    };

    const loadMore = () => {
        if (loadingMoreRef.current || loadingMore || page >= lastPage) return;
        loadingMoreRef.current = true;
        setLoadingMore(true);
        fetchData(page + 1, false);
    };

    const resetFilters = () => {
        setStatutFilter('');
        setFournisseurFilter(null);
        setSearch('');
    };

    const filtered = dettes.filter((d) => {
        if (!search) return true;
        const q = search.toLowerCase();
        return (d.fournisseur?.nom || '').toLowerCase().includes(q) || (d.description || '').toLowerCase().includes(q);
    });

    const restant = (d) => Number(d.montant) - (Number(d.montant_paye) || 0);
    const nbActives = dettes.filter((d) => restant(d) > 0).length;

    const openPaiement = (item) => {
        setSelectedDette(item);
        setMontantPaiement(String(restant(item)));
        setModePaiement('especes');
        setDatePaiement(todayWAT());
        setNote('');
    };

    const handlePayer = async () => {
        if (!selectedDette || !montantPaiement || parseFloat(montantPaiement) <= 0) {
            Alert.alert('Erreur', 'Veuillez entrer un montant valide.');
            return;
        }

        setSubmitting(true);
        try {
            await client.post(`/dettes-societe/${selectedDette.id}/payer`, {
                montant: parseFloat(montantPaiement),
                date_paiement: datePaiement,
                mode_paiement: modePaiement,
                notes: note,
            });
            Alert.alert('Succès', 'Paiement enregistré avec succès.');
            setSelectedDette(null);
            fetchData(1, true);
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Impossible d\'enregistrer le paiement.');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <View style={styles.container}>
            {/* Top Navigation Header replacing Drawer */}
            <TopHeaderNav navigation={navigation} activeCategory="dettes-societe" />

            {/* View Title Header */}
            <View style={styles.viewHeaderRow}>
                <View style={{ flex: 1 }}>
                    <Text style={styles.headerTitle}>Nos Dettes</Text>
                    <Text style={styles.headerSub}>Suivi des dettes envers les fournisseurs</Text>
                </View>
                <TouchableOpacity
                    style={styles.btnAddPill}
                    onPress={() => navigation.navigate('DetteSocieteCreate')}
                    activeOpacity={0.88}
                >
                    <Ionicons name="add-circle" size={18} color="#FFFFFF" />
                    <Text style={styles.btnAddPillText}>+ Dette</Text>
                </TouchableOpacity>
            </View>

            {/* Stats */}
            <View style={styles.statsRow}>
                <View style={styles.statCard}>
                    <Text style={[styles.statLabel, { color: Colors.error }]}>Total à payer</Text>
                    <Text style={[styles.statValue, { color: Colors.error }]}>{formatMoney(totalDettes)}</Text>
                </View>
                <View style={styles.statCard}>
                    <Text style={[styles.statLabel, { color: Colors.success }]}>Déjà soldé</Text>
                    <Text style={[styles.statValue, { color: Colors.success }]}>{formatMoney(totalSolde)}</Text>
                </View>
                <View style={styles.statCard}>
                    <Text style={[styles.statLabel, { color: Colors.warning }]}>Dettes actives</Text>
                    <Text style={[styles.statValue, { color: Colors.warning }]}>{nbActives}</Text>
                </View>
            </View>

            {/* Filtres */}
            <View style={styles.filtersWrap}>
                <View style={styles.searchBar}>
                    <Ionicons name="search" size={18} color={Colors.textLight} />
                    <TextInput
                        style={styles.searchInput}
                        placeholder="Rechercher (fournisseur, description...)"
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

                <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipScroll} contentContainerStyle={styles.chipScrollContent}>
                    {STATUTS.map((s) => (
                        <TouchableOpacity
                            key={'s' + s.key}
                            style={[styles.chip, statutFilter === s.key && styles.chipActive]}
                            onPress={() => setStatutFilter(s.key)}
                        >
                            <Text style={[styles.chipText, statutFilter === s.key && styles.chipTextActive]}>{s.label}</Text>
                        </TouchableOpacity>
                    ))}
                </ScrollView>

                <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipScroll} contentContainerStyle={styles.chipScrollContent}>
                    <TouchableOpacity
                        key="f-all"
                        style={[styles.chip, fournisseurFilter === null && styles.chipActive]}
                        onPress={() => setFournisseurFilter(null)}
                    >
                        <Text style={[styles.chipText, fournisseurFilter === null && styles.chipTextActive]}>Tous les fournisseurs</Text>
                    </TouchableOpacity>
                    {fournisseurs.map((f) => (
                        <TouchableOpacity
                            key={'f' + f.id}
                            style={[styles.chip, fournisseurFilter === f.id && styles.chipActive]}
                            onPress={() => setFournisseurFilter(f.id)}
                        >
                            <Text style={[styles.chipText, fournisseurFilter === f.id && styles.chipTextActive]}>{f.nom}</Text>
                        </TouchableOpacity>
                    ))}
                </ScrollView>

                {(statutFilter || fournisseurFilter || search) && (
                    <TouchableOpacity style={styles.resetBtn} onPress={resetFilters}>
                        <Ionicons name="refresh" size={14} color={Colors.primary} />
                        <Text style={styles.resetText}>Réinitialiser les filtres</Text>
                    </TouchableOpacity>
                )}
            </View>

            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                    <Text style={styles.loaderText}>Chargement des dettes fournisseurs...</Text>
                </View>
            ) : (
                <FlatList
                    data={filtered}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={styles.listContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                    onEndReached={loadMore}
                    onEndReachedThreshold={0.3}
                    ListFooterComponent={loadingMore ? <ActivityIndicator color={Colors.primary} style={{ padding: 16 }} /> : null}
                    ListEmptyComponent={
                        <View style={styles.emptyContainer}>
                            <Ionicons name="checkmark-circle-outline" size={48} color={Colors.success} />
                            <Text style={styles.emptyText}>Aucune dette envers les fournisseurs</Text>
                        </View>
                    }
                    renderItem={({ item }) => {
                        const reste = restant(item);
                        const statut = item.statut;
                        const badge = statut === 'solde'
                            ? { bg: Colors.success + '18', color: Colors.success, text: 'Soldé' }
                            : statut === 'partiel'
                                ? { bg: Colors.warning + '18', color: Colors.warning, text: 'Partiel' }
                                : { bg: Colors.error + '18', color: Colors.error, text: 'En cours' };
                        return (
                            <TouchableOpacity style={styles.card} activeOpacity={0.9} onPress={() => navigation.navigate('DetteSocieteShow', { id: item.id })}>
                                <View style={styles.cardHeader}>
                                    <Text style={styles.fournName}>{item.fournisseur?.nom || 'Fournisseur'}</Text>
                                    <View style={[styles.badge, { backgroundColor: badge.bg }]}>
                                        <Text style={[styles.badgeText, { color: badge.color }]}>{badge.text}</Text>
                                    </View>
                                </View>

                                <View style={styles.metaRow}>
                                    <Text style={styles.metaLabel}>Date : {formatDateFr(item.date_dette)}</Text>
                                    <Text style={styles.metaLabel}>Arrivage : {item.arrivage?.reference || '—'}</Text>
                                </View>

                                {item.description && <Text style={styles.desc}>{item.description}</Text>}

                                <View style={styles.rowGrid}>
                                    <View>
                                        <Text style={styles.metaLabel}>Montant</Text>
                                        <Text style={styles.metaVal}>{formatMoney(item.montant)}</Text>
                                    </View>
                                    <View>
                                        <Text style={styles.metaLabel}>Payé</Text>
                                        <Text style={[styles.metaVal, { color: Colors.success }]}>{formatMoney(item.montant_paye)}</Text>
                                    </View>
                                    <View>
                                        <Text style={styles.metaLabel}>Reste</Text>
                                        <Text style={[styles.metaVal, { color: reste > 0 ? Colors.error : Colors.success }]}>{formatMoney(reste)}</Text>
                                    </View>
                                </View>

                                {item.taux_de_change || item.montant_origine ? (
                                    <View style={styles.currencyRow}>
                                        {item.taux_de_change ? (
                                            <Text style={styles.curLabel}>Taux : {Number(item.taux_de_change).toLocaleString('fr-FR')} FCFA</Text>
                                        ) : null}
                                        {item.montant_origine ? (
                                            <Text style={styles.curLabel}>Origine : {Number(item.montant_origine).toLocaleString('fr-FR')} {item.devise || ''}</Text>
                                        ) : null}
                                    </View>
                                ) : null}

                                {reste > 0 && (
                                    <TouchableOpacity
                                        style={styles.payBtn}
                                        onPress={() => openPaiement(item)}
                                    >
                                        <Ionicons name="cash-outline" size={18} color="#FFF" />
                                        <Text style={styles.payBtnText}>Régler la Dette</Text>
                                    </TouchableOpacity>
                                )}
                            </TouchableOpacity>
                        );
                    }}
                />
            )}

            {/* Modal Paiement */}
            {selectedDette && (
                <Modal visible={!!selectedDette} animationType="slide" transparent>
                    <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
                        <View style={styles.modalOverlay}>
                            <View style={styles.modalContent}>
                                <View style={styles.modalHeader}>
                                    <Text style={styles.modalTitle}>Règlement Fournisseur</Text>
                                    <TouchableOpacity onPress={() => setSelectedDette(null)}>
                                        <Ionicons name="close" size={24} color={Colors.text} />
                                    </TouchableOpacity>
                                </View>

                                <Text style={styles.targetName}>{selectedDette.fournisseur?.nom}</Text>
                                <Text style={styles.targetSub}>Reste dû : {formatMoney(restant(selectedDette))}</Text>

                                <Text style={styles.inputLabel}>Montant du versement (FCFA)</Text>
                                <TextInput
                                    style={styles.input}
                                    keyboardType="numeric"
                                    value={montantPaiement}
                                    onChangeText={setMontantPaiement}
                                    placeholder="Ex: 50000"
                                />

                                <Text style={styles.inputLabel}>Mode de règlement</Text>
                                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipScrollContent}>
                                    {[
                                        { key: 'especes', label: 'Espèces' },
                                        { key: 'momo', label: 'Mobile Money' },
                                        { key: 'virement', label: 'Virement' },
                                        { key: 'cheque', label: 'Chèque' },
                                    ].map(m => (
                                        <TouchableOpacity
                                            key={m.key}
                                            style={[styles.chip, modePaiement === m.key && styles.chipActive]}
                                            onPress={() => setModePaiement(m.key)}
                                        >
                                            <Text style={[styles.chipText, modePaiement === m.key && styles.chipTextActive]}>{m.label}</Text>
                                        </TouchableOpacity>
                                    ))}
                                </ScrollView>

                                <Text style={styles.inputLabel}>Note / Référence</Text>
                                <TextInput
                                    style={styles.input}
                                    value={note}
                                    onChangeText={setNote}
                                    placeholder="Numéro de reçu ou note"
                                />

                                <TouchableOpacity style={styles.submitBtn} onPress={handlePayer} disabled={submitting}>
                                    {submitting ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>Confirmer le Règlement</Text>}
                                </TouchableOpacity>
                            </View>
                        </View>
                    </KeyboardAvoidingView>
                </Modal>
            )}
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    viewHeaderRow: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 16,
        paddingTop: 14,
        paddingBottom: 8,
        backgroundColor: '#FFFFFF',
    },
    addBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: Colors.primary, justifyContent: 'center', alignItems: 'center' },
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
    headerTitle: { fontSize: 20, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    headerSub: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    statsRow: { flexDirection: 'row', gap: 10, padding: 16, paddingBottom: 8 },
    statCard: { flex: 1, backgroundColor: '#F8FAFC', borderRadius: 12, padding: 12, borderWidth: 1, borderColor: '#E2E8F0' },
    statLabel: { fontSize: 10, fontFamily: 'PlusJakartaSans_500Medium' },
    statValue: { fontSize: 15, fontFamily: 'SpaceGrotesk_700Bold', marginTop: 4 },
    filtersWrap: { paddingHorizontal: 16, paddingBottom: 8 },
    searchBar: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 14, paddingHorizontal: 12, height: 44, marginBottom: 8, borderWidth: 1, borderColor: '#E2E8F0' },
    searchInput: { flex: 1, marginLeft: 8, fontFamily: 'PlusJakartaSans_400Regular', fontSize: 13, color: Colors.text },
    chipScroll: { marginBottom: 6 },
    chipScrollContent: { gap: 8 },
    chip: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 16, backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border },
    chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    chipText: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.textLight },
    chipTextActive: { color: '#FFF', fontFamily: 'Poppins_700Bold' },
    resetBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, alignSelf: 'flex-start', paddingVertical: 4 },
    resetText: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.primary },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 10 },
    loaderText: { fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    listContent: { padding: 16, paddingTop: 8, paddingBottom: 90 },
    emptyContainer: { padding: 40, alignItems: 'center', gap: 12 },
    emptyText: { fontSize: 14, color: Colors.textLight, fontFamily: 'Poppins_400Regular' },
    card: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, elevation: 1, gap: 8 },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    fournName: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.text },
    desc: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    metaRow: { flexDirection: 'row', justifyContent: 'space-between', flexWrap: 'wrap', gap: 4 },
    metaLabel: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
    badgeText: { fontSize: 12, fontFamily: 'Poppins_600SemiBold' },
    rowGrid: { flexDirection: 'row', justifyContent: 'space-between', backgroundColor: Colors.background, padding: 10, borderRadius: 12, marginTop: 4 },
    metaVal: { fontSize: 13, fontFamily: 'Poppins_700Bold', color: Colors.text },
    currencyRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 12, marginTop: 8 },
    curLabel: { fontSize: 12, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.primary },
    payBtn: { backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 10, flexDirection: 'row', justifyContent: 'center', alignItems: 'center', gap: 8, marginTop: 6 },
    payBtnText: { color: '#FFF', fontSize: 14, fontFamily: 'Poppins_600SemiBold' },
    fab: { position: 'absolute', bottom: 20, right: 20, width: 56, height: 56, borderRadius: 28, backgroundColor: Colors.secondary, justifyContent: 'center', alignItems: 'center', elevation: 5 },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: Colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20, gap: 10, maxHeight: '90%' },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    modalTitle: { fontSize: 18, fontFamily: 'Poppins_700Bold', color: Colors.text },
    targetName: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    targetSub: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    inputLabel: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text, marginTop: 6 },
    input: { backgroundColor: Colors.background, borderRadius: 12, paddingHorizontal: 14, paddingVertical: 10, borderWidth: 1, borderColor: Colors.border, fontSize: 14, fontFamily: 'Poppins_500Medium' },
    submitBtn: { backgroundColor: Colors.success, borderRadius: 14, paddingVertical: 14, alignItems: 'center', marginTop: 12 },
    submitBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'Poppins_700Bold' },
});

export default DettesSocieteScreen;
