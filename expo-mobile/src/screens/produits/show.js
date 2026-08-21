import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, Image, Alert, StatusBar
} from 'react-native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { formatDateFr, formatDateTimeFr } from '../../utils/formatDate';

const formatMoney = (val) => {
    if (val === null || val === undefined || val === '') return '0 F';
    return Math.round(Number(val)).toLocaleString('fr-FR') + ' FCFA';
};

const MVT_CONFIG = {
    entree_arrivage:    { label: 'Arrivage',       sign: 1 },
    sortie_vente:       { label: 'Sortie Vente',   sign: -1 },
    transfert_entree:   { label: 'Transfert Entrée', sign: 1 },
    transfert_sortie:   { label: 'Transfert Sortie', sign: -1 },
    ajustement_positif: { label: 'Ajust. +',       sign: 1 },
    ajustement_negatif: { label: 'Ajust. -',       sign: -1 },
};

const ShowProduitScreen = ({ navigation, route }) => {
    const insets = useSafeAreaInsets();
    const { id } = route?.params || {};

    if (!id) {
        return (
            <View style={styles.centerLoader}>
                <Text style={styles.loaderText}>Produit introuvable.</Text>
                <TouchableOpacity onPress={() => navigation.goBack()} style={{ marginTop: 12 }}>
                    <Text style={{ color: Colors.primary, fontWeight: '700' }}>Retour</Text>
                </TouchableOpacity>
            </View>
        );
    }

    const [produitData, setProduitData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [mouvements, setMouvements] = useState([]);
    const [mvtMeta, setMvtMeta] = useState({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
    const [mvtLoading, setMvtLoading] = useState(false);

    useEffect(() => {
        if (id) {
            fetchProduit();
            fetchMouvements(1, true);
        }
    }, [id]);

    const fetchProduit = async () => {
        try {
            const resp = await client.get(`/produits/${id}`);
            setProduitData(resp.data?.data || resp.data);
        } catch (e) {
            console.error('Error fetching produit detail:', e);
            Alert.alert('Erreur', 'Impossible de charger le produit.');
        } finally {
            setLoading(false);
        }
    };

    const fetchMouvements = async (page = 1, reset = false) => {
        if (mvtLoading) return;
        setMvtLoading(true);
        try {
            const resp = await client.get(`/produits/${id}/mouvements`, { params: { page, per_page: 10 } });
            const payload = resp.data?.data || [];
            const meta = resp.data?.meta || {};
            setMouvements(prev => (reset ? payload : [...prev, ...payload]));
            setMvtMeta({
                current_page: meta.current_page || page,
                last_page: meta.last_page || 1,
                per_page: meta.per_page || 10,
                total: meta.total || 0,
            });
        } catch (e) {
            console.error('Error fetching mouvements:', e);
        } finally {
            setMvtLoading(false);
        }
    };

    if (loading) {
        return (
            <View style={styles.centerLoader}>
                <ActivityIndicator size="large" color={Colors.primary} />
            </View>
        );
    }

    const produit = produitData?.produit || produitData;
    const stockParMagasin = produitData?.stockParMagasin || {};
    const stockCartouchesParMagasin = produitData?.stockCartouchesParMagasin || {};
    const magasins = produitData?.magasins || [];

    const totalCartons = Object.keys(stockParMagasin).length
        ? Object.values(stockParMagasin).reduce((s, v) => s + (Number(v) || 0), 0)
        : (Number(produit?.stock) || 0);
    const totalCartouches = Object.keys(stockCartouchesParMagasin).length
        ? Object.values(stockCartouchesParMagasin).reduce((s, v) => s + (Number(v) || 0), 0)
        : (Number(produit?.stock_cartouches) || 0);

    if (!produit) {
        return (
            <View style={styles.centerLoader}>
                <Text style={styles.errorText}>Produit introuvable</Text>
            </View>
        );
    }

    const imgSrc = produit.image
        ? (produit.image.startsWith('http') ? produit.image : `${client.defaults.baseURL.replace('/api', '')}/storage/${produit.image}`)
        : null;

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle}>{produit.nom}</Text>
                <TouchableOpacity onPress={() => navigation.navigate('ProduitEdit', { id: produit.id })} style={styles.topActionBtn}>
                    <Ionicons name="pencil-outline" size={18} color={Colors.primary} />
                </TouchableOpacity>
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>

                {/* Main Product Card */}
                <View style={styles.mainCard}>
                    <View style={styles.imageWrap}>
                        {imgSrc ? (
                            <Image source={{ uri: imgSrc }} style={styles.productImg} resizeMode="contain" />
                        ) : (
                            <View style={styles.placeholderImg}>
                                <Ionicons name="image-outline" size={48} color={Colors.border} />
                            </View>
                        )}
                    </View>

                    <View style={styles.headerInfo}>
                        <Text style={styles.productTitle}>{produit.nom}</Text>
                        <Text style={styles.productRef}>Réf: PRD-{produit.id}</Text>
                        {produit.description ? (
                            <Text style={styles.productDesc}>{produit.description}</Text>
                        ) : null}
                    </View>
                </View>

                {/* Section Prix & Seuil */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Prix & Caractéristiques</Text>

                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Prix de vente :</Text>
                        <Text style={[styles.infoVal, { color: Colors.primary, fontWeight: '800' }]}>
                            {formatMoney(produit.prix_vente_conseille || produit.prix)}
                        </Text>
                    </View>

                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Seuil d'alerte stock :</Text>
                        <Text style={styles.infoVal}>{produit.seuil_alerte ?? 5} carton(s)</Text>
                    </View>

                    {produit.a_cartouche ? (
                        <>
                            <View style={styles.infoRow}>
                                <Text style={styles.infoLabel}>Cartouches par carton :</Text>
                                <Text style={styles.infoVal}>{produit.cartouche_par_carton} cartouches</Text>
                            </View>

                            <View style={styles.infoRow}>
                                <Text style={styles.infoLabel}>Prix cartouche :</Text>
                                <Text style={[styles.infoVal, { color: Colors.primary }]}>
                                    {formatMoney(produit.prix_cartouche_effectif)}
                                </Text>
                            </View>
                        </>
                    ) : null}
                </View>

                {/* Section Stock par Magasin */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Stock par Magasin</Text>

                    {magasins.length > 0 ? (
                        magasins.map((m, idx) => {
                            const st = stockParMagasin[m.id] ?? 0;
                            const ctr = stockCartouchesParMagasin[m.id] ?? 0;
                            return (
                                <View key={idx} style={styles.magasinRow}>
                                    <Text style={styles.magasinName}>{m.nom}</Text>
                                    <View style={[styles.stockTag, { backgroundColor: st > (produit.seuil_alerte || 0) ? '#dcfce7' : '#fee2e2' }]}>
                                        <Text style={[styles.stockTagText, { color: st > (produit.seuil_alerte || 0) ? Colors.success : Colors.error }]}>
                                            {st} Carton{(st > 1) ? 's' : ''}{ctr > 0 ? ` + ${ctr} Ctr` : ''}
                                        </Text>
                                    </View>
                                </View>
                            );
                        })
                    ) : (
                        <Text style={styles.emptyText}>Aucun magasin associé</Text>
                    )}

                    <View style={styles.totalStockBox}>
                        <Text style={styles.totalStockLabel}>Stock total (tous magasins)</Text>
                        <Text style={styles.totalStockVal}>{totalCartons} Carton{(totalCartons > 1) ? 's' : ''}{totalCartouches > 0 ? ` + ${totalCartouches} Cartouche${(totalCartouches > 1) ? 's' : ''}` : ''}</Text>
                    </View>
                </View>

                {/* Section Mouvements Récents */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Mouvements Récents</Text>

                    {mouvements.length === 0 && !mvtLoading ? (
                        <Text style={styles.emptyText}>Aucun mouvement enregistré</Text>
                    ) : (
                        mouvements.map((mvt) => {
                            const cfg = MVT_CONFIG[mvt.type] || { label: mvt.type, sign: 1 };
                            const isEntree = cfg.sign > 0;
                            const qtyStr = (isEntree ? '+' : '-') + Math.abs(Number(mvt.quantite) || 0);
                            let refText = '';
                            if (mvt.reference?.reference) {
                                refText = `${cfg.label} ${mvt.reference.reference}`;
                            } else if (mvt.note) {
                                refText = mvt.note;
                            } else {
                                refText = cfg.label;
                            }
                            const desc = `${mvt.magasin?.nom || ''}${refText ? ' · ' + refText : ''} (${mvt.quantite} Carton${Number(mvt.quantite) > 1 ? 's' : ''})`;
                            const dateStr = mvt.date_mouvement
                                ? formatDateFr(mvt.date_mouvement)
                                : (mvt.created_at ? formatDateFr(mvt.created_at) : '');
                            return (
                                <View key={mvt.id} style={styles.mvtRow}>
                                    <View style={{ flex: 1, marginRight: 8 }}>
                                        <Text style={styles.mvtType}>{cfg.label}</Text>
                                        <Text style={[styles.mvtDate, { marginTop: 2 }]} numberOfLines={2}>{desc}</Text>
                                        <Text style={[styles.mvtDate, { marginTop: 2 }]}>{dateStr}</Text>
                                    </View>
                                    <Text style={[styles.mvtQty, { color: isEntree ? Colors.success : Colors.error }]}>
                                        {qtyStr}
                                    </Text>
                                </View>
                            );
                        })
                    )}

                    {mvtLoading ? (
                        <View style={styles.mvtLoader}>
                            <ActivityIndicator size="small" color={Colors.primary} />
                        </View>
                    ) : null}

                    {mvtMeta.current_page < mvtMeta.last_page ? (
                        <TouchableOpacity
                            style={styles.loadMoreBtn}
                            onPress={() => fetchMouvements(mvtMeta.current_page + 1, false)}
                        >
                            <Text style={styles.loadMoreText}>Voir plus de mouvements</Text>
                        </TouchableOpacity>
                    ) : null}
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
    mainCard: { backgroundColor: '#FFF', borderRadius: 16, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16, alignItems: 'center' },
    imageWrap: { width: '100%', height: 180, justifyContent: 'center', alignItems: 'center', marginBottom: 14 },
    productImg: { width: '100%', height: '100%' },
    placeholderImg: { width: 120, height: 120, borderRadius: 12, backgroundColor: '#f1f5f9', justifyContent: 'center', alignItems: 'center' },
    headerInfo: { width: '100%', alignItems: 'center' },
    badgeStatus: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12, marginBottom: 8 },
    badgeStatusText: { fontSize: 11, fontWeight: '700' },
    productTitle: { fontSize: 20, fontFamily: 'Poppins_700Bold', color: Colors.text, textAlign: 'center' },
    productRef: { fontSize: 12, color: Colors.textLight, marginTop: 2 },
    productDesc: { fontSize: 13, color: Colors.textLight, marginTop: 8, textAlign: 'center' },
    cardSection: { backgroundColor: '#FFF', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    cardTitle: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 12 },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    infoLabel: { fontSize: 13, color: Colors.textLight, fontFamily: 'Poppins_500Medium' },
    infoVal: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    magasinRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    magasinName: { fontSize: 13, fontWeight: '600', color: Colors.text, flexShrink: 1, flexWrap: 'wrap' },
    stockTag: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6, flexShrink: 0 },
    stockTagText: { fontSize: 11, fontWeight: '700' },
    mvtRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    mvtType: { fontSize: 13, fontWeight: '600', color: Colors.text },
    mvtDate: { fontSize: 11, color: Colors.textLight },
    mvtQty: { fontSize: 14, fontWeight: '800' },
    mvtLoader: { paddingVertical: 12, alignItems: 'center' },
    loadMoreBtn: { marginTop: 10, paddingVertical: 10, borderRadius: 10, backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border, alignItems: 'center' },
    loadMoreText: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.primary },
    emptyText: { textAlign: 'center', color: Colors.textLight, fontSize: 12, paddingVertical: 12 },
    totalStockBox: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', borderTopWidth: 1, borderTopColor: Colors.border, paddingTop: 12, marginTop: 4 },
    totalStockLabel: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text, flexShrink: 0 },
    totalStockVal: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.text, flexShrink: 1, flexWrap: 'wrap', textAlign: 'right' },
});

export default ShowProduitScreen;
