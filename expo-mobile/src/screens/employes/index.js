import React, { useState, useCallback, useRef } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    ActivityIndicator, RefreshControl, Modal, ScrollView, StatusBar, Alert, TextInput,
    KeyboardAvoidingView, Platform
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { toggleEmployeActive, deleteEmploye } from '../../api';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import TopHeaderNav from '../../components/TopHeaderNav';

const EmployesScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const passwordRef = useRef(null);
    const [employes, setEmployes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    // Modal ajout
    const [modalVisible, setModalVisible] = useState(false);
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [telephone, setTelephone] = useState('');
    const [role, setRole] = useState('vendeur');
    const [rolesSecondaires, setRolesSecondaires] = useState([]);
    const [submitting, setSubmitting] = useState(false);

    const toggleRoleSecondaire = (r) => {
        if (r === role) return;
        setRolesSecondaires(prev =>
            prev.includes(r) ? prev.filter(x => x !== r) : [...prev, r]
        );
    };

    const handleSelectPrimaryRole = (r) => {
        setRole(r);
        if (r === 'superviseur') {
            setRolesSecondaires([]);
        } else {
            setRolesSecondaires(prev => prev.filter(x => x !== r));
        }
    };

    const fetchData = useCallback(async () => {
        try {
            const resp = await client.get('/employes');
            const list = Array.isArray(resp.data?.data) ? resp.data.data : (Array.isArray(resp.data) ? resp.data : []);
            setEmployes(list);
        } catch (e) {
            console.error('Error fetching employes:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useFocusEffect(
        useCallback(() => {
            fetchData();
        }, [fetchData])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchData();
    };

    const handleToggleActive = (item) => {
        Alert.alert(
            item.actif ? 'Désactiver le compte' : 'Activer le compte',
            item.actif
                ? `Voulez-vous désactiver le compte de ${item.name} ? Il ne pourra plus se connecter.`
                : `Voulez-vous réactiver le compte de ${item.name} ?`,
            [
                { text: 'Annuler', style: 'cancel' },
                {
                    text: item.actif ? 'Désactiver' : 'Activer',
                    style: item.actif ? 'destructive' : 'default',
                    onPress: async () => {
                        try {
                            await toggleEmployeActive(item.id);
                            Alert.alert('Succès', item.actif ? 'Compte désactivé.' : 'Compte réactivé.');
                            fetchData();
                        } catch (e) {
                            Alert.alert('Erreur', e.response?.data?.message || 'Action échouée.');
                        }
                    }
                }
            ]
        );
    };

    const handleDelete = (item) => {
        Alert.alert(
            'Supprimer le compte',
            `Voulez-vous supprimer le compte de ${item.name} ?`,
            [
                { text: 'Annuler', style: 'cancel' },
                {
                    text: 'Supprimer',
                    style: 'destructive',
                    onPress: async () => {
                        try {
                            await deleteEmploye(item.id);
                            Alert.alert('Succès', 'Compte supprimé.');
                            fetchData();
                        } catch (e) {
                            Alert.alert('Erreur', e.response?.data?.message || 'Suppression échouée.');
                        }
                    }
                }
            ]
        );
    };

    const handleCreateEmploye = async () => {
        if (!name || !email || !password) {
            Alert.alert('Erreur', 'Veuillez remplir au moins le nom, l\'email et le mot de passe.');
            return;
        }

        setSubmitting(true);
        try {
            await client.post('/employes', {
                name,
                email,
                password,
                telephone,
                role,
                roles_secondaires: rolesSecondaires
            });
            Alert.alert('Succès', 'Membre du personnel créé avec succès.');
            setModalVisible(false);
            setName('');
            setEmail('');
            setPassword('');
            setTelephone('');
            setRole('vendeur');
            setRolesSecondaires([]);
            fetchData();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de la création.';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <View style={styles.container}>
            <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />

            {/* Top Navigation Header replacing Drawer */}
            <TopHeaderNav navigation={navigation} activeCategory="employes" />

            {/* View Action Header */}
            <View style={styles.viewHeaderRow}>
                <View style={{ flex: 1 }}>
                    <Text style={styles.headerTitle}>Gestion du Personnel</Text>
                    <Text style={styles.headerSub}>{employes.length} membre(s) enregistré(s)</Text>
                </View>

                {/* Stylish Add (+) Button */}
                <TouchableOpacity
                    style={styles.btnAddPill}
                    onPress={() => setModalVisible(true)}
                    activeOpacity={0.88}
                >
                    <Ionicons name="add-circle" size={18} color="#FFFFFF" />
                    <Text style={styles.btnAddPillText}>+ Personnel</Text>
                </TouchableOpacity>
            </View>

            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                    <Text style={styles.loaderText}>Chargement du personnel...</Text>
                </View>
            ) : (
                <FlatList
                    data={employes}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={styles.listContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                    ListEmptyComponent={
                        <View style={styles.emptyContainer}>
                            <Ionicons name="people-outline" size={48} color={Colors.textLight} />
                            <Text style={styles.emptyText}>Aucun membre du personnel</Text>
                        </View>
                    }
                    renderItem={({ item }) => (
                        <View style={styles.card}>
                            <View style={styles.cardHeader}>
                                <View style={styles.avatar}>
                                    <Text style={styles.avatarText}>{item.name ? item.name[0].toUpperCase() : 'E'}</Text>
                                </View>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.name}>{item.name}</Text>
                                    <Text style={styles.email}>{item.email}</Text>
                                </View>
                                <View style={[styles.badge, item.actif ? styles.badgeSuccess : styles.badgeError]}>
                                    <Text style={[styles.badgeText, item.actif ? styles.badgeSuccessText : styles.badgeErrorText]}>
                                        {item.actif ? 'Actif' : 'Inactif'}
                                    </Text>
                                </View>

                                <TouchableOpacity
                                    style={styles.editBtn}
                                    onPress={() => navigation.navigate('EmployeEdit', { item })}
                                >
                                    <Ionicons name="create-outline" size={20} color={Colors.primary} />
                                </TouchableOpacity>
                            </View>

                            <View style={styles.metaRow}>
                                <View style={styles.metaItem}>
                                    <Ionicons name="shield-outline" size={14} color={Colors.primary} />
                                    <Text style={styles.metaText}>Rôle : <Text style={{ fontFamily: 'Poppins_700Bold' }}>{item.role}</Text>{item.roles_secondaires?.length ? <Text style={{ color: Colors.textLight }}>  ·  {item.roles_secondaires.join(', ')}</Text> : null}</Text>
                                </View>
                                {item.telephone && (
                                    <View style={styles.metaItem}>
                                        <Ionicons name="call-outline" size={14} color={Colors.textLight} />
                                        <Text style={styles.metaText}>{item.telephone}</Text>
                                    </View>
                                )}
                            </View>

                            <View style={styles.actionRow}>
                                <TouchableOpacity
                                    style={[styles.actionBtn, item.actif ? styles.actionBtnWarn : styles.actionBtnSuccess]}
                                    onPress={() => handleToggleActive(item)}
                                >
                                    <Ionicons name={item.actif ? 'pause-circle-outline' : 'play-circle-outline'} size={16} color="#FFF" />
                                    <Text style={styles.actionBtnText}>{item.actif ? 'Désactiver' : 'Activer'}</Text>
                                </TouchableOpacity>
                                <TouchableOpacity
                                    style={[styles.actionBtn, styles.actionBtnDanger]}
                                    onPress={() => handleDelete(item)}
                                >
                                    <Ionicons name="trash-outline" size={16} color="#FFF" />
                                    <Text style={styles.actionBtnText}>Supprimer</Text>
                                </TouchableOpacity>
                            </View>
                        </View>
                    )}
                />
            )}

            {/* Modal Nouveau membre */}
            <Modal visible={modalVisible} animationType="slide" transparent>
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
                    <View style={styles.modalOverlay}>
                        <View style={styles.modalContent}>
                            <View style={styles.modalHeader}>
                                <Text style={styles.modalTitle}>Créer un membre du personnel</Text>
                                <TouchableOpacity onPress={() => setModalVisible(false)}>
                                    <Ionicons name="close" size={24} color={Colors.text} />
                                </TouchableOpacity>
                            </View>

                            <ScrollView style={{ maxHeight: 420 }} showsVerticalScrollIndicator={false} keyboardShouldPersistTaps="handled">
                                <Text style={styles.inputLabel}>Nom complet *</Text>
                                <TextInput style={styles.input} value={name} onChangeText={setName} placeholder="ex: Jean Dupont" />

                                <Text style={styles.inputLabel}>Email d'accès *</Text>
                                <TextInput style={styles.input} value={email} onChangeText={setEmail} keyboardType="email-address" placeholder="email@societe.com" autoCapitalize="none" />

                                <Text style={styles.inputLabel}>Mot de passe *</Text>
                                <View style={{ flexDirection: 'row', alignItems: 'center', position: 'relative' }}>
                                    <TextInput
                                        ref={passwordRef}
                                        style={[styles.input, { flex: 1, paddingRight: 40 }]}
                                        value={password}
                                        onChangeText={setPassword}
                                        secureTextEntry={!showPassword}
                                        placeholder="Mot de passe"
                                    />
                                    <TouchableOpacity
                                        style={{ position: 'absolute', right: 12, top: 12, padding: 4 }}
                                        onPress={() => {
                                            setShowPassword(!showPassword);
                                            setTimeout(() => passwordRef.current?.focus(), 50);
                                        }}
                                        activeOpacity={0.7}
                                    >
                                        <Ionicons name={showPassword ? "eye-outline" : "eye-off-outline"} size={20} color={Colors.textLight} />
                                    </TouchableOpacity>
                                </View>

                                <Text style={styles.inputLabel}>Téléphone</Text>
                                <TextInput style={styles.input} value={telephone} onChangeText={setTelephone} keyboardType="phone-pad" placeholder="+229 97 00 00 00" />

                                <Text style={styles.inputLabel}>Rôle principal *</Text>
                                <View style={styles.chipRow}>
                                    {['superviseur', 'vendeur', 'magasinier', 'controleur'].map(r => (
                                        <TouchableOpacity
                                            key={r}
                                            style={[styles.chip, role === r && styles.chipActive]}
                                            onPress={() => handleSelectPrimaryRole(r)}
                                        >
                                            <Text style={[styles.chipText, role === r && styles.chipTextActive]}>{r.toUpperCase()}</Text>
                                        </TouchableOpacity>
                                    ))}
                                </View>

                                {role !== 'superviseur' && (
                                    <>
                                        <Text style={[styles.inputLabel, { marginTop: 14 }]}>Rôles secondaires (optionnel)</Text>
                                        <Text style={{ fontSize: 11, color: Colors.textLight, marginBottom: 8 }}>Cochez un ou plusieurs rôles cumulables pour cet employé.</Text>
                                        <View style={styles.chipRow}>
                                            {['vendeur', 'magasinier', 'controleur'].map(r => {
                                                const isPrimary = role === r;
                                                const isSelected = rolesSecondaires.includes(r);
                                                return (
                                                    <TouchableOpacity
                                                        key={r}
                                                        style={[
                                                            styles.chip,
                                                            isSelected && { backgroundColor: Colors.secondary || '#1E293B', borderColor: Colors.secondary || '#1E293B' },
                                                            isPrimary && { opacity: 0.35, backgroundColor: '#F1F5F9' }
                                                        ]}
                                                        onPress={() => toggleRoleSecondaire(r)}
                                                        disabled={isPrimary}
                                                    >
                                                        <Ionicons
                                                            name={isSelected ? "checkbox" : "square-outline"}
                                                            size={14}
                                                            color={isSelected ? "#FFF" : Colors.textLight}
                                                            style={{ marginRight: 4 }}
                                                        />
                                                        <Text style={[styles.chipText, isSelected && { color: '#FFF', fontFamily: 'Poppins_700Bold' }]}>
                                                            {r.toUpperCase()}
                                                        </Text>
                                                    </TouchableOpacity>
                                                );
                                            })}
                                        </View>
                                    </>
                                )}
                            </ScrollView>

                            <TouchableOpacity style={styles.submitBtn} onPress={handleCreateEmploye} disabled={submitting}>
                                {submitting ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>Créer le membre</Text>}
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
    listContent: { padding: 16, paddingBottom: 32 },
    emptyContainer: { padding: 40, alignItems: 'center', gap: 12 },
    emptyText: { fontSize: 14, color: Colors.textLight, fontFamily: 'Poppins_400Regular' },
    card: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, elevation: 1, gap: 10 },
    cardHeader: { flexDirection: 'row', alignItems: 'center', gap: 12 },
    editBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#f1f5f9', justifyContent: 'center', alignItems: 'center', marginLeft: 8 },
    avatar: { width: 44, height: 44, borderRadius: 22, backgroundColor: Colors.primary, justifyContent: 'center', alignItems: 'center' },
    avatarText: { fontSize: 18, fontFamily: 'Poppins_700Bold', color: '#FFF' },
    name: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.text },
    email: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
    badgeSuccess: { backgroundColor: Colors.success + '15' },
    badgeSuccessText: { color: Colors.success, fontSize: 12, fontFamily: 'Poppins_600SemiBold' },
    badgeError: { backgroundColor: Colors.error + '15' },
    badgeErrorText: { color: Colors.error, fontSize: 12, fontFamily: 'Poppins_600SemiBold' },
    metaRow: { flexDirection: 'row', gap: 16, backgroundColor: Colors.background, padding: 10, borderRadius: 12 },
    metaItem: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    metaText: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.text },
    actionRow: { flexDirection: 'row', gap: 10, marginTop: 4 },
    actionBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, justifyContent: 'center', flex: 1, paddingVertical: 8, borderRadius: 10 },
    actionBtnText: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: '#FFF' },
    actionBtnWarn: { backgroundColor: Colors.warning },
    actionBtnSuccess: { backgroundColor: Colors.success },
    actionBtnDanger: { backgroundColor: Colors.error },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: Colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20, gap: 10 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 },
    modalTitle: { fontSize: 18, fontFamily: 'Poppins_700Bold', color: Colors.text },
    inputLabel: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text, marginTop: 8 },
    input: { backgroundColor: Colors.background, borderRadius: 12, paddingHorizontal: 14, paddingVertical: 10, borderWidth: 1, borderColor: Colors.border, fontSize: 14, fontFamily: 'Poppins_500Medium' },
    chipRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginTop: 4 },
    chip: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 16, backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border },
    chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    chipText: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.text },
    chipTextActive: { color: '#FFF' },
    submitBtn: { backgroundColor: Colors.primary, borderRadius: 14, paddingVertical: 14, alignItems: 'center', marginTop: 16 },
    submitBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'Poppins_700Bold' },
});

export default EmployesScreen;
