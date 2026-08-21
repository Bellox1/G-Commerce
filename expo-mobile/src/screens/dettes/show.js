import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity, Modal,
    ActivityIndicator, TextInput, Alert, StatusBar, Platform
} from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { formatDateFr, formatDateTimeFr } from '../../utils/formatDate';

const toLocalDate = (d) => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
};

const formatMoney = (val) => {
    if (val === null || val === undefined || val === '') return '0 F';
    return Number(val).toLocaleString('fr-FR') + ' FCFA';
};

const ShowDetteScreen = ({ navigation, route }) => {
    const insets = useSafeAreaInsets();
    const { id } = route?.params || {};
    const [detteData, setDetteData] = useState(null);
    const [loading, setLoading] = useState(true);

    // Formulaire de règlement
    const [montantPaiement, setMontantPaiement] = useState('');
    const [modePaiement, setModePaiement] = useState('especes');
    const [notePaiement, setNotePaiement] = useState('');
    const [submittingPaiement, setSubmittingPaiement] = useState(false);

    // Modification de l'échéance
    const [showEcheance, setShowEcheance] = useState(false);
    const [showEcheanceDate, setShowEcheanceDate] = useState(false);
    const [submittingEcheance, setSubmittingEcheance] = useState(false);

    const dette = detteData?.dette || detteData;
    const paiements = detteData?.paiements || dette?.paiements || [];

    // Badge d'échéance (En retard / Aujourd'hui / Dans X j)
    let echeanceBadge = null;
    if (dette?.date_echeance && (dette?.montant_restant > 0)) {
        const today = new Date();
        const ech = new Date(dette.date_echeance);
        const startToday = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        const startEch = new Date(ech.getFullYear(), ech.getMonth(), ech.getDate());
        const diff = Math.round((startEch - startToday) / 86400000);
        if (diff < 0) echeanceBadge = { label: 'En retard', color: Colors.error };
        else if (diff === 0) echeanceBadge = { label: "Aujourd'hui", color: Colors.warning };
        else echeanceBadge = { label: `Dans ${diff} j`, color: Colors.success };
    }

    const handleUpdateEcheance = async (option, customDate) => {
        setSubmittingEcheance(true);
        try {
            const payload = customDate
                ? { echeance_option: 'custom', date_echeance_custom: customDate }
                : { echeance_option: option };
            await client.put(`/dettes/${id}/echeance`, payload);
            setShowEcheance(false);
            setShowEcheanceDate(false);
            fetchDetteDetail();
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Mise à jour échouée.');
        } finally {
            setSubmittingEcheance(false);
        }
    };

    useEffect(() => {
        if (id) fetchDetteDetail();
    }, [id]);

    const fetchDetteDetail = async () => {
        try {
            const resp = await client.get(`/dettes/${id}`);
            setDetteData(resp.data?.data || resp.data);
        } catch (e) {
            console.error('Error fetching dette detail:', e);
            Alert.alert('Erreur', 'Impossible de charger la créance.');
        } finally {
            setLoading(false);
        }
    };

    const handleSubmittingPaiement = async () => {
        if (!montantPaiement || parseFloat(montantPaiement) <= 0) {
            Alert.alert('Erreur', 'Veuillez saisir un montant valide.');
            return;
        }

        setSubmittingPaiement(true);
        try {
            await client.post(`/dettes/${id}/payer`, {
                montant: parseFloat(montantPaiement),
                mode_paiement: modePaiement,
                note: notePaiement,
            });
            Alert.alert('Succès', 'Paiement de dette enregistré avec succès !');
            setMontantPaiement('');
            setNotePaiement('');
            fetchDetteDetail();
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Erreur lors de l\'enregistrement du paiement');
        } finally {
            setSubmittingPaiement(false);
        }
    };

    if (loading) {
        return (
            <View style={styles.centerLoader}>
                <ActivityIndicator size="large" color={Colors.primary} />
            </View>
        );
    }

    if (!dette) {
        return (
            <View style={styles.centerLoader}>
                <Text style={styles.errorText}>Dette introuvable</Text>
            </View>
        );
    }

    const isSolde = dette.statut === 'solde' || (dette.montant_restant <= 0);

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle}>Créance Client #{dette.id}</Text>
                <TouchableOpacity onPress={fetchDetteDetail} style={styles.topActionBtn}>
                    <Ionicons name="refresh-outline" size={18} color={Colors.primary} />
                </TouchableOpacity>
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>

                {/* Status Hero */}
                <View style={[styles.heroCard, { backgroundColor: isSolde ? '#f0fdf4' : '#fee2e2', borderColor: isSolde ? '#bbf7d0' : '#fca5a5' }]}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 }}>
                        <Text style={styles.heroClient}>{dette.client?.nom ? `${dette.client.nom} ${dette.client.prenom || ''}` : 'Client Anonyme'}</Text>
                        <View style={[styles.statusBadge, { backgroundColor: isSolde ? Colors.success : Colors.error }]}>
                            <Text style={styles.statusBadgeText}>{isSolde ? 'Soldé' : 'En Cours'}</Text>
                        </View>
                    </View>

                    <Text style={styles.heroSubtitle}>
                        {'Créance générée le '}{dette.created_at ? formatDateFr(dette.created_at) : '—'}
                        {dette.vente?.reference ? ' · Facture ' : ''}
                        {dette.vente?.reference ? (
                            <Text style={styles.heroLink} onPress={() => navigation.navigate('VenteShow', { id: dette.vente_id })}>{dette.vente.reference}</Text>
                        ) : null}
                    </Text>

                    <View style={styles.heroTotals}>
                        <View style={styles.heroTotalItem}>
                            <Text style={styles.heroTotalLabel}>Montant Initial</Text>
                            <Text style={styles.heroTotalVal}>{formatMoney(dette.montant_initial ?? dette.montant_total)}</Text>
                        </View>
                        <View style={styles.heroTotalItem}>
                            <Text style={styles.heroTotalLabel}>Remboursé</Text>
                            <Text style={[styles.heroTotalVal, { color: Colors.success }]}>{formatMoney(dette.montant_paye)}</Text>
                        </View>
                        <View style={styles.heroTotalItem}>
                            <Text style={styles.heroTotalLabel}>Reste à payer</Text>
                            <Text style={[styles.heroTotalVal, { color: isSolde ? Colors.success : Colors.error, fontWeight: '800' }]}>{formatMoney(dette.montant_restant)}</Text>
                        </View>
                    </View>

                    <View style={styles.echeanceRow}>
                        <View style={{ flex: 1 }}>
                            <Text style={styles.heroTotalLabel}>Date d'échéance</Text>
                            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 2 }}>
                                <Text style={styles.heroEcheanceVal}>
                                    {dette.date_echeance ? formatDateFr(dette.date_echeance) : 'Non définie'}
                                </Text>
                                {echeanceBadge ? (
                                    <View style={[styles.statusBadge, { backgroundColor: echeanceBadge.color }]}>
                                        <Text style={styles.statusBadgeText}>{echeanceBadge.label}</Text>
                                    </View>
                                ) : null}
                            </View>
                        </View>
                        <TouchableOpacity style={styles.echeanceBtn} onPress={() => setShowEcheance(true)}>
                            <Ionicons name="calendar-outline" size={16} color={Colors.primary} />
                            <Text style={styles.echeanceBtnText}>Modifier</Text>
                        </TouchableOpacity>
                    </View>
                </View>

                {/* Note de la dette */}
                {dette.notes ? (
                    <View style={styles.cardSection}>
                        <Text style={styles.cardTitle}>Note</Text>
                        <Text style={styles.noteText}>{dette.notes}</Text>
                    </View>
                ) : null}

                {/* Formulaire Enregistrer un Paiement */}
                {!isSolde && (
                    <View style={styles.cardSection}>
                        <Text style={styles.cardTitle}>Enregistrer un Paiement</Text>

                        <Text style={styles.inputLabel}>Montant versé (FCFA) *</Text>
                        <TextInput
                            style={styles.input}
                            keyboardType="numeric"
                            value={montantPaiement}
                            onChangeText={setMontantPaiement}
                            placeholder="ex: 10000"
                        />

                        <Text style={styles.inputLabel}>Mode de paiement</Text>
                        <View style={styles.modeRow}>
                            {[
                                { key: 'especes', label: 'Espèces' },
                                { key: 'mobile_money', label: 'Mobile Money' },
                                { key: 'cheque', label: 'Chèque' }
                            ].map(m => (
                                <TouchableOpacity
                                    key={m.key}
                                    style={[styles.modeChip, modePaiement === m.key && styles.modeChipActive]}
                                    onPress={() => setModePaiement(m.key)}
                                >
                                    <Text style={[styles.modeChipText, modePaiement === m.key && styles.modeChipTextActive]}>{m.label}</Text>
                                </TouchableOpacity>
                            ))}
                        </View>

                        <Text style={styles.inputLabel}>Remarque / Note</Text>
                        <TextInput
                            style={styles.input}
                            value={notePaiement}
                            onChangeText={setNotePaiement}
                            placeholder="Optionnel..."
                        />

                        <TouchableOpacity
                            style={styles.submitBtn}
                            onPress={handleSubmittingPaiement}
                            disabled={submittingPaiement}
                        >
                            {submittingPaiement ? (
                                <ActivityIndicator color="#FFF" />
                            ) : (
                                <Text style={styles.submitBtnText}>Valider l'encaissement</Text>
                            )}
                        </TouchableOpacity>
                    </View>
                )}

                {/* Historique des Règlements */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Historique des Règlements ({paiements.length})</Text>
                    {paiements.length > 0 ? (
                        paiements.map((p, idx) => (
                            <View key={idx} style={styles.paiementRow}>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.paiementDate}>{p.created_at ? formatDateTimeFr(p.created_at) : ''}</Text>
                                    <Text style={styles.paiementMode}>{p.mode_paiement || 'Espèces'} {p.notes ? `— ${p.notes}` : ''}</Text>
                                </View>
                                <Text style={styles.paiementVal}>+{formatMoney(p.montant)}</Text>
                            </View>
                        ))
                    ) : (
                        <Text style={styles.emptyText}>Aucun paiement enregistré pour l'instant</Text>
                    )}
                </View>

            </ScrollView>

            {/* Modal modification échéance */}
            <Modal visible={showEcheance} animationType="slide" transparent>
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Modifier l'échéance</Text>
                            <TouchableOpacity onPress={() => setShowEcheance(false)}>
                                <Ionicons name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>

                        {[
                            { key: 'today', label: "Aujourd'hui" },
                            { key: 'tomorrow', label: 'Demain' },
                            { key: 'after_tomorrow', label: 'Après-demain' },
                            { key: '6_days', label: 'Dans 6 jours' },
                            { key: '2_weeks', label: 'Dans 2 semaines' },
                            { key: '1_month', label: 'Dans 1 mois' },
                            { key: 'custom', label: 'Personnalisé...' },
                        ].map(o => (
                            <TouchableOpacity
                                key={o.key}
                                style={styles.optionRow}
                                onPress={() => {
                                    if (o.key === 'custom') {
                                        setShowEcheanceDate(true);
                                    } else {
                                        handleUpdateEcheance(o.key);
                                    }
                                }}
                            >
                                <Text style={styles.optionText}>{o.label}</Text>
                            </TouchableOpacity>
                        ))}
                    </View>
                </View>
            </Modal>

            {showEcheanceDate && (
                <DateTimePicker
                    value={dette.date_echeance ? new Date(dette.date_echeance) : new Date()}
                    mode="date"
                    display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                    onChange={(e, d) => {
                        setShowEcheanceDate(false);
                        if (d) handleUpdateEcheance('custom', toLocalDate(d));
                    }}
                />
            )}
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
    heroCard: { borderRadius: 16, padding: 16, borderWidth: 1, marginBottom: 16 },
    heroClient: { fontSize: 16, fontWeight: '700', color: Colors.text },
    statusBadge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8 },
    statusBadgeText: { color: '#FFF', fontSize: 11, fontWeight: '800' },
    heroLabel: { fontSize: 11, color: Colors.textLight, marginTop: 4 },
    heroAmount: { fontSize: 24, fontWeight: '800', marginTop: 2 },
    heroMetaRow: { flexDirection: 'row', justifyContent: 'space-between', marginTop: 12, paddingTop: 10, borderTopWidth: 1, borderTopColor: 'rgba(0,0,0,0.05)' },
    heroMetaText: { fontSize: 11, color: Colors.textLight, fontWeight: '600' },
    heroSubtitle: { fontSize: 11, color: Colors.textLight, marginBottom: 10 },
    heroLink: { color: Colors.primary, fontWeight: '700' },
    heroTotals: { flexDirection: 'row', justifyContent: 'space-between', marginTop: 12, paddingTop: 12, borderTopWidth: 1, borderTopColor: 'rgba(0,0,0,0.05)' },
    heroTotalItem: { flex: 1 },
    heroTotalLabel: { fontSize: 10, color: Colors.textLight, textTransform: 'uppercase' },
    heroTotalVal: { fontSize: 14, fontWeight: '700', marginTop: 2 },
    echeanceRow: { flexDirection: 'row', alignItems: 'center', marginTop: 12, paddingTop: 12, borderTopWidth: 1, borderTopColor: 'rgba(0,0,0,0.05)' },
    heroEcheanceVal: { fontSize: 14, fontWeight: '700', color: Colors.text },
    echeanceBtn: { flexDirection: 'row', alignItems: 'center', gap: 4, paddingHorizontal: 10, paddingVertical: 6, borderRadius: 8, backgroundColor: 'rgba(0,0,0,0.04)' },
    echeanceBtnText: { fontSize: 12, fontWeight: '700', color: Colors.primary },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.4)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: Colors.surface, borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 20, paddingBottom: 30 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
    modalTitle: { fontSize: 17, fontFamily: 'Poppins_700Bold', color: Colors.text },
    optionRow: { paddingVertical: 14, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    optionText: { fontSize: 14, fontWeight: '600', color: Colors.text },
    cardSection: { backgroundColor: '#FFF', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    cardTitle: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 12 },
    inputLabel: { fontSize: 12, fontWeight: '600', color: Colors.text, marginTop: 10, marginBottom: 4 },
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, backgroundColor: '#f8fafc' },
    modeRow: { flexDirection: 'row', gap: 8, marginVertical: 6 },
    modeChip: { flex: 1, paddingVertical: 8, borderRadius: 8, backgroundColor: '#f1f5f9', alignItems: 'center' },
    modeChipActive: { backgroundColor: Colors.primary },
    modeChipText: { fontSize: 11, fontWeight: '600', color: Colors.text },
    modeChipTextActive: { color: '#FFF', fontWeight: '700' },
    submitBtn: { backgroundColor: Colors.success, paddingVertical: 14, borderRadius: 10, alignItems: 'center', marginTop: 16 },
    submitBtnText: { color: '#FFF', fontWeight: '800', fontSize: 14 },
    paiementRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    paiementDate: { fontSize: 12, fontWeight: '600', color: Colors.text },
    paiementMode: { fontSize: 11, color: Colors.textLight, marginTop: 2 },
    paiementVal: { fontSize: 14, fontWeight: '800', color: Colors.success },
    noteText: { fontSize: 13, color: Colors.text, lineHeight: 19 },
    emptyText: { textAlign: 'center', color: Colors.textLight, fontSize: 12, paddingVertical: 12 },
});

export default ShowDetteScreen;
