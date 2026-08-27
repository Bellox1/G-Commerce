import React, { useRef, useEffect, useState } from 'react';
import {
    View, Text, StyleSheet, TouchableOpacity,
    Dimensions, ScrollView, Image, StatusBar
} from 'react-native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

const { width } = Dimensions.get('window');

const slides = [
    {
        bgColor: '#FFFFFF',
        img: require('../../../assets/gestion.png'),
        title: 'Gestion Intelligente',
        subtitle: 'Pilotez vos stocks, vos ventes et vos créances en temps réel avec une simplicité absolue.',
    },
    {
        bgColor: '#FFFFFF',
        img: require('../../../assets/facture.png'),
        title: 'Ventes & Facturation',
        subtitle: 'Enregistrez vos transactions instantanément et suivez l\'évolution de votre chiffre d\'affaires.',
    },
    {
        bgColor: '#FFFFFF',
        img: require('../../../assets/chiffre_affaire.png'),
        title: 'Stock & Suivi Arrivages',
        subtitle: 'Anticipez les ruptures et gardez un contrôle total sur l\'ensemble de vos magasins.',
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

                {/* Secondary Pill */}
                <TouchableOpacity
                    style={styles.btnSecondary}
                    onPress={() => navigation.navigate('Login')}
                    activeOpacity={0.7}
                >
                    <Ionicons name="person-outline" size={18} color={Colors.primary} />
                    <Text style={styles.btnSecondaryText}>Accéder à mon espace</Text>
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
        width: width * 0.72,
        height: width * 0.65,
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: '#F8FAFC',
        borderRadius: 24,
        padding: 24,
        marginBottom: 28,
        borderWidth: 1,
        borderColor: '#F1F5F9',
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
