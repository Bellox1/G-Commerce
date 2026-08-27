import React, { useState, useCallback, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, ActivityIndicator, Alert, StatusBar, TouchableOpacity
} from 'react-native';
import { useRoute } from '@react-navigation/core';
import { useFocusEffect } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Loader, StatusBadge } from '../../components/ui';

const STATUT_MAP = {
    livre: { label: 'Livré', color: Colors.success, bg: Colors.success + '20' },
    receptionne: { label: 'Réceptionné', color: Colors.success, bg: Colors.success + '20' },
    en_transit: { label: 'En transit', color: Colors.warning, bg: Colors.warning + '20' },
    en_attente: { label: 'En attente', color: Colors.textLight, bg: Colors.textLight + '20' },
};

const MOIS = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

const formatDate = (val) => {
    if (!val) return '—';
    const d = new Date(val);
    if (isNaN(d)) return '—';
    return `${d.getDate()} ${MOIS[d.getMonth()]} ${d.getFullYear()}`;
};

const formatDateTime = (val) => {
    if (!val) return '—';
    const d = new Date(val);
    if (isNaN(d)) return '—';
    const hh = String(d.getHours()).padStart(2, '0');
    const mm = String(d.getMinutes()).padStart(2, '0');
    return `${d.getDate()} ${MOIS[d.getMonth()]} ${d.getFullYear()} à ${hh}h${mm}`;
};

const ShowTransfertScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const route = useRoute();
    const { id } = route.params || {};
    const [transfert, setTransfert] = useState(null);
    const [loading, setLoading] = useState(true);
    const [recus, setRecus] = useState({});

    useEffect(() => {
        if (transfert?.produits) {
            const init = {};
            transfert.produits.forEach(p => { init[p.produit_id] = p.quantite; });
            setRecus(init);
        }
    }, [transfert]);

    const fetchTransfert = useCallback(async () => {
        try {
            const resp = await client.get(`/transferts/${id}`);
            setTransfert(resp.data?.data || resp.data);
        } catch (e) {
            console.error('Error fetching transfert detail:', e);
            const msg = e.response?.data?.message || 'Impossible de charger le transfert.';
            Alert.alert('Erreur', msg);
        } finally {
            setLoading(false);
        }
    }, [id]);

    useFocusEffect(
        useCallback(() => {
            fetchTransfert();
        }, [fetchTransfert])
    );

    const handleReception = async () => {
        try {
            const produits = Object.keys(recus).map(pid => ({ produit_id: Number(pid), quantite_recue: recus[pid] }));
            await client.post(`/transferts/${id}/reception`, { produits });
            Alert.alert('Succès', 'Transfert réceptionné avec succès.');
            fetchTransfert();
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Impossible de réceptionner le transfert.');
        }
    };

    if (loading) return <Loader />;

    if (!transfert) {
        return (
            <View style={styles.container}>
                <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                    <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                        <Ionicons name="arrow-back" size={20} color={Colors.text} />
                    </TouchableOpacity>
                    <Text style={styles.topTitle}>Détail Transfert</Text>
                    <View style={{ width: 36 }} />
                </View>
                <View style={styles.centerLoader}>
                    <Text style={styles.errorText}>Transfert introuvable</Text>
                </View>
            </View>
        );
    }

    const produits = transfert.produits && transfert.produits.length > 0
        ? transfert.produits
        : (transfert.produit ? [{ produit: transfert.produit, quantite: transfert.quantite }] : []);

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle}>Détail Transfert #{transfert.id}</Text>
                <TouchableOpacity onPress={() => navigation.navigate('TransfertEdit', { id })} style={styles.topActionBtn}>
                    <Ionicons name="pencil-outline" size={18} color={Colors.primary} />
                </TouchableOpacity>
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
                <View style={styles.summaryCard}>
                    <StatusBadge statut={transfert.statut || 'en_attente'} map={STATUT_MAP} />
                    <Text style={styles.createdAt}>
                        Créé le {formatDateTime(transfert.created_at)}{transfert.user?.name ? ` par ${transfert.user.name}` : ''}
                    </Text>
                </View>

                {transfert.statut === 'en_transit' ? (
                    <View style={styles.actionRow}>
                        <TouchableOpacity style={[styles.actionBtn, styles.actionReception]} onPress={handleReception}>
                            <Ionicons name="checkmark-circle-outline" size={18} color="#FFF" />
                            <Text style={styles.actionBtnText}>Valider la réception</Text>
                        </TouchableOpacity>
                        <TouchableOpacity style={[styles.actionBtn, styles.actionEdit]} onPress={() => navigation.navigate('TransfertEdit', { id })}>
                            <Ionicons name="create-outline" size={18} color="#FFF" />
                            <Text style={styles.actionBtnText}>Modifier</Text>
                        </TouchableOpacity>
                    </View>
                ) : null}

                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Détails du Transfert</Text>

                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Magasin Source :</Text>
                        <Text style={[styles.infoVal, { color: Colors.error }]}>{transfert.magasin_source?.nom || '—'}</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Magasin Destination :</Text>
                        <Text style={[styles.infoVal, { color: Colors.success }]}>{transfert.magasin_destination?.nom || '—'}</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Date du transfert :</Text>
                        <Text style={styles.infoVal}>{formatDate(transfert.date_transfert)}</Text>
                    </View>
                    {transfert.notes ? (
                        <View style={styles.notesBox}>
                            <Text style={styles.notesLabel}>Notes :</Text>
                            <Text style={styles.notesText}>{transfert.notes}</Text>
                        </View>
                    ) : null}
                </View>

                <View style={styles.cardSection}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
                        <Text style={styles.cardTitle}>Produits transférés</Text>
                        <View style={{ backgroundColor: Colors.primary + '15', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 }}>
                            <Text style={{ fontSize: 12, fontFamily: 'Poppins_700Bold', color: Colors.primary }}>
                                Total: {produits.reduce((sum, p) => sum + Number(p.quantite || 0), 0)}
                            </Text>
                        </View>
                    </View>
                    {produits.length > 0 ? (
                        <>
                            {produits.map((p, idx) => {
                                const pid = p.produit_id ?? p.produit?.id;
                                const recue = recus[pid] ?? p.quantite;
                                return (
                                    <View key={idx} style={styles.prodRow}>
                                        <View style={{ flex: 1 }}>
                                            <Text style={styles.prodName}>{p.produit?.nom || 'Produit'}</Text>
                                            <Text style={styles.prodSub}>Déclarée : {p.quantite}</Text>
                                        </View>
                                        {transfert.statut === 'en_transit' ? (
                                            <View style={styles.stepper}>
                                                <TouchableOpacity style={styles.stepperBtn} onPress={() => setRecus(prev => ({ ...prev, [pid]: Math.max(0, (prev[pid] ?? p.quantite) - 1) }))}>
                                                    <Ionicons name="remove" size={18} color={Colors.primary} />
                                                </TouchableOpacity>
                                                <Text style={styles.prodQty}>{recue}</Text>
                                                <TouchableOpacity style={styles.stepperBtn} onPress={() => setRecus(prev => ({ ...prev, [pid]: (prev[pid] ?? p.quantite) + 1 }))}>
                                                    <Ionicons name="add" size={18} color={Colors.primary} />
                                                </TouchableOpacity>
                                            </View>
                                        ) : (
                                            <Text style={[styles.prodQty, p.quantite_recue !== null && p.quantite_recue !== undefined && p.quantite_recue !== p.quantite ? { color: Colors.warning } : null]}>
                                                {p.quantite_recue !== null && p.quantite_recue !== undefined ? p.quantite_recue : recue} reçu(s)
                                            </Text>
                                        )}
                                    </View>
                                );
                            })}
                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginTop: 10, paddingTop: 10, borderTopWidth: 1, borderTopColor: Colors.border }}>
                                <Text style={{ fontSize: 13, fontFamily: 'Poppins_700Bold', color: Colors.text }}>Total général transféré :</Text>
                                <Text style={{ fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary }}>
                                    {produits.reduce((sum, p) => sum + Number(p.quantite || 0), 0)} unité(s)
                                </Text>
                            </View>
                        </>
                    ) : (
                        <Text style={styles.emptyText}>Aucun produit enregistré</Text>
                    )}
                </View>

                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Statut de livraison</Text>

                    {transfert.date_livraison ? (
                        <View style={styles.infoRow}>
                            <Text style={styles.infoLabel}>Date de livraison :</Text>
                            <Text style={styles.infoVal}>{formatDate(transfert.date_livraison)}</Text>
                        </View>
                    ) : null}
                    {transfert.livreur?.name ? (
                        <View style={styles.infoRow}>
                            <Text style={styles.infoLabel}>Contrôleur assigné :</Text>
                            <Text style={styles.infoVal}>{transfert.livreur.name}</Text>
                        </View>
                    ) : null}

                    {/* Trajet vertical — plus responsive */}
                    <View style={styles.trajet}>
                        <View style={styles.trajetRow}>
                            <View style={[styles.trajetIcon, { backgroundColor: '#fee2e2' }]}>
                                <Ionicons name="storefront-outline" size={18} color={Colors.error} />
                            </View>
                            <View style={{ flex: 1 }}>
                                <Text style={styles.trajetLabel}>Dépôt de départ</Text>
                                <Text style={[styles.trajetName, { color: Colors.error }]} numberOfLines={2}>{transfert.magasin_source?.nom || '—'}</Text>
                            </View>
                        </View>

                        <View style={styles.trajetArrow}>
                            <Ionicons name="arrow-down" size={20} color={Colors.primary} />
                        </View>

                        <View style={styles.trajetRow}>
                            <View style={[styles.trajetIcon, { backgroundColor: '#dcfce7' }]}>
                                <Ionicons name="storefront-outline" size={18} color={Colors.success} />
                            </View>
                            <View style={{ flex: 1 }}>
                                <Text style={styles.trajetLabel}>Dépôt d'arrivée</Text>
                                <Text style={[styles.trajetName, { color: Colors.success }]} numberOfLines={2}>{transfert.magasin_destination?.nom || '—'}</Text>
                            </View>
                        </View>
                    </View>
                </View>
            </ScrollView>
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#FFFFFF', paddingHorizontal: 16, paddingBottom: 12, borderBottomWidth: 1, borderBottomColor: '#E2E8F0' },
    backBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    topActionBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    topTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    errorText: { color: Colors.error, fontSize: 14, fontFamily: 'Poppins_500Medium' },
    scrollContent: { padding: 16, paddingBottom: 30 },
    summaryCard: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16, gap: 10 },
    actionRow: { flexDirection: 'row', gap: 10, marginBottom: 16 },
    actionBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, borderRadius: 12, paddingVertical: 12 },
    actionReception: { backgroundColor: Colors.success },
    actionEdit: { backgroundColor: Colors.primary },
    actionBtnText: { color: '#FFF', fontSize: 13, fontFamily: 'Poppins_700Bold' },
    createdAt: { fontSize: 12, color: Colors.textLight, fontFamily: 'Poppins_400Regular' },
    cardSection: { backgroundColor: Colors.surface, borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    cardTitle: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 12 },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    infoLabel: { fontSize: 13, color: Colors.textLight, fontFamily: 'Poppins_500Medium' },
    infoVal: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text, flexShrink: 1, textAlign: 'right', marginLeft: 12 },
    notesBox: { marginTop: 8 },
    notesLabel: { fontSize: 13, color: Colors.textLight, fontFamily: 'Poppins_500Medium' },
    notesText: { fontSize: 13, color: Colors.text, marginTop: 4 },
    prodRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    prodName: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    prodSub: { fontSize: 11, color: Colors.textLight, marginTop: 2 },
    prodQty: { fontSize: 13, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    stepper: { flexDirection: 'row', alignItems: 'center', gap: 10 },
    stepperBtn: { width: 30, height: 30, borderRadius: 8, backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border, justifyContent: 'center', alignItems: 'center' },
    trajet: { marginTop: 14, flexDirection: 'column', gap: 4 },
    trajetRow: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingVertical: 6 },
    trajetArrow: { paddingLeft: 24, paddingVertical: 2 },
    trajetIcon: { width: 36, height: 36, borderRadius: 8, justifyContent: 'center', alignItems: 'center', flexShrink: 0 },
    trajetLabel: { fontSize: 11, color: Colors.textLight, fontFamily: 'Poppins_600SemiBold', textTransform: 'uppercase' },
    trajetName: { fontSize: 14, fontFamily: 'Poppins_700Bold', flexShrink: 1 },
    emptyText: { textAlign: 'center', color: Colors.textLight, fontSize: 12, paddingVertical: 12 },
});

export default ShowTransfertScreen;
