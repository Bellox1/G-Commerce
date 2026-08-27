import React from 'react';
import {
    View,
    Text,
    StyleSheet,
    TouchableOpacity,
    Image,
} from 'react-native';
import { DrawerContentScrollView } from '@react-navigation/drawer';
import { DrawerActions } from '@react-navigation/native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useAuth } from '../context/AuthContext';
import Colors from '../theme/Colors';
import { Ionicons } from '@expo/vector-icons';

const ADMIN_ITEMS = [
    { key: 'home',         label: 'Tableau de bord',     icon: 'grid-outline',       screen: 'AdminHome' },
    { key: 'tenants',      label: 'Sociétés',            icon: 'business-outline',   screen: 'Tenants' },
    { key: 'prestataires', label: 'Partenaires',         icon: 'people-outline',     screen: 'admin-prestataires' },
    { key: 'commissions',  label: 'Commissions',         icon: 'wallet-outline',     screen: 'admin-commissions' },
];

const AdminDrawerContent = (props) => {
    const { user, logout } = useAuth();
    const insets = useSafeAreaInsets();

    const state = props.state;
    const activeRoute = state?.routes?.[state.index]?.name;

    const initials = user?.name
        ? user.name.split(' ').map((n) => n[0]).slice(0, 2).join('').toUpperCase()
        : 'SA';

    const renderItem = (item) => {
        const active = activeRoute === item.screen || (item.screen === 'Tenants' && activeRoute?.startsWith('Tenant'));
        return (
            <TouchableOpacity
                key={item.key}
                style={[styles.item, active && styles.itemActive]}
                onPress={() => {
                    props.navigation.navigate(item.screen);
                    props.navigation.dispatch(DrawerActions.closeDrawer());
                }}
            >
                <Ionicons
                    name={item.icon}
                    size={22}
                    color={active ? Colors.primary : Colors.text}
                    style={styles.itemIcon}
                />
                <Text style={[styles.itemLabel, active && styles.itemLabelActive]}>
                    {item.label}
                </Text>
            </TouchableOpacity>
        );
    };

    return (
        <View style={[styles.root, { paddingTop: insets.top }]}>
            <View style={styles.header}>
                <View style={styles.headerTop}>
                    <Image
                        source={require('../../assets/pilotix-logo.png')}
                        style={styles.logo}
                        resizeMode="contain"
                    />
                    <Text style={styles.brand}>PILOTIX</Text>
                </View>
                <View style={styles.profileRow}>
                    <View style={[styles.avatar, { backgroundColor: Colors.accent }]}>
                        <Text style={styles.avatarText}>{initials}</Text>
                    </View>
                    <View style={styles.profileInfo}>
                        <Text style={styles.userName} numberOfLines={1}>{user?.name || 'Super Admin'}</Text>
                        <Text style={styles.roleText}>Super Admin</Text>
                    </View>
                </View>
            </View>

            <DrawerContentScrollView
                {...props}
                style={{ flex: 1 }}
                contentContainerStyle={styles.scroll}
                showsVerticalScrollIndicator={false}
            >
                {ADMIN_ITEMS.map(renderItem)}
            </DrawerContentScrollView>

            <View style={[styles.footer, { paddingBottom: insets.bottom || 16 }]}>
                <TouchableOpacity style={styles.item} onPress={() => { props.navigation.navigate('Profile'); props.navigation.dispatch(DrawerActions.closeDrawer()); }}>
                    <Ionicons name="person-outline" size={22} color={Colors.text} style={styles.itemIcon} />
                    <Text style={styles.itemLabel}>Mon Profil</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.item} onPress={logout}>
                    <Ionicons name="log-out-outline" size={22} color={Colors.error} style={styles.itemIcon} />
                    <Text style={[styles.itemLabel, { color: Colors.error }]}>Déconnexion</Text>
                </TouchableOpacity>
            </View>
        </View>
    );
};

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: Colors.background },
    header: {
        paddingHorizontal: 20,
        paddingVertical: 18,
        borderBottomWidth: 1,
        borderBottomColor: Colors.border,
        backgroundColor: Colors.background,
    },
    headerTop: { flexDirection: 'row', alignItems: 'center', marginBottom: 16 },
    logo: { width: 34, height: 34 },
    brand: { marginLeft: 10, fontSize: 18, fontFamily: 'Poppins_700Bold', color: Colors.primary, letterSpacing: 1 },
    profileRow: { flexDirection: 'row', alignItems: 'center' },
    avatar: {
        width: 44, height: 44, borderRadius: 22,
        justifyContent: 'center', alignItems: 'center',
    },
    avatarText: { color: '#fff', fontSize: 16, fontFamily: 'Poppins_700Bold' },
    profileInfo: { marginLeft: 12, flex: 1 },
    userName: { fontSize: 15, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    roleText: { fontSize: 12.5, fontFamily: 'Poppins_500Medium', color: Colors.primary, marginTop: 2 },
    scroll: { paddingTop: 8, paddingHorizontal: 12, paddingBottom: 8 },
    item: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingVertical: 12,
        paddingHorizontal: 14,
        borderRadius: 12,
        marginBottom: 4,
    },
    itemActive: { backgroundColor: Colors.primary + '12' },
    itemIcon: { width: 26, textAlign: 'center' },
    itemLabel: { marginLeft: 14, fontSize: 15, fontFamily: 'Poppins_500Medium', color: Colors.text },
    itemLabelActive: { color: Colors.primary, fontFamily: 'Poppins_600SemiBold' },
    footer: {
        borderTopWidth: 1,
        borderTopColor: Colors.border,
        paddingTop: 8,
        paddingHorizontal: 12,
    },
});

export default AdminDrawerContent;
