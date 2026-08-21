import React from 'react';
import { View, Text, StyleSheet, ActivityIndicator, TouchableOpacity } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import Colors from '../theme/Colors';

export const Header = ({ title, onBack, onMenu, right }) => {
    const insets = useSafeAreaInsets();
    return (
        <View style={[styles.header, { paddingTop: insets.top + 8 }]}>
            <View style={styles.headerRow}>
                <View style={styles.leftSlot}>
                    {onMenu ? (
                        <TouchableOpacity onPress={onMenu} style={styles.iconBtn}>
                            <Ionicons name="apps" size={24} color={Colors.text} />
                        </TouchableOpacity>
                    ) : null}
                    {onBack ? (
                        <TouchableOpacity onPress={onBack} style={styles.iconBtn}>
                            <Ionicons name="arrow-back" size={24} color={Colors.text} />
                        </TouchableOpacity>
                    ) : null}
                    {!onMenu && !onBack ? <View style={styles.iconBtn} /> : null}
                </View>
                <Text style={styles.headerTitle} numberOfLines={1}>{title}</Text>
                <View style={styles.leftSlot}>{right}</View>
            </View>
        </View>
    );
};

export const Loader = () => (
    <View style={styles.loader}>
        <ActivityIndicator size="large" color={Colors.primary} />
    </View>
);

export const Empty = ({ text }) => (
    <View style={styles.empty}>
        <Ionicons name="inbox-outline" size={48} color={Colors.textLight} />
        <Text style={styles.emptyText}>{text}</Text>
    </View>
);

export const StatusBadge = ({ statut, map }) => {
    const cfg = map[statut] || { label: statut, color: Colors.textLight, bg: Colors.textLight + '20' };
    return (
        <View style={[styles.badge, { backgroundColor: cfg.bg }]}>
            <Text style={[styles.badgeText, { color: cfg.color }]}>{cfg.label}</Text>
        </View>
    );
};

const styles = StyleSheet.create({
    header: { backgroundColor: Colors.surface, borderBottomWidth: 1, borderBottomColor: Colors.border, paddingBottom: 10 },
    headerRow: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 12 },
    leftSlot: { flexDirection: 'row', alignItems: 'center', width: 96 },
    iconBtn: { width: 40, alignItems: 'center', justifyContent: 'center' },
    headerTitle: { flex: 1, textAlign: 'center', fontSize: 17, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    loader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    empty: { alignItems: 'center', justifyContent: 'center', paddingVertical: 60, paddingHorizontal: 24 },
    emptyText: { marginTop: 12, fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.textLight, textAlign: 'center' },
    badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 999, alignSelf: 'flex-start' },
    badgeText: { fontSize: 12, fontFamily: 'Poppins_600SemiBold' },
});
