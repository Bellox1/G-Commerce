import React, { useState, useEffect, useRef } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity, Image,
    TextInput, ActivityIndicator, Alert, StatusBar, Switch
} from 'react-native';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import * as ImagePicker from 'expo-image-picker';
import * as FileSystem from 'expo-file-system';
import { getImageUrl } from '../../utils/image';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import Toast, { useToast } from '../../components/Toast';


import { getCache } from '../../utils/offlineSync';

const MAX_IMAGE_BYTES = 8 * 1024 * 1024; // 8 Mo (serveur accepte 10 Mo)

const CreateProduitScreen = ({ navigation, route }) => {
    const insets = useSafeAreaInsets();
    const editing = route?.params?.item;
    const { toastRef, showToast } = useToast();

    const [magasins, setMagasins] = useState([]);
    const [loadingMagasins, setLoadingMagasins] = useState(true);
    const [stocks, setStocks] = useState({});
    const [stocksCartouches, setStocksCartouches] = useState({});
    const seuilAutoCalcDone = useRef(false);

    const [nom, setNom] = useState(editing?.nom || '');
    const [prixVenteConseille, setPrixVenteConseille] = useState(editing?.prix_vente_conseille ? String(editing.prix_vente_conseille) : '');
    const [seuilAlerte, setSeuilAlerte] = useState(editing?.seuil_alerte ? String(editing.seuil_alerte) : '5');
    const [seuilAuto, setSeuilAuto] = useState(!editing?.seuil_alerte);

    // Cartouches
    const [hasCartouche, setHasCartouche] = useState(!!editing?.a_cartouche);
    const [cartoucheParCarton, setCartoucheParCarton] = useState(editing?.cartouche_par_carton ? String(editing.cartouche_par_carton) : '');
    const [prixCartouche, setPrixCartouche] = useState(editing?.prix_cartouche ? String(editing.prix_cartouche) : '');

    const [description, setDescription] = useState(editing?.description || '');
    const [imageMode, setImageMode] = useState('file'); // 'file' | 'url'
    const [pickedImage, setPickedImage] = useState(null);
    const [imageUrlInput, setImageUrlInput] = useState('');
    const existingImage = editing?.image;
    const previewUri = pickedImage ? pickedImage.uri : (imageMode === 'url' && imageUrlInput.trim() ? imageUrlInput.trim() : (existingImage ? getImageUrl(existingImage) : null));

    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        fetchMagasins();
    }, []);

    // Auto : seuil d'alerte = ¼ du stock total (recalcul dynamique à chaque modification du stock)
    useEffect(() => {
        if (seuilAuto) {
            const totalStock = magasins.reduce((acc, m) => acc + (parseInt(stocks[m.id] || '0', 10) || 0), 0);
            if (totalStock > 0) {
                setSeuilAlerte(String(Math.ceil(totalStock / 4)));
            } else {
                setSeuilAlerte('5');
            }
        }
    }, [stocks, seuilAuto, magasins]);

    const fetchMagasins = async () => {
        try {
            let list = [];
            try {
                const resp = await client.get('/magasins');
                const raw = resp.data;
                if (Array.isArray(raw)) list = raw;
                else if (Array.isArray(raw?.data)) list = raw.data;
                else if (raw?.data && Array.isArray(raw.data.data)) list = raw.data.data;
            } catch (e) {}

            if (!list || list.length === 0) {
                const cached = await getCache('/magasins');
                if (Array.isArray(cached)) list = cached;
                else if (Array.isArray(cached?.data)) list = cached.data;
                else if (cached?.data && Array.isArray(cached.data.data)) list = cached.data.data;
            }

            setMagasins(list || []);

            if (editing && list && list.length > 0) {
                try {
                    const det = await client.get(`/produits/${editing.id}`);
                    const pData = det.data?.data || det.data;
                    const spm = det.data?.stockParMagasin || pData?.stockParMagasin || {};
                    const spmc = det.data?.stockCartouchesParMagasin || pData?.stockCartouchesParMagasin || {};
                    setStocks(Object.fromEntries(list.map(m => [m.id, String(spm[m.id] ?? 0)])));
                    setStocksCartouches(Object.fromEntries(list.map(m => [m.id, String(spmc[m.id] ?? 0)])));
                } catch (errDet) {}
            }
        } catch (e) {
            console.error('Error fetching magasins:', e);
        } finally {
            setLoadingMagasins(false);
        }
    };

    const chooseImageSource = () => {
        Alert.alert(
            'Importer une image',
            'Choisir la source de l\'image',
            [
                { text: 'Galerie', onPress: pickImage },
                { text: 'Appareil photo', onPress: takePhoto },
                { text: 'Annuler', style: 'cancel' },
            ]
        );
    };

    const setPickedFromAsset = async (a) => {
        const mime = a.mimeType || a.type || '';
        if (mime && !mime.startsWith('image/')) {
            showToast('Format non supporté (JPEG, PNG, GIF, WebP).', 'error');
            return;
        }

        // Compression / redimensionnement si le module natif est disponible
        const compressed = await compressImage(a.uri);
        const finalUri = compressed ? compressed.uri : a.uri;
        const finalSize = compressed ? compressed.size : (a.fileSize || 0);

        if (finalSize && finalSize > MAX_IMAGE_BYTES) {
            showToast('Image trop lourde (max 8 Mo). Veuillez réduire la résolution.', 'error');
            return;
        }

        setPickedImage({ uri: finalUri, name: a.fileName || 'produit.jpg', type: mime || 'image/jpeg' });
        setImageUrlInput('');
    };

    const pickImage = async () => {
        try {
            const perm = await ImagePicker.requestMediaLibraryPermissionsAsync();
            if (perm.status !== 'granted') {
                Alert.alert('Permission requise', 'Veuillez autoriser l\'accès à la galerie photos dans les paramètres.');
                return;
            }
            const result = await ImagePicker.launchImageLibraryAsync({
                mediaTypes: ImagePicker.MediaTypeOptions.Images,
                allowsEditing: false,
                quality: 0.7,
            });
            if (!result.canceled && result.assets && result.assets.length > 0) {
                await setPickedFromAsset(result.assets[0]);
            }
        } catch (e) {
            console.error('pickImage error:', e);
            showToast('Impossible de charger l\'image : ' + (e?.message || ''), 'error');
        }
    };

    const takePhoto = async () => {
        try {
            const perm = await ImagePicker.requestCameraPermissionsAsync();
            if (perm.status !== 'granted') {
                Alert.alert('Permission requise', 'Veuillez autoriser l\'accès à la caméra dans les paramètres de l\'application.');
                return;
            }
            const result = await ImagePicker.launchCameraAsync({
                mediaTypes: ImagePicker.MediaTypeOptions.Images,
                allowsEditing: false,
                quality: 0.7,
                exif: false,
            });
            if (!result.canceled && result.assets && result.assets.length > 0) {
                await setPickedFromAsset(result.assets[0]);
            }
        } catch (e) {
            console.error('takePhoto error:', e);
            showToast('Impossible de prendre la photo : ' + (e?.message || ''), 'error');
        }
    };

    // Compression lazy : n'échoue jamais l'app au démarrage si le module natif est absent
    const compressImage = async (uri) => {
        try {
            const manip = require('expo-image-manipulator');
            const { manipulateAsync, SaveFormat } = manip;
            const dims = await new Promise((res) =>
                Image.getSize(uri, (w, h) => res({ w, h }), () => res({ w: 0, h: 0 }))
            );
            let resize = [];
            if (dims.w && dims.h) {
                const scale = Math.min(1, 1280 / Math.max(dims.w, dims.h));
                if (scale < 1) {
                    resize = [{ resize: { width: Math.round(dims.w * scale), height: Math.round(dims.h * scale) } }];
                }
            }
            let compress = 0.7;
            let result = await manipulateAsync(uri, resize, { compress, format: SaveFormat.JPEG });
            let info = await FileSystem.getInfoAsync(result.uri);
            let attempts = 0;
            while (info.size > MAX_IMAGE_BYTES && compress > 0.1 && attempts < 8) {
                compress = Math.max(0.1, compress - 0.15);
                result = await manipulateAsync(uri, resize, { compress, format: SaveFormat.JPEG });
                info = await FileSystem.getInfoAsync(result.uri);
                attempts += 1;
            }
            return { uri: result.uri, size: info.size };
        } catch (e) {
            console.warn('compressImage indisponible (module natif absent) :', e.message);
            return null; // repli : on garde l'original
        }
    };

    const handleSubmit = async () => {
        if (!nom) {
            showToast('Le nom du produit est obligatoire.', 'error');
            return;
        }

        setSubmitting(true);
        try {
            const stocksToSend = {};
            const stocksCartouchesToSend = {};
            const cpc = hasCartouche && cartoucheParCarton ? Number(cartoucheParCarton) : 1;
            magasins.forEach(m => {
                stocksToSend[m.id] = parseInt(stocks[m.id] || '0', 10) || 0;
                let rawC = parseInt(stocksCartouches[m.id] || '0', 10) || 0;
                // Pas de cartouches si le produit n'est pas en cartouches,
                // et jamais assez pour former un carton complet.
                if (!hasCartouche) rawC = 0;
                else rawC = Math.max(0, Math.min(rawC, cpc - 1));
                stocksCartouchesToSend[m.id] = rawC;
            });

            const payload = {
                nom,
                stocks: stocksToSend,
                stocks_cartouches: stocksCartouchesToSend,
                prix_vente_conseille: prixVenteConseille ? Number(prixVenteConseille) : null,
                seuil_alerte: Number(seuilAlerte) || 5,
                a_cartouche: hasCartouche ? 1 : 0,
                cartouche_par_carton: hasCartouche && cartoucheParCarton ? Number(cartoucheParCarton) : null,
                prix_cartouche: hasCartouche && prixCartouche ? Number(prixCartouche) : null,
                description
            };

            const useUrl = imageMode === 'url' && imageUrlInput.trim();

            if (pickedImage) {
                const fd = new FormData();
                Object.keys(payload).forEach((k) => {
                    if (k === 'stocks') {
                        Object.keys(payload.stocks).forEach((mid) => fd.append(`stocks[${mid}]`, payload.stocks[mid]));
                    } else if (k === 'stocks_cartouches') {
                        Object.keys(payload.stocks_cartouches).forEach((mid) => fd.append(`stocks_cartouches[${mid}]`, payload.stocks_cartouches[mid]));
                    } else {
                        fd.append(k, payload[k] === null ? '' : payload[k]);
                    }
                });
                fd.append('image', { uri: pickedImage.uri, name: pickedImage.name, type: pickedImage.type });
                if (editing) {
                    fd.append('_method', 'PUT');
                    await client.post(`/produits/${editing.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
                } else {
                    await client.post('/produits', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
                }
                showToast(editing ? 'Produit modifié avec succès.' : 'Nouveau produit créé avec succès.', 'success');
            } else if (useUrl) {
                payload.image_url = imageUrlInput.trim();
                if (editing) {
                    await client.put(`/produits/${editing.id}`, payload);
                } else {
                    await client.post('/produits', payload);
                }
                showToast(editing ? 'Produit modifié avec succès.' : 'Nouveau produit créé avec succès.', 'success');
            } else {
                if (editing) {
                    await client.put(`/produits/${editing.id}`, payload);
                } else {
                    await client.post('/produits', payload);
                }
                showToast(editing ? 'Produit modifié avec succès.' : 'Nouveau produit créé avec succès.', 'success');
            }

            setTimeout(() => navigation.goBack(), 800);
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de l\'enregistrement';
            showToast(msg, 'error');
        } finally {
            setSubmitting(false);
        }
    };

    const submitLabel = editing ? 'Enregistrer les modifications' : 'Enregistrer le Produit';
    const title = editing ? 'Modifier l\'article' : 'Ajouter un nouvel article';

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle} numberOfLines={1}>{title}</Text>
                <View style={{ width: 36 }} />
            </View>

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>


                <View style={styles.cardSection}>
                    {/* Nom + Prix de Vente */}
                    <View style={{ flexDirection: 'row', gap: 10 }}>
                        <View style={{ flex: 1 }}>
                            <Text style={styles.fieldLabel}>Nom du produit *</Text>
                            <TextInput
                                style={styles.input}
                                value={nom}
                                onChangeText={setNom}
                                placeholder="Ex: Ricci Premium"
                            />
                        </View>

                        <View style={{ flex: 1 }}>
                            <Text style={styles.fieldLabel}>Prix de Vente (FCFA)</Text>
                            <TextInput
                                style={styles.input}
                                keyboardType="numeric"
                                value={prixVenteConseille}
                                onChangeText={setPrixVenteConseille}
                                placeholder="0"
                            />
                        </View>
                    </View>

                    {/* Stock par magasin (pleine largeur) */}
                    {magasins.length > 0 && (
                        <View style={{ marginTop: 4 }}>
                            <Text style={styles.fieldLabel}>Stock par magasin</Text>
                            <Text style={styles.helper}>Quantité dans chaque magasin. Le stock total est centralisé.</Text>
                            <View style={{ marginTop: 6 }}>
                                {magasins.map(m => (
                                    <View key={m.id} style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 8, gap: 10 }}>
                                        <Text style={[styles.helper, { flex: 1 }]}>{m.nom}</Text>
                                        <TextInput
                                            style={[styles.input, { width: 90 }]}
                                            keyboardType="numeric"
                                            value={stocks[m.id] || '0'}
                                            onChangeText={(v) => setStocks(s => ({ ...s, [m.id]: v }))}
                                            placeholder="0"
                                        />
                                        {hasCartouche ? (
                                            <>
                                                <Text style={styles.helper}>ctn</Text>
                                                <TextInput
                                                    style={[styles.input, { width: 70 }]}
                                                    keyboardType="numeric"
                                                    value={stocksCartouches[m.id] || '0'}
                                                    onChangeText={(v) => setStocksCartouches(s => ({ ...s, [m.id]: v }))}
                                                    placeholder="0"
                                                />
                                                <Text style={styles.helper}>ctr</Text>
                                            </>
                                        ) : null}
                                    </View>
                                ))}
                            </View>
                        </View>
                    )}

                    {/* Seuil d'alerte */}
                    <View style={{ marginTop: 4 }}>
                        <View style={{ flex: 1 }}>
                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 }}>
                                <Text style={styles.fieldLabel}>Seuil d'alerte stock</Text>
                                {seuilAuto ? (
                                    <View style={{ backgroundColor: '#e0f2fe', paddingHorizontal: 8, paddingVertical: 2, borderRadius: 10 }}>
                                        <Text style={{ fontSize: 10, color: '#0369a1', fontWeight: '700' }}>Mode Auto (¼)</Text>
                                    </View>
                                ) : (
                                    <TouchableOpacity onPress={() => setSeuilAuto(true)}>
                                        <Text style={{ fontSize: 11, color: Colors.primary, fontWeight: '700' }}>↻ Recalculer Auto (¼)</Text>
                                    </TouchableOpacity>
                                )}
                            </View>
                            <TextInput
                                style={styles.input}
                                keyboardType="numeric"
                                value={seuilAlerte}
                                onFocus={() => setSeuilAuto(false)}
                                onChangeText={(v) => {
                                    setSeuilAuto(false);
                                    setSeuilAlerte(v);
                                }}
                                placeholder="5"
                            />
                            <Text style={styles.helper}>
                                {seuilAuto ? 'Calculé automatiquement (¼ du stock total). Tapez une valeur pour personnaliser.' : 'Valeur personnalisée. Cliquez sur Recalculer Auto pour rétablir ¼ du stock.'}
                            </Text>
                        </View>
                    </View>

                    {/* Section Switch Cartouches */}
                    <View style={styles.switchRow}>
                        <Text style={styles.switchLabel}>Ce produit a des cartouches</Text>
                        <Switch
                            value={hasCartouche}
                            onValueChange={setHasCartouche}
                            trackColor={{ false: Colors.border, true: Colors.primary }}
                        />
                    </View>

                    {hasCartouche && (
                        <View style={{ flexDirection: 'row', gap: 10 }}>
                            <View style={{ flex: 1 }}>
                                <Text style={styles.fieldLabel}>Cartouches / carton *</Text>
                                <TextInput
                                    style={styles.input}
                                    keyboardType="numeric"
                                    value={cartoucheParCarton}
                                    onChangeText={setCartoucheParCarton}
                                    placeholder="Ex: 9"
                                />
                            </View>

                            <View style={{ flex: 1 }}>
                                <Text style={styles.fieldLabel}>Prix Cartouche (FCFA)</Text>
                                <TextInput
                                    style={styles.input}
                                    keyboardType="numeric"
                                    value={prixCartouche}
                                    onChangeText={setPrixCartouche}
                                    placeholder="Auto"
                                />
                            </View>
                        </View>
                    )}

                    {/* Image du produit */}
                    <View style={{ marginTop: 4 }}>
                        <Text style={styles.fieldLabel}>Image du produit <Text style={styles.opt}>— optionnel</Text></Text>
                        <View style={styles.imagePickerRow}>
                            {pickedImage ? (
                                <Image source={{ uri: pickedImage.uri }} style={styles.previewImg} />
                            ) : existingImage ? (
                                <Image source={{ uri: getImageUrl(existingImage) }} style={styles.previewImg} />
                            ) : (
                                <View style={styles.previewPlaceholder}>
                                    <Ionicons name="image-outline" size={40} color={Colors.textLight} />
                                </View>
                            )}
                            <View style={{ flex: 1 }}>
                                <TouchableOpacity style={styles.imageBtn} onPress={chooseImageSource}>
                                    <Ionicons name="camera-outline" size={18} color="#FFF" />
                                    <Text style={styles.imageBtnText}>Choisir une photo</Text>
                                </TouchableOpacity>
                                <Text style={styles.helper}>Formats : JPEG, PNG, GIF, WebP — max 10 Mo</Text>
                            </View>
                        </View>
                        {pickedImage && (
                            <TouchableOpacity onPress={() => setPickedImage(null)} style={{ marginTop: 6 }}>
                                <Text style={{ color: Colors.error, fontSize: 12 }}>Retirer l'image sélectionnée</Text>
                            </TouchableOpacity>
                        )}
                    </View>

                    {/* Description / Remarques */}
                    <View style={{ marginTop: 4 }}>
                        <Text style={styles.fieldLabel}>Description / Remarques</Text>
                        <TextInput
                            style={[styles.input, { height: 80, textAlignVertical: 'top' }]}
                            value={description}
                            onChangeText={setDescription}
                            placeholder="Écrivez vos remarques..."
                            multiline
                        />
                    </View>

                </View>

                <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                    {submitting ? (
                        <ActivityIndicator color="#FFF" />
                    ) : (
                        <Text style={styles.submitBtnText}>{submitLabel}</Text>
                    )}
                </TouchableOpacity>

            </KeyboardAwareScrollView>


            <Toast ref={toastRef} />
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#FFFFFF', paddingHorizontal: 16, paddingBottom: 12, borderBottomWidth: 1, borderBottomColor: '#E2E8F0' },
    backBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    topTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text, flex: 1, textAlign: 'center' },
    scrollContent: { padding: 16, paddingBottom: 40 },
    cardSection: { backgroundColor: '#FFF', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    fieldLabel: { fontSize: 12, fontWeight: '600', color: Colors.text, marginTop: 10, marginBottom: 4 },
    opt: { color: Colors.textLight, fontWeight: '400' },
    helper: { fontSize: 11, color: Colors.textLight, marginTop: 2, marginBottom: 2 },
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, backgroundColor: '#f8fafc' },
    chipWrap: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginVertical: 6 },
    chip: { paddingHorizontal: 16, paddingVertical: 10, borderRadius: 8, backgroundColor: '#f1f5f9', borderWidth: 1, borderColor: Colors.border },
    chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    chipText: { fontSize: 13, fontWeight: '600', color: Colors.text },
    chipTextActive: { color: '#FFF' },
    switchRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginVertical: 14, paddingTop: 10, borderTopWidth: 1, borderTopColor: '#f1f5f9' },
    switchLabel: { fontSize: 13, fontWeight: '700', color: Colors.text },
    submitBtn: { backgroundColor: Colors.primary, paddingVertical: 16, borderRadius: 12, alignItems: 'center', marginTop: 10 },
    submitBtnText: { color: '#FFF', fontWeight: '800', fontSize: 15 },
    imagePickerRow: { flexDirection: 'row', alignItems: 'center', gap: 12, marginTop: 10 },
    previewImg: { width: 72, height: 72, borderRadius: 10, backgroundColor: Colors.border },
    urlPreview: { width: '100%', height: 160, borderRadius: 10, marginTop: 8, backgroundColor: Colors.border },
    previewPlaceholder: { width: 72, height: 72, borderRadius: 10, backgroundColor: '#f1f5f9', justifyContent: 'center', alignItems: 'center', borderWidth: 1, borderColor: Colors.border },
    imageBtn: { flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: Colors.primary, paddingVertical: 10, paddingHorizontal: 12, borderRadius: 8, marginBottom: 8 },
    imageBtnRow: { flexDirection: 'row', gap: 8, marginBottom: 8 },
    imageBtnHalf: { flex: 1, justifyContent: 'center', marginBottom: 0 },
    imageBtnCamera: { backgroundColor: Colors.accent },
    imageBtnText: { color: '#FFF', fontWeight: '700', fontSize: 13 },
    imgTabs: { flexDirection: 'row', gap: 0, marginTop: 8, marginBottom: 8 },
    imgTab: { flexDirection: 'row', alignItems: 'center', gap: 6, paddingVertical: 8, paddingHorizontal: 16, borderWidth: 1, borderColor: Colors.border, backgroundColor: '#f1f5f9' },
    imgTabActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    imgTabText: { fontSize: 13, fontWeight: '600', color: Colors.textLight },
    imgTabTextActive: { color: '#FFF' },
});

export default CreateProduitScreen;
