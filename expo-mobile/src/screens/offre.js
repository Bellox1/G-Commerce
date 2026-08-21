import React, { useState, useEffect, useCallback } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, RefreshControl, Linking, SafeAreaView
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import Colors from '../theme/Colors';
import ScreenHeader from '../components/ScreenHeader';
import client from '../api/client';

const INK = '#1E293B';

const OffreScreen = () => {
    const navigation = useNavigation();

    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchOffre = useCallback(async () => {
        try {
            const resp = await client.get('/offre');
            const body = resp.data;
            setData(body?.data ?? body);
        } catch (e) {
            console.error('Erreur offre:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useEffect(() => { fetchOffre(); }, [fetchOffre]);
    const onRefresh = () => { setRefreshing(true); fetchOffre(); };

    const openLink = (url) => { Linking.openURL(url).catch(() => {}); };

    if (loading) {
        return (
            <SafeAreaView style={styles.safe}>
                <ScreenHeader title="Mon offre" navigation={navigation} />
                <View style={styles.center}><ActivityIndicator size="large" color={INK} /></View>
            </SafeAreaView>
        );
    }

    if (!data) {
        return (
            <SafeAreaView style={styles.safe}>
                <ScreenHeader title="Mon offre" navigation={navigation} />
                <View style={styles.center}><Text style={styles.muted}>Impossible de charger votre offre.</Text></View>
            </SafeAreaView>
        );
    }

    const days = data.jours_restants;
    const showWarn = data.actif && days !== null && days <= 7 && days > 0;
    const showExpired = !data.actif && !data.est_vie;

    const fmtPrix = (v) => new Intl.NumberFormat('fr-FR').format(v ?? 0);
    const ACCENT = { call: INK, wa: INK, mail: INK };

    return (
        <SafeAreaView style={styles.safe}>
            <ScreenHeader title="Mon offre" navigation={navigation} />
            <ScrollView
                style={styles.scroll}
                contentContainerStyle={styles.content}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={INK} />}
            >
                {showExpired && (
                    <View style={[styles.banner, styles.bannerExpired]}>
                                <Ionicons name="close-circle" size={22} color={INK} />
                        <Text style={styles.bannerText}>Votre abonnement est expiré. Certaines fonctionnalités sont suspendues — contactez-nous pour le renouveler.</Text>
                    </View>
                )}
                {showWarn && (
                    <View style={[styles.banner, styles.bannerWarn]}>
                                <Ionicons name="warning" size={22} color={INK} />
                        <Text style={styles.bannerText}>Votre abonnement expire dans <Text style={{ fontWeight: '800' }}>{days} jour(s)</Text> (le {data.expires_at}).</Text>
                    </View>
                )}

                <View style={styles.card}>
                    <View style={styles.cardHead}>
                        <View style={styles.badge}><Text style={styles.badgeText}>{data.offre_nom}</Text></View>
                        <View style={[styles.status, data.actif ? styles.statusOn : styles.statusOff]}>
                            <Text style={styles.statusText}>{data.actif ? 'Actif' : 'Expiré'}</Text>
                        </View>
                    </View>

                    <View style={styles.priceBox}>
                        {data.est_admin ? (
                            <>
                                <Text style={styles.priceVal}>Administrateur</Text>
                                <Text style={styles.priceSub}>Espace de gestion</Text>
                            </>
                        ) : data.est_vie ? (
                            <>
                                <Text style={styles.priceVal}>À vie</Text>
                                <Text style={styles.priceSub}>Licence dédiée</Text>
                            </>
                        ) : (
                            <>
                                <Text style={styles.priceVal}>{fmtPrix(data.prix)} FCFA</Text>
                                <Text style={styles.priceSub}>/ an</Text>
                            </>
                        )}
                    </View>

                    <View style={styles.meta}>
                        {data.est_admin ? (
                            <View style={styles.metaRow}><Ionicons name="shield-checkmark-outline" size={16} color={INK} /><Text style={styles.metaText}>Aucun abonnement client associé à ce compte.</Text></View>
                        ) : (
                            <>
                                <View style={styles.metaRow}><Ionicons name="calendar-outline" size={16} color={INK} /><Text style={styles.metaText}>Expiration : <Text style={{ fontWeight: '700' }}>{data.expires_at ?? '—'}</Text></Text></View>
                                {days !== null && !data.est_vie && (
                                    <View style={styles.metaRow}><Ionicons name="hourglass-outline" size={16} color={INK} /><Text style={styles.metaText}>{days > 0 ? `Reste ${days} jour(s)` : 'A expiré'}</Text></View>
                                )}
                            </>
                        )}
                    </View>

                    {!data.est_admin && (
                        <>
                            <Text style={styles.sectionTitle}>Ce qui est inclus</Text>
                            <View style={styles.features}>
                                {data.features.map((f, i) => (
                                    <View key={i} style={styles.feature}>
                                        <Ionicons name="checkmark-circle" size={18} color={INK} />
                                        <Text style={styles.featureText}>{f}</Text>
                                    </View>
                                ))}
                            </View>
                        </>
                    )}
                </View>

                <View style={[styles.card, styles.contactCard]}>
                    <Text style={styles.sectionTitle}>Renouveler mon abonnement</Text>
                    <Text style={styles.contactIntro}>Contactez notre équipe pour prolonger ou changer d'offre (comptant ou en 3 tranches).</Text>

                    <TouchableOpacity style={[styles.contactBtn, { borderColor: ACCENT.call }]} onPress={() => openLink(`tel:${data.contact.telephone}`)}>
                        <Ionicons name="call" size={18} color={ACCENT.call} />
                        <Text style={[styles.contactLabel, { color: ACCENT.call }]}>Appeler</Text>
                        <Text style={[styles.contactValue, { color: ACCENT.call }]}>{data.contact.telephone}</Text>
                    </TouchableOpacity>

                    <TouchableOpacity style={[styles.contactBtn, { borderColor: ACCENT.wa }]} onPress={() => openLink(`https://wa.me/${data.contact.whatsapp}`)}>
                        <Ionicons name="logo-whatsapp" size={18} color={ACCENT.wa} />
                        <Text style={[styles.contactLabel, { color: ACCENT.wa }]}>WhatsApp</Text>
                        <Text style={[styles.contactValue, { color: ACCENT.wa }]}>{data.contact.telephone}</Text>
                    </TouchableOpacity>

                    <TouchableOpacity style={[styles.contactBtn, { borderColor: ACCENT.mail }]} onPress={() => openLink(`mailto:${data.contact.email}`)}>
                        <Ionicons name="mail" size={18} color={ACCENT.mail} />
                        <Text style={[styles.contactLabel, { color: ACCENT.mail }]}>E-mail</Text>
                        <Text style={[styles.contactValue, { color: ACCENT.mail }]}>{data.contact.email}</Text>
                    </TouchableOpacity>

                    <TouchableOpacity style={styles.faqLink} onPress={() => navigation.navigate('FAQ', { tab: 'abonnement' })}>
                        <Ionicons name="help-circle-outline" size={16} color={INK} />
                        <Text style={styles.faqLinkText}>Voir la FAQ « Abonnement »</Text>
                    </TouchableOpacity>
                </View>
            </ScrollView>
        </SafeAreaView>
    );
};

const styles = StyleSheet.create({
    safe: { flex: 1, backgroundColor: '#F8FAFC' },
    scroll: { flex: 1 },
    content: { padding: 16, paddingBottom: 40 },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    muted: { color: Colors.textLight, fontSize: 14 },
    banner: { flexDirection: 'row', alignItems: 'flex-start', gap: 10, borderRadius: 14, padding: 14, marginBottom: 16 },
    bannerText: { flex: 1, fontSize: 13, lineHeight: 19, color: INK },
    bannerExpired: { backgroundColor: '#F1F5F9', borderWidth: 1, borderColor: '#CBD5E1' },
    bannerWarn: { backgroundColor: '#F1F5F9', borderWidth: 1, borderColor: '#CBD5E1' },
    card: { backgroundColor: '#fff', borderRadius: 18, padding: 20, marginBottom: 16, borderWidth: 1, borderColor: '#EEF2F6' },
    cardHead: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 14 },
    badge: { backgroundColor: '#F1F5F9', paddingHorizontal: 14, paddingVertical: 6, borderRadius: 50 },
    badgeText: { color: INK, fontWeight: '800', fontSize: 14 },
    status: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 50 },
    statusOn: { backgroundColor: '#E2E8F0' },
    statusOff: { backgroundColor: '#CBD5E1' },
    statusText: { fontSize: 12, fontWeight: '700', color: INK },
    priceBox: { marginBottom: 14 },
    priceVal: { fontSize: 30, fontWeight: '900', color: Colors.text, letterSpacing: -1 },
    priceSub: { fontSize: 14, fontWeight: '700', color: Colors.textLight },
    meta: { marginBottom: 18 },
    metaRow: { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 6 },
    metaText: { fontSize: 14, color: Colors.textLight },
    sectionTitle: { fontSize: 16, fontWeight: '700', fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text, marginBottom: 12, borderBottomWidth: 2, borderBottomColor: INK, alignSelf: 'flex-start', paddingBottom: 4 },
    features: { gap: 10 },
    feature: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    featureText: { fontSize: 14, color: '#475569' },
    contactCard: { backgroundColor: '#F8FAFC' },
    contactIntro: { fontSize: 13.5, color: Colors.textLight, fontFamily: 'PlusJakartaSans_400Regular', lineHeight: 20, marginBottom: 14 },
    contactBtn: { flexDirection: 'row', alignItems: 'center', gap: 10, borderRadius: 12, paddingVertical: 12, paddingHorizontal: 14, marginBottom: 10, backgroundColor: '#fff', borderWidth: 1, borderColor: '#E2E8F0' },
    contactLabel: { fontWeight: '700', fontSize: 14, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    contactValue: { opacity: 0.85, marginLeft: 'auto', fontSize: 12.5, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.text },
    faqLink: { flexDirection: 'row', alignItems: 'center', gap: 8, marginTop: 6 },
    faqLinkText: { color: INK, fontWeight: '700', fontFamily: 'PlusJakartaSans_700Bold', fontSize: 13.5 },
});

export default OffreScreen;
