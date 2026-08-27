import React, { useState, useCallback, useEffect } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    ActivityIndicator, RefreshControl, Modal, StatusBar, Alert, ScrollView, TextInput,
    KeyboardAvoidingView, Platform
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import DateRangeModal from '../../components/DateRangeModal';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { formatDateFr } from '../../utils/formatDate';
import TopHeaderNav from '../../components/TopHeaderNav';

const formatMoney = (val) => {
    if (!val && val !== 0) return '0 F';
    return Number(val).toLocaleString('fr-FR') + ' F';
};

const toLocalDate = (d) => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
};

const LivraisonsScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [livraisons, setLivraisons] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [filterStatut, setFilterStatut] = useState('tous');
    const [periode, setPeriode] = useState('aujourd_hui');
    const [dateDebut, setDateDebut] = useState(null);
    const [dateFin, setDateFin] = useState(null);
    const [showDateModal, setShowDateModal] = useState(false);
    const [stats, setStats] = useState(null);

    // Modal statut
    const [selectedVente, setSelectedVente] = useState(null);
    const [livNote, setLivNote] = useState('');
    const [submitting, setSubmitting] = useState(false);

    const fetchData = useCallback(async () => {
        try {
            const params = { periode };
            if (periode === 'perso') {
                if (dateDebut) params.date_debut = dateDebut;
                if (dateFin) params.date_fin = dateFin;
            }
            const resp = await client.get('/livraisons', { params });
            const list = Array.isArray(resp.data?.data?.data)
                ? resp.data.data.data
                : Array.isArray(resp.data?.data)
                    ? resp.data.data
                    : Array.isArray(resp.data) ? resp.data : [];
            setLivraisons(list);
            setStats(resp.data?.stats || null);
        } catch (e) {
            console.error('Error fetching livraisons:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [periode, dateDebut, dateFin]);

    useEffect(() => {
        fetchData();
    }, [periode, dateDebut, dateFin]);

    useFocusEffect(
        useCallback(() => {
            fetchData();
        }, [fetchData])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchData();
    };

    const handleUpdateStatut = async (newStatut) => {
        if (!selectedVente) return;
        setSubmitting(true);
        try {
            await client.put(`/livraisons/${selectedVente.id}/statut`, {
                statut_livraison: newStatut,
                note_livraison: livNote || null,
            });
            Alert.alert('Succès', 'Statut de livraison mis à jour.');
            setSelectedVente(null);
            setLivNote('');
            fetchData();
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Mise à jour échouée.');
        } finally {
            setSubmitting(false);
        }
    };

    const quickMarkAsDelivered = async (venteId) => {
        try {
            await client.put(`/livraisons/${venteId}/statut`, {
                statut_livraison: 'livre',
            });
            fetchData();
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Mise à jour échouée.');
        }
    };

    const filteredList = livraisons.filter(v => {
        if (filterStatut === 'tous') return true;
        return v.statut_livraison === filterStatut;
    });

    return (
        <View style={styles.container}>
            {/* Top Navigation Header replacing Drawer */}
            <TopHeaderNav navigation={navigation} activeCategory="livraisons" />

            {/* View Title Header */}
            <View style={styles.viewHeaderRow}>
                <Text style={styles.headerTitle}>Suivi des Livraisons</Text>
                <Text style={styles.headerSub}>Expéditions des commandes clients</Text>
            </View>

            {/* Filtres & Recherche — style identique à Nos Dettes */}
            <View style={styles.filtersWrap}>
                {/* Barre recherche + icône filtre dates */}
                <View style={styles.searchRow}>
                    <View style={styles.searchBar}>
                        <Ionicons name="search" size={18} color={Colors.textLight} />
                        <TextInput
                            style={styles.searchInput}
                            placeholder="Rechercher une livraison..."
                            placeholderTextColor={Colors.textLight}
                        />
                    </View>
                    <TouchableOpacity
                        style={[styles.filterMenuBtn, periode !== 'tous' && styles.filterMenuBtnActive]}
                        onPress={() => setShowDateModal(true)}
                    >
                        <Ionicons name="options-outline" size={20} color={periode !== 'tous' ? '#FFF' : Colors.primary} />
                    </TouchableOpacity>
                </View>

                {/* Filtre Statut (visible en permanence comme Nos Dettes) */}
                <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipScroll} contentContainerStyle={styles.chipScrollContent}>
                    {[
                        { key: 'tous', label: 'Tous statuts' },
                        { key: 'en_attente', label: 'Non livré' },
                        { key: 'livre', label: 'Livrées' },
                        { key: 'probleme', label: 'Problème' },
                    ].map(s => (
                        <TouchableOpacity
                            key={s.key}
                            style={[styles.chip, filterStatut === s.key && styles.chipActive]}
                            onPress={() => setFilterStatut(s.key)}
                        >
                            {s.key === 'en_attente' && <Ionicons name="bicycle" size={14} color={filterStatut === s.key ? '#FFFFFF' : Colors.warning} style={{ marginRight: 4 }} />}
                            <Text style={[styles.chipText, filterStatut === s.key && styles.chipTextActive]}>{s.label}</Text>
                        </TouchableOpacity>
                    ))}
                </ScrollView>
            </View>

            {periode !== 'tous' && (
                <TouchableOpacity style={styles.periodeSummary} onPress={() => setShowDateModal(true)}>
                    <Ionicons name="calendar-outline" size={14} color={Colors.primary} />
                        <Text style={styles.periodeSummaryText}>
                            {periode === 'aujourd_hui' ? "Aujourd'hui"
                                : periode === 'hier' ? 'Hier'
                                : periode === 'avant_hier' ? 'Avant-hier'
                                : (dateDebut || dateFin) ? `${formatDateFr(dateDebut)} → ${formatDateFr(dateFin)}`
                                : 'Période personnalisée'}
                        </Text>
                    <TouchableOpacity onPress={() => setPeriode('tous')}>
                        <Ionicons name="close-circle" size={16} color={Colors.textLight} />
                    </TouchableOpacity>
                </TouchableOpacity>
            )}

            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                    <Text style={styles.loaderText}>Chargement des livraisons...</Text>
                </View>
            ) : (
                <FlatList
                    data={filteredList}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={styles.listContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                    ListEmptyComponent={
                        <View style={styles.emptyContainer}>
                            <Ionicons name="bus-outline" size={48} color={Colors.textLight} />
                            <Text style={styles.emptyText}>Aucune livraison dans cette catégorie</Text>
                        </View>
                    }
                    renderItem={({ item }) => {
                        const statusColor = item.statut_livraison === 'livre' ? Colors.success : (item.statut_livraison === 'probleme' ? Colors.error : Colors.warning);
                        return (
                        <TouchableOpacity style={styles.card} activeOpacity={0.9} onPress={() => navigation.navigate('LivraisonShow', { id: item.id })}>
                            <View style={styles.cardHeader}>
                                <View style={[styles.livIconWrap, { backgroundColor: statusColor + '20' }]}>
                                    <Ionicons name="bicycle" size={20} color={statusColor} />
                                </View>
                                <View style={styles.factureRow}>
                                    <Ionicons name="document-text-outline" size={18} color={Colors.primary} />
                                    <View style={{ flex: 1 }}>
                                        <Text style={styles.factureText}>{item.reference || item.num_facture || `Vente #${item.id}`}</Text>
                                        <Text style={styles.amountTop}>{formatMoney(item.montant_total)}</Text>
                                    </View>
                                </View>
                                <View style={[styles.badge, item.statut_livraison === 'livre' ? styles.badgeSuccess : (item.statut_livraison === 'probleme' ? styles.badgeError : styles.badgeWarning)]}>
                                    <Text style={[styles.badgeText, item.statut_livraison === 'livre' ? styles.badgeSuccessText : (item.statut_livraison === 'probleme' ? styles.badgeErrorText : styles.badgeWarningText)]}>
                                        {item.statut_livraison === 'livre' ? 'Livrée' : (item.statut_livraison === 'probleme' ? 'Problème' : 'Non livré')}
                                    </Text>
                                </View>
                            </View>

                            <View style={styles.infoRow}>
                                <Ionicons name="person-outline" size={16} color={Colors.textLight} />
                                <Text style={styles.infoText}>{item.client?.nom || 'Client Anonyme'}</Text>
                            </View>
                            <View style={styles.infoRow}>
                                <Ionicons name="storefront-outline" size={16} color={Colors.textLight} />
                                <Text style={styles.infoText}>{item.magasin?.nom || 'Magasin'}</Text>
                            </View>

                            <View style={styles.divider} />

                            <View style={styles.cardFooter}>
                                {item.statut_livraison !== 'livre' ? (
                                    <TouchableOpacity
                                        style={styles.quickCheckBtn}
                                        onPress={() => quickMarkAsDelivered(item.id)}
                                        activeOpacity={0.8}
                                    >
                                        <Ionicons name="checkbox-outline" size={18} color="#166534" />
                                        <Text style={styles.quickCheckBtnText}>Cocher comme livré</Text>
                                    </TouchableOpacity>
                                ) : (
                                    <View style={styles.deliveredRow}>
                                        <Ionicons name="checkmark-circle" size={16} color={Colors.success} />
                                        <Text style={styles.deliveredRowText}>Livrée avec succès</Text>
                                    </View>
                                )}

                                <TouchableOpacity style={styles.actionBtn} onPress={() => setSelectedVente(item)}>
                                    <Text style={styles.actionBtnText}>Détails</Text>
                                    <Ionicons name="chevron-forward" size={16} color={Colors.primary} />
                                </TouchableOpacity>
                            </View>
                        </TouchableOpacity>
                    );
                    }}
                />
            )}

            {/* Modal de changement de statut */}
            {selectedVente && (
                <Modal visible={!!selectedVente} animationType="slide" transparent>
                                        <View style={styles.modalOverlay}>
                        <View style={styles.modalContent}>
                            <View style={styles.modalHeader}>
                                <Text style={styles.modalTitle}>Mettre à jour la livraison</Text>
                                <TouchableOpacity onPress={() => { setSelectedVente(null); setLivNote(''); }}>
                                    <Ionicons name="close" size={24} color={Colors.text} />
                                </TouchableOpacity>
                            </View>

                            <Text style={styles.subTitle}>Commande {selectedVente.reference || `#${selectedVente.id}`}</Text>

                            <View style={styles.btnStack}>
                                <TouchableOpacity style={[styles.statusOptionBtn, { backgroundColor: Colors.success + '20' }]} onPress={() => handleUpdateStatut('livre')}>
                                    <Ionicons name="checkmark-circle" size={20} color={Colors.success} />
                                    <Text style={[styles.statusOptionText, { color: Colors.success }]}>Marquer comme Livrée</Text>
                                </TouchableOpacity>

                                <TouchableOpacity style={[styles.statusOptionBtn, { backgroundColor: Colors.error + '20' }]} onPress={() => handleUpdateStatut('probleme')}>
                                    <Ionicons name="alert-circle" size={20} color={Colors.error} />
                                    <Text style={[styles.statusOptionText, { color: Colors.error }]}>Signaler un Problème</Text>
                                </TouchableOpacity>
                            </View>

                            <Text style={[styles.label, { marginTop: 12 }]}>Note (optionnel)</Text>
                            <TextInput
                                style={styles.input}
                                placeholder="Précisions sur la livraison..."
                                value={livNote}
                                onChangeText={setLivNote}
                            />
                        </View>
                    </View>

                </Modal>
            )}

            {/* Modal période personnalisée (corrigé : picker inline, ne ferme plus le Modal) */}
            <DateRangeModal
                visible={showDateModal}
                initialDebut={dateDebut}
                initialFin={dateFin}
                onApply={(d, f) => { setDateDebut(d); setDateFin(f); setPeriode('perso'); setShowDateModal(false); }}
                onClose={() => setShowDateModal(false)}
            />

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
    chipScroll: { marginBottom: 6 },
    chipScrollContent: { gap: 8 },
    chip: { flexDirection: 'row', alignItems: 'center', gap: 6, paddingHorizontal: 14, paddingVertical: 8, borderRadius: 20, backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border },
    chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    chipText: { fontSize: 13, fontFamily: 'PlusJakartaSans_600SemiBold', color: '#334155' },
    chipTextActive: { color: '#FFFFFF', fontFamily: 'PlusJakartaSans_700Bold' },
    filtersWrap: { paddingHorizontal: 16, paddingBottom: 8 },
    searchRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 8, gap: 8 },
    searchBar: { flex: 1, flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 14, paddingHorizontal: 12, height: 44, borderWidth: 1, borderColor: '#E2E8F0' },
    searchInput: { flex: 1, marginLeft: 8, fontFamily: 'PlusJakartaSans_400Regular', fontSize: 13, color: Colors.text },
    filterMenuBtn: { width: 44, height: 44, borderRadius: 14, backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', justifyContent: 'center', alignItems: 'center' },
    filterMenuBtnActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    periodeSummary: { flexDirection: 'row', alignItems: 'center', gap: 6, paddingHorizontal: 16, paddingVertical: 6, marginBottom: 4 },
    periodeSummaryText: { fontSize: 12, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.primary, flex: 1 },
    statsGrid: { flexDirection: 'row', paddingVertical: 8, paddingHorizontal: 16, gap: 8, backgroundColor: '#FFFFFF' },
    statCard: { flex: 1, backgroundColor: '#F8FAFC', borderRadius: 10, paddingVertical: 10, paddingHorizontal: 6, alignItems: 'center', borderWidth: 1, borderColor: '#E2E8F0' },
    statVal: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    statLbl: { fontSize: 10, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.textLight, marginTop: 2 },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 10 },
    loaderText: { fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    listContent: { padding: 16, paddingBottom: 32 },
    emptyContainer: { padding: 40, alignItems: 'center', gap: 12 },
    emptyText: { fontSize: 14, color: Colors.textLight, fontFamily: 'Poppins_400Regular' },
    card: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, elevation: 1, gap: 6, borderWidth: 1, borderColor: '#E2E8F0' },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', gap: 10 },
    factureRow: { flexDirection: 'row', alignItems: 'center', gap: 6, flex: 1 },
    livIconWrap: { width: 40, height: 40, borderRadius: 20, justifyContent: 'center', alignItems: 'center', marginRight: 4 },
    factureText: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.text },
    amountTop: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginTop: 2 },
    badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
    badgeSuccess: { backgroundColor: '#DCFCE7' },
    badgeSuccessText: { color: '#15803D', fontSize: 12, fontFamily: 'PlusJakartaSans_700Bold' },
    badgeWarning: { backgroundColor: '#FEF3C7' },
    badgeWarningText: { color: '#B45309', fontSize: 12, fontFamily: 'PlusJakartaSans_700Bold' },
    badgeError: { backgroundColor: '#FEE2E2' },
    badgeErrorText: { color: '#B91C1C', fontSize: 12, fontFamily: 'PlusJakartaSans_700Bold' },
    infoRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    infoText: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    divider: { height: 1, backgroundColor: Colors.border, marginVertical: 6 },
    cardFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    quickCheckBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        backgroundColor: '#F0FDF4',
        borderWidth: 1,
        borderColor: '#86EFAC',
        paddingHorizontal: 12,
        paddingVertical: 7,
        borderRadius: 10,
    },
    quickCheckBtnText: {
        fontSize: 12.5,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: '#166534',
    },
    deliveredRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 5,
    },
    deliveredRowText: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.success,
    },
    amount: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    actionBtn: { flexDirection: 'row', alignItems: 'center', gap: 4 },
    actionBtnText: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.primary },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: Colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20, gap: 12 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    modalTitle: { fontSize: 18, fontFamily: 'Poppins_700Bold', color: Colors.text },
    subTitle: { fontSize: 14, fontFamily: 'Poppins_500Medium', color: Colors.textLight, marginBottom: 8 },
    modalNote: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginBottom: 4 },
    btnStack: { gap: 10 },
    statusOptionBtn: { flexDirection: 'row', alignItems: 'center', gap: 12, padding: 14, borderRadius: 14 },
    statusOptionDisabled: { opacity: 0.4 },
    statusOptionText: { fontSize: 15, fontFamily: 'Poppins_700Bold' },
    periodeSummary: { flexDirection: 'row', alignItems: 'center', gap: 8, paddingHorizontal: 12, paddingVertical: 8, backgroundColor: Colors.surface, borderBottomWidth: 1, borderBottomColor: Colors.border },
    periodeSummaryText: { flex: 1, fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.primary },
    label: { fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.textLight },
    input: { backgroundColor: Colors.background, borderRadius: 10, borderWidth: 1, borderColor: Colors.border, paddingHorizontal: 12, paddingVertical: 10, fontSize: 14, color: Colors.text },
    dateBtn: { backgroundColor: Colors.background, borderRadius: 10, borderWidth: 1, borderColor: Colors.border, paddingHorizontal: 12, paddingVertical: 12 },
    dateBtnText: { fontSize: 14, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    submitBtn: { backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 14, alignItems: 'center' },
    submitBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'Poppins_700Bold' },
});

export default LivraisonsScreen;
