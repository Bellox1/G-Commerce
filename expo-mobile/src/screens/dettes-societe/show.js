import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, Alert, StatusBar
} from 'react-native';
import { useRoute } from '@react-navigation/core';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { Loader, StatusBadge } from '../../components/ui';
import { formatDateFr } from '../../utils/formatDate';

const formatMoney = (val) => {
    if (!val && val !== 0) return '0 F';
    return Number(val).toLocaleString('fr-FR') + ' F';
};

const STATUT_MAP = {
    solde: { label: 'SOLDÉ', color: Colors.success, bg: Colors.success + '20' },
    active: { label: 'ACTIVE', color: Colors.error, bg: Colors.error + '20' },
    en_cours: { label: 'ACTIVE', color: Colors.error, bg: Colors.error + '20' },
};

const formatDate = (val) => {
    return formatDateFr(val);
};

const ShowDetteSocieteScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const route = useRoute();
    const { id } = route.params || {};
    const [dette, setDette] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (id) fetchDette();
    }, [id]);

    const fetchDette = async () => {
        try {
            const resp = await client.get(`/dettes-societe/${id}`);
            setDette(resp.data?.data || resp.data);
        } catch (e) {
            console.error('Error fetching dette societe detail:', e);
            Alert.alert('Erreur', 'Impossible de charger la dette.');
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <Loader />;

    if (!dette) {
        return (
            <View style={styles.container}>
                <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                    <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                        <Ionicons name="arrow-back" size={20} color={Colors.text} />
                    </TouchableOpacity>
                    <Text style={styles.topTitle}>Dette Fournisseur</Text>
                    <View style={{ width: 36 }} />
                </View>
                <View style={styles.centerLoader}>
                    <Text style={styles.errorText}>Dette introuvable</Text>
                </View>
            </View>
        );
    }

    const montant = Number(dette.montant) || 0;
    const paye = Number(dette.montant_paye) || 0;
    const restant = montant - paye;
    const paiements = dette.paiements || [];

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle}>Dette Fournisseur #{dette.id}</Text>
                <TouchableOpacity onPress={fetchDette} style={styles.topActionBtn}>
                    <Ionicons name="refresh-outline" size={18} color={Colors.primary} />
                </TouchableOpacity>
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
                <View style={styles.summaryCard}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
                        <StatusBadge statut={dette.statut || 'active'} map={STATUT_MAP} />
                        <Text style={styles.createdAt}>Créée le {formatDate(dette.date_dette)}</Text>
                    </View>

                    <View style={styles.amountRow}>
                        <View style={styles.amountBox}>
                            <Text style={styles.amountLabel}>Montant total</Text>
                            <Text style={styles.amountVal}>{formatMoney(montant)}</Text>
                        </View>
                        <View style={styles.amountBox}>
                            <Text style={[styles.amountLabel, { color: Colors.success }]}>Déjà payé</Text>
                            <Text style={[styles.amountVal, { color: Colors.success }]}>{formatMoney(paye)}</Text>
                        </View>
                        <View style={styles.amountBox}>
                            <Text style={[styles.amountLabel, { color: Colors.error }]}>Reste à payer</Text>
                            <Text style={[styles.amountVal, { color: Colors.error, fontWeight: '800' }]}>{formatMoney(restant)}</Text>
                        </View>
                    </View>
                </View>

                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Informations</Text>

                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Fournisseur :</Text>
                        <Text style={styles.infoVal}>{dette.fournisseur?.nom || '—'}</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Arrivage :</Text>
                        <Text style={styles.infoVal}>{dette.arrivage?.reference || '—'}</Text>
                    </View>
                    {dette.description ? (
                        <View style={styles.infoRow}>
                            <Text style={styles.infoLabel}>Description :</Text>
                            <Text style={styles.infoVal}>{dette.description}</Text>
                        </View>
                    ) : null}
                    {dette.taux_de_change ? (
                        <View style={styles.infoRow}>
                            <Text style={styles.infoLabel}>Taux de change :</Text>
                            <Text style={styles.infoVal}>{Number(dette.taux_de_change).toLocaleString('fr-FR')} FCFA</Text>
                        </View>
                    ) : null}
                    {dette.montant_origine ? (
                        <View style={styles.infoRow}>
                            <Text style={styles.infoLabel}>Montant d'origine :</Text>
                            <Text style={styles.infoVal}>{Number(dette.montant_origine).toLocaleString('fr-FR')} {dette.devise || ''}</Text>
                        </View>
                    ) : null}
                </View>

                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Historique des paiements</Text>
                    {paiements.length > 0 ? (
                        paiements.map((p, idx) => (
                            <View key={idx} style={styles.paiementRow}>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.paiementDate}>{formatDate(p.date_paiement)}</Text>
                                    <Text style={styles.paiementMeta}>
                                        {p.mode_paiement}{p.user?.name ? ` · ${p.user.name}` : ''}
                                    </Text>
                                    {p.notes ? <Text style={styles.paiementNote}>{p.notes}</Text> : null}
                                </View>
                                <Text style={[styles.paiementMontant, { color: Colors.success }]}>
                                    +{formatMoney(Number(p.montant) || 0)}
                                </Text>
                            </View>
                        ))
                    ) : (
                        <Text style={styles.emptyText}>Aucun paiement enregistré</Text>
                    )}
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
    summaryCard: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    createdAt: { fontSize: 12, color: Colors.textLight, fontFamily: 'Poppins_400Regular' },
    amountRow: { flexDirection: 'row', justifyContent: 'space-between' },
    amountBox: { flex: 1, alignItems: 'center' },
    amountLabel: { fontSize: 11, color: Colors.textLight, fontFamily: 'Poppins_400Regular' },
    amountVal: { fontSize: 15, fontWeight: '750', color: Colors.text, marginTop: 4 },
    cardSection: { backgroundColor: Colors.surface, borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    cardTitle: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 12 },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    infoLabel: { fontSize: 13, color: Colors.textLight, fontFamily: 'Poppins_500Medium' },
    infoVal: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text, flexShrink: 1, textAlign: 'right', marginLeft: 12 },
    paiementRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    paiementDate: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    paiementMeta: { fontSize: 11, color: Colors.textLight, marginTop: 2 },
    paiementNote: { fontSize: 11, color: Colors.textLight, marginTop: 2, fontStyle: 'italic' },
    paiementMontant: { fontSize: 14, fontWeight: '800' },
    emptyText: { textAlign: 'center', color: Colors.textLight, fontSize: 12, paddingVertical: 12 },
});

export default ShowDetteSocieteScreen;
