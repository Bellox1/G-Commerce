import React from 'react';
import { View, Text, ScrollView, StyleSheet } from 'react-native';

export default class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { error: null };
    }

    static getDerivedStateFromError(error) {
        return { error };
    }

    componentDidCatch(error, errorInfo) {
        console.error('[ErrorBoundary]', error, errorInfo);
    }

    render() {
        if (this.state.error) {
            return (
                <View style={styles.container}>
                    <Text style={styles.title}>Erreur de rendu</Text>
                    <ScrollView style={styles.scroll}>
                        <Text style={styles.message}>{String(this.state.error && this.state.error.stack || this.state.error)}</Text>
                    </ScrollView>
                </View>
            );
        }
        return this.props.children;
    }
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#7F1D1D',
        padding: 20,
        paddingTop: 60,
    },
    title: {
        color: '#FFFFFF',
        fontSize: 20,
        fontWeight: 'bold',
        marginBottom: 16,
    },
    scroll: {
        flex: 1,
    },
    message: {
        color: '#FECACA',
        fontSize: 12,
        fontFamily: 'monospace',
    },
});
