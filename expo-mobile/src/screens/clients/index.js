import React, { useState, useCallback } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    TextInput, ActivityIndicator, RefreshControl, Modal, Linking, StatusBar, Alert,
    KeyboardAvoidingView, Platform, ScrollView
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import TopHeaderNav from '../../components/TopHeaderNav';

const formatMoney = (val) => {
    if (val === null || val === undefined || val === '') return '0 F';
    let n = Math.round(Number(val));
    if (!n || Math.abs(n) === 0) n = 0;
    return n.toLocaleString('fr-FR') + ' F';
};

const ClientsScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [clients, setClients] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [search, setSearch] = useState('');

    // Modal nouveau client
    const [showAddModal, setShowAddModal] = useState(false);
    const [nom, setNom] = useState('');
    const [telephone, setTelephone] = useState('');
    const [adresse, setAdresse] = useState('');
    const [limiteCredit, setLimiteCredit] = useState('');
    const [submitting, setSubmitting] = useState(false);

    const fetchClients = useCallback(async () => {
        try {
            const resp = await client.get('/clients');
            const list = resp.data?.data || (Array.isArray(resp.data) ? resp.data : []);
            setClients(list);
        } catch (e) {
            console.error('Error fetching clients:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useFocusEffect(
        useCallback(() => {
            fetchClients();
        }, [fetchClients])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchClients();
    };

    const handleCall = (phone) => {
        if (phone) {
            Linking.openURL(`tel:${phone}`);
        } else {
            Alert.alert('Information', 'Ce client n\'a pas de numéro enregistré.');
        }
    };

    const handleWhatsApp = (phone) => {
        if (!phone) {
            Alert.alert('Information', 'Ce client n\'a pas de numéro enregistré.');
            return;
        }
        const num = phone.replace(/[^0-9]/g, '');
        Linking.openURL(`https://wa.me/${num}`);
    };

    const handleCreateClient = async () => {
        if (!nom) {
            Alert.alert('Erreur', 'Le nom du client est obligatoire.');
            return;
        }
        setSubmitting(true);
        try {
            await client.post('/clients', {
                nom,
                telephone,
                adresse,
                limite_credit: limiteCredit ? Number(limiteCredit) : null
            });
            Alert.alert('Succès', 'Client ajouté avec succès');
            setShowAddModal(false);
            setNom('');
            setTelephone('');
            setAdresse('');
            setLimiteCredit('');
            fetchClients();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de la création du client';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmitting(false);
        }
    };

    const filteredClients = clients.filter(c =>
        c.nom?.toLowerCase().includes(search.toLowerCase()) ||
        c.telephone?.includes(search)
    );

    return (
        <View style={styles.container}>
            {/* Top Navigation Header replacing Drawer */}
            <TopHeaderNav navigation={navigation} activeCategory="clients" />

            {/* View Action Header */}
            <View style={styles.viewHeaderRow}>
                <View style={{ flex: 1 }}>
                    <Text style={styles.headerTitle}>Gestion des Clients</Text>
                    <Text style={styles.headerSub}>{clients.length} client(s) enregistré(s)</Text>
                </View>

                {/* Stylish Add (+) Button */}
                <TouchableOpacity
                    style={styles.btnAddPill}
                    onPress={() => setShowAddModal(true)}
                    activeOpacity={0.88}
                >
                    <Ionicons name="add-circle" size={18} color="#FFFFFF" />
                    <Text style={styles.btnAddPillText}>Client</Text>
                </TouchableOpacity>
            </View>

            {/* Search */}
            <View style={styles.searchBarContainer}>
                <View style={styles.searchBar}>
                    <Ionicons name="search" size={18} color={Colors.textLight} />
                    <TextInput
                        style={styles.searchInput}
                        placeholder="Rechercher par nom ou numéro..."
                        placeholderTextColor={Colors.textLight}
                        value={search}
                        onChangeText={setSearch}
                    />
                    {search ? (
                        <TouchableOpacity onPress={() => setSearch('')}>
                            <Ionicons name="close-circle" size={18} color={Colors.textLight} />
                        </TouchableOpacity>
                    ) : null}
                </View>
            </View>

            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                    <Text style={styles.loaderText}>Chargement des clients...</Text>
                </View>
            ) : (
                <FlatList
                    data={filteredClients}
                    keyExtractor={(item) => item.id.toString()}
                    contentContainerStyle={styles.listContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                    ListEmptyComponent={
                        <View style={styles.emptyBox}>
                            <Ionicons name="people-outline" size={50} color={Colors.border} />
                            <Text style={styles.emptyTitle}>Aucun client trouvé</Text>
                        </View>
                    }
                    renderItem={({ item }) => {
                        const totalDette = item.total_dettes || 0;
                        const hasDette = totalDette > 0;

                        return (
                            <TouchableOpacity
                                style={styles.card}
                                onPress={() => navigation.navigate('ClientShow', { id: item.id })}
                            >
                                <View style={styles.cardHeader}>
                                    <View style={styles.avatar}>
                                        <Text style={styles.avatarText}>{item.nom ? item.nom.slice(0, 2).toUpperCase() : 'C'}</Text>
                                    </View>

                                    <View style={styles.clientInfo}>
                                        <Text style={styles.clientName}>{item.nom}</Text>
                                        <Text style={styles.clientTel}>{item.telephone || 'Sans numéro'}</Text>
                                        {item.adresse ? <Text style={styles.clientSub}>{item.adresse}</Text> : null}
                                    </View>

                                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                                        {item.telephone ? (
                                            <View style={styles.actionRow}>
                                                <TouchableOpacity style={styles.callBtn} onPress={() => handleCall(item.telephone)}>
                                                    <Ionicons name="call" size={16} color="#FFF" />
                                                </TouchableOpacity>
                                                <TouchableOpacity style={styles.whatsappBtn} onPress={() => handleWhatsApp(item.telephone)}>
                                                    <Ionicons name="logo-whatsapp" size={16} color="#FFF" />
                                                </TouchableOpacity>
                                            </View>
                                        ) : null}
                                        <TouchableOpacity
                                            style={styles.editIconBtn}
                                            onPress={() => navigation.navigate('ClientEdit', { item })}
                                        >
                                            <Ionicons name="create-outline" size={16} color={Colors.textLight} />
                                        </TouchableOpacity>
                                        <TouchableOpacity
                                            style={[styles.editIconBtn, { backgroundColor: '#FEE2E2' }]}
                                            onPress={() => {
                                                Alert.alert(
                                                    'Supprimer le client',
                                                    `Voulez-vous vraiment supprimer ${item.nom} ?`,
                                                    [
                                                        { text: 'Annuler', style: 'cancel' },
                                                        {
                                                            text: 'Supprimer',
                                                            style: 'destructive',
                                                            onPress: async () => {
                                                                try {
                                                                    await client.delete(`/clients/${item.id}`);
                                                                    Alert.alert('Succès', 'Client supprimé.');
                                                                    fetchClients();
                                                                } catch (e) {
                                                                    Alert.alert('Erreur', e.response?.data?.message || 'Impossible de supprimer.');
                                                                }
                                                            },
                                                        },
                                                    ]
                                                );
                                            }}
                                        >
                                            <Ionicons name="trash-outline" size={16} color={Colors.error} />
                                        </TouchableOpacity>
                                    </View>
                                </View>

                                <View style={styles.detteRow}>
                                    <View style={styles.detteBox}>
                                        <Text style={styles.detteLabel}>Dettes en cours</Text>
                                        <Text style={[styles.detteVal, { color: hasDette ? Colors.error : Colors.success }]}>
                                            {formatMoney(totalDette)}
                                        </Text>
                                    </View>
                                    {item.limite_credit ? (
                                        <View style={styles.detteBox}>
                                            <Text style={styles.detteLabel}>Limite Crédit</Text>
                                            <Text style={styles.detteVal}>{formatMoney(item.limite_credit)}</Text>
                                        </View>
                                    ) : null}
                                </View>
                            </TouchableOpacity>
                        );
                    }}
                />
            )}

            {/* Modal Créer Client */}
            <Modal visible={showAddModal} transparent animationType="slide" onRequestClose={() => setShowAddModal(false)}>
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
                <View style={styles.modalOverlay}>
                    <View style={styles.modalCard}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Nouveau Client</Text>
                            <TouchableOpacity onPress={() => setShowAddModal(false)}>
                                <Ionicons name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>

                        <View style={styles.fieldGroup}>
                            <Text style={styles.label}>Nom complet *</Text>
                            <TextInput style={styles.input} placeholder="Ex: M. Oumar" value={nom} onChangeText={setNom} />
                        </View>

                        <View style={styles.fieldGroup}>
                            <Text style={styles.label}>Téléphone</Text>
                            <TextInput style={styles.input} keyboardType="phone-pad" placeholder="Ex: +229 97 00 00 00" value={telephone} onChangeText={setTelephone} />
                        </View>

                        <View style={styles.fieldGroup}>
                            <Text style={styles.label}>Adresse / Quartier</Text>
                            <TextInput style={styles.input} placeholder="Ex: Saint-Michel" value={adresse} onChangeText={setAdresse} />
                        </View>

                        <View style={styles.fieldGroup}>
                            <Text style={styles.label}>Limite de crédit (FCFA)</Text>
                            <TextInput style={styles.input} keyboardType="number-pad" placeholder="Ex: 500000" value={limiteCredit} onChangeText={setLimiteCredit} />
                        </View>

                        <TouchableOpacity style={styles.submitBtn} onPress={handleCreateClient} disabled={submitting}>
                            {submitting ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>Enregistrer le client</Text>}
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
    viewHeaderRow: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingHorizontal: 16,
        paddingTop: 14,
        paddingBottom: 8,
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
    searchBarContainer: {
        paddingHorizontal: 16,
        paddingBottom: 10,
        backgroundColor: '#FFFFFF',
    },
    searchBar: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 14, paddingHorizontal: 12, height: 44, borderWidth: 1, borderColor: '#E2E8F0' },
    searchInput: { flex: 1, marginLeft: 8, fontFamily: 'PlusJakartaSans_400Regular', fontSize: 13, color: Colors.text },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 10 },
    loaderText: { fontSize: 14, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    listContent: { padding: 16, paddingBottom: 80 },
    emptyBox: { alignItems: 'center', paddingVertical: 60, gap: 10 },
    emptyTitle: { fontSize: 16, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    card: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, elevation: 1, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.05, shadowRadius: 4, borderWidth: 1, borderColor: '#E2E8F0' },
    editIconBtn: { width: 32, height: 32, borderRadius: 16, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    cardHeader: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: 12 },
    avatar: { width: 46, height: 46, borderRadius: 23, backgroundColor: Colors.primary, justifyContent: 'center', alignItems: 'center' },
    avatarText: { fontSize: 20, fontFamily: 'SpaceGrotesk_700Bold', color: '#FFF' },
    clientInfo: { flex: 1 },
    clientName: { fontSize: 15, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.primary },
    clientTel: { fontSize: 13, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.text },
    clientSub: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    actionRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    callBtn: { width: 38, height: 38, borderRadius: 19, backgroundColor: Colors.success, justifyContent: 'center', alignItems: 'center' },
    whatsappBtn: { width: 38, height: 38, borderRadius: 19, backgroundColor: '#25D366', justifyContent: 'center', alignItems: 'center' },
    editBtnBottom: { flexDirection: 'row', justifyContent: 'center', alignItems: 'center', gap: 6, marginTop: 12, backgroundColor: '#eef2ff', borderRadius: 12, paddingVertical: 10, borderWidth: 1, borderColor: Colors.primary },
    editBtnBottomText: { fontSize: 14, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.primary },
    detteRow: { flexDirection: 'row', justifyContent: 'space-between', backgroundColor: Colors.background, borderRadius: 12, padding: 10 },
    detteBox: { flex: 1 },
    detteLabel: { fontSize: 11, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    detteVal: { fontSize: 14, fontFamily: 'SpaceGrotesk_700Bold', marginTop: 2 },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalCard: { backgroundColor: Colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    modalTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.primary },
    fieldGroup: { marginBottom: 12 },
    label: { fontSize: 12, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.textLight, marginBottom: 4 },
    input: { backgroundColor: Colors.background, borderRadius: 10, paddingHorizontal: 12, paddingVertical: 10, fontSize: 14, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.text, borderWidth: 1, borderColor: Colors.border },
    submitBtn: { backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 14, alignItems: 'center', marginTop: 12 },
    submitBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'PlusJakartaSans_700Bold' },
});

export default ClientsScreen;
