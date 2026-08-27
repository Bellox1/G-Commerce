import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, Alert, StatusBar, Linking
} from 'react-native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { formatDateFr, formatDateTimeFr } from '../../utils/formatDate';

const formatMoney = (val) => {
    if (val === null || val === undefined || val === '') return '0 FCFA';
    let n = Math.round(Number(val));
    if (!n || Math.abs(n) === 0) n = 0;
    return n.toLocaleString('fr-FR') + ' FCFA';
};

const ShowClientScreen = ({ navigation, route }) => {
    const insets = useSafeAreaInsets();
    const { id } = route?.params || {};
    const [clientData, setClientData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (id) fetchClientDetail();
    }, [id]);

    const fetchClientDetail = async () => {
        try {
            const resp = await client.get(`/clients/${id}`);
            setClientData(resp.data?.data || resp.data);
        } catch (e) {
            console.error('Error fetching client detail:', e);
            Alert.alert('Erreur', 'Impossible de charger la fiche client.');
        } finally {
            setLoading(false);
        }
    };

    const handleCall = (phone) => {
        if (phone) Linking.openURL(`tel:${phone}`);
    };

    const handleWhatsApp = (phone) => {
        if (!phone) return;
        const num = phone.replace(/\D/g, '');
        Linking.openURL(`https://wa.me/${num}`);
    };

    if (loading) {
        return (
            <View style={styles.centerLoader}>
                <ActivityIndicator size="large" color={Colors.primary} />
            </View>
        );
    }

    const c = clientData?.client || clientData;
    const dettes = clientData?.dettes || [];
    const ventes = clientData?.ventes || [];
    const detteTotale = dettes.reduce((sum, d) => sum + Number(d.montant_restant || 0), 0);

    if (!c) {
        return (
            <View style={styles.centerLoader}>
                <Text style={styles.errorText}>Client introuvable</Text>
            </View>
        );
    }

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle}>{c.nom} {c.prenom || ''}</Text>
                <TouchableOpacity onPress={() => navigation.navigate('ClientEdit', { id: c.id })} style={styles.topActionBtn}>
                    <Ionicons name="pencil-outline" size={18} color={Colors.primary} />
                </TouchableOpacity>
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>

                {/* Profile Card */}
                <View style={styles.profileCard}>
                    <View style={styles.avatarCircle}>
                        <Text style={styles.avatarText}>{(c.nom || 'C').slice(0, 2).toUpperCase()}</Text>
                    </View>
                    <Text style={styles.clientName}>{c.nom} {c.prenom || ''}</Text>
                    <Text style={styles.clientPhone}>📱 {c.telephone || 'Non renseigné'}</Text>
                    {c.adresse ? <Text style={styles.clientSub}>📍 {c.adresse}</Text> : null}

                    {c.telephone ? (
                        <View style={styles.contactRow}>
                            <TouchableOpacity style={styles.callBtn} onPress={() => handleCall(c.telephone)}>
                                <Ionicons name="call" size={18} color="#FFF" />
                                <Text style={styles.contactBtnText}>Appel</Text>
                            </TouchableOpacity>
                            <TouchableOpacity style={styles.whatsappBtn} onPress={() => handleWhatsApp(c.telephone)}>
                                <Ionicons name="logo-whatsapp" size={18} color="#FFF" />
                                <Text style={styles.contactBtnText}>WhatsApp</Text>
                            </TouchableOpacity>
                        </View>
                    ) : null}
                </View>

                {/* Financial Overview */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Bilan Financier Client</Text>

                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Limite de crédit autorisée :</Text>
                        <Text style={styles.infoVal}>{formatMoney(c.limite_credit || 500000)}</Text>
                    </View>

                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Dette actuelle en cours :</Text>
                        <Text style={[styles.infoVal, { color: detteTotale > 0 ? Colors.error : Colors.success, fontWeight: '800' }]}>
                            {formatMoney(detteTotale)}
                        </Text>
                    </View>
                </View>

                {/* Dettes / Créances */}
                {dettes.length > 0 && (
                    <View style={styles.cardSection}>
                        <Text style={styles.cardTitle}>Dettes & Créances ({dettes.length})</Text>
                        {dettes.map((d, i) => (
                            <TouchableOpacity
                                key={i}
                                style={styles.itemRow}
                                onPress={() => navigation.navigate('DetteShow', { id: d.id })}
                            >
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.itemRef}>Dette #{d.id}</Text>
                                    <Text style={styles.itemSub}>Échéance: {d.date_echeance ? formatDateFr(d.date_echeance) : 'N/A'}</Text>
                                </View>
                                <Text style={[styles.itemVal, { color: Colors.error }]}>{formatMoney(d.montant_restant)}</Text>
                            </TouchableOpacity>
                        ))}
                    </View>
                )}

                {/* Ventes / Achats Récents */}
                {ventes.length > 0 && (
                    <View style={styles.cardSection}>
                        <Text style={styles.cardTitle}>Historique d'Achats ({ventes.length})</Text>
                        {ventes.map((v, i) => (
                            <TouchableOpacity
                                key={i}
                                style={styles.itemRow}
                                onPress={() => navigation.navigate('VenteShow', { id: v.id })}
                            >
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.itemRef}>{v.reference || `Vente #${v.id}`}</Text>
                                    <Text style={styles.itemSub}>{v.created_at ? formatDateFr(v.created_at) : ''}</Text>
                                </View>
                                <Text style={[styles.itemVal, { color: Colors.primary }]}>{formatMoney(v.montant_total)}</Text>
                            </TouchableOpacity>
                        ))}
                    </View>
                )}

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
    profileCard: { backgroundColor: '#FFF', borderRadius: 16, padding: 20, borderWidth: 1, borderColor: Colors.border, alignItems: 'center', marginBottom: 16 },
    avatarCircle: { width: 60, height: 60, borderRadius: 30, backgroundColor: Colors.primary + '20', justifyContent: 'center', alignItems: 'center', marginBottom: 10 },
    avatarText: { fontSize: 22, fontWeight: '800', color: Colors.primary },
    clientName: { fontSize: 18, fontFamily: 'Poppins_700Bold', color: Colors.text },
    clientPhone: { fontSize: 13, color: Colors.textLight, marginTop: 4 },
    clientSub: { fontSize: 12, color: Colors.textLight, marginTop: 2 },
    contactRow: { flexDirection: 'row', justifyContent: 'center', gap: 12, marginTop: 14 },
    callBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: Colors.success, borderRadius: 10, paddingVertical: 8, paddingHorizontal: 16 },
    whatsappBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: '#25D366', borderRadius: 10, paddingVertical: 8, paddingHorizontal: 16 },
    contactBtnText: { color: '#FFF', fontSize: 13, fontFamily: 'Poppins_600SemiBold' },
    cardSection: { backgroundColor: '#FFF', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    cardTitle: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 12 },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    infoLabel: { fontSize: 13, color: Colors.textLight },
    infoVal: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    itemRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    itemRef: { fontSize: 13, fontWeight: '700', color: Colors.text },
    itemSub: { fontSize: 11, color: Colors.textLight, marginTop: 2 },
    itemVal: { fontSize: 13, fontWeight: '700' },
});

export default ShowClientScreen;
