import React, { useState, useCallback } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    ActivityIndicator, RefreshControl, StatusBar, Alert
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useNavigation, useFocusEffect, DrawerActions } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import Colors from '../../../theme/Colors';
import client from '../../../api/client';
import { Header, Loader, Empty, StatusBadge, PRESTATAIRE_STATUS } from '../components';

const formatDate = (d) => {
    if (!d) return '—';
    try { return new Date(d).toLocaleDateString('fr-FR'); } catch { return '—'; }
};

const PrestatairesScreen = () => {
    const insets = useSafeAreaInsets();
    const navigation = useNavigation();
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchData = useCallback(async () => {
        try {
            const res = await client.get('/prestataires');
            const list = res.data?.data || (Array.isArray(res.data) ? res.data : []);
            setItems(list);
        } catch (e) {
            console.error('Error fetching prestataires:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useFocusEffect(useCallback(() => { fetchData(); }, [fetchData]));

    const actOn = async (item, action) => {
        try {
            await client.post(`/prestataires/${item.id}/${action}`);
            Alert.alert('Succès', action === 'valider' ? 'Demande approuvée.' : 'Demande rejetée.');
            fetchData();
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Action impossible');
        }
    };

    const renderItem = ({ item }) => {
        const pending = item.statut === 'en_attente';
        return (
            <View style={styles.card}>
                <TouchableOpacity
                    style={styles.main}
                    onPress={() => navigation.navigate('admin-prestataire-show', { id: item.id })}
                >
                    <View style={styles.row}>
                        <Text style={styles.name}>{item.nom} {item.prenom}</Text>
                        <StatusBadge statut={item.statut} map={PRESTATAIRE_STATUS} />
                    </View>
                    <Text style={styles.sub}>{item.email}</Text>
                    <Text style={styles.sub}>{item.telephone}{item.entreprise ? `  •  ${item.entreprise}` : ''}</Text>
                    <Text style={styles.date}>{formatDate(item.created_at)}</Text>
                </TouchableOpacity>
                {pending && (
                    <View style={styles.actions}>
                        <TouchableOpacity
                            style={[styles.btn, { backgroundColor: Colors.success }]}
                            onPress={() => actOn(item, 'valider')}
                        >
                            <Ionicons name="checkmark" size={16} color="#fff" />
                            <Text style={styles.btnText}>Approuver</Text>
                        </TouchableOpacity>
                        <TouchableOpacity
                            style={[styles.btn, { backgroundColor: Colors.error }]}
                            onPress={() => actOn(item, 'rejeter')}
                        >
                            <Ionicons name="close" size={16} color="#fff" />
                            <Text style={styles.btnText}>Rejeter</Text>
                        </TouchableOpacity>
                    </View>
                )}
            </View>
        );
    };

    return (
        <View style={[styles.container, { paddingTop: insets.top }]}>
            <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />
            <Header title="Demandes de Partenaires" onMenu={() => navigation.dispatch(DrawerActions.openDrawer())} onBack={() => navigation.goBack()} />
            <View style={styles.body}>
                {loading ? <Loader /> : (
                    <FlatList
                        data={items}
                        keyExtractor={(it) => String(it.id)}
                        renderItem={renderItem}
                        contentContainerStyle={styles.list}
                        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={fetchData} />}
                        ListEmptyComponent={<Empty text="Aucune demande de partenariat reçue." />}
                    />
                )}
            </View>
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: Colors.background },
    body: { flex: 1 },
    list: { padding: 16, gap: 12 },
    card: { backgroundColor: Colors.cardBg, borderRadius: 16, padding: 16, borderWidth: 1, borderColor: Colors.border },
    main: { gap: 4 },
    row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    name: { fontSize: 16, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    sub: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    date: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginTop: 4 },
    actions: { flexDirection: 'row', gap: 10, marginTop: 12 },
    btn: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, paddingVertical: 10, borderRadius: 10 },
    btnText: { color: '#fff', fontSize: 13.5, fontFamily: 'Poppins_600SemiBold' },
});

export default PrestatairesScreen;
