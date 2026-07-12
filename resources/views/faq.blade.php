@extends('layouts.app')
@section('title', 'Guide d\'utilisation')
@section('subtitle', 'Apprenez à utiliser chaque module de G-STOCK')

@push('styles')
<style>
    .faq-tabs { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 32px; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
    .faq-tabs::-webkit-scrollbar { display: none; }
    .faq-tab { flex-shrink: 0; padding: 10px 18px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: 1px solid var(--border); background: #fff; color: var(--text-muted); display: flex; align-items: center; gap: 8px; transition: all 0.2s; white-space: nowrap; font-family: 'Inter', sans-serif; }
    .faq-tab:hover { border-color: var(--primary); color: var(--primary); }
    .faq-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .faq-tab i { font-size: 1.1rem; }
    .faq-panel { display: none; }
    .faq-panel.active { display: block; }
    .faq-section { background: #fff; border: 1px solid var(--border); border-radius: var(--radius-card); margin-bottom: 16px; overflow: hidden; }
    .faq-question { padding: 16px 20px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 12px; font-weight: 600; font-size: 0.95rem; transition: background 0.15s; }
    .faq-question:hover { background: #f8fafc; }
    .faq-question i { transition: transform 0.2s; color: var(--text-muted); flex-shrink: 0; }
    .faq-question.open i { transform: rotate(180deg); color: var(--primary); }
    .faq-answer { display: none; padding: 0 20px 16px; font-size: 0.9rem; line-height: 1.7; color: var(--text-muted); font-family: 'Inter', sans-serif; }
    .faq-answer.open { display: block; }
    .faq-answer ul, .faq-answer ol { margin: 8px 0; padding-left: 20px; }
    .faq-answer li { margin-bottom: 6px; }
    .faq-answer strong { color: var(--text); }
    .faq-answer .step { display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-start; }
    .faq-answer .step-num { background: var(--primary); color: #fff; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; flex-shrink: 0; margin-top: 2px; }
    .faq-answer .step-text { flex: 1; }
    .faq-tip { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 16px; margin-top: 12px; font-size: 0.85rem; color: #166534; display: flex; gap: 8px; align-items: flex-start; }
    .faq-tip i { margin-top: 2px; flex-shrink: 0; }
    .faq-warn { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 12px 16px; margin-top: 12px; font-size: 0.85rem; color: #9a3412; display: flex; gap: 8px; align-items: flex-start; }
    .faq-warn i { margin-top: 2px; flex-shrink: 0; }
    .faq-info { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 16px; margin-top: 12px; font-size: 0.85rem; color: #1e40af; display: flex; gap: 8px; align-items: flex-start; }
    .faq-info i { margin-top: 2px; flex-shrink: 0; }
    .faq-title-section { font-size: 1.2rem; font-weight: 700; color: var(--text); margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid var(--primary); display: inline-block; }
    @media (max-width: 640px) {
        .faq-tab { padding: 8px 14px; font-size: 0.8rem; }
        .faq-question { padding: 14px 16px; font-size: 0.9rem; }
        .faq-answer { padding: 0 16px 14px; font-size: 0.85rem; }
    }
</style>
@endpush

@section('content')

<div class="faq-tabs" id="faqTabs">
    <button class="faq-tab active" data-tab="dashboard"><i class="bi bi-speedometer2"></i> Tableau de bord</button>
    <button class="faq-tab" data-tab="produits"><i class="bi bi-box2"></i> Produits</button>
    <button class="faq-tab" data-tab="arrivages"><i class="bi bi-truck"></i> Arrivages</button>
    <button class="faq-tab" data-tab="ventes"><i class="bi bi-cart"></i> Ventes</button>
    <button class="faq-tab" data-tab="livraisons"><i class="bi bi-truck-flatbed"></i> Livraisons</button>
    <button class="faq-tab" data-tab="clients"><i class="bi bi-people"></i> Clients</button>
    <button class="faq-tab" data-tab="dettes"><i class="bi bi-credit-card-2-back"></i> Dettes</button>
    <button class="faq-tab" data-tab="mouvements"><i class="bi bi-arrow-left-right"></i> Mouvements</button>
    <button class="faq-tab" data-tab="stock"><i class="bi bi-boxes"></i> Stock</button>
    <button class="faq-tab" data-tab="depots"><i class="bi bi-shop"></i> Dépôts</button>
    <button class="faq-tab" data-tab="employes"><i class="bi bi-person-badge"></i> Employés</button>
</div>

{{-- ═══════════ TABLEAU DE BORD ═══════════ --}}
<div class="faq-panel active" id="panel-dashboard">
    <div class="faq-title-section">Tableau de bord</div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>À quoi sert le tableau de bord ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            C'est votre <strong>centre de contrôle principal</strong>. Il affiche en un coup d'œil l'ensemble des informations clés de votre société : chiffre d'affaires, dépenses, alertes de stock, dettes en retard, ventes récentes et performance de votre équipe. Vous pouvez naviguer entre les jours avec le sélecteur de date pour consulter les données de n'importe quel jour.
            <div class="faq-info"><i class="bi bi-shield-lock"></i> <div><strong>Qui voit quoi :</strong> Seul le <strong>DG</strong> (Directeur Général) a accès à l'ensemble des données chiffrées du tableau de bord (CA, marges, revenus, dépenses). Les vendeurs et magasiniers ne voient que leur propre activité.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment lire les statistiques ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <strong>Visibles par le DG uniquement :</strong>
            <ul>
                <li><strong>Ventes du jour</strong> : total encaissé ce jour par l'ensemble des vendeurs</li>
                <li><strong>Ventes du mois</strong> : cumul total des encaissements depuis le 1er du mois</li>
                <li><strong>Dépenses du jour</strong> : total des dépenses enregistrées aujourd'hui (loyers, achats, salaires...)</li>
                <li><strong>Dépenses du mois</strong> : cumul des dépenses depuis le 1er du mois</li>
                <li><strong>CA Net</strong> : ventes du jour minus dépenses du jour — votre bénéfice brut du jour</li>
                <li><strong>Revenu net mensuel</strong> : CA net du mois minus loyers de tous les magasins — ce qu'il vous reste réellement</li>
                <li><strong>Total dettes</strong> : montant total des créances clients non soldées (toutes dettes confondues)</li>
                <li><strong>Dettes en retard</strong> : nombre de dettes dont l'échéance est dépassée</li>
            </ul>
            <strong>Visible par les vendeurs :</strong>
            <ul>
                <li>Leur propre chiffre d'affaires du jour (montant personnel)</li>
                <li>Le nombre de ventes qu'ils ont réalisées</li>
            </ul>

        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment changer la date affichée ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            En haut du tableau de bord, vous voyez un <strong>sélecteur de date</strong>. Cliquez dessus et choisissez la date souhaitée. Toutes les données (ventes, dépenses, ventes récentes, performance par personne) se recalculent automatiquement pour cette date. Par défaut, c'est la date du jour qui est affichée.
            <div class="faq-tip"><i class="bi bi-lightbulb"></i> <div>Pour vérifier les ventes d'un jour précis (ex : la semaine dernière), changez simplement la date et les stats se mettent à jour instantanément.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment enregistrer une dépense rapide ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Dans la section <strong>"Dépenses du jour"</strong> du tableau de bord, cliquez sur le bouton <strong>"Nouvelle dépense"</strong>.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text">Entrez le <strong>montant</strong> de la dépense.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text">Ajoutez une <strong>description</strong> (optionnel mais recommandé) : ex "Achat cartons", "Facture électricité", "Pain pour le personnel".</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text"><strong>Message vocal (optionnel)</strong> : appuyez sur le bouton <strong>micro</strong> pour enregistrer un message audio décrivant la dépense. Utile si vous êtes pressé ou si vous préférez parler plutôt que taper.</div></div>
            <div class="step"><div class="step-num">5</div><div class="step-text">Validez. La dépense apparaît immédiatement dans la liste, et les statistiques (CA Net, Dépenses du jour) se mettent à jour.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment voir les produits en alerte de stock ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            La section <strong>"Alertes stock"</strong> du tableau de bord affiche automatiquement les produits dont le stock est inférieur ou égal au seuil d'alerte que vous avez défini. Le système calcule le stock en temps réel en additionnant :
            <ul>
                <li>Le stock initial du produit</li>
                <li>Plus les entrées (arrivages validés)</li>
                <li>Minus les sorties (ventes, transferts sortants)</li>
                <li>Plus/Moins les ajustements</li>
            </ul>
            <div class="faq-tip"><i class="bi bi-lightbulb"></i> <div>Si un produit affiche un stock de <strong>0</strong> ou en négatif, c'est qu'il y a un problème de synchronisation des entrées/sorties. Vérifiez les arrivages et transferts.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment suivre la performance de mon équipe ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            La section <strong>"Performance par collaborateur"</strong> affiche le chiffre d'affaires réalisé par <strong>chaque vendeur</strong> dans la journée sélectionnée. C'est un tableau avec le nom du vendeur et son montant total de ventes.
            <br><br>
            La section <strong>"Collaborateurs"</strong> affiche aussi le statut de connexion de vos employés : <span class="badge badge-success">En ligne</span> si connecté récemment, ou la date/heure de leur dernière connexion.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Que sont les "Dernières ventes" ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Cette section affiche les <strong>5 dernières ventes enregistrées</strong> dans la journée, avec le nom du client, le montant, le vendeur et l'heure. C'est un aperçu rapide pour vérifier que tout se passe bien dans la journée.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Que sont les "Produits les plus vendus" ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Le tableau affiche le <strong>Top 5 des produits les plus vendus</strong> du mois en cours, classés par quantité totale vendue. Cela vous aide à identifier vos produits phares et à anticiper les réapprovisionnements.
        </div>
    </div>
</div>

{{-- ═══════════ PRODUITS ═══════════ --}}
<div class="faq-panel" id="panel-produits">
    <div class="faq-title-section">Produits</div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>À quoi sert l'onglet Produits ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            C'est le <strong>catalogue complet</strong> de tous les produits que vous commercialisez. Chaque produit a une fiche avec son nom, ses prix (achat et vente), son seuil d'alerte, et éventuellement une image. C'est ici que vous créez, modifiez et organisez votre catalogue avant de pouvoir les vendre ou les réceptionner via les arrivages.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment ajouter un nouveau produit ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Allez dans <strong>Produits</strong> puis cliquez sur le bouton <strong>"Nouveau"</strong>.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text"><strong>Nom du produit</strong> : donnez un nom clair et reconnaissable (ex : "Savon Noir 250g", "Téléphone Samsung A14").</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text"><strong>Prix de vente</strong> : le prix que le client paiera. C'est ce prix qui sera utilisé automatiquement lors des ventes.</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text"><strong>Prix d'achat</strong> : le prix auquel vous achetez ce produit chez votre fournisseur. Utilisé pour calculer vos marges.</div></div>
            <div class="step"><div class="step-num">5</div><div class="step-text"><strong>Seuil d'alerte</strong> : la quantité minimale en stock. En dessous, le produit apparaît en alerte rouge sur le tableau de bord.</div></div>
            <div class="step"><div class="step-num">6</div><div class="step-text"><strong>Image</strong> (optionnel) : ajoutez une photo du produit pour l'identifier visuellement.</div></div>
            <div class="step"><div class="step-num">7</div><div class="step-text">Validez. Le produit apparaît dans la liste et est maintenant utilisable dans les arrivages et les ventes.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment modifier un produit existant ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Dans la liste des produits, cliquez sur le nom du produit à modifier.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text">Cliquez sur le bouton <strong>"Modifier"</strong> (icône crayon).</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text">Changez le champ souhaité (prix, nom, seuil...).</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text">Sauvegardez. Le nouveau prix s'appliquera <strong>uniquement aux prochaines ventes</strong>. Les ventes déjà enregistrées conservent leur prix d'origine.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment rechercher ou filtrer mes produits ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Plusieurs options de filtrage :
            <ul>
                <li><strong>Barre de recherche</strong> : tapez le nom ou une partie du nom pour filtrer en temps réel</li>
                <li><strong>Tri par colonnes</strong> : cliquez sur les en-têtes du tableau (nom, prix, stock) pour trier ascendant/descendant</li>
            </ul>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment supprimer un produit ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Cliquez sur le produit, puis sur <strong>"Supprimer"</strong> et confirmez. Attention : un produit qui a déjà été vendu ou réceptionné via un arrivage ne pourra pas être supprimé (pour préserver l'historique). Vous pourrez uniquement le désactiver.
        </div>
    </div>

    <div class="faq-tip"><i class="bi bi-lightbulb"></i> <div><strong>Bonnes pratiques :</strong> Définissez un seuil d'alerte adapté à votre rythme de vente. Un produit qui se vend rapidement nécessite un seuil plus élevé. Vérifiez régulièrement que vos prix de vente sont toujours compétitifs.</div></div>
</div>

{{-- ═══════════ ARRIVAGES ═══════════ --}}
<div class="faq-panel" id="panel-arrivages">
    <div class="faq-title-section">Arrivages</div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>À quoi sert l'onglet Arrivages ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            C'est ici que vous enregistrez <strong>toutes les entrées de marchandises</strong> dans vos magasins. Chaque fois que vous recevez des produits d'un fournisseur, vous créez un arrivage. C'est la seule façon de faire entrer du nouveau stock dans le système. Les arrivages sont liés à un fournisseur et à un magasin de destination.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment enregistrer un nouvel arrivage ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Allez dans <strong>Arrivages</strong> puis cliquez sur <strong>"Nouvel arrivage"</strong>.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text"><strong>Fournisseur</strong> (optionnel) : sélectionnez le fournisseur chez qui vous avez acheté. Si c'est un nouveau fournisseur, vous pouvez l'ajouter directement depuis le formulaire.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text"><strong>Magasin de destination</strong> : choisissez le magasin qui réceptionne la marchandise. Le stock de CE magasin sera augmenté.</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text"><strong>Ajoutez les produits</strong> : pour chaque produit reçu, indiquez :
                <ul>
                    <li>Le <strong>produit</strong> (recherchez par nom dans votre catalogue)</li>
                    <li>La <strong>quantité reçue</strong></li>
                    <li>Le <strong>prix d'achat unitaire</strong> (ce que vous avez payé au fournisseur)</li>
                </ul>
            </div></div>
            <div class="step"><div class="step-num">5</div><div class="step-text">Le système peut <strong>suggérer un prix de vente</strong> basé sur votre marge habituelle. Vous pouvez accepter ou modifier cette suggestion.</div></div>
            <div class="step"><div class="step-num">6</div><div class="step-text">Validez l'arrivage. Le stock du magasin est automatiquement mis à jour.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Quelle différence entre "Brouillon" et "Validé" ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <ul>
                <li><strong>Brouillon</strong> : l'arrivage est en cours de préparation. Les quantités ne sont PAS encore ajoutées au stock. Vous pouvez encore modifier les produits et quantités. C'est l'état par défaut lors de la création.</li>
                <li><strong>Validé</strong> : l'arrivage est confirmé et définitif. Le stock du magasin cible est automatiquement augmenté. Un mouvement de type "Entrée arrivage" est créé dans les mouvements de stock. <strong>Vous ne pouvez plus modifier un arrivage validé.</strong></li>
            </ul>
            <div class="faq-tip"><i class="bi bi-lightbulb"></i> <div>Utilisez le statut "Brouillon" pour préparer votre arrivage tranquillement. Une fois que tout est correct, validez-le pour que les quantités soient comptabilisées dans le stock.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment modifier un arrivage en brouillon ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Cliquez sur l'arrivage dans la liste, puis sur <strong>"Modifier"</strong>. Vous pouvez changer le fournisseur, le magasin, ajouter ou retirer des produits, modifier les quantités et les prix d'achat. Sauvegardez vos modifications. Tant que l'arrivage est en brouillon, vous pouvez le modifier librement.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment supprimer un arrivage ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <ul>
                <li><strong>Arrivage en brouillon</strong> : vous pouvez le supprimer librement, aucune incidence sur le stock.</li>
                <li><strong>Arrivage validé</strong> : la suppression est possible mais <strong>le stock sera automatiquement diminué</strong> des quantités correspondantes. Utilisez cette option si l'arrivage a été validé par erreur.</li>
            </ul>
            <div class="faq-warn"><i class="bi bi-exclamation-triangle"></i> <div>La suppression d'un arrivage validé est <strong>irréversible</strong>. Assurez-vous de bien comprendre l'impact avant de confirmer.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Puis-je ajouter un nouveau fournisseur depuis l'arrivage ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Oui. Lors de la création d'un arrivage, si votre fournisseur n'existe pas encore dans la liste, vous pouvez l'ajouter directement depuis le formulaire d'arrivage. Il sera ensuite disponible pour tous les prochains arrivages.
        </div>
    </div>
</div>

{{-- ═══════════ VENTES ═══════════ --}}
<div class="faq-panel" id="panel-ventes">
    <div class="faq-title-section">Ventes</div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>À quoi sert l'onglet Ventes ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            C'est le cœur de votre activité commerciale. C'est ici que vous enregistrez <strong>chaque transaction</strong> avec vos clients. Chaque vente créée :
            <ul>
                <li>Génère une <strong>facture</strong> imprimable</li>
                <li><strong>Diminue automatiquement</strong> le stock du magasin source</li>
                <li>Crée une <strong>dette</strong> si le client ne paie pas tout de suite</li>
                <li>Enregistre le <strong>vendeur</strong> responsable de la vente</li>
            </ul>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment créer une vente pas à pas ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Allez dans <strong>Ventes</strong> puis cliquez sur <strong>"Nouvelle vente"</strong>.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text"><strong>Client</strong> : sélectionnez un client existant dans la liste ou créez-en un nouveau. Si c'est une vente comptant au comptoir, vous pouvez laisser le champ vide.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text"><strong>Magasin source</strong> : choisissez le magasin depuis lequel la marchandise est prélevée. Le stock de CE magasin sera diminué.</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text"><strong>Ajoutez les produits</strong> : recherchez un produit par son nom dans la barre de recherche. Sélectionnez-le, puis indiquez la <strong>quantité</strong>. Le système vérifie automatiquement que le stock est suffisant.</div></div>
            <div class="step"><div class="step-num">5</div><div class="step-text"><strong>Total</strong> : le montant total se calcule automatiquement (prix de vente × quantité pour chaque article).</div></div>
            <div class="step"><div class="step-num">6</div><div class="step-text"><strong>Montant payé</strong> : entrez ce que le client paie maintenant :
                <ul>
                    <li><strong>Total</strong> : le client paie tout → vente comptant</li>
                    <li><strong>Partiel</strong> : le client avance une somme → acompte avec dette</li>
                    <li><strong>0</strong> : le client ne paie rien → dette totale (crédit)</li>
                </ul>
            </div></div>
            <div class="step"><div class="step-num">7</div><div class="step-text">Validez. La facture est générée, le stock est diminué, et si crédit, la dette est créée automatiquement.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Que signifie "vente à crédit" ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Quand un client ne paie pas tout de suite (ou paie une partie), c'est une <strong>vente à crédit</strong>. Le mécanisme :
            <ol>
                <li>Vous créez la vente avec le montant payé = 0 ou partiel</li>
                <li>Le système crée automatiquement une <strong>dette</strong> pour ce client égale au reste à payer</li>
                <li>La dette apparaît dans l'onglet <strong>Dettes</strong></li>
                <li>Quand le client paie, vous enregistrez le paiement depuis l'onglet Dettes</li>
                <li>La dette est mise à jour ou soldée</li>
            </ol>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment imprimer une facture ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Après avoir créé une vente ou en consultant une vente existante, cliquez sur le bouton <strong>"Imprimer"</strong>.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text">Une facture professionnelle s'ouvre dans un <strong>nouvel onglet</strong> avec toutes les informations : nom de la société, coordonnées du client, liste des articles, montant total, montant payé, reste à payer.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text">Utilisez <strong>Ctrl+P</strong> (ou Cmd+P sur Mac) pour l'imprimer ou l'envoyer en PDF.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment annuler ou supprimer une vente ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Allez dans le détail de la vente concernée.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text">Cliquez sur <strong>"Supprimer"</strong>.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text">Confirmez la suppression.</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text"><strong>Effet :</strong> le stock est automatiquement <strong>restauré</strong> (les quantités reviennent dans le magasin). Si une dette était liée à cette vente, elle est aussi supprimée.</div></div>
            <div class="faq-warn"><i class="bi bi-exclamation-triangle"></i> <div>Cette action est <strong>irréversible</strong>. Assurez-vous vraiment avant de supprimer une vente.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Le système alerte-t-il en cas de stock insuffisant ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Oui. Lorsque vous ajoutez un produit à la vente, le système vérifie automatiquement la <strong>quantité disponible</strong> dans le magasin source. Si vous essayez de vendre plus que ce qui est en stock, un message d'erreur vous empêche de valider la vente. Cela évite les stocks négatifs.
            <div class="faq-tip"><i class="bi bi-lightbulb"></i> <div>Si le stock est insuffisant, créez d'abord un <strong>transfert</strong> depuis un autre magasin qui a le produit, ou enregistrez un <strong>arrivage</strong> pour réapprovisionner.</div></div>
        </div>
    </div>
</div>

{{-- ═══════════ LIVRAISONS ═══════════ --}}
<div class="faq-panel" id="panel-livraisons">
    <div class="faq-title-section">Livraisons</div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>À quoi sert l'onglet Livraisons ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            C'est le <strong>centre de suivi</strong> de toutes les livraisons en cours. Chaque vente qui nécessite une livraison apparaît ici avec son statut, le client à livrer, l'adresse et le livreur assigné. Vous pouvez suivre l'avancement en temps réel.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment fonctionne le flux de livraison ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <ol>
                <li><strong>Vente créée</strong> avec statut livraison "En attente" → elle apparaît dans la liste des livraisons</li>
                <li><strong>Assignation</strong> : le DG ou le responsable sélectionne un livreur pour prendre en charge</li>
                <li><strong>En cours</strong> : le livreur est en route, il peut appeler le client en un clic</li>
                <li><strong>Livré</strong> : le client a réceptionné le colis → la livraison est terminée</li>
                <li><strong>Problème</strong> : le livreur signale un souci (client absent, adresse erronée...)</li>
            </ol>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Les différents statuts de livraison</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <ul>
                <li><span class="badge badge-gray">En attente</span> : la livraison n'a pas encore été prise en charge par un livreur. C'est le statut par défaut.</li>
                <li><span class="badge badge-warning">En cours</span> : le livreur a accepté la livraison et est en route vers le client.</li>
                <li><span class="badge badge-success">Livré</span> : le client a bien reçu sa commande. La livraison est terminée avec succès.</li>
                <li><span class="badge badge-danger">Problème signalé</span> : le livreur a rencontré un souci (client absent, mauvaise adresse, colis endommagé...). Une description de l'incident est enregistrée.</li>
            </ul>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment assigner un livreur ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Dans la liste des livraisons, repérez la livraison "En attente".</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text">Cliquez dessus pour voir le détail.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text">Sélectionnez un employé avec le rôle <strong>"Livreur"</strong> dans la liste déroulante.</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text">Le livreur verra la commande apparaître dans son interface et pourra commencer la livraison.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Que fait le livreur concrètement ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Depuis son interface (accessible sur mobile), le livreur peut :
            <ul>
                <li>Voir la liste de ses livraisons du jour avec les adresses et contacts clients</li>
                <li><strong>Appeler le client</strong> en un clic pour confirmer la livraison</li>
                <li><strong>Valider</strong> la livraison une fois le colis remis</li>
                <li><strong>Signaler un problème</strong> si le client est absent ou s'il y a un souci</li>
                <li>Consulter l'historique de ses tournées</li>
            </ul>
        </div>
    </div>
</div>

{{-- ═══════════ CLIENTS ═══════════ --}}
<div class="faq-panel" id="panel-clients">
    <div class="faq-title-section">Clients</div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>À quoi sert l'onglet Clients ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            C'est votre <strong>annuaire commercial</strong>. Vous y gérez tous vos clients : leurs coordonnées, leur historique de ventes, leurs dettes et leurs paiements. C'est aussi ici que vous créez de nouveaux clients lorsqu'un nouveau acheteur se présente.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment ajouter un nouveau client ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Allez dans <strong>Clients</strong> puis cliquez sur <strong>"Nouveau client"</strong>.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text"><strong>Nom</strong> : le nom complet ou le nom d'usage du client.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text"><strong>Téléphone</strong> (obligatoire) : le numéro de téléphone. C'est le champ le plus important car il permet de contacter le client pour les livraisons et les relances de dettes.</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text"><strong>Adresse</strong> (optionnel) : l'adresse physique du client, utile pour les livraisons.</div></div>
            <div class="step"><div class="step-num">5</div><div class="step-text"><strong>Ville</strong> (optionnel) : la ville de résidence du client.</div></div>
            <div class="step"><div class="step-num">6</div><div class="step-text"><strong>Email</strong> (optionnel) : l'adresse email du client.</div></div>
            <div class="step"><div class="step-num">7</div><div class="step-text">Validez. Le client est maintenant disponible lors de la création d'une vente.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Que contient la fiche d'un client ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            En cliquant sur un client, vous accédez à sa <strong>fiche complète</strong> :
            <ul>
                <li><strong>Coordonnées</strong> : nom, téléphone (cliquable pour appeler), adresse, ville, email</li>
                <li><strong>Historique des ventes</strong> : toutes les ventes réalisées avec ce client, avec dates et montants</li>
                <li><strong>Solde des dettes</strong> : montant total dû par le client</li>
                <li><strong>Paiements enregistrés</strong> : tous les versements effectués par le client</li>
                <li><strong>Crédit restant</strong> : combien il reste à payer</li>
            </ul>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment rechercher un client ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Utilisez la barre de recherche en haut du tableau. Vous pouvez chercher par :
            <ul>
                <li><strong>Nom</strong> : tapez les premières lettres du nom</li>
                <li><strong>Téléphone</strong> : tapez le numéro de téléphone</li>
            </ul>
            La recherche se fait en <strong>temps réel</strong> — les résultats se filtrent au fur et à mesure que vous tapez.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment modifier les informations d'un client ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Cliquez sur le client, puis sur <strong>"Modifier"</strong>. Vous pouvez changer n'importe quel champ (nom, téléphone, adresse...). Sauvegardez. Les modifications s'appliqueront immédiatement, y compris pour les futures ventes et livraisons.
        </div>
    </div>
</div>

{{-- ═══════════ DETTES ═══════════ --}}
<div class="faq-panel" id="panel-dettes">
    <div class="faq-title-section">Dettes</div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>À quoi sert l'onglet Dettes ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            C'est l'outil de <strong>suivi des créances clients</strong>. Chaque fois qu'un client achète à crédit (paie une partie ou rien), une dette est créée. Cet onglet vous permet de voir toutes les dettes, enregistrer les paiements, définir des échéances et suivre les retards. C'est essentiel pour votre trésorerie.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment sont créées les dettes ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Une dette est créée <strong>automatiquement</strong> lorsque vous enregistrez une vente avec un montant payé inférieur au total. Par exemple :
            <ul>
                <li>Vente de <strong>50 000 FCFA</strong>, le client paie <strong>20 000 FCFA</strong> → dette de <strong>30 000 FCFA</strong></li>
                <li>Vente de <strong>50 000 FCFA</strong>, le client ne paie rien → dette de <strong>50 000 FCFA</strong></li>
            </ul>
            Vous n'avez <strong>pas besoin</strong> de créer les dettes manuellement. Le système s'en charge via les ventes à crédit.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment enregistrer un paiement ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Allez dans <strong>Dettes</strong> et repérez la dette du client dans la liste.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text">Cliquez sur la dette pour voir le détail.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text">Cliquez sur <strong>"Enregistrer un paiement"</strong>.</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text">Entrez le <strong>montant versé</strong> par le client.</div></div>
            <div class="step"><div class="step-num">5</div><div class="step-text">Validez. La dette est mise à jour :
                <ul>
                    <li>Si le montant versé = reste à payer → la dette passe en statut <strong>"Soldé"</strong></li>
                    <li>Si le montant versé < reste à payer → la dette reste active avec le nouveau solde</li>
                </ul>
            </div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Les différents statuts des dettes</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <ul>
                <li><span class="badge badge-warning">En cours</span> : le client a encore du temps pour payer. C'est le statut par défaut d'une nouvelle dette.</li>
                <li><span class="badge badge-gray">Partiel</span> : un paiement partiel a été enregistré. Il reste encore du montant à payer.</li>
                <li><span class="badge badge-danger">En retard</span> : la date d'échéance est dépassée et le client n'a pas tout payé. Ces dettes apparaissent en alerte sur le tableau de bord.</li>
                <li><span class="badge badge-success">Soldé</span> : la totalité a été payée. La dette est clôturée.</li>
            </ul>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment définir une échéance ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Sur la fiche d'une dette, vous pouvez définir une <strong>date d'échéance</strong>. Passée cette date, si la dette n'est pas entièrement payée :
            <ul>
                <li>Le statut passe automatiquement en <strong>"En retard"</strong></li>
                <li>La dette apparaît dans les <strong>alertes du tableau de bord</strong></li>
                <li>Vous pouvez utiliser cette information pour relancer le client</li>
            </ul>
            <div class="faq-tip"><i class="bi bi-lightbulb"></i> <div>Définissez des échéances réalistes (ex : 15 jours, 30 jours) pour mieux gérer votre trésorerie et relancer les clients en temps utile.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment voir les dettes en retard ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Deux endroits :
            <ul>
                <li><strong>Tableau de bord</strong> : la section "Dettes en retard" affiche les 10 dettes les plus anciennes dont l'échéance est dépassée, avec le nom du client et le montant dû.</li>
                <li><strong>Onglet Dettes</strong> : filtrez par statut "En retard" pour voir la liste complète.</li>
            </ul>
        </div>
    </div>
</div>

{{-- ═══════════ MOUVEMENTS ═══════════ --}}
<div class="faq-panel" id="panel-mouvements">
    <div class="faq-title-section">Mouvements de stock</div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>À quoi sert l'onglet Mouvements ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            C'est le <strong>journal complet</strong> de toutes les entrées et sorties de stock dans un magasin. Chaque fois qu'un produit entre (arrivage, transfert entrée, ajustement positif) ou sort (vente, transfert sorti, ajustement négatif), un mouvement est enregistré. C'est l'outil de <strong>traçabilité</strong> par excellence : vous savez exactement qui a fait quoi, quand, et en quelle quantité.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment consulter les mouvements ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Allez dans <strong>Stock</strong> puis cliquez sur <strong>"Mouvements"</strong>.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text">Sélectionnez le <strong>magasin</strong> dont vous voulez voir les mouvements.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text">Vous verrez la liste chronologique de tous les mouvements, du plus récent au plus ancien.</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text">Chaque mouvement affiche : la <strong>date</strong>, le <strong>produit</strong>, le <strong>type</strong> de mouvement, la <strong>quantité</strong>, et <strong>l'utilisateur</strong> qui a effectué l'action.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Les différents types de mouvements</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <strong>Entrées (+) :</strong>
            <ul>
                <li><span class="badge badge-success">Entrée arrivage</span> : réception de marchandise via un arrivage validé. Le stock augmente.</li>
                <li><span class="badge badge-success">Transfert entrée</span> : un produit a été transféré depuis un autre magasin vers celui-ci. Le stock augmente.</li>
                <li><span class="badge badge-success">Ajustement positif</span> : un ajustement manuel a été fait pour augmenter le stock (ex : inventaire physique trouvé plus de stock que prévu).</li>
            </ul>
            <strong>Sorties (-) :</strong>
            <ul>
                <li><span class="badge badge-danger">Sortie vente</span> : un produit a été vendu. Le stock diminue.</li>
                <li><span class="badge badge-danger">Transfert sorti</span> : un produit a été transféré vers un autre magasin. Le stock diminue.</li>
                <li><span class="badge badge-danger">Ajustement négatif</span> : un ajustement manuel a été fait pour diminuer le stock (ex : produits cassés, périmés, volés).</li>
            </ul>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>À quoi servent les ajustements ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Les ajustements permettent de <strong>corriger le stock</strong> quand il ne correspond pas à la réalité physique. Scénarios courants :
            <ul>
                <li><strong>Inventaire physique</strong> : vous comptez les produits réellement et trouvez un écart avec le stock affiché</li>
                <li><strong>Produits cassés</strong> : un produit s'est cassé en magasin → ajustement négatif</li>
                <li><strong>Produits périmés</strong> : un produit a expiré → ajustement négatif</li>
                <li><strong>Vol</strong> : un produit a été volé → ajustement négatif</li>
                <li><strong>Erreur de saisie</strong> : une vente ou un arrivage a été enregistré en quantité incorrecte → ajustement pour corriger</li>
            </ul>
            <div class="faq-warn"><i class="bi bi-exclamation-triangle"></i> <div>Chaque ajustement est <strong>tracé</strong> dans l'historique avec l'utilisateur responsable. Utilisez les ajustements avec parcimonie.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment faire un ajustement ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Dans la page <strong>Stock</strong>, cliquez sur <strong>"Ajuster"</strong>.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text">Sélectionnez le <strong>magasin</strong> et le <strong>produit</strong> concerné.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text">Choisissez le type : <strong>positif</strong> (ajouter) ou <strong>négatif</strong> (retirer).</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text">Indiquez la <strong>quantité</strong> à ajuster.</div></div>
            <div class="step"><div class="step-num">5</div><div class="step-text">Validez. Le mouvement est enregistré dans l'historique et le stock est mis à jour.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Filtrer les mouvements par type</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Vous pouvez filtrer la liste des mouvements par type pour ne voir que ce qui vous intéresse :
            <ul>
                <li>Uniquement les <strong>entrées</strong> (arrivages)</li>
                <li>Uniquement les <strong>sorties</strong> (ventes)</li>
                <li>Uniquement les <strong>transferts</strong></li>
                <li>Uniquement les <strong>ajustements</strong></li>
            </ul>
            Utilisez les filtres en haut de la page pour affiner votre recherche.
        </div>
    </div>

    <div class="faq-tip"><i class="bi bi-lightbulb"></i> <div><strong>Astuce :</strong> Consultez régulièrement les mouvements pour détecter toute anomalie. Si un produit a une quantité inattendue, les mouvements vous diront exactement d'où vient l'écart.</div></div>
</div>

{{-- ═══════════ STOCK ═══════════ --}}
<div class="faq-panel" id="panel-stock">
    <div class="faq-title-section">Stock</div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>À quoi sert l'onglet Stock ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            C'est la <strong>vue en temps réel</strong> de l'état de vos stocks par magasin. Vous y voyez pour chaque produit la quantité disponible dans un magasin donné, les produits en alerte (stock bas), et vous pouvez lancer des ajustements. Le stock est calculé automatiquement : stock initial + entrées - sorties ± ajustements.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment consulter le stock d'un magasin ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Allez dans <strong>Stock</strong>.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text">Sélectionnez le <strong>magasin</strong> souhaité dans le filtre en haut de la page.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text">Vous voyez la liste de tous les produits avec leurs <strong>quantités disponibles</strong>. Les produits dont le stock est ≤ au seuil d'alerte sont mis en évidence <strong>en rouge</strong>.</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text">Vous pouvez cliquer sur un produit pour voir son détail et les mouvements associés.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment est calculé le stock ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Le stock affiché est le résultat d'un <strong>calcul dynamique</strong> :
            <br><br>
            <strong>Stock disponible = Stock de base + Entrées - Sorties ± Ajustements</strong>
            <ul>
                <li><strong>Stock de base</strong> : la quantité initiale du produit au moment de sa création</li>
                <li><strong>+ Entrées</strong> : tous les arrivages validés pour ce magasin</li>
                <li><strong>- Sorties</strong> : toutes les ventes et transferts sortants de ce magasin</li>
                <li><strong>± Ajustements</strong> : les corrections manuelles (positives ou négatives)</li>
            </ul>
            Le calcul est fait en <strong>temps réel</strong> — chaque nouvelle vente, arrivage ou transfert met à jour instantanément le stock affiché.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Les produits en alerte de stock</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Un produit est en <strong>alerte</strong> lorsque son stock est inférieur ou égal au <strong>seuil d'alerte</strong> que vous avez défini. Ces produits apparaissent :
            <ul>
                <li>En <strong>rouge</strong> dans la page Stock</li>
                <li>Dans la section <strong>"Alertes stock"</strong> du tableau de bord</li>
            </ul>
            C'est un signal pour <strong>réapprovisionner</strong> ce produit via un nouvel arrivage ou un transfert depuis un autre magasin.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Que faire quand un produit est en alerte ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Trois options :
            <ul>
                <li><strong>Arrivage</strong> : créez un nouvel arrivage pour réapprovisionner le magasin depuis un fournisseur</li>
                <li><strong>Transfert</strong> : si le produit est disponible dans un autre magasin, transférez-en la quantité nécessaire</li>
                <li><strong>Ajustement</strong> : si le stock physique est correct mais le stock affiché est faux, faites un ajustement pour corriger</li>
            </ul>
        </div>
    </div>
</div>

{{-- ═══════════ DÉPÔTS ═══════════ --}}
<div class="faq-panel" id="panel-depots">
    <div class="faq-title-section">Dépôts (Magasins)</div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>À quoi sert l'onglet Dépôts ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            C'est ici que vous gérez vos <strong>points de stockage physique</strong>. Chaque dépôt (ou magasin) est un endroit où vous stockez et vendez vos produits. G-STOCK est conçu pour la <strong>multi-magasins</strong> : vous pouvez en avoir autant que nécessaire (Magasin Principal, Entrepôt, Boutique de quartier...). Chaque dépôt a son propre stock, ses propres ventes et ses propres statistiques.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment ajouter un nouveau dépôt ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Allez dans <strong>Dépôts</strong> puis cliquez sur <strong>"Nouveau"</strong>.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text"><strong>Nom du dépôt</strong> : donnez un nom clair (ex : "Magasin Central", "Dépôt B", "Boutique Rue 12").</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text"><strong>Loyer mensuel</strong> (optionnel) : le coût du loyer de ce local. Ce montant est automatiquement déduit du revenu net sur le tableau de bord.</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text">Validez. Le dépôt est maintenant utilisable comme source ou destination pour les arrivages, ventes et transferts.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Pourquoi avoir plusieurs dépôts ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            La multi-magasin vous permet de :
            <ul>
                <li><strong>Suivre le stock indépendamment</strong> par point de vente — vous savez exactement ce qu'il y a dans chaque magasin</li>
                <li><strong>Transférer des produits</strong> entre vos points de stockage pour rééquilibrer l'offre</li>
                <li><strong>Comparer la performance</strong> de chaque magasin (ventes, rentabilité)</li>
                <li><strong>Isoler les responsabilités</strong> : chaque magasinier gère son propre stock</li>
                <li><strong>Calculer la rentabilité</strong> par magasin en prenant en compte le loyer de chacun</li>
            </ul>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment modifier ou supprimer un dépôt ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <ul>
                <li><strong>Modifier</strong> : cliquez sur le dépôt, puis modifiez le nom ou le loyer. Sauvegardez.</li>
                <li><strong>Supprimer</strong> : cliquez sur "Supprimer" et confirmez. Attention : un dépôt qui contient encore des produits en stock ou des ventes associées ne pourra pas être supprimé pour préserver l'historique.</li>
            </ul>
            <div class="faq-warn"><i class="bi bi-exclamation-triangle"></i> <div>Avant de supprimer un dépôt, transférez d'abord tout le stock restant vers un autre magasin via l'onglet <strong>Transferts</strong>.</div></div>
        </div>
    </div>
</div>

{{-- ═══════════ EMPLOYÉS ═══════════ --}}
<div class="faq-panel" id="panel-employes">
    <div class="faq-title-section">Employés</div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>À quoi sert l'onglet Employés ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            C'est ici que vous gérez les <strong>comptes utilisateurs</strong> de votre équipe. Chaque employé a un compte avec un email et un mot de passe pour se connecter à G-STOCK. Vous définissez leurs <strong>rôles</strong> pour contrôler ce que chacun peut voir et faire dans l'application. C'est un outil de sécurité essentiel pour protéger vos données commerciales.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment ajouter un nouvel employé ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Allez dans <strong>Employés</strong> puis cliquez sur <strong>"Nouvel employé"</strong>.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text"><strong>Nom</strong> : le nom complet de l'employé (ex : "Amadou Diallo").</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text"><strong>Email</strong> : l'adresse email qui servira d'identifiant de connexion. Chaque email doit être unique.</div></div>
            <div class="step"><div class="step-num">4</div><div class="step-text"><strong>Mot de passe</strong> : definez un mot de passe sécurisé. L'employé pourra le changer plus tard depuis son profil.</div></div>
            <div class="step"><div class="step-num">5</div><div class="step-text"><strong>Rôle principal</strong> : sélectionnez le rôle principal de l'employé :
                <ul>
                    <li><strong>Vendeur</strong> : accès aux ventes et clients</li>
                    <li><strong>Magasinier</strong> : accès au stock, arrivages et transferts</li>
                    <li><strong>Livreur</strong> : accès aux livraisons assignées</li>
                </ul>
            </div></div>
            <div class="step"><div class="step-num">6</div><div class="step-text"><strong>Rôles secondaires</strong> (optionnel) : un employé peut cumuler plusieurs rôles. Par exemple, un magasinier peut aussi avoir le rôle de vendeur s'il fait à la fois les stocks et les ventes. Cochez les rôles secondaires souhaités.</div></div>
            <div class="step"><div class="step-num">7</div><div class="step-text">Validez. L'employé peut maintenant se connecter avec son email et mot de passe.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Les différents rôles en détail</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <strong>Vendeur :</strong>
            <ul>
                <li>Peut créer des ventes et des factures</li>
                <li>Peut gérer les clients (ajout, modification)</li>
                <li>Peut voir ses <strong>propres</strong> ventes et son chiffre d'affaires</li>
                <li>Ne voit PAS les ventes des autres vendeurs</li>
                <li>Ne voit PAS les données financières globales (CA total, marges, dépenses)</li>
            </ul>
            <strong>Magasinier :</strong>
            <ul>
                <li>Peut gérer les stocks (consulter, ajuster)</li>
                <li>Peut enregistrer les arrivages (réception de marchandises)</li>
                <li>Peut créer des transferts entre magasins</li>
                <li>Peut consulter l'historique des mouvements</li>
                <li>Ne voit PAS les ventes ni les dettes clients</li>
            </ul>
            <strong>Livreur :</strong>
            <ul>
                <li>Peut voir les livraisons qui lui sont assignées</li>
                <li>Peut valider une livraison ou signaler un problème</li>
                <li>Peut appeler le client directement depuis l'application</li>
                <li>Ne voit PAS les ventes, stocks, ni données financières</li>
            </ul>
            <div class="faq-tip"><i class="bi bi-lightbulb"></i> <div>Un employé a toujours un <strong>rôle principal</strong> et peut en plus avoir des <strong>rôles secondaires</strong>. Par exemple, un magasinier qui fait aussi les ventes aura le rôle principal "Magasinier" et le rôle secondaire "Vendeur". Cela lui donne accès aux deux sections.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Quelle différence entre rôle principal et rôle secondaire ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <ul>
                <li><strong>Rôle principal</strong> : le rôle principal de l'employé, celui qu'il exerce au quotidien. C'est obligatoire.</li>
                <li><strong>Rôles secondaires</strong> : des rôles supplémentaires que l'employé peut aussi exercer. C'est optionnel.</li>
            </ul>
            <strong>Exemple concret :</strong>
            <ul>
                <li>Un magasinier qui fait aussi les ventes → rôle principal : <strong>Magasinier</strong>, rôle secondaire : <strong>Vendeur</strong></li>
                <li>Un vendeur qui aide aussi aux livraisons → rôle principal : <strong>Vendeur</strong>, rôle secondaire : <strong>Livreur</strong></li>
                <li>Un livreur qui peut aussi gérer les stocks → rôle principal : <strong>Livreur</strong>, rôle secondaire : <strong>Magasinier</strong></li>
            </ul>
            <div class="faq-tip"><i class="bi bi-lightbulb"></i> <div>Lors de la création ou modification d'un employé, vous choisissez le rôle principal dans la liste déroulante, puis vous cochez les rôles secondaires souhaités.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Qui peut créer des employés ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <strong>Uniquement le DG</strong> (Directeur Général) peut ajouter, modifier ou supprimer des employés. Les vendeurs et magasiniers n'ont absolument pas accès à cette section. C'est une mesure de sécurité pour que personne ne puisse créer de comptes non autorisés.
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment voir qui est en ligne ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Sur le <strong>tableau de bord</strong>, la section <strong>"Collaborateurs"</strong> affiche la liste de vos employés avec leur statut :
            <ul>
                <li><span class="badge badge-success">En ligne</span> : l'employé est connecté actuellement ou s'est connecté il y a moins de 5 minutes</li>
                <li><span class="badge badge-gray">Hors ligne</span> : la date et l'heure de sa dernière connexion s'affichent</li>
            </ul>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment modifier un employé ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Dans la liste des employés, cliquez sur l'icône <strong>Modifier</strong> (crayon) à côté de l'employé concerné.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text">Vous pouvez changer : le nom, l'email, le mot de passe, le rôle principal et les rôles secondaires.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text">Sauvegardez les modifications. Les changements prennent effet immédiatement.</div></div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-question" onclick="toggleFaq(this)">
            <span>Comment supprimer un employé ?</span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-answer">
            <div class="step"><div class="step-num">1</div><div class="step-text">Cliquez sur l'icône <strong>Supprimer</strong> (corbeille) à côté de l'employé.</div></div>
            <div class="step"><div class="step-num">2</div><div class="step-text">Confirmez la suppression.</div></div>
            <div class="step"><div class="step-num">3</div><div class="step-text"><strong>Effet :</strong> l'employé ne pourra plus se connecter. Cependant, son <strong>historique de ventes est conservé</strong> pour garder une trace complète.</div></div>
            <div class="faq-warn"><i class="bi bi-exclamation-triangle"></i> <div>La suppression est <strong>irréversible</strong>. Si vous voulez juste empêcher l'accès temporairement, modifiez plutôt son mot de passe.</div></div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.faq-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.faq-tab').forEach(function(t) { t.classList.remove('active'); });
        document.querySelectorAll('.faq-panel').forEach(function(p) { p.classList.remove('active'); });
        this.classList.add('active');
        document.getElementById('panel-' + this.dataset.tab).classList.add('active');
    });
});

function toggleFaq(el) {
    var answer = el.nextElementSibling;
    var isOpen = el.classList.contains('open');
    var panel = el.closest('.faq-panel');
    panel.querySelectorAll('.faq-question.open').forEach(function(q) { q.classList.remove('open'); q.nextElementSibling.classList.remove('open'); });
    if (!isOpen) {
        el.classList.add('open');
        answer.classList.add('open');
    }
}
</script>

@endsection
