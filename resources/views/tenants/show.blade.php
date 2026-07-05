@extends('layouts.app')
@section('title', "Société : " . $tenant->nom)

@section('content')
<div style="margin-bottom: 20px; display: flex; gap: 10px; justify-content: space-between; align-items: center; flex-wrap: wrap;">
    <a href="{{ route('tenants.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
    <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-pencil-square"></i> Modifier la Société
    </a>
</div>

<div class="page-grid page-grid-3">
    <!-- Liste des Magasins et Utilisateurs -->
    <div>
        <!-- Magasins -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h3><i class="bi bi-shop"></i> Magasins / Dépôts associés</h3>
            </div>

            <div class="table-wrap" style="margin-bottom: 20px;">
                <table>
                    <thead>
                        <tr>
                            <th>Nom du Magasin</th>
                            <th>Adresse / Ville</th>
                            <th style="text-align: center; width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenant->magasins as $m)
                        <tr>
                            <td style="font-weight: 600;">{{ $m->nom }}</td>
                            <td>{{ $m->adresse ?? '-' }}</td>
                            <td style="text-align: center;">
                                <form action="{{ route('tenants.magasins.destroy', [$tenant, $m]) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce magasin ? Cette action est irréversible.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px;" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 16px;">Aucun magasin créé pour le moment.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Ajouter un magasin -->
            <div style="background: var(--bg); border: 1px solid var(--border); border-radius: 6px; padding: 15px;">
                <h4 style="margin: 0 0 10px; font-size: 0.95rem;"><i class="bi bi-plus-circle"></i> Ajouter un magasin ou dépôt</h4>
                <form action="{{ route('tenants.magasins.store', $tenant) }}" method="POST">
                    @csrf
                    <div class="form-row form-row-2">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Nom du Magasin *</label>
                            <input type="text" name="nom" class="form-control" placeholder="Ex : Boutique Cotonou" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Adresse / Localisation</label>
                            <input type="text" name="adresse" class="form-control" placeholder="Ex : Cotonou, St Michel">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; margin-top: 12px;">
                        Ajouter le magasin
                    </button>
                </form>
            </div>
        </div>

        <!-- Utilisateurs -->
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-people-fill"></i> Employés & Rôles</h3>
            </div>
            <div class="table-wrap" style="margin-bottom: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Nom complet</th>
                            <th>Email / Connexion</th>
                            <th>Magasin assigné</th>
                            <th>Rôle principal</th>
                            <th>Rôles secondaires</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenant->users as $u)
                        <tr>
                            <td style="font-weight: 600;">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge badge-gray">{{ $u->magasin?->nom ?? 'Tous' }}</span></td>
                            <td>
                                @if($u->role === 'admin')
                                    <span class="badge badge-success">Administrateur</span>
                                @elseif($u->role === 'vendeur')
                                    <span class="badge badge-warning">Vendeur</span>
                                @elseif($u->role === 'livreur')
                                    <span class="badge badge-success" style="background:#dbeafe; color:#1d4ed8;">Livreur</span>
                                @elseif($u->role === 'magasinier')
                                    <span class="badge badge-warning" style="background:#ffedd5; color:#c2410c;">Magasinier</span>
                                @else
                                    <span class="badge badge-gray">{{ $u->role }}</span>
                                @endif
                            </td>
                            <td>
                                @if($u->roles_secondaires && count($u->roles_secondaires) > 0)
                                    @foreach($u->roles_secondaires as $rs)
                                        <span class="badge badge-gray" style="font-size: 0.75rem; margin-right: 2px;">{{ $rs }}</span>
                                    @endforeach
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">Aucun</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 16px;">Aucun utilisateur créé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Détails de la société -->
    <div>
        <div class="card">
            <h3 style="margin-bottom: 15px;"><i class="bi bi-info-square"></i> Fiche d'identité</h3>
            
            <div style="display: flex; flex-direction: column; gap: 14px; font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                <div>
                    <label class="form-label" style="margin-bottom: 2px;">Nom / Raison Sociale :</label>
                    <div style="font-weight: 700;">{{ $tenant->nom }}</div>
                </div>
                
                @if($tenant->marque)
                <div>
                    <label class="form-label" style="margin-bottom: 2px;">Marque :</label>
                    <div style="font-weight: 600; color: var(--primary);">{{ $tenant->marque }}</div>
                </div>
                @endif

                @if($tenant->activite)
                <div>
                    <label class="form-label" style="margin-bottom: 2px;">Secteur d'activité :</label>
                    <div>{{ $tenant->activite }}</div>
                </div>
                @endif

                <div>
                    <label class="form-label" style="margin-bottom: 2px;">Pays & Ville :</label>
                    <div>{{ $tenant->ville ?? 'Non renseignée' }} ({{ $tenant->pays }})</div>
                </div>

                @if($tenant->telephone)
                <div>
                    <label class="form-label" style="margin-bottom: 2px;">Téléphone principal :</label>
                    <div>{{ $tenant->telephone }}</div>
                </div>
                @endif

                @if($tenant->email)
                <div>
                    <label class="form-label" style="margin-bottom: 2px;">E-mail principal :</label>
                    <div><a href="mailto:{{ $tenant->email }}">{{ $tenant->email }}</a></div>
                </div>
                @endif

                <div>
                    <label class="form-label" style="margin-bottom: 2px;">Statut :</label>
                    <div>
                        @if($tenant->actif)
                            <span class="badge badge-success">Actif</span>
                        @else
                            <span class="badge badge-danger">Inactif</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
