import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, ActivityIndicator, Alert, StatusBar,
    TouchableOpacity, Modal, TextInput,
    KeyboardAvoidingView, Platform
} from 'react-native';
import { useRoute } from '@react-navigation/core';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Header, Loader, StatusBadge } from '../../components/ui';
import { useAuth } from '../../context/AuthContext';
import { formatDateFr, formatDateTimeFr } from '../../utils/formatDate';

const LIVRAISON_STATUTS = [
    { key: 'livre', label: 'Livrée', color: Colors.success },
    { key: 'probleme', label: 'Problème', color: Colors.error },
];

const formatMoney = (val) => {
    if (!val && val !== 0) return '0 FCFA';
    return Number(val).toLocaleString('fr-FR') + ' FCFA';
};

const STATUT_MAP = {
    livre: { label: 'LIVRÉ', color: Colors.success, bg: Colors.success + '20' },
    probleme: { label: 'PROBLÈME SIGNALÉ', color: Colors.error, bg: Colors.error + '20' },
    en_attente: { label: 'NON LIVRÉ', color: Colors.warning, bg: Colors.warning + '20' },
};

const formatDate = (val) => {
    return formatDateFr(val);
};

const formatDateTime = (val) => {
    return formatDateTimeFr(val);
};

const clientName = (client) => {
    if (!client) return 'Vente Directe (Anonyme)';
    return client.nomComplet || `${client.prenom || ''} ${client.nom || ''}`.trim() || 'Vente Directe (Anonyme)';
};

const ShowLivraisonScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const route = useRoute();
    const { user } = useAuth();
    const { id } = route.params || {};
    const [vente, setVente] = useState(null);
    const [loading, setLoading] = useState(true);
    const [showLivModal, setShowLivModal] = useState(false);
    const [livStatut, setLivStatut] = useState(null);
    const [livNote, setLivNote] = useState('');
    const [actionLoading, setActionLoading] = useState(false);

    const isControleur = user?.role === 'controleur'
        || (Array.isArray(user?.roles_secondaires) && user.roles_secondaires.includes('controleur'));

    useEffect(() => {
        if (id) fetchVente();
    }, [id]);

    const fetchVente = async () => {
        try {
            const resp = await client.get(`/livraisons/${id}`);
            setVente(resp.data?.data || resp.data);
        } catch (e) {
            console.error('Error fetching livraison detail:', e);
            Alert.alert('Erreur', 'Impossible de charger la livraison.');
        } finally {
            setLoading(false);
        }
    };

    const openLivModal = () => {
        setLivStatut(vente.statut_livraison || 'en_attente');
        setLivNote('');
        setShowLivModal(true);
    };

    const handleLivraison = async () => {
        if (!livStatut) return;
        try {
            setActionLoading(true);
            await client.put(`/livraisons/${vente.id}/statut`, {
                statut_livraison: livStatut,
                note_livraison: livNote || null,
            });
            Alert.alert('Succès', 'Statut de livraison mis à jour.');
            setShowLivModal(false);
            fetchVente();
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Erreur lors de la mise à jour');
        } finally {
            setActionLoading(false);
        }
    };

    if (loading) return <Loader />;

    if (!vente) {
        return (
            <View style={styles.container}>
                <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                    <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                        <Ionicons name="arrow-back" size={20} color={Colors.text} />
                    </TouchableOpacity>
                    <Text style={styles.topTitle}>Détail Livraison</Text>
                    <View style={{ width: 36 }} />
                </View>
                <View style={styles.centerLoader}>
                    <Text style={styles.errorText}>Livraison introuvable</Text>
                </View>
            </View>
        );
    }

    const lignes = vente.lignes || [];

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle}>Détail Livraison #{vente.id}</Text>
                <TouchableOpacity onPress={fetchVente} style={styles.topActionBtn}>
                    <Ionicons name="refresh-outline" size={18} color={Colors.primary} />
                </TouchableOpacity>
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
                <View style={styles.summaryCard}>
                    <View style={[styles.livIconWrap, { backgroundColor: ((vente.statut_livraison || 'en_attente') === 'livre' ? Colors.success : ((vente.statut_livraison || 'en_attente') === 'probleme' ? Colors.error : Colors.warning)) + '20' }]}>
                        <Ionicons name="bicycle" size={22} color={(vente.statut_livraison || 'en_attente') === 'livre' ? Colors.success : ((vente.statut_livraison || 'en_attente') === 'probleme' ? Colors.error : Colors.warning)} />
                    </View>
                    <View style={{ flex: 1 }}>
                        <StatusBadge statut={vente.statut_livraison || 'en_attente'} map={STATUT_MAP} />
                        {vente.date_livraison ? (
                            <Text style={styles.createdAt}>Livrée le {formatDateTime(vente.date_livraison)}</Text>
                        ) : null}
                    </View>
                </View>

                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Articles de cette commande</Text>
                    {lignes.length > 0 ? (
                        <>
                            {lignes.map((l, idx) => (
                                <View key={idx} style={styles.ligneRow}>
                                    <View style={{ flex: 1 }}>
                                        <Text style={styles.ligneName}>{l.produit?.nom || 'Produit'}</Text>
                                        <Text style={styles.ligneSub}>Qté : {l.quantite} · {formatMoney(l.prix_vente)}</Text>
                                    </View>
                                    <Text style={styles.ligneTotal}>{formatMoney(l.total_ligne)}</Text>
                                </View>
                            ))}
                            <View style={styles.totalRow}>
                                <Text style={styles.totalLabel}>Montant Vente Total :</Text>
                                <Text style={[styles.totalVal, { color: Colors.primary }]}>{formatMoney(vente.montant_total)}</Text>
                            </View>
                        </>
                    ) : (
                        <Text style={styles.emptyText}>Aucun article enregistré</Text>
                    )}
                </View>

                {vente.notes ? (
                    <View style={styles.cardSection}>
                        <Text style={styles.cardTitle}>Note client ou vendeur</Text>
                        <Text style={styles.noteText}>{vente.notes}</Text>
                    </View>
                ) : null}

                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Info Livraison</Text>

                    <TouchableOpacity style={styles.statusBtn} onPress={openLivModal}>
                        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8 }}>
                            <Ionicons name="swap-horizontal-outline" size={18} color={Colors.primary} />
                            <Text style={styles.statusBtnText}>Changer le statut de livraison</Text>
                        </View>
                        <Ionicons name="chevron-forward" size={18} color={Colors.primary} />
                    </TouchableOpacity>

                    {vente.livreur?.name ? (
                        <View style={styles.infoRow}>
                            <Text style={styles.infoLabel}>Contrôleur assigné :</Text>
                            <Text style={styles.infoVal}>{vente.livreur.name}</Text>
                        </View>
                    ) : null}
                    {vente.note_livraison ? (
                        <View style={styles.notesBox}>
                            <Text style={styles.notesLabel}>Commentaire de livraison :</Text>
                            <Text style={styles.notesText}>{vente.note_livraison}</Text>
                        </View>
                    ) : null}
                </View>

                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Client & Magasin</Text>

                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Client :</Text>
                        <Text style={styles.infoVal}>{clientName(vente.client)}</Text>
                    </View>
                    {!isControleur && vente.client?.telephone ? (
                        <View style={styles.infoRow}>
                            <Text style={styles.infoLabel}>Téléphone :</Text>
                            <Text style={styles.infoVal}>{vente.client.telephone}</Text>
                        </View>
                    ) : null}
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Magasin d'expédition :</Text>
                        <Text style={styles.infoVal}>{vente.magasin?.nom || '—'}</Text>
                    </View>
                </View>
            </ScrollView>

            {/* Modal statut livraison */}
            <Modal visible={showLivModal} transparent animationType="slide" onRequestClose={() => setShowLivModal(false)}>
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Statut de livraison</Text>
                            <TouchableOpacity onPress={() => setShowLivModal(false)}>
                                <Ionicons name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>

                        {LIVRAISON_STATUTS.map(s => {
                            const locked = s.key === 'en_attente'
                                && (vente.statut_livraison === 'livre' || vente.statut_livraison === 'probleme');
                            return (
                                <TouchableOpacity
                                    key={s.key}
                                    style={[styles.statusOption, livStatut === s.key && styles.statusOptionActive, locked && styles.statusOptionLocked]}
                                    disabled={locked}
                                    onPress={() => setLivStatut(s.key)}
                                >
                                    <View style={[styles.dot, { backgroundColor: s.color }]} />
                                    <Text style={[styles.statusOptionText, livStatut === s.key && styles.statusOptionTextActive]}>{s.label}</Text>
                                    {livStatut === s.key && <Ionicons name="checkmark" size={18} color={Colors.primary} />}
                                </TouchableOpacity>
                            );
                        })}
                        <Text style={[styles.label, { marginTop: 12 }]}>Note (optionnel)</Text>
                        <TextInput
                            style={styles.input}
                            placeholder="Précisions sur la livraison..."
                            value={livNote}
                            onChangeText={setLivNote}
                        />
                        <TouchableOpacity style={styles.submitBtn} onPress={handleLivraison} disabled={actionLoading}>
                            {actionLoading ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>Mettre à jour</Text>}
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
    topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#FFFFFF', paddingHorizontal: 16, paddingBottom: 12, borderBottomWidth: 1, borderBottomColor: '#E2E8F0' },
    backBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    topActionBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    topTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    errorText: { color: Colors.error, fontSize: 14, fontFamily: 'Poppins_500Medium' },
    scrollContent: { padding: 16, paddingBottom: 30 },
    summaryCard: { flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.surface, borderRadius: 16, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16, gap: 12 },
    livIconWrap: { width: 44, height: 44, borderRadius: 22, justifyContent: 'center', alignItems: 'center' },
    createdAt: { fontSize: 12, color: Colors.textLight, fontFamily: 'Poppins_400Regular' },
    cardSection: { backgroundColor: Colors.surface, borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    cardTitle: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 12 },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    infoLabel: { fontSize: 13, color: Colors.textLight, fontFamily: 'Poppins_500Medium' },
    infoVal: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text, flexShrink: 1, textAlign: 'right', marginLeft: 12 },
    notesBox: { marginTop: 8 },
    notesLabel: { fontSize: 13, color: Colors.textLight, fontFamily: 'Poppins_500Medium' },
    notesText: { fontSize: 13, color: Colors.text, marginTop: 4 },
    noteText: { fontSize: 13, color: Colors.text, fontFamily: 'Poppins_400Regular' },
    ligneRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    ligneName: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    ligneSub: { fontSize: 11, color: Colors.textLight, marginTop: 2 },
    ligneTotal: { fontSize: 13, fontFamily: 'Poppins_700Bold', color: Colors.text },
    totalRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 10, paddingTop: 10, borderTopWidth: 1, borderTopColor: Colors.border },
    totalLabel: { fontSize: 13, fontFamily: 'Poppins_700Bold', color: Colors.text },
    totalVal: { fontSize: 15, fontFamily: 'Poppins_700Bold' },
    emptyText: { textAlign: 'center', color: Colors.textLight, fontSize: 12, paddingVertical: 12 },
    statusBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: Colors.primary + '10', borderRadius: 12, padding: 12, marginBottom: 12, borderWidth: 1, borderColor: Colors.primary + '30' },
    statusBtnText: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.primary },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: Colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20, gap: 10 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    modalTitle: { fontSize: 18, fontFamily: 'Poppins_700Bold', color: Colors.text },
    label: { fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.textLight },
    input: { backgroundColor: Colors.background, borderRadius: 10, borderWidth: 1, borderColor: Colors.border, paddingHorizontal: 12, paddingVertical: 10, fontSize: 14, color: Colors.text },
    submitBtn: { backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 14, alignItems: 'center', marginTop: 6 },
    submitBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'Poppins_700Bold' },
    statusOption: { flexDirection: 'row', alignItems: 'center', gap: 10, paddingVertical: 12, paddingHorizontal: 12, borderRadius: 10, borderWidth: 1, borderColor: Colors.border, marginBottom: 8 },
    statusOptionActive: { borderColor: Colors.primary, backgroundColor: Colors.primary + '0A' },
    statusOptionLocked: { opacity: 0.4 },
    dot: { width: 12, height: 12, borderRadius: 6 },
    statusOptionText: { flex: 1, fontSize: 14, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    statusOptionTextActive: { color: Colors.primary },
    statusOptionNote: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginTop: 2, marginBottom: 4 },
});

export default ShowLivraisonScreen;
