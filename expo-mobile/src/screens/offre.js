import React, { useState, useEffect, useCallback } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, RefreshControl, Linking
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
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
    const [error, setError] = useState(false);

    const fetchOffre = useCallback(async () => {
        setError(false);
        try {
            const resp = await client.get('/offre');
            const body = resp.data;
            const parsed = body?.data ?? body;
            // Guard: if offline/empty response, treat as error
            if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed) || !parsed.offre_nom) {
                setError(true);
                setData(null);
            } else {
                setData(parsed);
            }
        } catch (e) {
            console.error('Erreur offre:', e);
            setError(true);
            setData(null);
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
            <View style={styles.safe}>
                <ScreenHeader title="Mon offre" navigation={navigation} />
                <View style={styles.center}><ActivityIndicator size="large" color={INK} /></View>
            </View>
        );
    }

    if (!data) {
        return (
            <View style={styles.safe}>
                <ScreenHeader title="Mon offre" navigation={navigation} />
                <View style={styles.center}>
                    <Ionicons name="cloud-offline-outline" size={48} color="#94A3B8" style={{ marginBottom: 16 }} />
                    <Text style={[styles.muted, { textAlign: 'center', marginBottom: 20 }]}>
                        {error ? 'Données non disponibles hors-ligne.\nVeuillez activer votre connexion et réessayer.' : 'Impossible de charger votre offre.'}
                    </Text>
                    <TouchableOpacity
                        onPress={() => { setLoading(true); fetchOffre(); }}
                        style={{ backgroundColor: '#1E293B', paddingHorizontal: 24, paddingVertical: 12, borderRadius: 12 }}
                    >
                        <Text style={{ color: '#fff', fontWeight: '700', fontSize: 14 }}>Réessayer</Text>
                    </TouchableOpacity>
                </View>
            </View>
        );
    }

    const days = data.jours_restants;
    const showWarn = data.actif && days !== null && days <= 7 && days > 0;
    const showExpired = !data.actif && !data.est_vie;

    const fmtPrix = (v) => new Intl.NumberFormat('fr-FR').format(v ?? 0);
    const ACCENT = { call: INK, wa: INK, mail: INK };

    return (
        <View style={styles.safe}>
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

                <View style={[styles.card, styles.cardDark]}>
                    <View style={styles.cardHead}>
                        <View style={[styles.badge, styles.badgeDark]}><Text style={[styles.badgeText, styles.badgeTextDark]}>{data.offre_nom}</Text></View>
                        <View style={[styles.status, data.actif ? styles.statusOnDark : styles.statusOffDark]}>
                            <Text style={[styles.statusText, styles.statusTextDark]}>{data.actif ? 'Actif' : 'Expiré'}</Text>
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
                                <Text style={[styles.priceVal, styles.priceValDark]}>{fmtPrix(data.prix)} FCFA</Text>
                                <Text style={[styles.priceSub, styles.priceSubDark]}>/ an</Text>
                            </>
                        )}
                    </View>

                    <View style={styles.meta}>
                        {data.est_admin ? (
                            <View style={styles.metaRow}><Ionicons name="shield-checkmark-outline" size={16} color="#93C5FD" /><Text style={[styles.metaText, styles.metaTextDark]}>Aucun abonnement client associé à ce compte.</Text></View>
                        ) : (
                            <>
                                <View style={styles.metaRow}><Ionicons name="calendar-outline" size={16} color="#93C5FD" /><Text style={[styles.metaText, styles.metaTextDark]}>Expiration : <Text style={{ fontWeight: '700', color: '#fff' }}>{data.expires_at ?? '—'}</Text></Text></View>
                                {days !== null && !data.est_vie && (
                                    <View style={styles.metaRow}><Ionicons name="hourglass-outline" size={16} color="#93C5FD" /><Text style={[styles.metaText, styles.metaTextDark]}>{days > 0 ? `Reste ${days} jour(s)` : 'A expiré'}</Text></View>
                                )}
                            </>
                        )}
                    </View>

                    {!data.est_admin && (
                        <>
                            <Text style={[styles.sectionTitle, styles.sectionTitleDark]}>Ce qui est inclus</Text>
                            <View style={styles.features}>
                                {data.features.map((f, i) => (
                                    <View key={i} style={styles.feature}>
                                        <Ionicons name="checkmark-circle" size={18} color="#60A5FA" />
                                        <Text style={[styles.featureText, styles.featureTextDark]}>{f}</Text>
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

                {/* Bloc incitation montée en offre */}
                {!data.est_admin && data.offre_code === 'essentiel' && (
                    <View style={styles.upgradeBlock}>
                        {/* Décoration de fond */}
                        <View style={styles.upgradeDeco} />

                        <View style={styles.upgradeHeader}>
                            <View style={styles.upgradeIconBox}>
                                <Ionicons name="rocket" size={26} color="#fff" />
                            </View>
                            <View style={{ flex: 1 }}>
                                <View style={styles.upgradeTag}>
                                    <Text style={styles.upgradeTagText}>✨ Passez à l'offre supérieure</Text>
                                </View>
                                <Text style={styles.upgradeTitle}>Votre commerce grandit ?{'\n'}PILOTIX grandit avec vous.</Text>
                            </View>
                        </View>

                        <Text style={styles.upgradeDesc}>
                            Vous êtes sur l'offre <Text style={{ color: '#fff', fontWeight: '800' }}>{data.offre_nom}</Text>. Si votre activité implique des{' '}
                            <Text style={styles.upgradeEmphasis}>importations</Text>, plusieurs{' '}
                            <Text style={styles.upgradeEmphasis}>magasins/dépôts</Text> ou une{' '}
                            <Text style={styles.upgradeEmphasis}>équipe élargie</Text> — l'offre supérieure vous offrira bien plus de puissance.
                        </Text>

                        <View style={styles.upgradeReasons}>
                            {[
                                { icon: 'cube', text: 'Importation & arrivages — Coûts de revient et marges à l\'importation.' },
                                { icon: 'business', text: 'Multi-magasins & dépôts — Gérez plusieurs points de vente depuis un seul compte.' },
                                { icon: 'people', text: 'Équipe & multi-postes — Vendeurs, magasiniers, livreurs et superviseurs.' },
                                { icon: 'stats-chart', text: 'Statistiques avancées — Analyse par magasin, produit et période.' },
                            ].map((r, i) => (
                                <View key={i} style={styles.upgradeReason}>
                                    <Ionicons name={r.icon} size={16} color="#93C5FD" />
                                    <Text style={styles.upgradeReasonText}>{r.text}</Text>
                                </View>
                            ))}
                        </View>

                        <View style={styles.upgradeNote}>
                            <Ionicons name="information-circle" size={18} color="#93C5FD" style={{ marginTop: 1 }} />
                            <Text style={styles.upgradeNoteText}>
                                Pas d'importation, pas de multi-magasins, vous gérez seul ?{' '}
                                <Text style={{ color: '#fff', fontWeight: '700' }}>Votre offre actuelle est suffisante</Text> — renouvelez simplement à l'échéance.
                            </Text>
                        </View>

                        <TouchableOpacity
                            style={styles.upgradeCta}
                            onPress={() => openLink(`https://wa.me/${data.contact.whatsapp}`)}
                            activeOpacity={0.85}
                        >
                            <Ionicons name="logo-whatsapp" size={20} color="#fff" />
                            <Text style={styles.upgradeCtaText}>Discuter de la montée en offre</Text>
                        </TouchableOpacity>
                    </View>
                )}
            </ScrollView>
        </View>
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
    /* Dark card — offre en cours */
    cardDark: {
        backgroundColor: '#0F172A',
        borderColor: 'transparent',
        overflow: 'hidden',
    },
    cardHead: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 14 },
    badge: { backgroundColor: '#F1F5F9', paddingHorizontal: 14, paddingVertical: 6, borderRadius: 50 },
    badgeDark: { backgroundColor: 'rgba(255,255,255,0.15)' },
    badgeText: { color: INK, fontWeight: '800', fontSize: 14 },
    badgeTextDark: { color: '#fff' },
    status: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 50 },
    statusOn: { backgroundColor: '#E2E8F0' },
    statusOff: { backgroundColor: '#CBD5E1' },
    statusOnDark: { backgroundColor: 'rgba(74,222,128,0.2)' },
    statusOffDark: { backgroundColor: 'rgba(239,68,68,0.2)' },
    statusText: { fontSize: 12, fontWeight: '700', color: INK },
    statusTextDark: { color: '#4ADE80' },
    priceBox: { marginBottom: 14 },
    priceVal: { fontSize: 30, fontWeight: '900', color: Colors.text, letterSpacing: -1 },
    priceValDark: { color: '#fff' },
    priceSub: { fontSize: 14, fontWeight: '700', color: Colors.textLight },
    priceSubDark: { color: 'rgba(255,255,255,0.6)' },
    meta: { marginBottom: 18 },
    metaRow: { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 6 },
    metaText: { fontSize: 14, color: Colors.textLight },
    metaTextDark: { color: 'rgba(255,255,255,0.7)' },
    sectionTitle: { fontSize: 16, fontWeight: '700', fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text, marginBottom: 12, borderBottomWidth: 2, borderBottomColor: INK, alignSelf: 'flex-start', paddingBottom: 4 },
    sectionTitleDark: { color: '#fff', borderBottomColor: 'rgba(255,255,255,0.3)' },
    features: { gap: 10 },
    feature: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    featureText: { fontSize: 14, color: '#475569' },
    featureTextDark: { color: 'rgba(255,255,255,0.8)' },
    contactCard: { backgroundColor: '#F8FAFC' },
    contactIntro: { fontSize: 13.5, color: Colors.textLight, fontFamily: 'PlusJakartaSans_400Regular', lineHeight: 20, marginBottom: 14 },
    contactBtn: { flexDirection: 'row', alignItems: 'center', gap: 10, borderRadius: 12, paddingVertical: 12, paddingHorizontal: 14, marginBottom: 10, backgroundColor: '#fff', borderWidth: 1, borderColor: '#E2E8F0' },
    contactLabel: { fontWeight: '700', fontSize: 14, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    contactValue: { opacity: 0.85, marginLeft: 'auto', fontSize: 12.5, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.text },
    faqLink: { flexDirection: 'row', alignItems: 'center', gap: 8, marginTop: 6 },
    faqLinkText: { color: INK, fontWeight: '700', fontFamily: 'PlusJakartaSans_700Bold', fontSize: 13.5 },

    /* ---- Bloc montée en offre ---- */
    upgradeBlock: {
        borderRadius: 20,
        overflow: 'hidden',
        backgroundColor: '#0F172A',
        padding: 20,
        marginBottom: 16,
        position: 'relative',
    },
    upgradeDeco: {
        position: 'absolute',
        top: -50, right: -50,
        width: 160, height: 160,
        borderRadius: 80,
        backgroundColor: 'rgba(255,255,255,0.04)',
    },
    upgradeHeader: { flexDirection: 'row', alignItems: 'flex-start', gap: 12, marginBottom: 14 },
    upgradeIconBox: {
        width: 50, height: 50,
        borderRadius: 14,
        backgroundColor: 'rgba(255,255,255,0.12)',
        alignItems: 'center',
        justifyContent: 'center',
        flexShrink: 0,
    },
    upgradeTag: {
        alignSelf: 'flex-start',
        backgroundColor: 'rgba(255,255,255,0.15)',
        borderRadius: 50,
        paddingHorizontal: 10,
        paddingVertical: 3,
        marginBottom: 6,
    },
    upgradeTagText: { color: '#fff', fontSize: 11, fontWeight: '700', letterSpacing: 0.3 },
    upgradeTitle: { fontSize: 15.5, fontWeight: '800', color: '#fff', lineHeight: 22, fontFamily: 'PlusJakartaSans_700Bold' },
    upgradeDesc: { fontSize: 13, color: 'rgba(255,255,255,0.75)', lineHeight: 20, marginBottom: 16 },
    upgradeEmphasis: { color: '#93C5FD', fontWeight: '700' },
    upgradeReasons: { gap: 10, marginBottom: 16 },
    upgradeReason: { flexDirection: 'row', alignItems: 'flex-start', gap: 10 },
    upgradeReasonText: { flex: 1, fontSize: 12.5, color: 'rgba(255,255,255,0.8)', lineHeight: 18 },
    upgradeNote: {
        flexDirection: 'row',
        alignItems: 'flex-start',
        gap: 10,
        backgroundColor: 'rgba(255,255,255,0.08)',
        borderRadius: 12,
        padding: 12,
        marginBottom: 18,
    },
    upgradeNoteText: { flex: 1, fontSize: 12.5, color: 'rgba(255,255,255,0.7)', lineHeight: 18 },
    upgradeCta: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 10,
        backgroundColor: '#25D366',
        borderRadius: 12,
        paddingVertical: 13,
        paddingHorizontal: 20,
    },
    upgradeCtaText: { color: '#fff', fontWeight: '800', fontSize: 14, fontFamily: 'PlusJakartaSans_700Bold' },
});

export default OffreScreen;
