import React, { useState, useCallback } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    TextInput, RefreshControl, StatusBar
} from 'react-native';
import { useFocusEffect, DrawerActions } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { Header, Loader, Empty, StatusBadge } from '../../components/ui';

const statusMap = {
    actif: { label: 'Actif', color: Colors.success, bg: '#dcfce7' },
    inactif: { label: 'Inactif', color: Colors.error, bg: '#fee2e2' },
};

const TenantsScreen = ({ navigation }) => {
    const [tenants, setTenants] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [search, setSearch] = useState('');

    const fetchTenants = useCallback(async () => {
        try {
            const resp = await client.get('/tenants');
            const payload = resp.data?.data;
            const list = Array.isArray(payload) ? payload : (payload?.data || []);
            setTenants(list);
        } catch (e) {
            console.error('Error fetching tenants:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useFocusEffect(
        useCallback(() => {
            fetchTenants();
        }, [fetchTenants])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchTenants();
    };

    const filtered = tenants.filter(t =>
        t.nom?.toLowerCase().includes(search.toLowerCase()) ||
        t.marque?.toLowerCase().includes(search.toLowerCase()) ||
        t.email?.toLowerCase().includes(search.toLowerCase())
    );

    const renderItem = ({ item }) => (
        <TouchableOpacity
            style={styles.card}
            onPress={() => navigation.navigate('TenantShow', { id: item.id })}
        >
            <View style={styles.cardHeader}>
                <View style={styles.avatar}>
                    <Text style={styles.avatarText}>{item.nom ? item.nom[0].toUpperCase() : 'S'}</Text>
                </View>
                <View style={styles.info}>
                    <Text style={styles.name}>{item.nom}</Text>
                    <Text style={styles.sub}>{item.marque || 'Sans marque'}</Text>
                    {item.email ? <Text style={styles.subLight}>{item.email}</Text> : null}
                </View>
                <StatusBadge statut={item.actif ? 'actif' : 'inactif'} map={statusMap} />
            </View>

            <View style={styles.metaRow}>
                <View style={styles.meta}>
                    <Ionicons name="location-outline" size={13} color={Colors.textLight} />
                    <Text style={styles.metaText}> {item.ville || '-'}, {item.pays || '-'}</Text>
                </View>
                <View style={styles.meta}>
                    <Ionicons name="call-outline" size={13} color={Colors.textLight} />
                    <Text style={styles.metaText}> {item.telephone || 'N/A'}</Text>
                </View>
            </View>

            <View style={styles.counts}>
                <View style={styles.countBox}>
                    <Text style={styles.countVal}>{item.magasins_count ?? 0}</Text>
                    <Text style={styles.countLbl}>Magasin(s)</Text>
                </View>
                <View style={styles.countBox}>
                    <Text style={styles.countVal}>{item.users_count ?? 0}</Text>
                    <Text style={styles.countLbl}>Utilisateur(s)</Text>
                </View>
                {item.activite ? (
                    <View style={styles.countBox}>
                        <Text style={styles.countActivite} numberOfLines={1}>{item.activite}</Text>
                        <Text style={styles.countLbl}>Activité</Text>
                    </View>
                ) : null}
            </View>
        </TouchableOpacity>
    );

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />

            <Header
                title="Sociétés (Tenants)"
                onMenu={() => navigation.dispatch(DrawerActions.openDrawer())}
                right={
                    <TouchableOpacity onPress={() => navigation.navigate('TenantCreate')}>
                        <Ionicons name="add" size={26} color={Colors.primary} />
                    </TouchableOpacity>
                }
            />

            <View style={styles.searchWrap}>
                <Ionicons name="search" size={18} color={Colors.textLight} />
                <TextInput
                    style={styles.searchInput}
                    placeholder="Rechercher une société..."
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

            {loading ? (
                <Loader />
            ) : (
                <FlatList
                    data={filtered}
                    keyExtractor={(item) => item.id.toString()}
                    contentContainerStyle={styles.listContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                    ListEmptyComponent={<Empty text="Aucune société trouvée" />}
                    renderItem={renderItem}
                />
            )}
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: Colors.background },
    searchWrap: { flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.surface, borderBottomWidth: 1, borderBottomColor: Colors.border, paddingHorizontal: 16, height: 48 },
    searchInput: { flex: 1, marginLeft: 8, fontFamily: 'Poppins_400Regular', fontSize: 14, color: Colors.text },
    listContent: { padding: 16, paddingBottom: 80 },
    card: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, borderWidth: 1, borderColor: Colors.border, elevation: 1, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.05, shadowRadius: 4 },
    cardHeader: { flexDirection: 'row', alignItems: 'center', gap: 12 },
    avatar: { width: 46, height: 46, borderRadius: 23, backgroundColor: Colors.primary, justifyContent: 'center', alignItems: 'center' },
    avatarText: { fontSize: 20, fontFamily: 'Poppins_700Bold', color: '#FFF' },
    info: { flex: 1 },
    name: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    sub: { fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.text },
    subLight: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    metaRow: { flexDirection: 'row', justifyContent: 'space-between', marginTop: 10 },
    meta: { flexDirection: 'row', alignItems: 'center' },
    metaText: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginLeft: 4 },
    counts: { flexDirection: 'row', gap: 10, marginTop: 12, backgroundColor: Colors.background, borderRadius: 12, padding: 10 },
    countBox: { flex: 1, alignItems: 'center' },
    countVal: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.text },
    countActivite: { fontSize: 12, fontFamily: 'Poppins_600SemiBold', color: Colors.text, maxWidth: 90 },
    countLbl: { fontSize: 11, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginTop: 2 },
});

export default TenantsScreen;
