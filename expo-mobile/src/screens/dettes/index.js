import React, { useState, useCallback } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    TextInput, ActivityIndicator, RefreshControl, Modal, ScrollView, StatusBar, Alert
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import { formatDateFr } from '../../utils/formatDate';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import TopHeaderNav from '../../components/TopHeaderNav';

const formatMoney = (val) => {
    if (!val && val !== 0) return '0 F';
    return Number(val).toLocaleString('fr-FR') + ' F';
};

const DettesScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [dettes, setDettes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [search, setSearch] = useState('');
    const [filterStatut, setFilterStatut] = useState('tous');
    const [showFilterModal, setShowFilterModal] = useState(false);

    // Modal Paiement Dette
    const [payItem, setPayItem] = useState(null);
    const [payMontant, setPayMontant] = useState('');
    const [payMode, setPayMode] = useState('especes'); // especes, mobile_money, virement
    const [submittingPay, setSubmittingPay] = useState(false);

    const fetchDettes = useCallback(async () => {
        try {
            const resp = await client.get('/dettes');
            const list = resp.data?.data?.data || resp.data?.data || (Array.isArray(resp.data) ? resp.data : []);
            setDettes(list);
        } catch (e) {
            console.error('Error fetching dettes:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useFocusEffect(
        useCallback(() => {
            fetchDettes();
        }, [fetchDettes])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchDettes();
    };

    const handleRegisterPay = async () => {
        if (!payMontant || isNaN(payMontant) || Number(payMontant) <= 0) {
            Alert.alert('Erreur', 'Veuillez saisir un montant valide.');
            return;
        }
        setSubmittingPay(true);
        try {
            await client.post(`/dettes/${payItem.id}/payer`, {
                montant: Number(payMontant),
                mode_paiement: payMode,
            });
            Alert.alert('Succès', 'Paiement de dette enregistré');
            setPayItem(null);
            setPayMontant('');
            fetchDettes();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de l\'enregistrement du paiement';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmittingPay(false);
        }
    };

    const filteredDettes = dettes.filter(d => {
        const matchesSearch =
            d.client?.nom?.toLowerCase().includes(search.toLowerCase()) ||
            d.vente?.reference?.toLowerCase().includes(search.toLowerCase());

        if (filterStatut === 'tous') return matchesSearch;
        return matchesSearch && d.statut === filterStatut;
    });

    const getStatutBadge = (statut) => {
        switch (statut) {
            case 'solde': return { label: 'Soldé', color: '#15803D', bg: '#DCFCE7' };
            case 'partiel': return { label: 'Partiel', color: '#B45309', bg: '#FEF3C7' };
            case 'en_retard': return { label: 'En retard', color: '#B91C1C', bg: '#FEE2E2' };
            case 'en_cours': return { label: 'En cours', color: '#1D4ED8', bg: '#DBEAFE' };
            default: return { label: statut || 'Autre', color: '#475569', bg: '#F1F5F9' };
        }
    };

    const getEcheanceInfo = (dateStr) => {
        if (!dateStr) return { label: 'Non définie', color: '#475569', bg: '#F1F5F9' };
        const cleanDate = dateStr.includes('T') ? dateStr.split('T')[0] : dateStr;
        const d = new Date(cleanDate + 'T00:00:00');
        if (isNaN(d.getTime())) return { label: dateStr, color: '#475569', bg: '#F1F5F9' };
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (d < today) return { label: 'En retard', color: '#B91C1C', bg: '#FEE2E2' };
        if (d.getTime() === today.getTime()) return { label: "Aujourd'hui", color: '#B45309', bg: '#FEF3C7' };
        const diff = Math.ceil((d.getTime() - today.getTime()) / 86400000);
        if (isNaN(diff)) return { label: 'Valide', color: '#15803D', bg: '#DCFCE7' };
        return { label: `Dans ${diff} j`, color: '#15803D', bg: '#DCFCE7' };
    };

    return (
        <View style={styles.container}>
            {/* Top Navigation Header replacing Drawer */}
            <TopHeaderNav navigation={navigation} activeCategory="dettes" />

            {/* View Action Header */}
            <View style={styles.viewHeaderRow}>
                <View style={{ flex: 1 }}>
                    <Text style={styles.headerTitle}>Suivi des Dettes Clients</Text>
                    <Text style={styles.headerSub}>{filteredDettes.length} dette(s) enregistrée(s)</Text>
                </View>

                {/* Stylish Add (+) Button */}
                <TouchableOpacity
                    style={styles.btnAddPill}
                    onPress={() => navigation.navigate('DetteCreate')}
                    activeOpacity={0.88}
                >
                    <Ionicons name="add-circle" size={18} color="#FFFFFF" />
                    <Text style={styles.btnAddPillText}>+ Dette</Text>
                </TouchableOpacity>
            </View>

            {/* Search + Filter Menu Button */}
            <View style={styles.searchBarContainer}>
                <View style={styles.searchRow}>
                    <View style={styles.searchBar}>
                        <Ionicons name="search" size={18} color={Colors.textLight} />
                        <TextInput
                            style={styles.searchInput}
                            placeholder="Rechercher par client ou référence..."
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
                    <TouchableOpacity
                        style={[styles.filterMenuBtn, (filterStatut && filterStatut !== 'tous') && styles.filterMenuBtnActive]}
                        onPress={() => setShowFilterModal(true)}
                    >
                        <Ionicons name="options-outline" size={20} color={(filterStatut && filterStatut !== 'tous') ? '#FFF' : Colors.primary} />
                    </TouchableOpacity>
                </View>
            </View>

            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                    <Text style={styles.loaderText}>Chargement des dettes...</Text>
                </View>
            ) : (
                <FlatList
                    data={filteredDettes}
                    keyExtractor={(item) => item.id.toString()}
                    contentContainerStyle={styles.listContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                    ListEmptyComponent={
                        <View style={styles.emptyBox}>
                            <Ionicons name="wallet-outline" size={50} color={Colors.border} />
                            <Text style={styles.emptyTitle}>Aucune dette enregistrée</Text>
                        </View>
                    }
                    renderItem={({ item }) => {
                        const badge = getStatutBadge(item.statut);
                        const echeance = getEcheanceInfo(item.date_echeance);
                        const isSolde = item.statut === 'solde' || item.montant_restant <= 0;

                    return (
                        <TouchableOpacity
                            style={styles.card}
                            onPress={() => navigation.navigate('DetteShow', { id: item.id })}
                        >
                            <View style={styles.cardHeader}>
                                <View style={styles.clientBox}>
                                    <Ionicons name="person" size={16} color={Colors.primary} />
                                    <Text style={styles.clientName}>{item.client?.nom ? `${item.client.nom} ${item.client.prenom || ''}` : 'Client Anonyme'}</Text>
                                </View>
                                <View style={[styles.badge, { backgroundColor: badge.bg }]}>
                                    <Text style={[styles.badgeText, { color: badge.color }]}>{badge.label}</Text>
                                </View>
                            </View>

                            {item.vente?.reference && (
                                <Text style={styles.venteRef}>Vente rattachée: {item.vente.reference}</Text>
                            )}

                            <View style={styles.echeanceRow}>
                                <Ionicons name="calendar-outline" size={14} color={Colors.textLight} />
                                <Text style={styles.echeanceLabel}>
                                    Échéance: {item.date_echeance ? formatDateFr(item.date_echeance) : 'Non définie'}
                                </Text>
                                <View style={[styles.miniBadge, { backgroundColor: echeance.bg }]}>
                                    <Text style={[styles.miniBadgeText, { color: echeance.color }]}>{echeance.label}</Text>
                                </View>
                            </View>

                            <View style={styles.amountsRow}>
                                    <View style={styles.amountBox}>
                                        <Text style={styles.amountLabel}>Dette Initiale</Text>
                                        <Text style={styles.amountVal}>{formatMoney(item.montant_total || item.montant)}</Text>
                                    </View>
                                    <View style={styles.amountBox}>
                                        <Text style={styles.amountLabel}>Reste à Payer</Text>
                                        <Text style={[styles.amountVal, { color: isSolde ? Colors.success : Colors.error }]}>
                                            {formatMoney(item.montant_restant)}
                                        </Text>
                                    </View>
                                </View>

                                {!isSolde && (
                                    <TouchableOpacity
                                        style={styles.payBtn}
                                        onPress={() => setPayItem(item)}
                                    >
                                        <Ionicons name="cash-outline" size={16} color="#FFF" />
                                        <Text style={styles.payBtnText}>Enregistrer un Règlement</Text>
                                    </TouchableOpacity>
                                )}
                            </TouchableOpacity>
                        );
                    }}
                />
            )}

            {/* Modal Encaisser Dette */}
            <Modal visible={!!payItem} transparent animationType="slide" onRequestClose={() => setPayItem(null)}>
                <View style={styles.modalOverlay}>
                    <View style={styles.modalCard}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Règlement de Dette #{payItem?.id}</Text>
                            <TouchableOpacity onPress={() => setPayItem(null)}>
                                <Ionicons name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>

                        <Text style={styles.clientSubtitle}>Client: {payItem?.client?.nom || 'Client Anonyme'}</Text>
                        <Text style={styles.resteSubtitle}>Reste à payer: {formatMoney(payItem?.montant_restant)}</Text>

                        <View style={styles.fieldGroup}>
                            <Text style={styles.label}>Montant versé (FCFA) *</Text>
                            <TextInput
                                style={styles.input}
                                keyboardType="number-pad"
                                placeholder="ex: 10000"
                                value={payMontant}
                                onChangeText={setPayMontant}
                            />
                        </View>

                        <View style={styles.fieldGroup}>
                            <Text style={styles.label}>Mode de règlement</Text>
                            <View style={styles.modeRow}>
                                {[
                                    { key: 'especes', label: 'Espèces' },
                                    { key: 'mobile_money', label: 'Mobile Money' },
                                    { key: 'cheque', label: 'Chèque' }
                                ].map(m => (
                                    <TouchableOpacity
                                        key={m.key}
                                        style={[styles.modeChip, payMode === m.key && styles.modeChipActive]}
                                        onPress={() => setPayMode(m.key)}
                                    >
                                        <Text style={[styles.modeChipText, payMode === m.key && styles.modeChipTextActive]}>{m.label}</Text>
                                    </TouchableOpacity>
                                ))}
                            </View>
                        </View>

                        <TouchableOpacity style={styles.submitBtn} onPress={handleRegisterPay} disabled={submittingPay}>
                            {submittingPay ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>Confirmer le règlement</Text>}
                        </TouchableOpacity>
                    </View>
                </View>
            </Modal>

            {/* Modal de filtre statut dettes */}
            <Modal visible={showFilterModal} transparent animationType="slide" onRequestClose={() => setShowFilterModal(false)}>
                <View style={styles.modalOverlay}>
                    <View style={styles.modalCard}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Filtrer les dettes</Text>
                            <TouchableOpacity onPress={() => setShowFilterModal(false)}>
                                <Ionicons name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>

                        <Text style={styles.label}>Statut de la dette</Text>
                        <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginVertical: 12 }}>
                            {[
                                { key: 'tous', label: 'Toutes les dettes' },
                                { key: 'en_cours', label: 'En cours' },
                                { key: 'partiel', label: 'Partielles' },
                                { key: 'en_retard', label: 'En retard' },
                                { key: 'solde', label: 'Soldées' },
                            ].map(s => {
                                const active = filterStatut === s.key;
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

                        <TouchableOpacity
                            style={[styles.submitBtn, { marginTop: 16 }]}
                            onPress={() => setShowFilterModal(false)}
                        >
                            <Text style={styles.submitBtnText}>Appliquer</Text>
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
    searchBarContainer: {
        paddingHorizontal: 16,
        paddingBottom: 10,
        backgroundColor: '#FFFFFF',
    },
    searchRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    searchBar: { flex: 1, flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 14, paddingHorizontal: 12, height: 44, borderWidth: 1, borderColor: '#E2E8F0' },
    filterMenuBtn: { width: 44, height: 44, borderRadius: 14, backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', justifyContent: 'center', alignItems: 'center' },
    filterMenuBtnActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    searchInput: { flex: 1, marginLeft: 8, fontFamily: 'PlusJakartaSans_400Regular', fontSize: 13, color: Colors.text },
    filterChipModal: { paddingHorizontal: 14, paddingVertical: 8, borderRadius: 16, backgroundColor: '#F1F5F9', borderWidth: 1, borderColor: '#E2E8F0' },
    filterChipModalActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    filterChipModalText: { fontSize: 13, fontFamily: 'PlusJakartaSans_500Medium', color: '#334155' },
    filterChipModalTextActive: { color: '#FFF', fontFamily: 'PlusJakartaSans_700Bold' },
    filtersScroll: { marginHorizontal: 0, marginBottom: 8 },
    filtersContent: { paddingHorizontal: 16, gap: 8 },
    filterChip: { paddingHorizontal: 14, paddingVertical: 8, borderRadius: 20, backgroundColor: '#F1F5F9' },
    filterChipActive: { backgroundColor: Colors.primary },
    filterText: { fontSize: 13, fontFamily: 'PlusJakartaSans_600SemiBold', color: '#334155' },
    filterTextActive: { fontFamily: 'PlusJakartaSans_700Bold', color: '#FFFFFF' },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 10 },
    loaderText: { fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    listContent: { padding: 16, paddingBottom: 40 },
    emptyBox: { alignItems: 'center', paddingVertical: 60, gap: 10 },
    emptyTitle: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.text },
    card: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, elevation: 1, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.05, shadowRadius: 4, borderWidth: 1, borderColor: '#E2E8F0' },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
    clientBox: { flexDirection: 'row', alignItems: 'center', gap: 6, flex: 1 },
    clientName: { fontSize: 15, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.primary },
    badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
    badgeText: { fontSize: 11, fontFamily: 'PlusJakartaSans_700Bold' },
    venteRef: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginBottom: 10 },
    echeanceRow: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 10 },
    echeanceLabel: { fontSize: 12, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.text },
    miniBadge: { paddingHorizontal: 8, paddingVertical: 3, borderRadius: 8 },
    miniBadgeText: { fontSize: 10, fontFamily: 'PlusJakartaSans_700Bold' },
    amountsRow: { flexDirection: 'row', gap: 12, backgroundColor: '#F8FAFC', borderRadius: 12, padding: 12, marginBottom: 12, borderWidth: 1, borderColor: '#E2E8F0' },
    amountBox: { flex: 1 },
    amountLabel: { fontSize: 11, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    amountVal: { fontSize: 14, fontFamily: 'SpaceGrotesk_700Bold', marginTop: 2, color: Colors.text },
    payBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, backgroundColor: Colors.primary, borderRadius: 20, paddingVertical: 10 },
    payBtnText: { color: '#FFF', fontSize: 13, fontFamily: 'PlusJakartaSans_700Bold' },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalCard: { backgroundColor: Colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10 },
    modalTitle: { fontSize: 18, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    clientSubtitle: { fontSize: 14, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    resteSubtitle: { fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.error, marginBottom: 16 },
    fieldGroup: { marginBottom: 12 },
    label: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.textLight, marginBottom: 4 },
    input: { backgroundColor: Colors.background, borderRadius: 10, paddingHorizontal: 12, paddingVertical: 10, fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.text, borderWidth: 1, borderColor: Colors.border },
    modeRow: { flexDirection: 'row', gap: 8 },
    modeChip: { flex: 1, paddingVertical: 8, borderRadius: 8, backgroundColor: Colors.background, alignItems: 'center', borderWidth: 1, borderColor: Colors.border },
    modeChipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    modeChipText: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.text },
    modeChipTextActive: { color: '#FFF', fontFamily: 'Poppins_700Bold' },
    submitBtn: { backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 14, alignItems: 'center', marginTop: 12 },
    submitBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'Poppins_700Bold' },
});

export default DettesScreen;
