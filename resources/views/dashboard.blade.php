@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('page-title', '  de bord')

@push('styles')
<style>
    @media (max-width: 640px) {
        .dash-date-form { flex-wrap: wrap; width: 100%; }
        .dash-date-form label { font-size: .75rem; }
        .dash-date-form input { max-width: 100%; flex: 1; min-width: 100px; }
        .dash-table td { font-size: .75rem; padding: 6px 6px; }
        .dash-table th { font-size: .65rem; padding: 6px 6px; }
        .dash-table .ref-date { font-size: .6rem; }
        .dash-footer { padding: 10px 12px !important; }
        .page-grid { gap: 12px; }
    }
</style>
@endpush

@section('actions')
<form method="GET" action="{{ route('dashboard') }}" class="dash-date-form" style="display:flex; align-items:center; gap:8px; margin-right:40px;">
    <label style="font-size:.85rem; font-weight:600; color:var(--text); white-space:nowrap;">Date :</label>
    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" style="padding:5px 10px; border:1px solid var(--border); border-radius:6px; font-size:.85rem; max-width:150px;">
    @if($date !== today()->format('Y-m-d'))
        <a href="{{ route('dashboard') }}" style="font-size:.8rem; color:var(--primary); text-decoration:none; white-space:nowrap;">Réinitialiser</a>
    @endif
</form>
@endsection

@section('content')

