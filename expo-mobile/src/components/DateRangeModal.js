import React, { useState, useEffect } from 'react';
import { View, Text, Modal, TouchableOpacity, StyleSheet, Platform } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import DateTimePicker from '@react-native-community/datetimepicker';
import Colors from '../theme/Colors';

const toDate = (str) => (str ? new Date(str) : new Date());
const toStr = (date) => {
    const d = date || new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
};

const fmtFr = (str) => {
    if (!str) return 'Choisir une date';
    const [y, m, d] = str.split('-');
    return `${d}/${m}/${y}`;
};

/**
 * Modal de sélection de période (Du / Au).
 * Le DateTimePicker est rendu EN LIGNE (display="spinner") à l'intérieur du Modal :
 * cela évite le bug Android où un picker en "dialog" ferme aussi le Modal parent.
 */
const DateRangeModal = ({ visible, initialDebut = null, initialFin = null, onApply, onClose }) => {
    const [debut, setDebut] = useState(toStr(new Date()));
    const [fin, setFin] = useState(toStr(new Date()));
    const [field, setField] = useState(null); // 'debut' | 'fin' | null

    useEffect(() => {
        if (visible) {
            setDebut(initialDebut || toStr(new Date()));
            setFin(initialFin || initialDebut || toStr(new Date()));
            setField(null);
        }
    }, [visible, initialDebut, initialFin]);

    const apply = () => {
        onApply && onApply(debut, fin);
        onClose && onClose();
    };

    return (
        <Modal visible={visible} transparent animationType="slide" onRequestClose={onClose}>
            <View style={styles.overlay}>
                <View style={styles.content}>
                    <View style={styles.header}>
                        <Text style={styles.title}>Période</Text>
                        <TouchableOpacity onPress={onClose}>
                            <Ionicons name="close" size={24} color={Colors.text} />
                        </TouchableOpacity>
                    </View>

                    <Text style={styles.label}>Du</Text>
                    <TouchableOpacity style={styles.dateBtn} onPress={() => setField('debut')}>
                        <Ionicons name="calendar-outline" size={18} color={Colors.primary} />
                        <Text style={styles.dateBtnText}>{fmtFr(debut)}</Text>
                    </TouchableOpacity>

                    <Text style={[styles.label, { marginTop: 12 }]}>Au</Text>
                    <TouchableOpacity style={styles.dateBtn} onPress={() => setField('fin')}>
                        <Ionicons name="calendar-outline" size={18} color={Colors.primary} />
                        <Text style={styles.dateBtnText}>{fmtFr(fin)}</Text>
                    </TouchableOpacity>

                    {field && (
                        <View style={styles.pickerWrap}>
                            <DateTimePicker
                                value={field === 'debut' ? toDate(debut) : toDate(fin)}
                                mode="date"
                                display="spinner"
                                minimumDate={field === 'fin' ? toDate(debut) : undefined}
                                maximumDate={field === 'debut' ? toDate(fin) : undefined}
                                onChange={(e, d) => {
                                    if (d) {
                                        const s = toStr(d);
                                        if (field === 'debut') setDebut(s);
                                        else setFin(s);
                                    }
                                    setField(null);
                                }}
                                style={{ width: '100%' }}
                            />
                        </View>
                    )}

                    <TouchableOpacity style={[styles.submitBtn, { marginTop: 16 }]} onPress={apply}>
                        <Text style={styles.submitText}>Appliquer</Text>
                    </TouchableOpacity>
                </View>
            </View>
        </Modal>
    );
};

const styles = StyleSheet.create({
    overlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.45)', justifyContent: 'flex-end' },
    content: { backgroundColor: '#fff', borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 20, paddingBottom: 30 },
    header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    title: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    label: { fontSize: 13, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.textLight, marginBottom: 6 },
    dateBtn: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border, borderRadius: 12, paddingVertical: 12, paddingHorizontal: 14 },
    dateBtnText: { fontSize: 15, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.text },
    pickerWrap: { marginTop: 10, backgroundColor: '#F8FAFC', borderRadius: 12, paddingVertical: 4 },
    submitBtn: { backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 14, alignItems: 'center' },
    submitText: { color: '#fff', fontSize: 15, fontFamily: 'PlusJakartaSans_700Bold' },
});

export default DateRangeModal;
