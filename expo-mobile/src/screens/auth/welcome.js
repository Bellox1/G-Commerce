import React, { useRef, useEffect, useState } from 'react';
import {
    View, Text, StyleSheet, TouchableOpacity,
    Dimensions, ScrollView, Image, StatusBar, Linking
} from 'react-native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { BASE_URL } from '../../api/client';

const { width } = Dimensions.get('window');

const slides = [
    {
        bgColor: '#FFFFFF',
        img: require('../../../assets/stock_img.png'),
        title: 'Stocks & Arrivages',
        subtitle: 'Gestion multi-magasins, suivi des arrivages fournisseurs et mouvements de stock en temps réel.',
    },
    {
        bgColor: '#FFFFFF',
        img: require('../../../assets/facture.png'),
        title: 'Ventes, Caisse & Livraisons',
        subtitle: 'Facturation rapide (comptant ou acompte), gestion des ventes et suivi complet de vos livraisons.',
    },
    {
        bgColor: '#FFFFFF',
        img: require('../../../assets/gestion.png'),
        title: 'Trésorerie & Dettes',
        subtitle: 'Suivez la trésorerie globale, vos créances clients et le règlement des dettes société sans omission.',
    },
    {
        bgColor: '#FFFFFF',
        img: require('../../../assets/chiffre_affaire.png'),
        title: 'Analytique & Hors-Ligne',
        subtitle: 'Visualisez vos bénéfices réels, chiffre d\'affaires et continuez de travailler 100% hors-ligne.',
    },
];

const WelcomeScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const scrollRef = useRef(null);
    const [currentIndex, setCurrentIndex] = useState(0);

    useEffect(() => {
        const timer = setInterval(() => {
            const next = (currentIndex + 1) % slides.length;
            scrollRef.current?.scrollTo({ x: next * width, animated: true });
            setCurrentIndex(next);
        }, 4000);
        return () => clearInterval(timer);
    }, [currentIndex]);

    const handleScroll = (e) => {
        const idx = Math.round(e.nativeEvent.contentOffset.x / width);
        setCurrentIndex(idx);
    };

    const handleOpenContact = () => {
        const url = `${BASE_URL}/#contact`;
        Linking.openURL(url).catch(err => console.error("Erreur ouverture URL contact:", err));
    };

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Header Brand */}
            <View style={[styles.headerLogo, { paddingTop: Math.max(insets.top + 8, 28) }]}>
                <Image
                    source={require('../../../assets/pilotix-logo.png')}
                    style={styles.logoImage}
                    resizeMode="contain"
                />
            </View>

            {/* Slides Content */}
            <ScrollView
                ref={scrollRef}
                horizontal
                pagingEnabled
                showsHorizontalScrollIndicator={false}
                onMomentumScrollEnd={handleScroll}
                scrollEventThrottle={16}
                style={styles.scrollView}
            >
                {slides.map((slide, i) => (
                    <View key={i} style={styles.slide}>
                        <View style={styles.illustrationWrapper}>
                            <Image source={slide.img} style={styles.illustration} resizeMode="contain" />
                        </View>

                        <View style={styles.textWrapper}>
                            <Text style={styles.title}>{slide.title}</Text>
                            <Text style={styles.subtitle}>{slide.subtitle}</Text>
                        </View>
                    </View>
                ))}
            </ScrollView>

            {/* Progress Bars (Image 2 style) */}
            <View style={styles.progressRow}>
                {slides.map((_, i) => (
                    <View
                        key={i}
                        style={[
                            styles.progressPill,
                            i === currentIndex ? styles.progressPillActive : styles.progressPillInactive
                        ]}
                    />
                ))}
            </View>

            {/* Pill Buttons Container (Image 2 style) */}
            <View style={[styles.actionsContainer, { paddingBottom: Math.max(insets.bottom + 16, 32) }]}>
                {/* Main Action Pill */}
                <TouchableOpacity
                    style={styles.btnPrimary}
                    onPress={() => navigation.navigate('Login')}
                    activeOpacity={0.88}
                >
                    <Text style={styles.btnPrimaryText}>Se Connecter</Text>
                    <Ionicons name="arrow-forward" size={18} color="#FFFFFF" />
                </TouchableOpacity>

                {/* Secondary Pill: Demande de création */}
                <TouchableOpacity
                    style={styles.btnSecondary}
                    onPress={handleOpenContact}
                    activeOpacity={0.7}
                >
                    <Ionicons name="rocket-outline" size={18} color={Colors.primary} />
                    <Text style={styles.btnSecondaryText}>Faire une demande de création</Text>
                </TouchableOpacity>

                {/* Bottom prompt */}
                <View style={styles.footerPrompt}>
                    <Text style={styles.footerPromptText}>Vous avez déjà un compte ? </Text>
                    <TouchableOpacity onPress={() => navigation.navigate('Login')}>
                        <Text style={styles.footerPromptLink}>Connexion</Text>
                    </TouchableOpacity>
                </View>
            </View>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#FFFFFF',
    },
    headerLogo: {
        alignItems: 'center',
        justifyContent: 'center',
        paddingHorizontal: 24,
    },
    logoImage: {
        width: 170,
        height: 55,
    },
    scrollView: {
        flex: 1,
    },
    slide: {
        width,
        alignItems: 'center',
        justifyContent: 'center',
        paddingHorizontal: 32,
    },
    illustrationWrapper: {
        width: width * 0.88,
        height: width * 0.78,
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 20,
    },
    illustration: {
        width: '100%',
        height: '100%',
    },
    textWrapper: {
        alignItems: 'center',
        paddingHorizontal: 8,
    },
    title: {
        fontSize: 25,
        fontFamily: 'SpaceGrotesk_700Bold',
        color: Colors.text,
        textAlign: 'center',
        marginBottom: 10,
        lineHeight: 32,
    },
    subtitle: {
        fontSize: 14,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textLight,
        textAlign: 'center',
        lineHeight: 22,
    },
    progressRow: {
        flexDirection: 'row',
        justifyContent: 'center',
        alignItems: 'center',
        gap: 8,
        marginBottom: 20,
    },
    progressPill: {
        height: 6,
        borderRadius: 3,
    },
    progressPillActive: {
        width: 32,
        backgroundColor: Colors.primary,
    },
    progressPillInactive: {
        width: 12,
        backgroundColor: '#E2E8F0',
    },
    actionsContainer: {
        paddingHorizontal: 24,
        gap: 12,
    },
    btnPrimary: {
        backgroundColor: Colors.primary,
        height: 54,
        borderRadius: 27, // Pill Shape
        flexDirection: 'row',
        justifyContent: 'center',
        alignItems: 'center',
        gap: 8,
        elevation: 3,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.25,
        shadowRadius: 8,
    },
    btnPrimaryText: {
        color: '#FFFFFF',
        fontSize: 16,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
    btnSecondary: {
        backgroundColor: Colors.primaryLight,
        height: 54,
        borderRadius: 27, // Pill Shape
        flexDirection: 'row',
        justifyContent: 'center',
        alignItems: 'center',
        gap: 8,
    },
    btnSecondaryText: {
        color: Colors.primary,
        fontSize: 15,
        fontFamily: 'PlusJakartaSans_600SemiBold',
    },
    footerPrompt: {
        flexDirection: 'row',
        justifyContent: 'center',
        alignItems: 'center',
        marginTop: 6,
    },
    footerPromptText: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textLight,
    },
    footerPromptLink: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.primary,
    },
});

export default WelcomeScreen;
