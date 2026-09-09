import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    TextInput, ActivityIndicator, Alert, RefreshControl, Modal,
    KeyboardAvoidingView, Platform
} from 'react-native';
import { useAuth } from '../context/AuthContext';
import Colors from '../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../api/client';
import ScreenHeader from '../components/ScreenHeader';

const ProfileScreen = ({ navigation }) => {
    const { user, logout, updateProfile, refreshUser } = useAuth();

    const [editing, setEditing] = useState(false);
    const [name, setName] = useState(user?.name || '');
    const [telephone, setTelephone] = useState(user?.telephone || '');
    const [email, setEmail] = useState(user?.email || '');
    const [loadingInfo, setLoadingInfo] = useState(false);

    // Confirmation mot de passe pour changement d'email
    const [emailPassword, setEmailPassword] = useState('');
    const [showEmailPass, setShowEmailPass] = useState(false);
    const [securityModalVisible, setSecurityModalVisible] = useState(false);

    // Sécurité
    const [currentPassword, setCurrentPassword] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [showCurrPass, setShowCurrPass] = useState(false);
    const [showNewPass, setShowNewPass] = useState(false);
    const [showConfPass, setShowConfPass] = useState(false);
    const [loadingSecurity, setLoadingSecurity] = useState(false);
    const [showSecurity, setShowSecurity] = useState(false);

    // Suppression de compte
    const [deletePassword, setDeletePassword] = useState('');
    const [loadingDelete, setLoadingDelete] = useState(false);
    const [showDelete, setShowDelete] = useState(false);

    const [refreshing, setRefreshing] = useState(false);

    useEffect(() => {
        if (user) {
            setName(user.name || '');
            setTelephone(user.telephone || '');
            setEmail(user.email || '');
            setEmailPassword('');
        }
    }, [user]);

    const onRefresh = async () => {
        setRefreshing(true);
        try {
            await refreshUser();
        } catch (e) {
            console.error(e);
        } finally {
            setRefreshing(false);
        }
    };

    const handleSaveInfo = async () => {
        if (!name || !email) {
            Alert.alert('Erreur', 'Le nom et l\'email sont obligatoires.');
            return;
        }

        const emailHasChanged = email.trim().toLowerCase() !== user?.email?.toLowerCase();
        if (emailHasChanged) {
            setEmailPassword('');
            setSecurityModalVisible(true);
            return;
        }

        await executeSaveProfile('');
    };

    const executeSaveProfile = async (pwd) => {
        setLoadingInfo(true);
        try {
            await updateProfile({
                name,
                email,
                telephone,
                current_password_email: pwd
            });
            Alert.alert('Succès', 'Profil mis à jour avec succès.');
            setSecurityModalVisible(false);
            setEmailPassword('');
            setEditing(false);
        } catch (error) {
            const msg = error.response?.data?.message || 'Erreur lors de la mise à jour';
            Alert.alert('Erreur', msg);
        } finally {
            setLoadingInfo(false);
        }
    };

    const handleChangePassword = async () => {
        if (!currentPassword || !newPassword || !confirmPassword) {
            Alert.alert('Erreur', 'Veuillez remplir tous les champs du mot de passe');
            return;
        }
        if (newPassword !== confirmPassword) {
            Alert.alert('Erreur', 'Le nouveau mot de passe et sa confirmation ne correspondent pas');
            return;
        }
        setLoadingSecurity(true);
        try {
            await client.put('/profile/password', {
                current_password: currentPassword,
                password: newPassword,
                password_confirmation: confirmPassword
            });
            Alert.alert('Succès', 'Mot de passe modifié avec succès');
            setCurrentPassword('');
            setNewPassword('');
            setConfirmPassword('');
            setShowSecurity(false);
        } catch (error) {
            const msg = error.response?.data?.message || 'Mot de passe actuel incorrect ou données invalides';
            Alert.alert('Erreur', msg);
        } finally {
            setLoadingSecurity(false);
        }
    };

    const handleDeleteAccount = () => {
        if (!deletePassword) {
            Alert.alert('Erreur', 'Veuillez saisir votre mot de passe pour confirmer.');
            return;
        }
        Alert.alert(
            'Supprimer le compte',
            'Cette action est irréversible. Toutes vos données seront supprimées.',
            [
                { text: 'Annuler', style: 'cancel' },
                {
                    text: 'Supprimer définitivement',
                    style: 'destructive',
                    onPress: async () => {
                        setLoadingDelete(true);
                        try {
                            await client.delete('/profile', { data: { delete_password: deletePassword } });
                            Alert.alert('Compte supprimé', 'Votre compte a été supprimé avec succès.');
                            setDeletePassword('');
                            setShowDelete(false);
                            await logout();
                        } catch (error) {
                            const msg = error.response?.data?.message || 'Mot de passe incorrect.';
                            Alert.alert('Erreur', msg);
                        } finally {
                            setLoadingDelete(false);
                        }
                    }
                }
            ]
        );
    };

    const handleLogout = () => {
        Alert.alert(
            'Déconnexion',
            'Êtes-vous sûr de vouloir vous déconnecter ?',
            [
                { text: 'Annuler', style: 'cancel' },
                { text: 'Déconnexion', style: 'destructive', onPress: () => logout() }
            ]
        );
    };

    const initials = user?.name
        ? user.name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
        : '?';

    const roleLabel = user?.role
        ? user.role.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
        : 'Utilisateur';

    const secondaryRoles = Array.isArray(user?.roles_secondaires) ? user.roles_secondaires : [];

    return (
        <View style={styles.container}>
            <ScreenHeader
                navigation={navigation}
                title="Profil"
                subtitle="Gérez vos informations personnelles"
            />

            <KeyboardAvoidingView
                behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
                style={{ flex: 1 }}
                keyboardVerticalOffset={Platform.OS === 'ios' ? 0 : 24}
            >
            <ScrollView
                contentContainerStyle={styles.scrollContent}
                showsVerticalScrollIndicator={false}
                keyboardShouldPersistTaps="handled"
                refreshControl={
                    <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />
                }
            >
                {/* Bloc profil */}
                <View style={styles.profileBlock}>
                    <View style={styles.avatar}>
                        <Text style={styles.avatarText}>{initials}</Text>
                    </View>
                    <View style={styles.profileInfo}>
                        <Text style={styles.profileName}>{user?.name || 'Nom'}</Text>
                        <Text style={styles.profileEmail}>{user?.email || ''}</Text>
                        <View style={styles.roleBadge}>
                            <Ionicons name="shield-checkmark" size={13} color={Colors.primary} />
                            <Text style={styles.roleText}>{roleLabel}</Text>
                            {secondaryRoles.map((r, i) => (
                                <Text key={i} style={styles.roleSecondary}>{r.replace(/_/g, ' ')}</Text>
                            ))}
                        </View>
                    </View>
                </View>

                {user?.tenant?.nom && (
                    <View style={styles.tenantBox}>
                        <Ionicons name="business-outline" size={14} color={Colors.textLight} />
                        <Text style={styles.tenantText}>{user.tenant.nom}</Text>
                    </View>
                )}

                {/* Carte Informations personnelles */}
                <View style={styles.card}>
                    <View style={styles.cardHeader}>
                        <View style={styles.cardTitleRow}>
                            <Ionicons name="person-outline" size={20} color={Colors.primary} />
                            <Text style={styles.cardTitle}>Informations personnelles</Text>
                        </View>
                        <TouchableOpacity onPress={() => setEditing(!editing)}>
                            <Text style={styles.editBtnText}>{editing ? 'Annuler' : 'Modifier'}</Text>
                        </TouchableOpacity>
                    </View>

                    <View style={styles.fieldGroup}>
                        <Text style={styles.label}>Nom</Text>
                        {editing ? (
                            <TextInput style={styles.input} value={name} onChangeText={setName} />
                        ) : (
                            <Text style={styles.value}>{user?.name}</Text>
                        )}
                    </View>

                    <View style={styles.fieldGroup}>
                        <Text style={styles.label}>Email</Text>
                        {editing ? (
                            <TextInput style={styles.input} value={email} onChangeText={setEmail} keyboardType="email-address" autoCapitalize="none" />
                        ) : (
                            <Text style={styles.value}>{user?.email || '—'}</Text>
                        )}
                    </View>



                    <View style={styles.fieldGroup}>
                        <Text style={styles.label}>Téléphone</Text>
                        {editing ? (
                            <TextInput style={styles.input} value={telephone} onChangeText={setTelephone} keyboardType="phone-pad" />
                        ) : (
                            <Text style={styles.value}>{user?.telephone || '—'}</Text>
                        )}
                    </View>

                    {editing && (
                        <TouchableOpacity style={styles.saveBtn} onPress={handleSaveInfo} disabled={loadingInfo}>
                            {loadingInfo ? <ActivityIndicator color="#FFF" /> : <Text style={styles.saveBtnText}>Enregistrer</Text>}
                        </TouchableOpacity>
                    )}
                </View>

                {/* Carte Modifier le mot de passe */}
                <View style={styles.card}>
                    <TouchableOpacity style={styles.cardHeader} onPress={() => setShowSecurity(!showSecurity)}>
                        <View style={styles.cardTitleRow}>
                            <Ionicons name="lock-closed-outline" size={20} color={Colors.primary} />
                            <Text style={styles.cardTitle}>Modifier le mot de passe</Text>
                        </View>
                        <Ionicons name={showSecurity ? 'chevron-up' : 'chevron-down'} size={20} color={Colors.textLight} />
                    </TouchableOpacity>

                    {showSecurity && (
                        <View style={{ marginTop: 12 }}>
                            <View style={styles.fieldGroup}>
                                <Text style={styles.label}>Mot de passe actuel</Text>
                                <View style={{ flexDirection: 'row', alignItems: 'center', position: 'relative' }}>
                                    <TextInput style={[styles.input, { flex: 1, paddingRight: 40 }]} secureTextEntry={!showCurrPass} value={currentPassword} onChangeText={setCurrentPassword} placeholder="••••••••" placeholderTextColor={Colors.textLight} />
                                    <TouchableOpacity style={{ position: 'absolute', right: 12, top: 12, padding: 4 }} onPress={() => setShowCurrPass(!showCurrPass)} activeOpacity={0.7}>
                                        <Ionicons name={showCurrPass ? "eye-outline" : "eye-off-outline"} size={20} color={Colors.textLight} />
                                    </TouchableOpacity>
                                </View>
                            </View>

                            <View style={styles.fieldGroup}>
                                <Text style={styles.label}>Nouveau mot de passe</Text>
                                <View style={{ flexDirection: 'row', alignItems: 'center', position: 'relative' }}>
                                    <TextInput style={[styles.input, { flex: 1, paddingRight: 40 }]} secureTextEntry={!showNewPass} value={newPassword} onChangeText={setNewPassword} placeholder="••••••••" placeholderTextColor={Colors.textLight} />
                                    <TouchableOpacity style={{ position: 'absolute', right: 12, top: 12, padding: 4 }} onPress={() => setShowNewPass(!showNewPass)} activeOpacity={0.7}>
                                        <Ionicons name={showNewPass ? "eye-outline" : "eye-off-outline"} size={20} color={Colors.textLight} />
                                    </TouchableOpacity>
                                </View>
                            </View>

                            <View style={styles.fieldGroup}>
                                <Text style={styles.label}>Confirmer le mot de passe</Text>
                                <View style={{ flexDirection: 'row', alignItems: 'center', position: 'relative' }}>
                                    <TextInput style={[styles.input, { flex: 1, paddingRight: 40 }]} secureTextEntry={!showConfPass} value={confirmPassword} onChangeText={setConfirmPassword} placeholder="••••••••" placeholderTextColor={Colors.textLight} />
                                    <TouchableOpacity style={{ position: 'absolute', right: 12, top: 12, padding: 4 }} onPress={() => setShowConfPass(!showConfPass)} activeOpacity={0.7}>
                                        <Ionicons name={showConfPass ? "eye-outline" : "eye-off-outline"} size={20} color={Colors.textLight} />
                                    </TouchableOpacity>
                                </View>
                            </View>

                            <TouchableOpacity style={styles.saveBtn} onPress={handleChangePassword} disabled={loadingSecurity}>
                                {loadingSecurity ? <ActivityIndicator color="#FFF" /> : <Text style={styles.saveBtnText}>Modifier</Text>}
                            </TouchableOpacity>
                        </View>
                    )}
                </View>

                {/* Suppression de compte discrète & sécurisée */}
                <View style={{ marginTop: 24, marginBottom: 12, alignItems: 'center' }}>
                    <TouchableOpacity onPress={() => setShowDelete(!showDelete)} style={{ paddingVertical: 8 }}>
                        <Text style={{ fontSize: 12, color: '#94a3b8', textDecorationLine: 'underline' }}>
                            Options de compte avancées...
                        </Text>
                    </TouchableOpacity>

                    {showDelete && (
                        <View style={[styles.card, styles.dangerCard, { width: '100%', marginTop: 8 }]}>
                            <Text style={styles.dangerHint}>
                                Cette action supprime votre compte de manière irréversible.
                            </Text>

                            <View style={styles.fieldGroup}>
                                <Text style={styles.label}>Mot de passe pour confirmer</Text>
                                <TextInput style={styles.input} secureTextEntry value={deletePassword} onChangeText={setDeletePassword} placeholder="••••••••" placeholderTextColor={Colors.textLight} />
                            </View>

                            <TouchableOpacity style={[styles.saveBtn, styles.deleteBtn]} onPress={handleDeleteAccount} disabled={loadingDelete}>
                                {loadingDelete ? <ActivityIndicator color="#FFF" /> : <Text style={styles.saveBtnText}>Confirmer la suppression</Text>}
                            </TouchableOpacity>
                        </View>
                    )}
                </View>

                {/* Déconnexion */}
                <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout}>
                    <Ionicons name="log-out-outline" size={22} color={Colors.error} />
                    <Text style={styles.logoutBtnText}>Se déconnecter</Text>
                </TouchableOpacity>

                <View style={styles.appVersion}>
                    <Text style={styles.appVersionText}>PILOTIX v1.1.1</Text>
                </View>
            </ScrollView>
            </KeyboardAvoidingView>

            {/* Modal de sécurité pour modification d'email */}
            <Modal visible={securityModalVisible} animationType="fade" transparent>
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
                    <View style={{ flex: 1, backgroundColor: 'rgba(15,23,42,0.6)', justifyContent: 'center', alignItems: 'center', padding: 20 }}>
                        <View style={{ backgroundColor: '#FFF', width: '100%', borderRadius: 20, padding: 20, elevation: 5, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.15, shadowRadius: 10 }}>
                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
                                <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                    <Ionicons name="shield-checkmark" size={22} color={Colors.primary} style={{ marginRight: 8 }} />
                                    <Text style={{ fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.text }}>Confirmation de sécurité</Text>
                                </View>
                                <TouchableOpacity onPress={() => setSecurityModalVisible(false)}>
                                    <Ionicons name="close" size={24} color={Colors.textLight} />
                                </TouchableOpacity>
                            </View>

                            <Text style={{ fontSize: 13, color: Colors.textLight, marginBottom: 16, lineHeight: 18 }}>
                                Pour confirmer la modification de votre e-mail vers <Text style={{ fontFamily: 'Poppins_700Bold', color: Colors.primary }}>{email}</Text>, veuillez saisir votre mot de passe actuel :
                            </Text>

                            <View style={{ marginBottom: 20 }}>
                                <Text style={{ fontSize: 12, fontFamily: 'Poppins_600SemiBold', color: Colors.text, marginBottom: 6 }}>
                                    Mot de passe actuel *
                                </Text>
                                <View style={{ flexDirection: 'row', alignItems: 'center', position: 'relative' }}>
                                    <TextInput
                                        style={[styles.input, { flex: 1, paddingRight: 40 }]}
                                        secureTextEntry={!showEmailPass}
                                        value={emailPassword}
                                        onChangeText={setEmailPassword}
                                        placeholder="Mot de passe actuel"
                                        placeholderTextColor={Colors.textLight}
                                        autoFocus
                                    />
                                    <TouchableOpacity
                                        style={{ position: 'absolute', right: 12, top: 12, padding: 4 }}
                                        onPress={() => setShowEmailPass(!showEmailPass)}
                                        activeOpacity={0.7}
                                    >
                                        <Ionicons name={showEmailPass ? "eye-outline" : "eye-off-outline"} size={20} color={Colors.textLight} />
                                    </TouchableOpacity>
                                </View>
                                <Text style={{ fontSize: 10, color: Colors.textLight, marginTop: 4 }}>🔒 Limité à 3 tentatives maximum.</Text>
                            </View>

                            <View style={{ flexDirection: 'row', justifyContent: 'flex-end', gap: 10 }}>
                                <TouchableOpacity
                                    style={{ paddingVertical: 10, paddingHorizontal: 16, borderRadius: 8, backgroundColor: '#F1F5F9' }}
                                    onPress={() => setSecurityModalVisible(false)}
                                >
                                    <Text style={{ fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text }}>Annuler</Text>
                                </TouchableOpacity>

                                <TouchableOpacity
                                    style={{ paddingVertical: 10, paddingHorizontal: 16, borderRadius: 8, backgroundColor: Colors.primary }}
                                    onPress={() => {
                                        if (!emailPassword) {
                                            Alert.alert('Erreur', 'Veuillez saisir votre mot de passe actuel.');
                                            return;
                                        }
                                        executeSaveProfile(emailPassword);
                                    }}
                                    disabled={loadingInfo}
                                >
                                    {loadingInfo ? (
                                        <ActivityIndicator color="#FFF" />
                                    ) : (
                                        <Text style={{ fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: '#FFF' }}>Confirmer</Text>
                                    )}
                                </TouchableOpacity>
                            </View>
                        </View>
                    </View>
                </KeyboardAvoidingView>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#FFFFFF',
    },
    scrollContent: {
        padding: 16,
        paddingBottom: 32,
    },
    profileBlock: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: Colors.surface,
        borderRadius: 16,
        padding: 16,
        marginBottom: 12,
        borderWidth: 1,
        borderColor: '#E2E8F0',
    },
    avatar: {
        width: 56,
        height: 56,
        borderRadius: 28,
        backgroundColor: Colors.primary,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 14,
    },
    avatarText: {
        fontSize: 20,
        fontFamily: 'SpaceGrotesk_700Bold',
        color: '#FFF',
    },
    profileInfo: {
        flex: 1,
    },
    profileName: {
        fontSize: 17,
        fontFamily: 'SpaceGrotesk_700Bold',
        color: Colors.text,
    },
    profileEmail: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textLight,
        marginTop: 1,
    },
    roleBadge: {
        flexDirection: 'row',
        alignItems: 'center',
        flexWrap: 'wrap',
        gap: 6,
        backgroundColor: Colors.primaryLight,
        paddingHorizontal: 10,
        paddingVertical: 4,
        borderRadius: 20,
        marginTop: 8,
        alignSelf: 'flex-start',
    },
    roleText: {
        fontSize: 11,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.primary,
    },
    roleSecondary: {
        fontSize: 11,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.primary,
        textTransform: 'capitalize',
    },
    tenantBox: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        backgroundColor: Colors.surfaceElevated,
        borderRadius: 12,
        paddingHorizontal: 14,
        paddingVertical: 10,
        marginBottom: 12,
    },
    tenantText: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_500Medium',
        color: Colors.text,
    },
    card: {
        backgroundColor: Colors.surface,
        borderRadius: 16,
        padding: 16,
        marginBottom: 12,
        borderWidth: 1,
        borderColor: '#E2E8F0',
    },
    dangerCard: {
        borderWidth: 1,
        borderColor: Colors.error,
    },
    cardHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 14,
    },
    cardTitleRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 8,
    },
    cardTitle: {
        fontSize: 16,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.text,
    },
    editBtnText: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.primary,
    },
    fieldGroup: {
        marginBottom: 12,
    },
    label: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_500Medium',
        color: Colors.textLight,
        marginBottom: 4,
    },
    value: {
        fontSize: 15,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.text,
    },
    input: {
        backgroundColor: '#F8FAFC',
        borderRadius: 12,
        paddingHorizontal: 14,
        paddingVertical: 12,
        fontSize: 14,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.text,
        borderWidth: 1,
        borderColor: '#E2E8F0',
    },
    dangerHint: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textLight,
        marginBottom: 12,
    },
    saveBtn: {
        backgroundColor: Colors.primary,
        borderRadius: 12,
        paddingVertical: 13,
        alignItems: 'center',
        marginTop: 8,
    },
    saveBtnText: {
        color: '#FFF',
        fontSize: 14,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
    deleteBtn: {
        backgroundColor: Colors.error,
    },
    logoutBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 8,
        backgroundColor: '#FEF2F2',
        borderRadius: 14,
        paddingVertical: 14,
        marginTop: 4,
        marginBottom: 16,
    },
    logoutBtnText: {
        color: Colors.error,
        fontSize: 15,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
    appVersion: {
        alignItems: 'center',
        marginBottom: 8,
    },
    appVersionText: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textMuted,
    },
});

export default ProfileScreen;
