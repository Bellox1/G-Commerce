import React, { useState } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    TextInput, ActivityIndicator, Alert, StatusBar
} from 'react-native';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

const CreateEmployeScreen = ({ navigation, route }) => {
    const insets = useSafeAreaInsets();
    const editing = route?.params?.item;

    const [name, setName] = useState(editing?.name || '');
    const [email, setEmail] = useState(editing?.email || '');
    const [tel, setTel] = useState(editing?.tel || editing?.telephone || '');
    const [role, setRole] = useState(editing?.role || 'vendeur'); // admin, vendeur, magasinier, controleur
    const [salaireMensuel, setSalaireMensuel] = useState(editing?.salaire ? String(editing.salaire) : '0');
    const [secondaryRoles, setSecondaryRoles] = useState(editing?.roles_secondaires || []);
    const [password, setPassword] = useState('');
    const [submitting, setSubmitting] = useState(false);

    const toggleSecondary = (r) => {
        setSecondaryRoles(prev =>
            prev.includes(r) ? prev.filter(x => x !== r) : [...prev, r]
        );
    };

    // Le rôle principal ne peut pas être cumulé en tant que rôle secondaire
    const selectPrimaryRole = (r) => {
        setRole(r);
        setSecondaryRoles(prev => prev.filter(x => x !== r));
    };

    const handleSubmit = async () => {
        if (!name || !email) {
            Alert.alert('Erreur', 'Le nom et l\'adresse email du membre sont obligatoires.');
            return;
        }

        if (!editing && !password) {
            Alert.alert('Erreur', 'Le mot de passe par défaut est obligatoire pour la création d\'un membre.');
            return;
        }

        setSubmitting(true);
        try {
            const payload = {
                name,
                email,
                telephone: tel,
                role,
                salaire: Number(salaireMensuel) || 0,
                roles_secondaires: secondaryRoles,
                ...(password ? { password } : {})
            };

            if (editing) {
                await client.put(`/employes/${editing.id}`, payload);
                Alert.alert('Succès', 'Membre modifié avec succès.');
            } else {
                await client.post('/employes', payload);
                Alert.alert('Succès', 'Nouveau membre du personnel créé avec succès.');
            }

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
                <Text style={styles.topTitle}>{editing ? 'Modifier le membre' : 'Nouveau Collaborateur'}</Text>
                <View style={{ width: 36 }} />
            </View>

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>


                <View style={styles.cardSection}>
                    <Text style={styles.fieldLabel}>Nom complet *</Text>
                    <TextInput
                        style={styles.input}
                        value={name}
                        onChangeText={setName}
                        placeholder="Ex: Jean Dupont"
                    />

                    <Text style={styles.fieldLabel}>Adresse Email *</Text>
                    <TextInput
                        style={styles.input}
                        keyboardType="email-address"
                        autoCapitalize="none"
                        value={email}
                        onChangeText={setEmail}
                        placeholder="jean@exemple.com"
                    />

                    <Text style={styles.fieldLabel}>Numéro de Téléphone</Text>
                    <TextInput
                        style={styles.input}
                        keyboardType="phone-pad"
                        value={tel}
                        onChangeText={setTel}
                        placeholder="+229 97 00 00 00"
                    />

                    <Text style={styles.fieldLabel}>Rôle principal *</Text>
                    <View style={styles.roleGrid}>
                        {([
                            { key: 'vendeur', label: 'Vendeur' },
                            { key: 'magasinier', label: 'Magasinier' },
                            { key: 'controleur', label: 'Contrôleur' },
                            ...(editing ? [{ key: 'superviseur', label: 'Superviseur' }] : []),
                        ]).map(r => (
                            <TouchableOpacity
                                key={r.key}
                                style={[styles.roleChip, role === r.key && styles.roleChipActive]}
                                onPress={() => selectPrimaryRole(r.key)}
                            >
                                <Text style={[styles.roleChipText, role === r.key && styles.roleChipTextActive]}>{r.label}</Text>
                            </TouchableOpacity>
                        ))}
                    </View>

                    <Text style={styles.fieldLabel}>Rôles secondaires (optionnel)</Text>
                    <View style={styles.roleGrid}>
                        {[
                            { key: 'superviseur', label: 'Superviseur' },
                            { key: 'vendeur', label: 'Vendeur' },
                            { key: 'magasinier', label: 'Magasinier' },
                            { key: 'controleur', label: 'Contrôleur' },
                        ].filter(r => r.key !== role).map(r => (
                            <TouchableOpacity
                                key={r.key}
                                style={[styles.roleChip, secondaryRoles.includes(r.key) && styles.roleChipActive]}
                                onPress={() => toggleSecondary(r.key)}
                            >
                                <Text style={[styles.roleChipText, secondaryRoles.includes(r.key) && styles.roleChipTextActive]}>{r.label}</Text>
                            </TouchableOpacity>
                        ))}
                    </View>

                    <Text style={styles.fieldLabel}>Salaire Mensuel (FCFA)</Text>
                    <TextInput
                        style={styles.input}
                        keyboardType="numeric"
                        value={salaireMensuel}
                        onChangeText={setSalaireMensuel}
                        placeholder="Ex: 100000"
                    />

                    <Text style={styles.fieldLabel}>{editing ? 'Nouveau mot de passe (Laisser vide si inchangé)' : 'Mot de passe initial *'}</Text>
                    <TextInput
                        style={styles.input}
                        secureTextEntry
                        value={password}
                        onChangeText={setPassword}
                        placeholder="••••••••"
                    />
                </View>

                <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                    {submitting ? (
                        <ActivityIndicator color="#FFF" />
                    ) : (
                        <Text style={styles.submitBtnText}>{editing ? 'Enregistrer les modifications' : 'Créer le compte membre'}</Text>
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
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, backgroundColor: '#f8fafc' },
    roleGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginVertical: 6 },
    roleChip: { paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8, backgroundColor: '#f1f5f9' },
    roleChipActive: { backgroundColor: Colors.primary },
    roleChipText: { fontSize: 12, fontWeight: '600', color: Colors.text },
    roleChipTextActive: { color: '#FFF' },
    submitBtn: { backgroundColor: Colors.primary, paddingVertical: 16, borderRadius: 12, alignItems: 'center', marginTop: 10 },
    submitBtnText: { color: '#FFF', fontWeight: '800', fontSize: 15 },
});

export default CreateEmployeScreen;
