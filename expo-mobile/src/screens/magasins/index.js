import React, { useState, useCallback } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    TextInput, ActivityIndicator, RefreshControl, Modal, StatusBar, Alert,
    KeyboardAvoidingView, Platform, ScrollView
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import TopHeaderNav from '../../components/TopHeaderNav';

const formatMoney = (val) => {
    if (val === null || val === undefined || val === '') return '—';
    const n = Number(val);
    if (!isFinite(n)) return '—';
    return n.toLocaleString('fr-FR') + ' F';
};

const MagasinsScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [magasins, setMagasins] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    // Modal Création / Modification
    const [showModal, setShowModal] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [nom, setNom] = useState('');
    const [adresse, setAdresse] = useState('');
    const [ville, setVille] = useState('');
    const [loyer, setLoyer] = useState('');
    const [submitting, setSubmitting] = useState(false);

    const fetchMagasins = useCallback(async () => {
        try {
            const resp = await client.get('/magasins');
            const list = Array.isArray(resp.data?.data)
                ? resp.data.data
                : Array.isArray(resp.data)
                    ? resp.data
                    : [];
            setMagasins(list);
        } catch (e) {
            console.error('Error fetching magasins:', e);
            setMagasins([]);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useFocusEffect(
        useCallback(() => {
            fetchMagasins();
        }, [fetchMagasins])
    );

    const openAddModal = () => {
        setEditingId(null);
        setNom('');
        setAdresse('');
        setVille('');
        setLoyer('');
        setShowModal(true);
    };

    const onRefresh = () => {
        setRefreshing(true);
        fetchMagasins();
    };

    const openCreate = () => {
        setEditingId(null);
        setNom('');
        setAdresse('');
        setVille('');
        setLoyer('');
        setShowModal(true);
    };

    const openEdit = (item) => {
        setEditingId(item.id);
        setNom(item.nom || '');
        setAdresse(item.adresse || '');
        setVille(item.ville || '');
        setLoyer(item.loyer != null ? String(item.loyer) : '');
        setShowModal(true);
    };

    const closeModal = () => setShowModal(false);

    const handleSubmit = async () => {
        if (!nom) {
            Alert.alert('Erreur', 'Le nom du dépôt est obligatoire.');
            return;
        }

        setSubmitting(true);
        try {
            const payload = {
                nom,
                adresse,
                ville,
                loyer: loyer ? Number(loyer) : null,
            };

            if (editingId) {
                await client.put(`/magasins/${editingId}`, payload);
                Alert.alert('Succès', 'Dépôt modifié avec succès');
            } else {
                await client.post('/magasins', payload);
                Alert.alert('Succès', 'Nouveau dépôt créé avec succès');
            }
            closeModal();
            fetchMagasins();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de l\'enregistrement du dépôt';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <View style={styles.container}>
            {/* Top Navigation Header replacing Drawer */}
            <TopHeaderNav navigation={navigation} activeCategory="magasins" />

            {/* View Action Header */}
            <View style={styles.viewHeaderRow}>
                <View style={{ flex: 1 }}>
                    <Text style={styles.headerTitle}>Gestion des Dépôts</Text>
                    <Text style={styles.headerSub}>{magasins.length} dépôt(s) enregistré(s)</Text>
                </View>

                {/* Stylish Add (+) Button */}
                <TouchableOpacity
                    style={styles.btnAddPill}
                    onPress={openAddModal}
                    activeOpacity={0.88}
                >
                    <Ionicons name="add-circle" size={18} color="#FFFFFF" />
                    <Text style={styles.btnAddPillText}>Dépôt</Text>
                </TouchableOpacity>
            </View>

            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                    <Text style={styles.loaderText}>Chargement des dépôts...</Text>
                </View>
            ) : (
                <FlatList
                    data={magasins}
                    keyExtractor={(item) => item.id.toString()}
                    contentContainerStyle={styles.listContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                    ListEmptyComponent={
                        <View style={styles.emptyBox}>
                            <Ionicons name="business-outline" size={50} color={Colors.border} />
                            <Text style={styles.emptyTitle}>Aucun dépôt enregistré</Text>
                        </View>
                    }
                    renderItem={({ item }) => (
                        <View style={styles.card}>
                            <View style={styles.cardHeader}>
                                <View style={styles.iconBox}>
                                    <Ionicons name="business" size={24} color={Colors.primary} />
                                </View>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.magName}>{item.nom}</Text>
                                    <Text style={styles.magSub}>{item.adresse || 'Sans adresse'} {item.ville ? `• ${item.ville}` : ''}</Text>
                                </View>
                                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                                    <TouchableOpacity style={styles.editBtn} onPress={() => openEdit(item)}>
                                        <Ionicons name="pencil" size={18} color={Colors.primary} />
                                    </TouchableOpacity>
                                    <TouchableOpacity
                                        style={[styles.editBtn, { backgroundColor: '#FEE2E2' }]}
                                        onPress={() => {
                                            Alert.alert(
                                                'Supprimer le dépôt',
                                                `Voulez-vous vraiment supprimer le dépôt ${item.nom} ?`,
                                                [
                                                    { text: 'Annuler', style: 'cancel' },
                                                    {
                                                        text: 'Supprimer',
                                                        style: 'destructive',
                                                        onPress: async () => {
                                                            try {
                                                                await client.delete(`/magasins/${item.id}`);
                                                                Alert.alert('Succès', 'Dépôt supprimé avec succès.');
                                                                fetchMagasins();
                                                            } catch (e) {
                                                                Alert.alert('Erreur', e.response?.data?.message || 'Impossible de supprimer ce dépôt.');
                                                            }
                                                        },
                                                    },
                                                ]
                                            );
                                        }}
                                    >
                                        <Ionicons name="trash-outline" size={18} color={Colors.error} />
                                    </TouchableOpacity>
                                </View>
                            </View>

                            <View style={styles.loyerBox}>
                                <Text style={styles.loyerLabel}>Loyer Mensuel :</Text>
                                <Text style={styles.loyerVal}>{formatMoney(item.loyer)}</Text>
                            </View>
                        </View>
                    )}
                />
            )}


            {/* Modal Création / Modification */}
            <Modal visible={showModal} transparent animationType="slide" onRequestClose={closeModal}>
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
                    <View style={styles.modalOverlay}>
                        <ScrollView contentContainerStyle={{ flexGrow: 1, justifyContent: 'flex-end' }} keyboardShouldPersistTaps="handled">
                            <View style={styles.modalCard}>
                                <View style={styles.modalHeader}>
                                    <Text style={styles.modalTitle}>{editingId ? 'Modifier le Dépôt' : 'Nouveau Dépôt'}</Text>
                                    <TouchableOpacity onPress={closeModal}>
                                        <Ionicons name="close" size={24} color={Colors.text} />
                                    </TouchableOpacity>
                                </View>

                                <View style={styles.fieldGroup}>
                                    <Text style={styles.label}>Nom du Dépôt *</Text>
                                    <TextInput style={styles.input} placeholder="Ex: Dépôt Saint-Michel" value={nom} onChangeText={setNom} />
                                </View>

                                <View style={styles.fieldGroup}>
                                    <Text style={styles.label}>Adresse</Text>
                                    <TextInput style={styles.input} placeholder="Ex: Avenue Clozel" value={adresse} onChangeText={setAdresse} />
                                </View>

                                <View style={styles.fieldGroup}>
                                    <Text style={styles.label}>Ville</Text>
                                    <TextInput style={styles.input} placeholder="Ex: Cotonou" value={ville} onChangeText={setVille} />
                                </View>

                                <View style={styles.fieldGroup}>
                                    <Text style={styles.label}>Loyer Mensuel (FCFA)</Text>
                                    <TextInput style={styles.input} keyboardType="number-pad" placeholder="Ex: 150000" value={loyer} onChangeText={setLoyer} />
                                </View>

                                <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                                    {submitting ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>{editingId ? 'Enregistrer' : 'Enregistrer le dépôt'}</Text>}
                                </TouchableOpacity>
                            </View>
                        </ScrollView>
                    </View>
                </KeyboardAvoidingView>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    viewHeaderRow: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingHorizontal: 16,
        paddingTop: 14,
        paddingBottom: 10,
        backgroundColor: '#FFFFFF',
    },
    headerTitle: { fontSize: 20, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    headerSub: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    btnAddPill: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        backgroundColor: Colors.primary,
        paddingHorizontal: 14,
        paddingVertical: 8,
        borderRadius: 20,
        elevation: 2,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.2,
        shadowRadius: 4,
    },
    btnAddPillText: {
        color: '#FFFFFF',
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 10 },
    loaderText: { fontSize: 14, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    listContent: { padding: 16, paddingBottom: 80 },
    emptyBox: { alignItems: 'center', paddingVertical: 60, gap: 10 },
    emptyTitle: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.text },
    card: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, elevation: 1 },
    cardHeader: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: 12 },
    iconBox: { width: 44, height: 44, borderRadius: 12, backgroundColor: Colors.primary + '15', justifyContent: 'center', alignItems: 'center' },
    magName: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    magSub: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginTop: 2 },
    editBtn: { padding: 8, marginLeft: 8 },
    loyerBox: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', backgroundColor: Colors.background, borderRadius: 10, padding: 10 },
    loyerLabel: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.textLight },
    loyerVal: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    fab: { position: 'absolute', bottom: 20, right: 20, width: 56, height: 56, borderRadius: 28, backgroundColor: Colors.secondary, justifyContent: 'center', alignItems: 'center', elevation: 5 },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalCard: { backgroundColor: Colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    modalTitle: { fontSize: 18, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    fieldGroup: { marginBottom: 12 },
    label: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.textLight, marginBottom: 4 },
    input: { backgroundColor: Colors.background, borderRadius: 10, paddingHorizontal: 12, paddingVertical: 10, fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.text, borderWidth: 1, borderColor: Colors.border },
    submitBtn: { backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 14, alignItems: 'center', marginTop: 12 },
    submitBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'Poppins_700Bold' },
});

export default MagasinsScreen;
