import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity, Image,
    TextInput, ActivityIndicator, Alert, Switch, StatusBar
} from 'react-native';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import * as ImagePicker from 'expo-image-picker';
import { getImageUrl } from '../../utils/image';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useRoute } from '@react-navigation/core';
import { Header } from '../../components/ui';

const ProduitEditScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const route = useRoute();
    const { item, id } = route.params || {};

    const [magasins, setMagasins] = useState([]);
    const [loadingMagasins, setLoadingMagasins] = useState(true);
    const [stocks, setStocks] = useState({});

    const formatIntPrice = (val) => {
        if (val === null || val === undefined || val === '') return '';
        const n = Math.round(Number(val));
        return isNaN(n) ? '' : String(n);
    };

    const [nom, setNom] = useState(item?.nom || '');
    const [prixVenteConseille, setPrixVenteConseille] = useState(formatIntPrice(item?.prix_vente_conseille));
    const [seuilAlerte, setSeuilAlerte] = useState(item?.seuil_alerte ? String(item.seuil_alerte) : '5');

    const [hasCartouche, setHasCartouche] = useState(!!item?.a_cartouche);
    const [cartoucheParCarton, setCartoucheParCarton] = useState(item?.cartouche_par_carton ? String(item.cartouche_par_carton) : '');
    const [prixCartouche, setPrixCartouche] = useState(formatIntPrice(item?.prix_cartouche));

    const [description, setDescription] = useState(item?.description || '');
    const [pickedImage, setPickedImage] = useState(null);
    const [imageUrlInput, setImageUrlInput] = useState('');
    const existingImage = item?.image;
    const previewUri = pickedImage ? pickedImage.uri : (imageUrlInput.trim() || (existingImage ? getImageUrl(existingImage) : null));

    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        fetchMagasins();
    }, []);

    const fetchMagasins = async () => {
        try {
            const resp = await client.get('/magasins');
            const list = resp.data?.data || (Array.isArray(resp.data) ? resp.data : []);
            setMagasins(list);
            const pid = id || item?.id;
            if (pid) {
                const det = await client.get(`/produits/${pid}`);
                const pData = det.data?.data || det.data;
                const pObj = pData?.produit || pData;
                const spm = det.data?.stockParMagasin || pData?.stockParMagasin || {};
                setStocks(Object.fromEntries(list.map(m => [m.id, String(spm[m.id] ?? 0)])));

                if (pObj && pObj.nom) {
                    setNom(pObj.nom);
                    if (pObj.prix_vente_conseille !== undefined) setPrixVenteConseille(formatIntPrice(pObj.prix_vente_conseille));
                    if (pObj.seuil_alerte !== undefined) setSeuilAlerte(pObj.seuil_alerte ? String(pObj.seuil_alerte) : '5');
                    if (pObj.a_cartouche !== undefined) setHasCartouche(!!pObj.a_cartouche);
                    if (pObj.cartouche_par_carton !== undefined) setCartoucheParCarton(pObj.cartouche_par_carton ? String(pObj.cartouche_par_carton) : '');
                    if (pObj.prix_cartouche !== undefined) setPrixCartouche(formatIntPrice(pObj.prix_cartouche));
                    if (pObj.description !== undefined) setDescription(pObj.description || '');
                }
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
            const res = await manipulateAsync(
                uri,
                resize,
                { compress: 0.7, format: SaveFormat.JPEG }
            );
            return res;
        } catch (e) {
            console.warn('compressImage error:', e);
            return null;
        }
    };

    const setPickedFromAsset = async (a) => {
        const mime = a.mimeType || a.type || '';
        if (mime && !mime.startsWith('image/')) {
            Alert.alert('Erreur', 'Format non supporté (JPEG, PNG, GIF, WebP).');
            return;
        }

        const compressed = await compressImage(a.uri);
        const finalUri = compressed ? compressed.uri : a.uri;

        setPickedImage({ uri: finalUri, name: a.fileName || 'produit.jpg', type: mime || 'image/jpeg' });
        setImageUrlInput('');
    };

    const pickImage = async () => {
        try {
            const perm = await ImagePicker.requestMediaLibraryPermissionsAsync();
            if (perm.status !== 'granted') {
                Alert.alert('Permission requise', 'Veuillez autoriser l\'accès à la galerie photos.');
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
            Alert.alert('Erreur', 'Impossible de charger l\'image.');
        }
    };

    const takePhoto = async () => {
        try {
            const perm = await ImagePicker.requestCameraPermissionsAsync();
            if (perm.status !== 'granted') {
                Alert.alert('Permission requise', 'Veuillez autoriser l\'accès à l\'appareil photo.');
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
            Alert.alert('Erreur', 'Impossible de prendre la photo.');
        }
    };

    const handleSubmit = async () => {
        if (!nom) {
            Alert.alert('Erreur', 'Le nom du produit est obligatoire.');
            return;
        }

        setSubmitting(true);
        try {
            const stocksToSend = {};
            magasins.forEach(m => { stocksToSend[m.id] = parseInt(stocks[m.id] || '0', 10) || 0; });

            const payload = {
                nom,
                stocks: stocksToSend,
                prix_vente_conseille: prixVenteConseille ? Number(prixVenteConseille) : null,
                seuil_alerte: Number(seuilAlerte) || 5,
                a_cartouche: hasCartouche ? 1 : 0,
                cartouche_par_carton: hasCartouche && cartoucheParCarton ? Number(cartoucheParCarton) : null,
                prix_cartouche: hasCartouche && prixCartouche ? Number(prixCartouche) : null,
                description
            };

            if (pickedImage) {
                const fd = new FormData();
                Object.keys(payload).forEach((k) => {
                    if (k === 'stocks') {
                        Object.keys(payload.stocks).forEach((mid) => fd.append(`stocks[${mid}]`, payload.stocks[mid]));
                    } else {
                        fd.append(k, payload[k] === null ? '' : payload[k]);
                    }
                });
                fd.append('image', { uri: pickedImage.uri, name: pickedImage.name, type: pickedImage.type });
                fd.append('_method', 'PUT');
                await client.post(`/produits/${id || item?.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
            } else if (imageUrlInput.trim()) {
                payload.image_url = imageUrlInput.trim();
                await client.put(`/produits/${id || item?.id}`, payload);
            } else {
                await client.put(`/produits/${id || item?.id}`, payload);
            }
            Alert.alert('Succès', 'Produit modifié avec succès.');
            navigation.goBack();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de l\'enregistrement';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle}>Modifier Produit</Text>
                <View style={{ width: 36 }} />
            </View>

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>


                <View style={styles.cardSection}>
                    <Text style={styles.fieldLabel}>Nom du produit *</Text>
                    <TextInput
                        style={styles.input}
                        value={nom}
                        onChangeText={setNom}
                        placeholder="Ex: Ricci Premium"
                    />

                    {magasins.length > 0 && (
                        <View style={{ marginTop: 4 }}>
                            <Text style={styles.fieldLabel}>Stock par magasin</Text>
                            <Text style={styles.helper}>Quantité dans chaque magasin. Le stock total est centralisé.</Text>
                            <View style={{ marginTop: 6 }}>
                                {magasins.map(m => (
                                    <View key={m.id} style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 8, gap: 10 }}>
                                        <Text style={[styles.helper, { flex: 1 }]}>{m.nom}</Text>
                                        <TextInput
                                            style={[styles.input, { width: 110 }]}
                                            keyboardType="numeric"
                                            value={stocks[m.id] || '0'}
                                            onChangeText={(v) => setStocks(s => ({ ...s, [m.id]: v }))}
                                            placeholder="0"
                                        />
                                    </View>
                                ))}
                            </View>
                        </View>
                    )}

                    <View style={{ flexDirection: 'row', gap: 10 }}>
                        <View style={{ flex: 1 }}>
                            <Text style={styles.fieldLabel}>Seuil d'alerte</Text>
                            <TextInput
                                style={styles.input}
                                keyboardType="numeric"
                                value={seuilAlerte}
                                onChangeText={setSeuilAlerte}
                                placeholder="5"
                            />
                        </View>
                    </View>

                    <View style={{ marginTop: 4 }}>
                        <Text style={styles.fieldLabel}>Prix de Vente (FCFA)</Text>
                        <TextInput
                            style={styles.input}
                            keyboardType="numeric"
                            value={prixVenteConseille}
                            onChangeText={setPrixVenteConseille}
                            placeholder="0"
                        />
                    </View>

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

                    <Text style={styles.fieldLabel}>Description / Notes</Text>
                    <TextInput
                        style={[styles.input, { height: 70 }]}
                        multiline
                        value={description}
                        onChangeText={setDescription}
                        placeholder="Optionnel..."
                    />
                    <Text style={styles.fieldLabel}>Image du produit</Text>
                    <View style={styles.imagePickerRow}>
                        {previewUri ? (
                            <Image source={{ uri: previewUri }} style={styles.previewImg} />
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

                <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                    {submitting ? (
                        <ActivityIndicator color="#FFF" />
                    ) : (
                        <Text style={styles.submitBtnText}>Enregistrer les modifications</Text>
                    )}
                </TouchableOpacity>

            </KeyboardAwareScrollView>

        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#FFFFFF', paddingHorizontal: 16, paddingBottom: 12, borderBottomWidth: 1, borderBottomColor: '#E2E8F0' },
    backBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    topTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    scrollContent: { padding: 16, paddingBottom: 40 },
    cardSection: { backgroundColor: '#FFF', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    fieldLabel: { fontSize: 12, fontWeight: '600', color: Colors.text, marginTop: 10, marginBottom: 4 },
    helper: { fontSize: 11, color: Colors.textLight, marginTop: 2, marginBottom: 2 },
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, backgroundColor: '#f8fafc' },
    inputDisabled: { backgroundColor: '#f1f5f9', color: Colors.textLight },
    chipRow: { gap: 8, marginVertical: 6 },
    chip: { paddingHorizontal: 14, paddingVertical: 8, borderRadius: 8, backgroundColor: '#f1f5f9' },
    chipActive: { backgroundColor: Colors.primary },
    chipText: { fontSize: 12, fontWeight: '600', color: Colors.text },
    chipTextActive: { color: '#FFF' },
    switchRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginVertical: 14, paddingTop: 10, borderTopWidth: 1, borderTopColor: '#f1f5f9' },
    switchLabel: { fontSize: 13, fontWeight: '700', color: Colors.text },
    submitBtn: { backgroundColor: Colors.primary, paddingVertical: 16, borderRadius: 12, alignItems: 'center', marginTop: 10 },
    submitBtnText: { color: '#FFF', fontWeight: '800', fontSize: 15 },
    imagePickerRow: { flexDirection: 'row', alignItems: 'center', gap: 12, marginTop: 10 },
    previewImg: { width: 72, height: 72, borderRadius: 10, backgroundColor: Colors.border },
    previewPlaceholder: { width: 72, height: 72, borderRadius: 10, backgroundColor: '#f1f5f9', justifyContent: 'center', alignItems: 'center', borderWidth: 1, borderColor: Colors.border },
    imageBtn: { flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: Colors.primary, paddingVertical: 10, paddingHorizontal: 12, borderRadius: 8, marginBottom: 8 },
    imageBtnText: { color: '#FFF', fontWeight: '700', fontSize: 13 },
});

export default ProduitEditScreen;
