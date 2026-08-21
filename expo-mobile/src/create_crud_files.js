const fs = require('fs');
const path = require('path');

const srcDir = '/home/bellox/Busness/E-Stock/expo-mobile/src/screens';

const templateCreate = (moduleName, fields, endpoint) => `import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, ScrollView, Alert, ActivityIndicator } from 'react-native';
import client from '../../api/client';
import Colors from '../../theme/Colors';

const Create${moduleName}Screen = ({ navigation, route }) => {
  const editing = route?.params?.item;
  const [loading, setLoading] = useState(false);
  const [form, setForm] = useState(editing || {});

  const handleSubmit = async () => {
    try {
      setLoading(true);
      if (editing) {
        await client.put(\`/${endpoint}/\${editing.id}\`, form);
      } else {
        await client.post('/${endpoint}', form);
      }
      Alert.alert('Succès', editing ? 'Modifié avec succès' : 'Créé avec succès');
      navigation.goBack();
    } catch (e) {
      Alert.alert('Erreur', e.response?.data?.message || 'Une erreur est survenue');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={styles.container}>
      {/* TODO: Add fields for ${fields} */}
      <TouchableOpacity style={styles.btn} onPress={handleSubmit} disabled={loading}>
        {loading ? <ActivityIndicator color="#fff" /> : <Text style={styles.btnText}>{editing ? 'Modifier' : 'Créer'}</Text>}
      </TouchableOpacity>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#fff', padding: 16 },
  btn: { backgroundColor: '#105e49', padding: 16, borderRadius: 12, alignItems: 'center', marginTop: 24 },
  btnText: { color: '#fff', fontWeight: '700', fontSize: 16 },
});

export default Create${moduleName}Screen;
`;

const templateShow = (moduleName) => `import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import Colors from '../../theme/Colors';

const Show${moduleName}Screen = ({ navigation, route }) => {
  const { id } = route?.params || {};
  return (
    <ScrollView style={styles.container}>
      <Text style={styles.title}>Détail ${moduleName} {id}</Text>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#fff', padding: 16 },
  title: { fontSize: 24, fontWeight: 'bold' }
});

export default Show${moduleName}Screen;
`;

const templateMouvements = () => `import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, ActivityIndicator } from 'react-native';
import client from '../../api/client';
import Colors from '../../theme/Colors';

const MouvementsScreen = () => {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    client.get('/stock/mouvements').then(res => {
      setData(res.data);
      setLoading(false);
    }).catch(e => {
      console.error(e);
      setLoading(false);
    });
  }, []);

  return (
    <View style={styles.container}>
      {loading ? <ActivityIndicator size="large" color="#105e49" /> : (
        <FlatList data={data} keyExtractor={item => item.id.toString()} renderItem={({item}) => <Text>{item.id}</Text>} />
      )}
    </View>
  );
};

const styles = StyleSheet.create({ container: { flex: 1, backgroundColor: '#fff', padding: 16 } });
export default MouvementsScreen;
`;

const files = [
    { path: 'produits/create.js', content: templateCreate('Produit', 'nom, description, prix_achat, prix_vente, stock, seuil_alerte, categorie, magasin_id', 'produits') },
    { path: 'produits/show.js', content: templateShow('Produit') },
    { path: 'clients/create.js', content: templateCreate('Client', 'nom, telephone, email, adresse', 'clients') },
    { path: 'clients/show.js', content: templateShow('Client') },
    { path: 'arrivages/create.js', content: templateCreate('Arrivage', 'fournisseur_id, date_arrivage, lignes de produits', 'arrivages') },
    { path: 'arrivages/show.js', content: templateShow('Arrivage') },
    { path: 'dettes/create.js', content: templateCreate('Dette', 'client_id, montant, date_echeance', 'dettes') },
    { path: 'dettes/show.js', content: templateShow('Dette') },
    { path: 'dettes-societe/create.js', content: templateCreate('DetteSociete', 'fournisseur/description, montant', 'dettes-societe') },
    { path: 'employes/create.js', content: templateCreate('Employe', 'nom, prenom, role, salaire, telephone', 'employes') },
    { path: 'magasins/create.js', content: templateCreate('Magasin', 'nom, adresse, loyer', 'magasins') },
    { path: 'transferts/create.js', content: templateCreate('Transfert', 'magasin_source_id, magasin_destination_id, produit_id, quantite', 'transferts') },
    { path: 'stock/mouvements.js', content: templateMouvements() },
    { path: 'ventes/show.js', content: templateShow('Vente') }
];

files.forEach(file => {
    fs.writeFileSync(path.join(srcDir, file.path), file.content, 'utf8');
    console.log('Created ' + file.path);
});
