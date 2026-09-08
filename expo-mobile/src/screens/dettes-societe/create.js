import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    TextInput, ActivityIndicator, Alert, StatusBar, Platform
} from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { getDettesSociete } from '../../api';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

const CreateDetteSocieteScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [fournisseurs, setFournisseurs] = useState([]);
    const [arrivages, setArrivages] = useState([]);

    const [fournisseurId, setFournisseurId] = useState(null);
    const [arrivageId, setArrivageId] = useState(null);
    const [montant, setMontant] = useState('');
    const [description, setDescription] = useState('');
    const [dateDetteObj, setDateDetteObj] = useState(new Date());
    const [showDatePicker, setShowDatePicker] = useState(false);
    const [devise, setDevise] = useState('');
    const [tauxDeChange, setTauxDeChange] = useState('');
    const [montantOrigine, setMontantOrigine] = useState('');
    const [submitting, setSubmitting] = useState(false);

    // Calcul automatique du montant FCFA à partir de la devise d'origine
    useEffect(() => {
        if (montantOrigine && tauxDeChange) {
            const orig = parseFloat(String(montantOrigine).replace(',', '.'));
            const taux = parseFloat(String(tauxDeChange).replace(',', '.'));
            if (!isNaN(orig) && !isNaN(taux) && orig > 0 && taux > 0) {
                setMontant(String(Math.round(orig * taux)));
            }
        }
    }, [montantOrigine, tauxDeChange]);

    useEffect(() => {
        const load = async () => {
            try {
                const resp = await getDettesSociete();
                setFournisseurs(resp.data?.fournisseurs || []);
                setArrivages(resp.data?.arrivages || []);
            } catch (e) {
                console.error('Error loading fournisseurs/arrivages:', e);
            }
        };
        load();
    }, []);

    const onPickArrivage = (a) => {
        setArrivageId(a.id);
        setMontant(String(a.total_cout_reel ?? ''));
        if (a.fournisseur_id) setFournisseurId(a.fournisseur_id);
        if (a.reference) setDescription('Arrivage ' + a.reference);
    };

    const handleSubmit = async () => {
        if (!montant || Number(montant) <= 0) {
            Alert.alert('Erreur', 'Veuillez saisir un montant valide.');
            return;
        }

        setSubmitting(true);
        try {
            const formattedDate = dateDetteObj ? dateDetteObj.toISOString().split('T')[0] : null;
            await client.post('/dettes-societe', {
                fournisseur_id: fournisseurId || null,
                arrivage_id: arrivageId || null,
                montant: Number(montant),
                devise: devise || null,
                taux_de_change: tauxDeChange ? Number(tauxDeChange) : null,
                montant_origine: montantOrigine ? Number(montantOrigine) : null,
                description: description || null,
                date_dette: formattedDate,
            });

            Alert.alert('Succès', 'Dette société enregistrée avec succès.');
            navigation.goBack();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de la création de la dette';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmitting(false);
        }
    };

    const formatDateDisplay = (d) => {
        if (!d) return '';
        return new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' }).format(d);
    };

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle}>Nouvelle Dette Fournisseur</Text>
                <View style={{ width: 36 }} />
            </View>

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>


                <View style={styles.cardSection}>
                    <Text style={styles.fieldLabel}>Fournisseur (optionnel)</Text>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipScroll} contentContainerStyle={styles.chipScrollContent}>
                        <TouchableOpacity style={[styles.chip, fournisseurId === null && styles.chipActive]} onPress={() => setFournisseurId(null)}>
                            <Text style={[styles.chipText, fournisseurId === null && styles.chipTextActive]}>Aucun</Text>
                        </TouchableOpacity>
                        {fournisseurs.map((f) => (
                            <TouchableOpacity key={f.id} style={[styles.chip, fournisseurId === f.id && styles.chipActive]} onPress={() => { setFournisseurId(f.id); if (f.devise) setDevise(f.devise); }}>
                                <Text style={[styles.chipText, fournisseurId === f.id && styles.chipTextActive]}>{f.nom}</Text>
                            </TouchableOpacity>
                        ))}
                    </ScrollView>
                </View>

                <View style={styles.cardSection}>
                    <Text style={styles.fieldLabel}>Arrivage associé (optionnel)</Text>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipScroll} contentContainerStyle={styles.chipScrollContent}>
                        <TouchableOpacity style={[styles.chip, arrivageId === null && styles.chipActive]} onPress={() => setArrivageId(null)}>
                            <Text style={[styles.chipText, arrivageId === null && styles.chipTextActive]}>Aucun</Text>
                        </TouchableOpacity>
                        {arrivages.map((a) => (
                            <TouchableOpacity key={a.id} style={[styles.chip, arrivageId === a.id && styles.chipActive]} onPress={() => onPickArrivage(a)}>
                                <Text style={[styles.chipText, arrivageId === a.id && styles.chipTextActive]}>{a.reference}</Text>
                            </TouchableOpacity>
                        ))}
                    </ScrollView>
                </View>

                <View style={styles.cardSection}>
                    <Text style={styles.fieldLabel}>Devise d'origine (optionnel)</Text>
                    <TextInput style={styles.input} value={devise} onChangeText={setDevise} placeholder="Ex: USD, EUR, CNY" autoCapitalize="characters" />

                    <Text style={styles.fieldLabel}>Taux de change (1 {devise || 'devise'} = X FCFA)</Text>
                    <TextInput style={styles.input} keyboardType="numeric" value={tauxDeChange} onChangeText={setTauxDeChange} placeholder="Ex: 600" />

                    <Text style={styles.fieldLabel}>Montant d'origine ({devise || 'devise'})</Text>
                    <TextInput style={styles.input} keyboardType="numeric" value={montantOrigine} onChangeText={setMontantOrigine} placeholder="Ex: 2000" />
                </View>

                <View style={styles.cardSection}>
                    <Text style={styles.fieldLabel}>Montant dû (FCFA) *</Text>
                    <TextInput style={styles.input} keyboardType="numeric" value={montant} onChangeText={setMontant} placeholder="Ex: 150000" />

                    <Text style={styles.fieldLabel}>Description</Text>
                    <TextInput style={styles.input} value={description} onChangeText={setDescription} placeholder="Ex: Arrivage ARR-2026-004" />

                    <Text style={styles.fieldLabel}>Date de la dette *</Text>
                    <TouchableOpacity
                        style={[styles.input, { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingVertical: 12 }]}
                        onPress={() => setShowDatePicker(true)}
                    >
                        <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                            <Ionicons name="calendar-outline" size={18} color={Colors.primary} style={{ marginRight: 8 }} />
                            <Text style={{ color: Colors.text, fontSize: 14 }}>
                                {formatDateDisplay(dateDetteObj)}
                            </Text>
                        </View>
                        <Ionicons name="chevron-down" size={16} color={Colors.textLight} />
                    </TouchableOpacity>

                    {showDatePicker && (
                        <DateTimePicker
                            value={dateDetteObj}
                            mode="date"
                            display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                            maximumDate={new Date()}
                            onChange={(event, selectedDate) => {
                                setShowDatePicker(false);
                                if (selectedDate) setDateDetteObj(selectedDate);
                            }}
                        />
                    )}
                </View>

                <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                    {submitting ? (
                        <ActivityIndicator color="#FFF" />
                    ) : (
                        <Text style={styles.submitBtnText}>Enregistrer la dette</Text>
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
    chipScroll: { marginBottom: 4 },
    chipScrollContent: { gap: 8 },
    chip: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 16, backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border },
    chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    chipText: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.textLight },
    chipTextActive: { color: '#FFF', fontFamily: 'Poppins_700Bold' },
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, backgroundColor: '#f8fafc' },
    submitBtn: { backgroundColor: Colors.primary, paddingVertical: 16, borderRadius: 12, alignItems: 'center', marginTop: 10 },
    submitBtnText: { color: '#FFF', fontWeight: '800', fontSize: 15 },
});

export default CreateDetteSocieteScreen;