{{-- Bannière d'introduction de la société --}}
<div class="card" style="background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; border: none; padding: 20px 24px; margin-bottom: 24px; display: flex; flex-direction: row; align-items: center; gap: 16px; border-radius: var(--radius-card); box-shadow: var(--shadow-card);">
    <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
        <i class="bi bi-building" style="color: #fff;"></i>
    </div>
    <div>
        <h2 style="margin: 0; font-size: 1.35rem; font-weight: 800; color: #fff; font-family: 'Montserrat', sans-serif;">{{ $tenant->nom }}</h2>
        <p style="margin: 2px 0 0; font-size: 0.85rem; color: rgba(255,255,255,0.85); font-family: 'Inter', sans-serif;">Espace commercial connecté &bull; Marque : {{ $tenant->marque ?? 'N/A' }}</p>
    </div>
</div>

{{-- ─── Stats ─── --}}
@if(in_array(auth()->user()->role, ['super_admin', 'admin']))
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:16px;">
    <div style="display:flex; gap:6px; flex-wrap:wrap;"></div>
    <a href="{{ route('analytique') }}" class="btn btn-primary btn-sm" style="display:inline-flex; align-items:center; gap:6px; padding:8px 18px; font-size:.85rem;">
        <i class="bi bi-bar-chart-line"></i> Analyse avancée
    </a>
</div>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-currency-exchange"></i></div>
        <div>
            <div class="stat-val">{{ number_format($ventesJour, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Encaissé le {{ \Carbon\Carbon::parse($date)->format('d/m') }} (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
            <div class="stat-val">{{ number_format($caJour, 0, ',', ' ') }}</div>
            <div class="stat-lbl">C.A. du {{ \Carbon\Carbon::parse($date)->format('d/m') }} (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
            <div class="stat-val">{{ number_format($ventesMois, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Ventes du mois (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-cash-stack"></i></div>
        <div>
            <div class="stat-val">{{ number_format($depenseMois, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Dépenses du mois (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-bar-chart"></i></div>
        <div>
            <div class="stat-val">{{ number_format($caMois, 0, ',', ' ') }}</div>
            <div class="stat-lbl">C.A. du mois (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-building"></i></div>
        <div>
            <div class="stat-val" style="color:var(--danger);">{{ number_format($totalLoyerMois, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Loyers des dépôts (FCFA/mois)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
            <div class="stat-val" style="color:var(--success);">{{ number_format($revenuNetMois, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Revenu net du mois (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-receipt"></i></div>
        <div>
            <div class="stat-val">{{ $nbVentesJour }}</div>
            <div class="stat-lbl">Ventes du {{ \Carbon\Carbon::parse($date)->format('d/m') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
        <div>
            <div class="stat-val">{{ number_format($dettePaiementsJour, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Dettes encaissées le {{ \Carbon\Carbon::parse($date)->format('d/m') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-truck"></i></div>
        <div>
            <div class="stat-val">{{ $nbLivraisonsEnAttente }}</div>
            <div class="stat-lbl">Livraisons en attente
                <a href="{{ route('livraisons.index', ['statut' => 'en_attente']) }}" style="font-size:.7rem; color:var(--primary); text-decoration:none; margin-left:4px;">Gérer</a>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
        <div>
            <div class="stat-val">{{ $livraisonsDuJour }}</div>
            <div class="stat-lbl">Livrées aujourd'hui</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-credit-card-2-back"></i></div>
        <div>
            <div class="stat-val">{{ number_format($totalDettes, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Dettes actives (FCFA)
                @if($dettesEnRetard > 0)
                    <span class="badge badge-danger" style="margin-left:4px;">{{ $dettesEnRetard }} en retard</span>
                @endif
            </div>
        </div>
    </div>
    @if(count($stockAlertes) > 0)
    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-exclamation-triangle"></i></div>
        <div>
            <div class="stat-val">{{ count($stockAlertes) }}</div>
            <div class="stat-lbl">Produits en alerte stock</div>
        </div>
    </div>
    @endif
</div>

{{-- Formulaire dépense du jour --}}
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h3><i class="bi bi-cash"></i> Enregistrer une dépense</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('dashboard.depense.store') }}" enctype="multipart/form-data" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="audio_base64" id="depense-audio-base64" value="">
            <div style="flex: 1; min-width: 150px;">
                <label class="form-label" style="font-size: .8rem;">Montant (FCFA) *</label>
                <input type="number" name="montant" class="form-control" min="1" required placeholder="0">
            </div>
            <div style="flex: 2; min-width: 200px;" id="desc-group">
                <label class="form-label" style="font-size: .8rem;">Description</label>
                <div style="display: flex; gap: 4px; align-items: center;">
                    <input type="text" name="description" id="depense-desc" class="form-control" placeholder="Ex: Eau, transport, réparation..." maxlength="255" style="flex: 1;">
                    <button type="button" id="mic-btn" class="btn btn-outline-secondary" title="Enregistrer un vocal" style="padding: 6px 10px; flex-shrink:0;">
                        <i class="bi bi-mic"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Ajouter</button>
        </form>

        <div style="margin-top: 16px;">
            <h4 style="font-size: .9rem; font-weight: 600; margin-bottom: 8px; color: #475569;">
                <i class="bi bi-list-ul"></i> Dépenses du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
            </h4>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align: right;">Montant (FCFA)</th>
                            <th>Enregistré par</th>
                            <th>Heure</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($depensesDuJour as $d)
                        <tr>
                            <td>
                                @if($d->audio_path)
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span>{{ $d->description ?: '-' }}</span>
                                    <audio controls style="height:32px;width:140px;">
                                        <source src="{{ asset('storage/' . $d->audio_path) }}" type="audio/webm">
                                    </audio>
                                </div>
                                @else
                                    {{ $d->description ?: '-' }}
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 600; color: #dc2626;">-{{ number_format($d->montant, 0, ',', ' ') }}</td>
                            <td>{{ $d->user?->name ?? 'N/A' }}</td>
                            <td>{{ $d->created_at->format('H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center; color:var(--text-muted); padding:16px;">Aucune dépense ce jour</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($depensesDuJour->count() > 0)
                    <tfoot>
                        <tr style="background: #f1f5f9; font-weight: 700;">
                            <td>Total</td>
                            <td style="text-align: right; color: #dc2626;">-{{ number_format($depenseJour, 0, ',', ' ') }} FCFA</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const micBtn = document.getElementById('mic-btn');
    const descInput = document.getElementById('depense-desc');
    const descGroup = document.getElementById('desc-group');
    const audioBase64Input = document.getElementById('depense-audio-base64');
    const form = micBtn?.closest('form');
    if (!micBtn || !descInput || !audioBase64Input || !form) return;

    // ── Diagnostics ──
    const diag = document.createElement('div');
    diag.style.cssText = 'font-size:.7rem;color:#64748b;margin-top:2px;';
    micBtn.closest('div').after(diag);

    function log(msg) { diag.textContent = msg; }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        micBtn.disabled = true; micBtn.style.opacity = '.4';
        log('❌ Micro non supporté par le navigateur');
        return;
    }

    let mr = null, chunks = [], stream = null, busy = false, sec = 0, timer = null;

    micBtn.addEventListener('click', function(e) {
        e.preventDefault();

        if (mr) {
            if (mr.state === 'recording') {
                mr.stop();
                log('⏹️ Arrêt…');
                return;
            }
            if (mr.state === 'paused') {
                log('▶️ Reprise…');
                mr.resume();
                micBtn.className = 'btn btn-danger';
                micBtn.innerHTML = '<i class="bi bi-stop-fill"></i>';
                micBtn.title = 'Arrêter';
                return;
            }
        }

        // ── Démarrer ──
        log('🎤 Demande micro…');
        navigator.mediaDevices.getUserMedia({ audio: true }).then(function(s) {
            log('✅ Micro OK');
            stream = s;
            mr = new MediaRecorder(s);
            chunks = [];
            sec = 0;

            if (descGroup) descGroup.style.display = 'none';
            micBtn.className = 'btn btn-danger';
            micBtn.innerHTML = '<i class="bi bi-stop-fill"></i>';
            micBtn.title = 'Arrêter';

            let ind = document.getElementById('mic-indicator');
            if (!ind) {
                ind = document.createElement('div');
                ind.id = 'mic-indicator';
                micBtn.closest('div').after(ind);
            }
            ind.style.cssText = 'font-size:.75rem;color:#dc2626;font-weight:600;margin-top:4px;display:flex;align-items:center;gap:4px;';
            ind.innerHTML = '<span style="display:inline-block;width:8px;height:8px;background:#dc2626;border-radius:50%;animation:mic-pulse 1s infinite;"></span> 🔴 <span id="mic-timer">0s</span>';

            timer = setInterval(function() {
                sec++;
                const el = document.getElementById('mic-timer');
                if (el) el.textContent = sec + 's';
            }, 1000);

            mr.ondataavailable = function(e) {
                if (e.data.size > 0) chunks.push(e.data);
                log('📦 Données: ' + chunks.length + ' blocs');
            };

            mr.onstop = function() {
                log('⏹️ Traitement…');
                if (timer) { clearInterval(timer); timer = null; }
                const oldInd = document.getElementById('mic-indicator');

                if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
                mr = null;

                if (descGroup) descGroup.style.display = '';
                micBtn.className = 'btn btn-outline-secondary';
                micBtn.innerHTML = '<i class="bi bi-mic"></i>';
                micBtn.title = 'Enregistrer';

                if (chunks.length === 0) {
                    oldInd?.remove();
                    log('⚠️ Aucune donnée audio');
                    return;
                }

                const blob = new Blob(chunks, { type: 'audio/webm' });
                log('✅ ' + (blob.size/1024).toFixed(0) + ' Ko');

                const url = URL.createObjectURL(blob);
                const audio = document.createElement('audio');
                audio.controls = true;
                audio.src = url;
                audio.style.cssText = 'width:100%;margin-top:4px;';
                if (oldInd) oldInd.replaceWith(audio);

                busy = true;
                const r = new FileReader();
                r.onloadend = function() {
                    audioBase64Input.value = r.result;
                    busy = false;
                    log('✅ Prêt à envoyer');
                };
                r.readAsDataURL(blob);
            };

            mr.onerror = function(e) {
                log('❌ Erreur: ' + (e.error || 'inconnue'));
                document.getElementById('mic-indicator')?.remove();
                if (timer) { clearInterval(timer); timer = null; }
                if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
                mr = null;
                if (descGroup) descGroup.style.display = '';
                micBtn.className = 'btn btn-outline-secondary';
                micBtn.innerHTML = '<i class="bi bi-mic"></i>';
            };

            try {
                mr.start(1000);
                log('🔴 Enregistrement…');
            } catch(e) {
                log('❌ Erreur démarrage: ' + e.message);
            }
        }).catch(function(err) {
            log('❌ ' + err.message);
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                alert('Veuillez autoriser le microphone dans les paramètres du navigateur.');
            }
        });
    });

    // Stop recording on submit
    form.addEventListener('submit', function(e) {
        if (busy) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Audio…';
            const wait = setInterval(function() {
                if (!busy) {
                    clearInterval(wait);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-plus-circle"></i> Ajouter';
                    form.submit();
                }
            }, 100);
            return;
        }
        if (mr && (mr.state === 'recording' || mr.state === 'paused')) {
            e.preventDefault();
            mr.stop();
            const check = setInterval(function() {
                if (!mr) { clearInterval(check); form.submit(); }
            }, 50);
        }
    });
})();
</script>
<style>
@keyframes mic-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .2; } }
</style>

{{-- Stats par personne --}}
@if(count($statsParPersonne) > 0)
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h3><i class="bi bi-people"></i> Ventes par vendeur le {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Vendeur</th>
                    <th style="text-align: right;">Ventes (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statsParPersonne as $stat)
                <tr>
                    <td style="font-weight: 600;">{{ $stat->user?->name ?? 'N/A' }}</td>
                    <td style="text-align: right;">{{ number_format($stat->total_ventes, 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@else
    @if(count($stockAlertes) > 0)
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="stat-val">{{ count($stockAlertes) }}</div>
                <div class="stat-lbl">Produits en alerte stock</div>
            </div>
        </div>
    </div>
    @endif
@endif

{{-- ─── Grille principale ─── --}}
<div class="page-grid page-grid-3">

    {{-- Dernières ventes --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="bi bi-receipt"></i> Ventes du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h3>
            <a href="{{ route('ventes.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus"></i> Nouvelle vente
            </a>
        </div>
        <div class="table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Client</th>
                        <th>Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dernieresVentes as $v)
                    <tr>
                        <td>
                            <a href="{{ route('ventes.show', $v) }}" style="color:var(--primary); font-weight:500; text-decoration:none;">
                                {{ $v->reference }}
                            </a>
                            <div class="ref-date" style="font-size:.7rem; color:var(--text-muted);">{{ $v->date_vente->format('d/m H:i') }}</div>
                        </td>
                        <td>@if($v->client){{ $v->client->nomComplet() }}@else<i style="color:#94a3b8">Anonyme</i>@endif</td>
                        <td style="font-weight:600;">{{ number_format($v->montant_total, 0, ',', ' ') }}</td>
                        <td>
                            @if($v->statut_paiement === 'paye')
                                <span class="badge badge-success">Payé</span>
                            @elseif($v->statut_paiement === 'partiel')
                                <span class="badge badge-warning">Partiel</span>
                            @else
                                <span class="badge badge-danger">Impayé</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:24px;">Aucune vente ce jour</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="dash-footer" style="padding:12px 20px; border-top:1px solid var(--border);">
            <a href="{{ route('ventes.index') }}" style="font-size:.8rem; color:var(--primary); text-decoration:none;">
                Voir toutes les ventes <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    {{-- Colonne droite --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Dettes en retard --}}
        @if(count($dettesEnRetardListe) > 0)
        <div class="card" style="border-left: 4px solid var(--danger);">
            <div class="card-header">
                <h3 style="color:var(--danger);"><i class="bi bi-exclamation-triangle-fill"></i> Créances en retard ({{ $dettesEnRetard }})</h3>
                <a href="{{ route('dettes.index') }}" style="font-size:.8rem; color:var(--primary); text-decoration:none;">
                    Gérer <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body" style="padding:0;">
                @foreach($dettesEnRetardListe as $dette)
                <a href="/dettes/{{ $dette->id }}" style="display:flex; align-items:center; gap:12px; padding:10px 16px; border-bottom:1px solid var(--border); text-decoration:none; color:inherit; transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <div style="flex:1; min-width:0;">
                        <span style="font-size:.85rem; font-weight:600;">{{ $dette->client?->nomComplet() ?: 'Anonyme' }}</span>
                        <div style="font-size:.7rem; color:var(--text-muted);">
                            Échéance : {{ $dette->date_echeance?->format('d/m/Y') ?: 'N/A' }}
                            @if($dette->vente)
                                · {{ $dette->vente->reference }}
                            @endif
                        </div>
                    </div>
                    <span style="font-weight:700; color:var(--danger); white-space:nowrap;">
                        {{ number_format($dette->montant_restant, 0, ',', ' ') }} F
                    </span>
                    <span style="font-size:.75rem; color:var(--primary); white-space:nowrap;">
                        <i class="bi bi-wallet2"></i>
                    </span>
                </a>
                @endforeach
                <div style="padding:12px 16px;">
                    <a href="{{ count($dettesEnRetardListe) === 1 ? url('/dettes/' . $dettesEnRetardListe->first()->id) : route('dettes.index', ['statut' => 'en_retard']) }}" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-cash-stack"></i> {{ count($dettesEnRetardListe) === 1 ? 'Encaisser cette créance' : 'Voir toutes les créances en retard' }}
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Stock alertes --}}
        @if(count($stockAlertes) > 0)
        <div class="card">
            <div class="card-header">
                <h3 style="color:var(--warning);"><i class="bi bi-exclamation-triangle"></i> Stock critique</h3>
            </div>
            <div class="card-body" style="padding:0;">
                @foreach($stockAlertes as $alerte)
                <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-bottom:1px solid var(--border);">
                    <span style="font-size:.85rem; font-weight:500;">{{ $alerte['produit']->nom }}</span>
                    <span class="badge {{ $alerte['stock'] <= 0 ? 'badge-danger' : 'badge-warning' }}">
                        {{ $alerte['stock'] }} Carton
                    </span>
                </div>
                @endforeach
                <div style="padding:12px 16px;">
                    <a href="{{ route('arrivages.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-truck"></i> Commander un arrivage
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Stock critique --}}
        @if($magasinPrincipal && $stockApercu->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 style="color:var(--danger);"><i class="bi bi-exclamation-triangle"></i> Stock critique</h3>
                <span style="font-size:.75rem; color:var(--text-muted);">{{ $magasinPrincipal->nom }}</span>
            </div>
            <div class="card-body" style="padding:0; max-height: 280px; overflow-y: auto;">
                @foreach($stockApercu as $s)
                <a href="{{ route('produits.show', $s['produit']) }}" style="display:flex; align-items:center; justify-content:space-between; padding:8px 16px; border-bottom:1px solid var(--border); text-decoration:none; color:inherit; transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <span style="font-size:.8rem;">{{ $s['produit']->nom }}</span>
                    <span class="badge badge-danger">
                        {{ $s['stock'] }} Carton
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Top produits --}}
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-bar-chart"></i> Top produits (mois)</h3>
            </div>
            <div class="card-body" style="padding:0;">
                @forelse($topProduits as $i => $p)
                <div style="display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid var(--border);">
                    <span style="width:20px; height:20px; background:var(--primary); color:#fff; border-radius:50%;
                                 display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:700; flex-shrink:0;">
                        {{ $i + 1 }}
                    </span>
                    <span style="font-size:.85rem; flex:1;">{{ $p->nom }}</span>
                    <span style="font-size:.8rem; font-weight:600; color:var(--success);">{{ $p->total_vendu }}</span>
                </div>
                @empty
                <div style="padding:24px; text-align:center; color:var(--text-muted); font-size:.85rem;">
                    Aucune vente ce mois
                </div>
                @endforelse
            </div>
        </div>

        {{-- Collaborateurs & Activité --}}
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-people"></i> Activité des employés</h3>
            </div>
            <div class="card-body" style="padding:0; max-height: 250px; overflow-y: auto;">
                @forelse($employes as $emp)
                    @php
                        $isOnline = false;
                        if ($emp->last_seen) {
                            $isOnline = \Carbon\Carbon::parse($emp->last_seen)->diffInMinutes(now()) < 5;
                        }
                    @endphp
                    <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid var(--border);">
                        {{-- Indicateur de statut en ligne/hors ligne --}}
                        <div style="position:relative;">
                            <div style="width:36px; height:36px; background:#e2e8f0; color:#475569; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem;">
                                {{ strtoupper(substr($emp->name, 0, 2)) }}
                            </div>
                            <span style="position:absolute; bottom:-1px; right:-1px; width:11px; height:11px; border-radius:50%; border:2px solid #fff;
                                         background: {{ $isOnline ? '#22c55e' : '#94a3b8' }};"></span>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:.85rem; font-weight:600; color:var(--text); text-overflow:ellipsis; overflow:hidden; white-space:nowrap; margin-bottom: 2px;">
                                {{ $emp->name }}
                            </div>
                            <div style="font-size:.72rem; color:var(--text-muted); text-transform:capitalize;">
                                {{ $emp->role }}
                            </div>
                        </div>
                        <div style="text-align:right; font-size:.75rem; color:var(--text-muted);">
                            @if($isOnline)
                                <span style="color:#22c55e; font-weight:650;">En ligne</span>
                            @elseif($emp->last_seen)
                                {{ \Carbon\Carbon::parse($emp->last_seen)->diffForHumans() }}
                            @else
                                <span style="color:#94a3b8; font-style:italic;">Jamais connecté</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="padding:24px; text-align:center; color:var(--text-muted); font-size:.85rem;">
                        Aucun autre employé enregistré
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
