// Contenu FAQ synchronisé avec resources/views/faq.blade.php (web)
// Syntaxe inline : **gras**  et  [badge:TYPE:Libellé]  (TYPE: success|warning|danger|gray|info)

const FAQ_DATA = [
  {
    id: 'dashboard',
    label: 'Accueil',
    icon: 'grid-outline',
    title: 'Accueil',
    sections: [
      {
        q: `À quoi sert le tableau de bord ?`,
        blocks: [
          { t: 'p', text: `C'est votre **centre de contrôle principal**. Il affiche en un coup d'œil l'ensemble des informations clés de votre société : chiffre d'affaires, dépenses, alertes de stock, dettes en retard, ventes récentes et performance de votre équipe. Vous pouvez naviguer entre les jours avec le sélecteur de date pour consulter les données de n'importe quel jour.` },
          { t: 'info', text: `**Qui voit quoi :** Seul le **DG** (Directeur Général) a accès à l'ensemble des données chiffrées du tableau de bord (CA, marges, revenus, dépenses). Les vendeurs et magasiniers ne voient que leur propre activité.` },
        ],
      },
      {
        q: `Comment lire les statistiques ?`,
        blocks: [
          { t: 'p', text: `**Visibles par le DG uniquement :**` },
          { t: 'ul', items: [
            `**Ventes du jour** : total encaissé ce jour par l'ensemble des vendeurs`,
            `**Ventes du mois** : cumul total des encaissements depuis le 1er du mois`,
            `**Dépenses du jour** : total des dépenses enregistrées aujourd'hui (loyers, achats, salaires...)`,
            `**Dépenses du mois** : cumul des dépenses depuis le 1er du mois`,
            `**CA Net** : ventes du jour minus dépenses du jour — votre bénéfice brut du jour`,
            `**Revenu net mensuel** : CA net du mois minus loyers de tous les magasins — ce qu'il vous reste réellement`,
            `**Total dettes** : montant total des créances clients non soldées (toutes dettes confondues)`,
            `**Dettes en retard** : nombre de dettes dont l'échéance est dépassée`,
          ] },
          { t: 'p', text: `**Visible par les vendeurs :**` },
          { t: 'ul', items: [
            `Leur propre chiffre d'affaires du jour (montant personnel)`,
            `Le nombre de ventes qu'ils ont réalisées`,
          ] },
        ],
      },
      {
        q: `Comment changer la date affichée ?`,
        blocks: [
          { t: 'p', text: `En haut du tableau de bord, vous voyez un **sélecteur de date**. Cliquez dessus et choisissez la date souhaitée. Toutes les données (ventes, dépenses, ventes récentes, performance par personne) se recalculent automatiquement pour cette date. Par défaut, c'est la date du jour qui est affichée.` },
          { t: 'tip', text: `Pour vérifier les ventes d'un jour précis (ex : la semaine dernière), changez simplement la date et les stats se mettent à jour instantanément.` },
        ],
      },
      {
        q: `Comment enregistrer une dépense rapide ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Dans la section **"Dépenses du jour"** du tableau de bord, cliquez sur le bouton **"Nouvelle dépense"**.` },
            { num: 2, text: `Entrez le **montant** de la dépense.` },
            { num: 3, text: `Ajoutez une **description** (optionnel mais recommandé) : ex "Achat cartons", "Facture électricité", "Pain pour le personnel".` },
            { num: 4, text: `**Message vocal (optionnel)** : appuyez sur le bouton **micro** pour enregistrer un message audio décrivant la dépense. Utile si vous êtes pressé ou si vous préférez parler plutôt que taper.` },
            { num: 5, text: `Validez. La dépense apparaît immédiatement dans la liste, et les statistiques (CA Net, Dépenses du jour) se mettent à jour.` },
          ] },
        ],
      },
      {
        q: `Comment voir les produits en alerte de stock ?`,
        blocks: [
          { t: 'p', text: `La section **"Alertes stock"** du tableau de bord affiche automatiquement les produits dont le stock est inférieur ou égal au seuil d'alerte que vous avez défini. Le système calcule le stock en temps réel en additionnant :` },
          { t: 'ul', items: [
            `Le stock initial du produit`,
            `Plus les entrées (arrivages validés)`,
            `Minus les sorties (ventes, transferts sortants)`,
            `Plus/Moins les ajustements`,
          ] },
          { t: 'tip', text: `Si un produit affiche un stock de **0** ou en négatif, c'est qu'il y a un problème de synchronisation des entrées/sorties. Vérifiez les arrivages et transferts.` },
        ],
      },
      {
        q: `Comment suivre la performance de mon équipe ?`,
        blocks: [
          { t: 'p', text: `La section **"Performance par collaborateur"** affiche le chiffre d'affaires réalisé par **chaque vendeur** dans la journée sélectionnée. C'est un tableau avec le nom du vendeur et son montant total de ventes.` },
          { t: 'p', text: `La section **"Collaborateurs"** affiche aussi le statut de connexion de vos employés : [badge:success:En ligne] si connecté récemment, ou la date/heure de leur dernière connexion.` },
        ],
      },
      {
        q: `Que sont les "Dernières ventes" ?`,
        blocks: [
          { t: 'p', text: `Cette section affiche les **5 dernières ventes enregistrées** dans la journée, avec le nom du client, le montant, le vendeur et l'heure. C'est un aperçu rapide pour vérifier que tout se passe bien dans la journée.` },
        ],
      },
      {
        q: `Que sont les "Produits les plus vendus" ?`,
        blocks: [
          { t: 'p', text: `Le tableau affiche le **Top 5 des produits les plus vendus** du mois en cours, classés par quantité totale vendue. Cela vous aide à identifier vos produits phares et à anticiper les réapprovisionnements.` },
        ],
      },
    ],
  },

  {
    id: 'produits',
    label: 'Produits',
    icon: 'cube-outline',
    title: 'Produits',
    sections: [
      {
        q: `À quoi sert l'onglet Produits ?`,
        blocks: [
          { t: 'p', text: `C'est le **catalogue complet** de tous les produits que vous commercialisez. Chaque produit a une fiche avec son nom, ses prix (achat et vente), son seuil d'alerte, et éventuellement une image. C'est ici que vous créez, modifiez et organisez votre catalogue avant de pouvoir les vendre ou les réceptionner via les arrivages.` },
        ],
      },
      {
        q: `Comment ajouter un nouveau produit ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Allez dans **Produits** puis cliquez sur le bouton **"Nouveau"**.` },
            { num: 2, text: `**Nom du produit** : donnez un nom clair et reconnaissable (ex : "Savon Noir 250g", "Téléphone Samsung A14").` },
            { num: 3, text: `**Prix de vente** : le prix que le client paiera. C'est ce prix qui sera utilisé automatiquement lors des ventes.` },
            { num: 4, text: `**Prix d'achat** : le prix auquel vous achetez ce produit chez votre fournisseur. Utilisé pour calculer vos marges.` },
            { num: 5, text: `**Seuil d'alerte** : la quantité minimale en stock. En dessous, le produit apparaît en alerte rouge sur le tableau de bord.` },
            { num: 6, text: `**Image** (optionnel) : ajoutez une photo du produit pour l'identifier visuellement.` },
            { num: 7, text: `Validez. Le produit apparaît dans la liste et est maintenant utilisable dans les arrivages et les ventes.` },
          ] },
        ],
      },
      {
        q: `Comment modifier un produit existant ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Dans la liste des produits, cliquez sur le nom du produit à modifier.` },
            { num: 2, text: `Cliquez sur le bouton **"Modifier"** (icône crayon).` },
            { num: 3, text: `Changez le champ souhaité (prix, nom, seuil...).` },
            { num: 4, text: `Sauvegardez. Le nouveau prix s'appliquera **uniquement aux prochaines ventes**. Les ventes déjà enregistrées conservent leur prix d'origine.` },
          ] },
        ],
      },
      {
        q: `Comment rechercher ou filtrer mes produits ?`,
        blocks: [
          { t: 'p', text: `Plusieurs options de filtrage :` },
          { t: 'ul', items: [
            `**Barre de recherche** : tapez le nom ou une partie du nom pour filtrer en temps réel`,
            `**Tri par colonnes** : cliquez sur les en-têtes du tableau (nom, prix, stock) pour trier ascendant/descendant`,
          ] },
        ],
      },
      {
        q: `Comment supprimer un produit ?`,
        blocks: [
          { t: 'p', text: `Cliquez sur le produit, puis sur **"Supprimer"** et confirmez. Attention : un produit qui a déjà été vendu ou réceptionné via un arrivage ne pourra pas être supprimé (pour préserver l'historique). Vous pourrez uniquement le désactiver.` },
        ],
      },
    ],
    notes: [
      { t: 'tip', text: `**Bonnes pratiques :** Définissez un seuil d'alerte adapté à votre rythme de vente. Un produit qui se vend rapidement nécessite un seuil plus élevé. Vérifiez régulièrement que vos prix de vente sont toujours compétitifs.` },
    ],
  },

  {
    id: 'arrivages',
    label: 'Arrivages',
    icon: 'cart-outline',
    title: 'Arrivages',
    sections: [
      {
        q: `À quoi sert l'onglet Arrivages ?`,
        blocks: [
          { t: 'p', text: `C'est ici que vous enregistrez **toutes les entrées de marchandises** dans vos magasins. Chaque fois que vous recevez des produits d'un fournisseur, vous créez un arrivage. C'est la seule façon de faire entrer du nouveau stock dans le système. Les arrivages sont liés à un fournisseur et à un magasin de destination.` },
        ],
      },
      {
        q: `Comment enregistrer un nouvel arrivage ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Allez dans **Arrivages** puis cliquez sur **"Nouvel arrivage"**.` },
            { num: 2, text: `**Fournisseur** (optionnel) : sélectionnez le fournisseur chez qui vous avez acheté. Si c'est un nouveau fournisseur, vous pouvez l'ajouter directement depuis le formulaire.` },
            { num: 3, text: `**Magasin de destination** : choisissez le magasin qui réceptionne la marchandise. Le stock de CE magasin sera augmenté.` },
            { num: 4, text: `**Ajoutez les produits** : pour chaque produit reçu, indiquez :`, items: [
              `Le **produit** (recherchez par nom dans votre catalogue)`,
              `La **quantité reçue**`,
              `Le **prix d'achat unitaire** (ce que vous avez payé au fournisseur)`,
            ] },
            { num: 5, text: `Le système peut **suggérer un prix de vente** basé sur votre marge habituelle. Vous pouvez accepter ou modifier cette suggestion.` },
            { num: 6, text: `Validez l'arrivage. Le stock du magasin est automatiquement mis à jour.` },
          ] },
        ],
      },
      {
        q: `Quelle différence entre "Brouillon" et "Validé" ?`,
        blocks: [
          { t: 'ul', items: [
            `**Brouillon** : l'arrivage est en cours de préparation. Les quantités ne sont PAS encore ajoutées au stock. Vous pouvez encore modifier les produits et quantités. C'est l'état par défaut lors de la création.`,
            `**Validé** : l'arrivage est confirmé et définitif. Le stock du magasin cible est automatiquement augmenté. Un mouvement de type "Entrée arrivage" est créé dans les mouvements de stock. **Vous ne pouvez plus modifier un arrivage validé.**`,
          ] },
          { t: 'tip', text: `Utilisez le statut "Brouillon" pour préparer votre arrivage tranquillement. Une fois que tout est correct, validez-le pour que les quantités soient comptabilisées dans le stock.` },
        ],
      },
      {
        q: `Comment modifier un arrivage en brouillon ?`,
        blocks: [
          { t: 'p', text: `Cliquez sur l'arrivage dans la liste, puis sur **"Modifier"**. Vous pouvez changer le fournisseur, le magasin, ajouter ou retirer des produits, modifier les quantités et les prix d'achat. Sauvegardez vos modifications. Tant que l'arrivage est en brouillon, vous pouvez le modifier librement.` },
        ],
      },
      {
        q: `Comment supprimer un arrivage ?`,
        blocks: [
          { t: 'ul', items: [
            `**Arrivage en brouillon** : vous pouvez le supprimer librement, aucune incidence sur le stock.`,
            `**Arrivage validé** : la suppression est possible mais **le stock sera automatiquement diminué** des quantités correspondantes. Utilisez cette option si l'arrivage a été validé par erreur.`,
          ] },
          { t: 'warn', text: `La suppression d'un arrivage validé est **irréversible**. Assurez-vous de bien comprendre l'impact avant de confirmer.` },
        ],
      },
      {
        q: `Puis-je ajouter un nouveau fournisseur depuis l'arrivage ?`,
        blocks: [
          { t: 'p', text: `Oui. Lors de la création d'un arrivage, si votre fournisseur n'existe pas encore dans la liste, vous pouvez l'ajouter directement depuis le formulaire d'arrivage. Il sera ensuite disponible pour tous les prochains arrivages.` },
        ],
      },
    ],
  },

  {
    id: 'ventes',
    label: 'Ventes',
    icon: 'cart-outline',
    title: 'Ventes',
    sections: [
      {
        q: `À quoi sert l'onglet Ventes ?`,
        blocks: [
          { t: 'p', text: `C'est le cœur de votre activité commerciale. C'est ici que vous enregistrez **chaque transaction** avec vos clients. Chaque vente créée :` },
          { t: 'ul', items: [
            `Génère une **facture** imprimable`,
            `**Diminue automatiquement** le stock du magasin source`,
            `Crée une **dette** si le client ne paie pas tout de suite`,
            `Enregistre le **vendeur** responsable de la vente`,
          ] },
        ],
      },
      {
        q: `Comment créer une vente pas à pas ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Allez dans **Ventes** puis cliquez sur **"Nouvelle vente"**.` },
            { num: 2, text: `**Client** : sélectionnez un client existant dans la liste ou créez-en un nouveau. Si c'est une vente comptant au comptoir, vous pouvez laisser le champ vide.` },
            { num: 3, text: `**Magasin source** : choisissez le magasin depuis lequel la marchandise est prélevée. Le stock de CE magasin sera diminué.` },
            { num: 4, text: `**Ajoutez les produits** : recherchez un produit par son nom dans la barre de recherche. Sélectionnez-le, puis indiquez la **quantité**. Le système vérifie automatiquement que le stock est suffisant.` },
            { num: 5, text: `**Total** : le montant total se calcule automatiquement (prix de vente × quantité pour chaque article).` },
            { num: 6, text: `**Montant payé** : entrez ce que le client paie maintenant :`, items: [
              `**Total** : le client paie tout → vente comptant`,
              `**Partiel** : le client avance une somme → acompte avec dette`,
              `**0** : le client ne paie rien → dette totale (crédit)`,
            ] },
            { num: 7, text: `Validez. La facture est générée, le stock est diminué, et si crédit, la dette est créée automatiquement.` },
          ] },
        ],
      },
      {
        q: `Que signifie "vente à crédit" ?`,
        blocks: [
          { t: 'p', text: `Quand un client ne paie pas tout de suite (ou paie une partie), c'est une **vente à crédit**. Le mécanisme :` },
          { t: 'ol', items: [
            `Vous créez la vente avec le montant payé = 0 ou partiel`,
            `Le système crée automatiquement une **dette** pour ce client égale au reste à payer`,
            `La dette apparaît dans l'onglet **Dettes**`,
            `Quand le client paie, vous enregistrez le paiement depuis l'onglet Dettes`,
            `La dette est mise à jour ou soldée`,
          ] },
        ],
      },
      {
        q: `Comment imprimer une facture ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Après avoir créé une vente ou en consultant une vente existante, cliquez sur le bouton **"Imprimer"**.` },
            { num: 2, text: `Une facture professionnelle s'ouvre dans un **nouvel onglet** avec toutes les informations : nom de la société, coordonnées du client, liste des articles, montant total, montant payé, reste à payer.` },
            { num: 3, text: `Utilisez **Ctrl+P** (ou Cmd+P sur Mac) pour l'imprimer ou l'envoyer en PDF.` },
          ] },
        ],
      },
      {
        q: `Comment annuler ou supprimer une vente ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Allez dans le détail de la vente concernée.` },
            { num: 2, text: `Cliquez sur **"Supprimer"**.` },
            { num: 3, text: `Confirmez la suppression.` },
            { num: 4, text: `**Effet :** le stock est automatiquement **restauré** (les quantités reviennent dans le magasin). Si une dette était liée à cette vente, elle est aussi supprimée.` },
          ] },
          { t: 'warn', text: `Cette action est **irréversible**. Assurez-vous vraiment avant de supprimer une vente.` },
        ],
      },
      {
        q: `Le système alerte-t-il en cas de stock insuffisant ?`,
        blocks: [
          { t: 'p', text: `Oui. Lorsque vous ajoutez un produit à la vente, le système vérifie automatiquement la **quantité disponible** dans le magasin source. Si vous essayez de vendre plus que ce qui est en stock, un message d'erreur vous empêche de valider la vente. Cela évite les stocks négatifs.` },
          { t: 'tip', text: `Si le stock est insuffisant, créez d'abord un **transfert** depuis un autre magasin qui a le produit, ou enregistrez un **arrivage** pour réapprovisionner.` },
        ],
      },
    ],
  },

  {
    id: 'livraisons',
    label: 'Livraisons',
    icon: 'bicycle-outline',
    title: 'Livraisons',
    sections: [
      {
        q: `À quoi sert l'onglet Livraisons ?`,
        blocks: [
          { t: 'p', text: `C'est le **centre de suivi** de toutes les livraisons en cours. C'est le **contrôleur** qui contrôle les livraisons : chaque vente nécessitant une livraison apparaît ici avec son statut, le client à livrer, l'adresse et le contrôleur assigné. Vous pouvez suivre l'avancement en temps réel.` },
        ],
      },
      {
        q: `Comment fonctionne le flux de livraison ?`,
        blocks: [
          { t: 'ol', items: [
            `**Vente créée** avec statut livraison "En attente" → elle apparaît dans la liste des livraisons`,
            `**Assignation** : le DG ou le responsable sélectionne un controleur pour prendre en charge`,
            `**En cours** : le controleur est en route, il peut appeler le client en un clic`,
            `**Livré** : le client a réceptionné le colis → la livraison est terminée`,
            `**Problème** : le controleur signale un souci (client absent, adresse erronée...)`,
          ] },
        ],
      },
      {
        q: `Les différents statuts de livraison`,
        blocks: [
          { t: 'ul', items: [
            `[badge:gray:En attente] : la livraison n'a pas encore été prise en charge par un controleur. C'est le statut par défaut.`,
            `[badge:warning:En cours] : le controleur a accepté la livraison et est en route vers le client.`,
            `[badge:success:Livré] : le client a bien reçu sa commande. La livraison est terminée avec succès.`,
            `[badge:danger:Problème signalé] : le controleur a rencontré un souci (client absent, mauvaise adresse, colis endommagé...). Une description de l'incident est enregistrée.`,
          ] },
        ],
      },
      {
        q: `Comment assigner un controleur ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Dans la liste des livraisons, repérez la livraison "En attente".` },
            { num: 2, text: `Cliquez dessus pour voir le détail.` },
            { num: 3, text: `Sélectionnez un employé avec le rôle **"Contrôleur"** dans la liste déroulante.` },
            { num: 4, text: `Le controleur verra la commande apparaître dans son interface et pourra commencer la livraison.` },
          ] },
        ],
      },
      {
        q: `Que fait le controleur concrètement ?`,
        blocks: [
          { t: 'p', text: `Depuis son interface (accessible sur mobile), le controleur peut :` },
          { t: 'ul', items: [
            `Voir la liste de ses livraisons du jour avec les adresses et contacts clients`,
            `**Appeler le client** en un clic pour confirmer la livraison`,
            `**Valider** la livraison une fois le colis remis`,
            `**Signaler un problème** si le client est absent ou s'il y a un souci`,
            `Consulter l'historique de ses tournées`,
          ] },
        ],
      },
    ],
  },

  {
    id: 'clients',
    label: 'Clients',
    icon: 'people-outline',
    title: 'Clients',
    sections: [
      {
        q: `À quoi sert l'onglet Clients ?`,
        blocks: [
          { t: 'p', text: `C'est votre **annuaire commercial**. Vous y gérez tous vos clients : leurs coordonnées, leur historique de ventes, leurs dettes et leurs paiements. C'est aussi ici que vous créez de nouveaux clients lorsqu'un nouveau acheteur se présente.` },
        ],
      },
      {
        q: `Comment ajouter un nouveau client ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Allez dans **Clients** puis cliquez sur **"Nouveau client"**.` },
            { num: 2, text: `**Nom** : le nom complet ou le nom d'usage du client.` },
            { num: 3, text: `**Téléphone** (obligatoire) : le numéro de téléphone. C'est le champ le plus important car il permet de contacter le client pour les livraisons et les relances de dettes.` },
            { num: 4, text: `**Adresse** (optionnel) : l'adresse physique du client, utile pour les livraisons.` },
            { num: 5, text: `**Ville** (optionnel) : la ville de résidence du client.` },
            { num: 6, text: `**Email** (optionnel) : l'adresse email du client.` },
            { num: 7, text: `Validez. Le client est maintenant disponible lors de la création d'une vente.` },
          ] },
        ],
      },
      {
        q: `Que contient la fiche d'un client ?`,
        blocks: [
          { t: 'p', text: `En cliquant sur un client, vous accédez à sa **fiche complète** :` },
          { t: 'ul', items: [
            `**Coordonnées** : nom, téléphone (cliquable pour appeler), adresse, ville, email`,
            `**Historique des ventes** : toutes les ventes réalisées avec ce client, avec dates et montants`,
            `**Solde des dettes** : montant total dû par le client`,
            `**Paiements enregistrés** : tous les versements effectués par le client`,
            `**Crédit restant** : combien il reste à payer`,
          ] },
        ],
      },
      {
        q: `Comment rechercher un client ?`,
        blocks: [
          { t: 'p', text: `Utilisez la barre de recherche en haut du tableau. Vous pouvez chercher par :` },
          { t: 'ul', items: [
            `**Nom** : tapez les premières lettres du nom`,
            `**Téléphone** : tapez le numéro de téléphone`,
          ] },
          { t: 'p', text: `La recherche se fait en **temps réel** — les résultats se filtrent au fur et à mesure que vous tapez.` },
        ],
      },
      {
        q: `Comment modifier les informations d'un client ?`,
        blocks: [
          { t: 'p', text: `Cliquez sur le client, puis sur **"Modifier"**. Vous pouvez changer n'importe quel champ (nom, téléphone, adresse...). Sauvegardez. Les modifications s'appliqueront immédiatement, y compris pour les futures ventes et livraisons.` },
        ],
      },
    ],
  },

  {
    id: 'dettes',
    label: 'Dettes',
    icon: 'wallet-outline',
    title: 'Dettes',
    sections: [
      {
        q: `À quoi sert l'onglet Dettes ?`,
        blocks: [
          { t: 'p', text: `C'est l'outil de **suivi des créances clients**. Chaque fois qu'un client achète à crédit (paie une partie ou rien), une dette est créée. Cet onglet vous permet de voir toutes les dettes, enregistrer les paiements, définir des échéances et suivre les retards. C'est essentiel pour votre trésorerie.` },
        ],
      },
      {
        q: `Comment sont créées les dettes ?`,
        blocks: [
          { t: 'p', text: `Une dette est créée **automatiquement** lorsque vous enregistrez une vente avec un montant payé inférieur au total. Par exemple :` },
          { t: 'ul', items: [
            `Vente de **50 000 FCFA**, le client paie **20 000 FCFA** → dette de **30 000 FCFA**`,
            `Vente de **50 000 FCFA**, le client ne paie rien → dette de **50 000 FCFA**`,
          ] },
          { t: 'p', text: `Vous n'avez **pas besoin** de créer les dettes manuellement. Le système s'en charge via les ventes à crédit.` },
        ],
      },
      {
        q: `Comment enregistrer un paiement ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Allez dans **Dettes** et repérez la dette du client dans la liste.` },
            { num: 2, text: `Cliquez sur la dette pour voir le détail.` },
            { num: 3, text: `Cliquez sur **"Enregistrer un paiement"**.` },
            { num: 4, text: `Entrez le **montant versé** par le client.` },
            { num: 5, text: `Validez. La dette est mise à jour :`, items: [
              `Si le montant versé = reste à payer → la dette passe en statut **"Soldé"**`,
              `Si le montant versé < reste à payer → la dette reste active avec le nouveau solde`,
            ] },
          ] },
        ],
      },
      {
        q: `Les différents statuts des dettes`,
        blocks: [
          { t: 'ul', items: [
            `[badge:warning:En cours] : le client a encore du temps pour payer. C'est le statut par défaut d'une nouvelle dette.`,
            `[badge:gray:Partiel] : un paiement partiel a été enregistré. Il reste encore du montant à payer.`,
            `[badge:danger:En retard] : la date d'échéance est dépassée et le client n'a pas tout payé. Ces dettes apparaissent en alerte sur le tableau de bord.`,
            `[badge:success:Soldé] : la totalité a été payée. La dette est clôturée.`,
          ] },
        ],
      },
      {
        q: `Comment définir une échéance ?`,
        blocks: [
          { t: 'p', text: `Sur la fiche d'une dette, vous pouvez définir une **date d'échéance**. Passée cette date, si la dette n'est pas entièrement payée :` },
          { t: 'ul', items: [
            `Le statut passe automatiquement en **"En retard"**`,
            `La dette apparaît dans les **alertes du tableau de bord**`,
            `Vous pouvez utiliser cette information pour relancer le client`,
          ] },
          { t: 'tip', text: `Définissez des échéances réalistes (ex : 15 jours, 30 jours) pour mieux gérer votre trésorerie et relancer les clients en temps utile.` },
        ],
      },
      {
        q: `Comment voir les dettes en retard ?`,
        blocks: [
          { t: 'p', text: `Deux endroits :` },
          { t: 'ul', items: [
            `**Tableau de bord** : la section "Dettes en retard" affiche les 10 dettes les plus anciennes dont l'échéance est dépassée, avec le nom du client et le montant dû.`,
            `**Onglet Dettes** : filtrez par statut "En retard" pour voir la liste complète.`,
          ] },
        ],
      },
    ],
  },

  {
    id: 'mouvements',
    label: 'Mouvements',
    icon: 'swap-horizontal-outline',
    title: 'Mouvements de stock',
    sections: [
      {
        q: `À quoi sert l'onglet Mouvements ?`,
        blocks: [
          { t: 'p', text: `C'est le **journal complet** de toutes les entrées et sorties de stock dans un magasin. Chaque fois qu'un produit entre (arrivage, transfert entrée, ajustement positif) ou sort (vente, transfert sorti, ajustement négatif), un mouvement est enregistré. C'est l'outil de **traçabilité** par excellence : vous savez exactement qui a fait quoi, quand, et en quelle quantité.` },
        ],
      },
      {
        q: `Comment consulter les mouvements ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Allez dans **Stock** puis cliquez sur **"Mouvements"**.` },
            { num: 2, text: `Sélectionnez le **magasin** dont vous voulez voir les mouvements.` },
            { num: 3, text: `Vous verrez la liste chronologique de tous les mouvements, du plus récent au plus ancien.` },
            { num: 4, text: `Chaque mouvement affiche : la **date**, le **produit**, le **type** de mouvement, la **quantité**, et **l'utilisateur** qui a effectué l'action.` },
          ] },
        ],
      },
      {
        q: `Les différents types de mouvements`,
        blocks: [
          { t: 'p', text: `**Entrées (+) :**` },
          { t: 'ul', items: [
            `[badge:success:Entrée arrivage] : réception de marchandise via un arrivage validé. Le stock augmente.`,
            `[badge:success:Transfert entrée] : un produit a été transféré depuis un autre magasin vers celui-ci. Le stock augmente.`,
            `[badge:success:Ajustement positif] : un ajustement manuel a été fait pour augmenter le stock (ex : inventaire physique trouvé plus de stock que prévu).`,
          ] },
          { t: 'p', text: `**Sorties (-) :**` },
          { t: 'ul', items: [
            `[badge:danger:Sortie vente] : un produit a été vendu. Le stock diminue.`,
            `[badge:danger:Transfert sorti] : un produit a été transféré vers un autre magasin. Le stock diminue.`,
            `[badge:danger:Ajustement négatif] : un ajustement manuel a été fait pour diminuer le stock (ex : produits cassés, périmés, volés).`,
          ] },
        ],
      },
      {
        q: `À quoi servent les ajustements ?`,
        blocks: [
          { t: 'p', text: `Les ajustements permettent de **corriger le stock** quand il ne correspond pas à la réalité physique. Scénarios courants :` },
          { t: 'ul', items: [
            `**Inventaire physique** : vous comptez les produits réellement et trouvez un écart avec le stock affiché`,
            `**Produits cassés** : un produit s'est cassé en magasin → ajustement négatif`,
            `**Produits périmés** : un produit a expiré → ajustement négatif`,
            `**Vol** : un produit a été volé → ajustement négatif`,
            `**Erreur de saisie** : une vente ou un arrivage a été enregistré en quantité incorrecte → ajustement pour corriger`,
          ] },
          { t: 'warn', text: `Chaque ajustement est **tracé** dans l'historique avec l'utilisateur responsable. Utilisez les ajustements avec parcimonie.` },
        ],
      },
      {
        q: `Comment faire un ajustement ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Dans la page **Stock**, cliquez sur **"Ajuster"**.` },
            { num: 2, text: `Sélectionnez le **magasin** et le **produit** concerné.` },
            { num: 3, text: `Choisissez le type : **positif** (ajouter) ou **négatif** (retirer).` },
            { num: 4, text: `Indiquez la **quantité** à ajuster.` },
            { num: 5, text: `Validez. Le mouvement est enregistré dans l'historique et le stock est mis à jour.` },
          ] },
        ],
      },
    ],
  },

  {
    id: 'stock',
    label: 'Stock',
    icon: 'layers-outline',
    title: 'Stock',
    sections: [
      {
        q: `À quoi sert l'onglet Stock ?`,
        blocks: [
          { t: 'p', text: `C'est la **vue en temps réel** de l'état de vos stocks par magasin. Vous y voyez pour chaque produit la quantité disponible dans un magasin donné, les produits en alerte (stock bas), et vous pouvez lancer des ajustements. Le stock est calculé automatiquement : stock initial + entrées - sorties ± ajustements.` },
        ],
      },
      {
        q: `Comment consulter le stock d'un magasin ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Allez dans **Stock**.` },
            { num: 2, text: `Sélectionnez le **magasin** souhaité dans le filtre en haut de la page.` },
            { num: 3, text: `Vous voyez la liste de tous les produits avec leurs **quantités disponibles**. Les produits dont le stock est ≤ au seuil d'alerte sont mis en évidence **en rouge**.` },
            { num: 4, text: `Vous pouvez cliquer sur un produit pour voir son détail et les mouvements associés.` },
          ] },
        ],
      },
      {
        q: `Comment est calculé le stock ?`,
        blocks: [
          { t: 'p', text: `Le stock affiché est le résultat d'un **calcul dynamique** :` },
          { t: 'p', text: `**Stock disponible = Stock de base + Entrées - Sorties ± Ajustements**` },
          { t: 'ul', items: [
            `**Stock de base** : la quantité initiale du produit au moment de sa création`,
            `**+ Entrées** : tous les arrivages validés pour ce magasin`,
            `**- Sorties** : toutes les ventes et transferts sortants de ce magasin`,
            `**± Ajustements** : les corrections manuelles (positives ou négatives)`,
          ] },
          { t: 'p', text: `Le calcul est fait en **temps réel** — chaque nouvelle vente, arrivage ou transfert met à jour instantanément le stock affiché.` },
        ],
      },
      {
        q: `Les produits en alerte de stock`,
        blocks: [
          { t: 'p', text: `Un produit est en **alerte** lorsque son stock est inférieur ou égal au **seuil d'alerte** que vous avez défini. Ces produits apparaissent :` },
          { t: 'ul', items: [
            `En **rouge** dans la page Stock`,
            `Dans la section **"Alertes stock"** du tableau de bord`,
          ] },
          { t: 'p', text: `C'est un signal pour **réapprovisionner** ce produit via un nouvel arrivage ou un transfert depuis un autre magasin.` },
        ],
      },
      {
        q: `Que faire quand un produit est en alerte ?`,
        blocks: [
          { t: 'p', text: `Trois options :` },
          { t: 'ul', items: [
            `**Arrivage** : créez un nouvel arrivage pour réapprovisionner le magasin depuis un fournisseur`,
            `**Transfert** : si le produit est disponible dans un autre magasin, transférez-en la quantité nécessaire`,
            `**Ajustement** : si le stock physique est correct mais le stock affiché est faux, faites un ajustement pour corriger`,
          ] },
        ],
      },
    ],
  },

  {
    id: 'depots',
    label: 'Dépôts',
    icon: 'storefront-outline',
    title: 'Dépôts (Magasins)',
    sections: [
      {
        q: `À quoi sert l'onglet Dépôts ?`,
        blocks: [
          { t: 'p', text: `C'est ici que vous gérez vos **points de stockage physique**. Chaque dépôt (ou magasin) est un endroit où vous stockez et vendez vos produits. Pilotix est conçu pour la **multi-magasins** : vous pouvez en avoir autant que nécessaire (Magasin Principal, Entrepôt, Boutique de quartier...). Chaque dépôt a son propre stock, ses propres ventes et ses propres statistiques.` },
        ],
      },
      {
        q: `Comment ajouter un nouveau dépôt ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Allez dans **Dépôts** puis cliquez sur **"Nouveau"**.` },
            { num: 2, text: `**Nom du dépôt** : donnez un nom clair (ex : "Magasin Central", "Dépôt B", "Boutique Rue 12").` },
            { num: 3, text: `**Loyer mensuel** (optionnel) : le coût du loyer de ce local. Ce montant est automatiquement déduit du revenu net sur le tableau de bord.` },
            { num: 4, text: `Validez. Le dépôt est maintenant utilisable comme source ou destination pour les arrivages, ventes et transferts.` },
          ] },
        ],
      },
      {
        q: `Pourquoi avoir plusieurs dépôts ?`,
        blocks: [
          { t: 'p', text: `La multi-magasin vous permet de :` },
          { t: 'ul', items: [
            `**Suivre le stock indépendamment** par point de vente — vous savez exactement ce qu'il y a dans chaque magasin`,
            `**Transférer des produits** entre vos points de stockage pour rééquilibrer l'offre`,
            `**Comparer la performance** de chaque magasin (ventes, rentabilité)`,
            `**Isoler les responsabilités** : chaque magasinier gère son propre stock`,
            `**Calculer la rentabilité** par magasin en prenant en compte le loyer de chacun`,
          ] },
        ],
      },
      {
        q: `Comment modifier ou supprimer un dépôt ?`,
        blocks: [
          { t: 'ul', items: [
            `**Modifier** : cliquez sur le dépôt, puis modifiez le nom ou le loyer. Sauvegardez.`,
            `**Supprimer** : cliquez sur "Supprimer" et confirmez. Attention : un dépôt qui contient encore des produits en stock ou des ventes associées ne pourra pas être supprimé pour préserver l'historique.`,
          ] },
          { t: 'warn', text: `Avant de supprimer un dépôt, transférez d'abord tout le stock restant vers un autre magasin via l'onglet **Transferts**.` },
        ],
      },
    ],
  },

  {
    id: 'employes',
    label: 'Personnel',
    icon: 'people-circle-outline',
    title: 'Personnel',
    sections: [
      {
        q: `À quoi sert l'onglet Personnel ?`,
        blocks: [
          { t: 'p', text: `C'est ici que vous gérez les **comptes utilisateurs** de votre équipe. Chaque employé a un compte avec un email et un mot de passe pour se connecter à Pilotix. Vous définissez leurs **rôles** pour contrôler ce que chacun peut voir et faire dans l'application. C'est un outil de sécurité essentiel pour protéger vos données commerciales.` },
        ],
      },
      {
        q: `Comment ajouter un nouveau membre ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Allez dans **Personnel** puis cliquez sur **"Nouveau membre"**.` },
            { num: 2, text: `**Nom** : le nom complet de l'employé (ex : "Amadou Diallo").` },
            { num: 3, text: `**Email** : l'adresse email qui servira d'identifiant de connexion. Chaque email doit être unique.` },
            { num: 4, text: `**Mot de passe** : definez un mot de passe sécurisé. L'employé pourra le changer plus tard depuis son profil.` },
            { num: 5, text: `**Rôle principal** : sélectionnez le rôle principal de l'employé :`, items: [
              `**Vendeur (Caissier)** : accès aux ventes et clients`,
              `**Magasinier** : accès au stock, arrivages et transferts`,
              `**Contrôleur** : accès aux livraisons assignées`,
            ] },
            { num: 6, text: `**Rôles secondaires** (optionnel) : un employé peut cumuler plusieurs rôles. Par exemple, un magasinier peut aussi avoir le rôle de vendeur s'il fait à la fois les stocks et les ventes. Cochez les rôles secondaires souhaités.` },
            { num: 7, text: `Validez. L'employé peut maintenant se connecter avec son email et mot de passe.` },
          ] },
        ],
      },
      {
        q: `Les différents rôles en détail`,
        blocks: [
          { t: 'p', text: `**Vendeur (Caissier) :**` },
          { t: 'ul', items: [
            `C'est le **caissier** de l'entreprise : il enregistre les ventes en caisse`,
            `Peut créer des ventes et des factures`,
            `Peut gérer les clients (ajout, modification)`,
            `Peut voir ses **propres** ventes et son chiffre d'affaires`,
            `Ne voit PAS les ventes des autres vendeurs`,
            `Ne voit PAS les données financières globales (CA total, marges, dépenses)`,
          ] },
          { t: 'p', text: `**Magasinier :**` },
          { t: 'ul', items: [
            `Peut gérer les stocks (consulter, ajuster)`,
            `Peut enregistrer les arrivages (réception de marchandises)`,
            `Peut créer des transferts entre magasins`,
            `Peut consulter l'historique des mouvements`,
            `Ne voit PAS les ventes ni les dettes clients`,
          ] },
          { t: 'p', text: `**Contrôleur :**` },
          { t: 'ul', items: [
            `Peut voir les livraisons qui lui sont assignées`,
            `Peut valider une livraison ou signaler un problème`,
            `Peut appeler le client directement depuis l'application`,
            `Ne voit PAS les ventes, stocks, ni données financières`,
          ] },
          { t: 'tip', text: `Un employé a toujours un **rôle principal** et peut en plus avoir des **rôles secondaires**. Par exemple, un magasinier qui fait aussi les ventes aura le rôle principal "Magasinier" et le rôle secondaire "Vendeur". Cela lui donne accès aux deux sections.` },
        ],
      },
      {
        q: `Quelle différence entre rôle principal et rôle secondaire ?`,
        blocks: [
          { t: 'ul', items: [
            `**Rôle principal** : le rôle principal de l'employé, celui qu'il exerce au quotidien. C'est obligatoire.`,
            `**Rôles secondaires** : des rôles supplémentaires que l'employé peut aussi exercer. C'est optionnel.`,
          ] },
          { t: 'p', text: `**Exemple concret :**` },
          { t: 'ul', items: [
            `Un magasinier qui fait aussi les ventes → rôle principal : **Magasinier**, rôle secondaire : **Vendeur**`,
            `Un vendeur qui aide aussi aux livraisons → rôle principal : **Vendeur**, rôle secondaire : **Contrôleur**`,
            `Un controleur qui peut aussi gérer les stocks → rôle principal : **Contrôleur**, rôle secondaire : **Magasinier**`,
          ] },
          { t: 'tip', text: `Lors de la création ou modification d'un employé, vous choisissez le rôle principal dans la liste déroulante, puis vous cochez les rôles secondaires souhaités.` },
        ],
      },
      {
        q: `Qui peut créer des membres du personnel ?`,
        blocks: [
          { t: 'p', text: `**Uniquement le DG** (Directeur Général) peut ajouter, modifier ou supprimer des employés. Les vendeurs et magasiniers n'ont absolument pas accès à cette section. C'est une mesure de sécurité pour que personne ne puisse créer de comptes non autorisés.` },
        ],
      },
      {
        q: `Comment voir qui est en ligne ?`,
        blocks: [
          { t: 'p', text: `Sur le **tableau de bord**, la section **"Collaborateurs"** affiche la liste de vos employés avec leur statut :` },
          { t: 'ul', items: [
            `[badge:success:En ligne] : l'employé est connecté actuellement ou s'est connecté il y a moins de 5 minutes`,
            `[badge:gray:Hors ligne] : la date et l'heure de sa dernière connexion s'affichent`,
          ] },
        ],
      },
      {
        q: `Comment modifier un membre ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Dans la liste des employés, cliquez sur l'icône **Modifier** (crayon) à côté de l'employé concerné.` },
            { num: 2, text: `Vous pouvez changer : le nom, l'email, le mot de passe, le rôle principal et les rôles secondaires.` },
            { num: 3, text: `Sauvegardez les modifications. Les changements prennent effet immédiatement.` },
          ] },
        ],
      },
      {
        q: `Comment supprimer un membre ?`,
        blocks: [
          { t: 'steps', steps: [
            { num: 1, text: `Cliquez sur l'icône **Supprimer** (corbeille) à côté de l'employé.` },
            { num: 2, text: `Confirmez la suppression.` },
            { num: 3, text: `**Effet :** l'employé ne pourra plus se connecter. Cependant, son **historique de ventes est conservé** pour garder une trace complète.` },
          ] },
          { t: 'warn', text: `La suppression est **irréversible**. Si vous voulez juste empêcher l'accès temporairement, **désactivez le compte** (bouton "Désactiver" dans la liste du personnel) plutôt que de le supprimer. L'employé ne pourra plus se connecter, mais son historique sera conservé.` },
        ],
      },
    ],
  },
  {
    id: 'abonnement',
    label: 'Abonnement',
    icon: 'star-outline',
    title: 'Abonnement',
    sections: [
      {
        q: `Quelle est mon offre actuelle et quand expire-t-elle ?`,
        blocks: [
          { t: 'p', text: `Ouvrez **« Mon offre »** (étoile en haut à droite de l'application ou dans le menu web). Vous y voyez le nom de l'offre, son prix, sa date d'expiration et le nombre de jours restants.` },
          { t: 'info', text: `Vous recevez aussi des **notifications automatiques** 7 jours, 3 jours et 1 jour avant l'expiration, ainsi qu'à la date d'expiration.` },
        ],
      },
      {
        q: `Comment renouveler mon abonnement ?`,
        blocks: [
          { t: 'p', text: `Contactez notre équipe depuis la page **« Mon offre »** (boutons Appel, WhatsApp ou E-mail). Nous convenons du règlement (comptant ou en **3 tranches**) puis nous prolongeons votre accès.` },
          { t: 'tip', text: `Renouvelez avant l'expiration pour éviter toute coupure de service.` },
        ],
      },
      {
        q: `Que se passe-t-il quand mon abonnement expire ?`,
        blocks: [
          { t: 'p', text: `À l'expiration, un **bandeau d'alerte** s'affiche et certaines fonctionnalités peuvent être suspendues selon votre offre (ex. : importations/arrivages, multi-magasins, statistiques avancées, multi-utilisateurs). Vos **données restent intactes** : dès le renouvellement, tout redevient accessible.` },
          { t: 'warn', text: `Seule l'offre **Locale** est à vie (sans expiration).` },
        ],
      },
      {
        q: `Puis-je changer d'offre (monter en gamme) ?`,
        blocks: [
          { t: 'p', text: `Oui. Vous pouvez passer d'**Essentiel** à **Professionnel** ou **Entreprise** à tout moment. Contactez-nous depuis la page « Mon offre » et nous ajustons votre licence (complément calculé au prorata de la période restante).` },
        ],
      },
      {
        q: `Proposez-vous un paiement en plusieurs fois ?`,
        blocks: [
          { t: 'p', text: `Oui, le règlement peut se faire en **3 tranches** (ex. : 3 × 25 000 FCFA pour l'offre Professionnel, frais de dossier inclus). Le détail vous est communiqué lors du renouvellement.` },
        ],
      },
      {
        q: `Mon abonnement inclut-il les mises à jour ?`,
        blocks: [
          { t: 'p', text: `Oui. Toutes les mises à jour de l'application (web et mobile), la **sync hors-connexion** et le support sont inclus dans votre abonnement annuel, quel que soit le niveau d'offre.` },
        ],
      },
    ],
  },
];

export default FAQ_DATA;
