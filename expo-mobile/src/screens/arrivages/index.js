import React, { useState, useCallback } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    ActivityIndicator, RefreshControl, Modal, ScrollView, StatusBar, Alert
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { formatDateFr, formatDateTimeFr } from '../../utils/formatDate';
import TopHeaderNav from '../../components/TopHeaderNav';

    const formatMoney = (val) => {
        if (val === null || val === undefined || val === '') return '0 F';
        let n = Math.round(Number(val));
        if (!n || Math.abs(n) === 0) n = 0;
        return n.toLocaleString('fr-FR') + ' F';
    };

    const formatNaira = (val) => {
        if (val === null || val === undefined || val === '') return '0 ₦';
        let n = Math.round(Number(val));
        if (!n || Math.abs(n) === 0) n = 0;
        return n.toLocaleString('fr-FR') + ' ₦';
    };

    const computeBenefice = (item) => {
        if (!item) return 0;
        const revenuAttendu = (item.produits || []).reduce(
            (sum, p) => sum + (Number(p.prix_vente_suggere) || 0) * (Number(p.pivot?.quantite) || Number(p.quantite) || 0),
            0
        );
        return revenuAttendu - (Number(item.total_cout_reel) || 0);
    };

const ArrivagesScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [arrivages, setArrivages] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [loadingMore, setLoadingMore] = useState(false);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [totalArrivages, setTotalArrivages] = useState(0);
    const [selectedArrivage, setSelectedArrivage] = useState(null);
    const [validatingId, setValidatingId] = useState(null);

    const fetchArrivages = useCallback(async (pageToLoad = 1, reset = false) => {
        try {
            const resp = await client.get('/arrivages', { params: { page: pageToLoad, per_page: 10 } });
            const pag = resp.data?.data;
            const list = Array.isArray(pag?.data) ? pag.data : [];
            setArrivages(prev => (reset ? list : [...prev, ...list]));
            setTotalArrivages(pag?.total ?? list.length);
            setLastPage(pag?.last_page || 1);
            setPage(pageToLoad);
        } catch (e) {
            console.error('Error fetching arrivages:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
            setLoadingMore(false);
        }
    }, []);

    useFocusEffect(
        useCallback(() => {
            fetchArrivages(1, true);
        }, [fetchArrivages])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchArrivages(1, true);
    };

    const loadMore = () => {
        if (loadingMore || refreshing) return;
        if (page >= lastPage) return;
        setLoadingMore(true);
        fetchArrivages(page + 1, false);
    };

    const handleValiderArrivage = async (arrivageId) => {
        Alert.alert(
            'Validation Arrivage',
            'Êtes-vous sûr de vouloir valider cet arrivage ? Les stocks seront automatiquement mis à jour.',
            [
                { text: 'Annuler', style: 'cancel' },
                {
                    text: 'Valider',
                    onPress: async () => {
                        setValidatingId(arrivageId);
                        try {
                            const response = await client.post(`/arrivages/${arrivageId}/valider`);

                            // Le client.js intercepte les POST hors-ligne et retourne data.offline = true
                            if (response?.data?.offline || response?.isOfflineQueue) {
                                // Mise à jour immédiate du statut local dans la liste
                                setArrivages(prev =>
                                    prev.map(a =>
                                        a.id === arrivageId
                                            ? { ...a, statut: 'receptionne_offline' }
                                            : a
                                    )
                                );
                                setSelectedArrivage(null);
                                Alert.alert(
                                    '📶 Hors-ligne',
                                    'Arrivage marqué comme réceptionné.\nLa synchronisation se fera automatiquement dès le retour de la connexion.',
                                    [{ text: 'OK' }]
                                );
                            } else {
                                // Succès en ligne normal
                                Alert.alert('✅ Succès', 'Arrivage validé avec succès ! Le stock a été mis à jour.');
                                setSelectedArrivage(null);
                                fetchArrivages(1, true);
                            }
                        } catch (e) {
                            const msg = e.response?.data?.message || 'Erreur lors de la validation';
                            Alert.alert('Erreur', msg);
                        } finally {
                            setValidatingId(null);
                        }
                    }
                }
            ]
        );
    };

    const getStatutBadge = (statut) => {
        switch (statut) {
            case 'receptionne': return { label: 'Réceptionné', color: Colors.success, bg: Colors.success + '18' };
            case 'valide': return { label: 'Validé', color: Colors.success, bg: Colors.success + '18' };
            case 'integre': return { label: 'Intégré', color: Colors.success, bg: Colors.success + '18' };
            case 'receptionne_offline':
            case 'en_attente_sync': return { label: '⏳ En attente de connexion', color: '#c2410c', bg: '#fff7ed', icon: 'cloud-offline-outline' };
            case 'en_cours': return { label: 'En attente', color: Colors.warning, bg: Colors.warning + '18' };
            case 'annule': return { label: 'Annulé', color: Colors.error, bg: Colors.error + '18' };
            default: return { label: statut || 'En attente', color: Colors.warning, bg: Colors.warning + '18' };
        }
    };

    const getFournisseurs = (arr) => {
        const noms = (arr?.produits || [])
            .map((p) => p.fournisseur?.nom || p.produit?.fournisseur?.nom)
            .filter(Boolean);
        const uniques = [...new Set(noms)];
        if (uniques.length > 0) return uniques.join(', ');
        return arr?.fournisseur?.nom || '—';
    };

    const DEVISE_SYM = { NGN: '₦', EUR: '€', USD: '$', CNY: '¥', XOF: 'FCFA', AUTRE: '' };
    const formatOrigine = (val, devise) => {
        if (!val && val !== 0) return '0';
        return Math.round(Number(val)).toLocaleString('fr-FR') + ' ' + (DEVISE_SYM[devise] || '₦');
    };

    return (
        <View style={styles.container}>
            {/* Top Navigation Header replacing Drawer */}
            <TopHeaderNav navigation={navigation} activeCategory="arrivages" />

            {/* View Action Header */}
            <View style={styles.viewHeaderRow}>
                <View style={{ flex: 1 }}>
                    <Text style={styles.headerTitle}>Arrivages & Importations</Text>
                    <Text style={styles.headerSub}>{totalArrivages} arrivage(s) enregistré(s)</Text>
                </View>

                {/* Stylish Add (+) Button */}
                <TouchableOpacity
                    style={styles.btnAddPill}
                    onPress={() => navigation.navigate('ArrivageCreate')}
                    activeOpacity={0.88}
                >
                    <Ionicons name="add-circle" size={18} color="#FFFFFF" />
                    <Text style={styles.btnAddPillText}>Arrivage</Text>
                </TouchableOpacity>
            </View>

            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                    <Text style={styles.loaderText}>Chargement des arrivages...</Text>
                </View>
            ) : (
                <FlatList
                    data={arrivages}
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
                            <Ionicons name="cube-outline" size={50} color={Colors.border} />
                            <Text style={styles.emptyTitle}>Aucun arrivage enregistré</Text>
                        </View>
                    }
                    renderItem={({ item }) => {
                        const badge = getStatutBadge(item.statut);
                        const dateStr = item.created_at ? formatDateFr(item.created_at) : '—';
                        const totalFrais = (Number(item.frais_transport) || 0) + (Number(item.frais_douane) || 0) + (Number(item.frais_manutention) || 0) + (Number(item.frais_divers) || 0);
                        const totalQte = (item.produits || []).reduce((s, p) => s + Number(p.pivot?.quantite || p.quantite || 0), 0);

                        return (
                            <TouchableOpacity
                                style={styles.card}
                                onPress={() => navigation.navigate('ArrivageShow', { id: item.id })}
                            >
                                <View style={styles.cardTop}>
                                    <View style={styles.refBox}>
                                        <Ionicons name="git-pull-request" size={16} color={Colors.primary} />
                                        <Text style={styles.refText}>{item.reference || `ARR-#${item.id}`}</Text>
                                    </View>
                                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8 }}>
                                        <View style={[styles.badge, { backgroundColor: badge.bg }]}>
                                            <Text style={[styles.badgeText, { color: badge.color }]}>{badge.label}</Text>
                                        </View>
                                        <TouchableOpacity
                                            style={styles.editIconBtn}
                                            onPress={() => navigation.navigate('ArrivageEdit', { item })}
                                        >
                                            <Ionicons name="create-outline" size={16} color={Colors.primary} />
                                        </TouchableOpacity>
                                    </View>
                                </View>

                                <View style={styles.cardBody}>
                                    <View style={styles.infoRow}>
                                        <Ionicons name="person-outline" size={14} color={Colors.textLight} />
                                        <Text style={styles.infoText} numberOfLines={1}>Fournisseur(s): {getFournisseurs(item)}</Text>
                                    </View>
                                    <View style={styles.infoRow}>
                                        <Ionicons name="layers-outline" size={14} color={Colors.textLight} />
                                        <Text style={styles.infoText}>{item.produits?.length || 0} produit(s) · {totalQte} unité(s)</Text>
                                    </View>
                                    <View style={styles.infoRow}>
                                        <Ionicons name="business-outline" size={14} color={Colors.textLight} />
                                        <Text style={styles.infoText}>Magasin destination: {item.magasin?.nom || '—'}</Text>
                                    </View>
                                    <View style={styles.infoRow}>
                                        <Ionicons name="calendar-outline" size={14} color={Colors.textLight} />
                                        <Text style={styles.infoSub}>{dateStr}</Text>
                                    </View>
                                </View>

                                <View style={styles.cardFooter}>
                                    <View>
                                        <Text style={styles.footerLabel}>Taux (Devise/FCFA)</Text>
                                        <Text style={styles.footerVal}>{item.taux_change ? `1 ${DEVISE_SYM[item.devise_origine] || '₦'} = ${Number(item.taux_change).toLocaleString('fr-FR')}` : '—'}</Text>
                                    </View>
                                    <View style={{ alignItems: 'flex-end' }}>
                                        <Text style={styles.footerLabel}>Valeur Origine</Text>
                                        <Text style={styles.footerVal}>{formatOrigine(item.total_valeur_origine, item.devise_origine)}</Text>
                                    </View>
                                </View>

                                <View style={styles.cardFooterRow}>
                                    <View>
                                        <Text style={styles.footerLabel}>Total Frais</Text>
                                        <Text style={styles.footerVal}>{formatMoney(totalFrais)}</Text>
                                    </View>
                                    <View style={{ alignItems: 'flex-end' }}>
                                        <Text style={styles.footerLabel}>Coût Total Réel</Text>
                                        <Text style={styles.footerVal}>{formatMoney(item.total_cout_reel)}</Text>
                                    </View>
                                </View>

                                <View style={styles.cardFooterRow}>
                                    <View>
                                        <Text style={styles.footerLabel}>Bénéfice Prévu</Text>
                                        <Text style={[styles.footerVal, { color: computeBenefice(item) >= 0 ? Colors.success : Colors.error }]}>
                                            {formatMoney(computeBenefice(item))}
                                        </Text>
                                    </View>
                                </View>
                            </TouchableOpacity>
                        );
                    }}
                />
            )}

            {/* Modal Détail Arrivage */}
            <Modal visible={!!selectedArrivage} transparent animationType="slide" onRequestClose={() => setSelectedArrivage(null)}>
                <View style={styles.modalOverlay}>
                    <View style={styles.modalCard}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Détail Arrivage #{selectedArrivage?.id}</Text>
                            <TouchableOpacity onPress={() => setSelectedArrivage(null)}>
                                <Ionicons name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>

                        <ScrollView style={{ maxHeight: 350 }}>
                            <View style={styles.modalRow}>
                                <Text style={styles.modalLabel}>Référence :</Text>
                                <Text style={styles.modalVal}>{selectedArrivage?.reference || `ARR-#${selectedArrivage?.id}`}</Text>
                            </View>
                            <View style={styles.modalRow}>
                                <Text style={styles.modalLabel}>Fournisseur(s) :</Text>
                                <Text style={styles.modalVal}>{getFournisseurs(selectedArrivage)}</Text>
                            </View>
                            <View style={styles.modalRow}>
                                <Text style={styles.modalLabel}>Magasin destination :</Text>
                                <Text style={styles.modalVal}>{selectedArrivage?.magasin?.nom || '—'}</Text>
                            </View>
                            <View style={styles.modalRow}>
                                <Text style={styles.modalLabel}>Statut :</Text>
                                <Text style={[styles.modalVal, { color: getStatutBadge(selectedArrivage?.statut).color }]}>{getStatutBadge(selectedArrivage?.statut).label}</Text>
                            </View>
                            <View style={styles.modalRow}>
                                <Text style={styles.modalLabel}>Valeur Origine :</Text>
                                <Text style={styles.modalVal}>{formatOrigine(selectedArrivage?.total_valeur_origine, selectedArrivage?.devise_origine)}</Text>
                            </View>
                            <View style={styles.modalRow}>
                                <Text style={styles.modalLabel}>Coût Total Réel :</Text>
                                <Text style={styles.modalVal}>{formatMoney(selectedArrivage?.total_cout_reel)}</Text>
                            </View>
                            <View style={styles.modalRow}>
                                <Text style={styles.modalLabel}>Bénéfice :</Text>
                                <Text style={[styles.modalVal, { color: computeBenefice(selectedArrivage) >= 0 ? Colors.success : Colors.error }]}>{formatMoney(computeBenefice(selectedArrivage))}</Text>
                            </View>
                            <View style={styles.modalRow}>
                                <Text style={styles.modalLabel}>Quantité totale :</Text>
                                <Text style={styles.modalVal}>
                                    {(selectedArrivage?.produits || []).reduce((s, p) => s + Number(p.pivot?.quantite || p.quantite || 0), 0)} unité(s)
                                </Text>
                            </View>

                            <Text style={styles.modalSectionTitle}>Produits de l'arrivage</Text>
                            {selectedArrivage?.produits?.map((p, i) => (
                                <View key={i} style={styles.ligneRow}>
                                    <Text style={styles.ligneName}>{p.nom || p.produit?.nom || 'Article'}</Text>
                                    <Text style={styles.ligneQty}>{p.pivot?.quantite || p.quantite || 0} {(p.produit?.unite || 'Carton') + (Number(p.pivot?.quantite || p.quantite || 0) > 1 ? 's' : '')}</Text>
                                    <Text style={styles.lignePrice}>{formatOrigine(p.prix_unitaire_origine || p.pivot?.prix_unitaire_origine, selectedArrivage?.devise_origine)}</Text>
                                </View>
                            ))}
                        </ScrollView>

                        {!['receptionne', 'valide', 'integre', 'receptionne_offline', 'en_attente_sync'].includes(selectedArrivage?.statut) && (
                            <TouchableOpacity
                                style={styles.validerBtn}
                                onPress={() => handleValiderArrivage(selectedArrivage.id)}
                                disabled={validatingId === selectedArrivage?.id}
                            >
                                {validatingId === selectedArrivage?.id ? (
                                    <ActivityIndicator color="#FFF" />
                                ) : (
                                    <Text style={styles.validerBtnText}>Valider et mettre à jour le stock</Text>
                                )}
                            </TouchableOpacity>
                        )}
                        {['receptionne_offline', 'en_attente_sync'].includes(selectedArrivage?.statut) && (
                            <View style={{ backgroundColor: '#fff7ed', borderRadius: 12, padding: 14, marginTop: 16, flexDirection: 'row', alignItems: 'center', gap: 10 }}>
                                <Ionicons name="cloud-offline-outline" size={20} color="#c2410c" />
                                <Text style={{ color: '#c2410c', fontSize: 13, fontFamily: 'PlusJakartaSans_600SemiBold', flex: 1 }}>
                                    En attente de connexion — sera synchronisé automatiquement
                                </Text>
                            </View>
                        )}
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
        paddingBottom: 10,
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
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 10 },
    loaderText: { fontSize: 14, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    listContent: { padding: 16, paddingBottom: 80 },
    emptyBox: { alignItems: 'center', paddingVertical: 60, gap: 10 },
    emptyTitle: { fontSize: 16, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    card: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, elevation: 1, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.05, shadowRadius: 4, borderWidth: 1, borderColor: '#E2E8F0' },
    editIconBtn: { width: 30, height: 30, borderRadius: 15, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    cardTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10 },
    refBox: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    refText: { fontSize: 15, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.primary },
    badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
    badgeText: { fontSize: 11, fontFamily: 'PlusJakartaSans_700Bold' },
    cardBody: { gap: 4, marginBottom: 12 },
    infoRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    infoText: { fontSize: 13, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.text },
    infoSub: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    cardFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 10, borderTopWidth: 1, borderTopColor: Colors.border },
    cardFooterRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 8, marginTop: 8 },
    footerLabel: { fontSize: 11, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    footerVal: { fontSize: 13, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalCard: { backgroundColor: Colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    modalTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.primary },
    modalRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 6, borderBottomWidth: 1, borderBottomColor: Colors.border },
    modalLabel: { fontSize: 13, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.textLight },
    modalVal: { fontSize: 13, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.text },
    modalSectionTitle: { fontSize: 14, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.primary, marginTop: 16, marginBottom: 8 },
    ligneRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 6 },
    ligneName: { flex: 1, fontSize: 13, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.text },
    ligneQty: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginHorizontal: 8 },
    lignePrice: { fontSize: 13, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.primary },
    validerBtn: { backgroundColor: Colors.success, borderRadius: 12, paddingVertical: 14, alignItems: 'center', marginTop: 16 },
    validerBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'PlusJakartaSans_700Bold' },
});

export default ArrivagesScreen;
