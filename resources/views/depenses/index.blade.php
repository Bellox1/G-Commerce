@extends('layouts.app')

@section('title', 'Dépenses & Trésorerie')
@section('page-title', 'Dépenses & Trésorerie')

@php
    $today = today();
    $hier = today()->subDay();
    $il_y_a_7 = today()->subDays(6);
    $lundi = today()->startOfWeek();
    $presets = [
        'aujourd_hui' => ['label' => "Aujourd'hui", 'debut' => $today->format('Y-m-d'), 'fin' => $today->format('Y-m-d')],
        'hier'        => ['label' => 'Hier',       'debut' => $hier->format('Y-m-d'),  'fin' => $hier->format('Y-m-d')],
        '7_jours'     => ['label' => '7 derniers jours', 'debut' => $il_y_a_7->format('Y-m-d'), 'fin' => $today->format('Y-m-d')],
        'semaine'     => ['label' => 'Cette semaine', 'debut' => $lundi->format('Y-m-d'), 'fin' => $today->format('Y-m-d')],
    ];
    $activePreset = null;
    foreach ($presets as $key => $p) {
        if ($p['debut'] === $debut && $p['fin'] === $fin) { $activePreset = $key; break; }
    }
@endphp

@section('content')

<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <h3 style="display:flex; align-items:center; gap:8px; margin:0;">
            <i class="bi bi-cash-stack"></i> Dépenses &amp; Trésorerie
            <span style="font-size:0.75rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 10px; font-weight:700;">{{ $depenses->count() }}</span>
        </h3>
        <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <span style="font-weight:800; font-size:1.1rem; color:var(--danger);">-{{ number_format($total, 0, ',', ' ') }} FCFA</span>
            <a href="{{ route('dashboard', ['date' => $debut]) }}#depenses" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Saisir une dépense
            </a>
        </div>
    </div>

    <div class="card-body" style="padding:16px;">
        {{-- Filtre de période --}}
        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; align-items:center;">
            @foreach($presets as $key => $p)
                <a href="{{ route('depenses.index', ['date_debut' => $p['debut'], 'date_fin' => $p['fin']]) }}"
                   class="btn btn-sm {{ $activePreset === $key ? 'btn-primary' : 'btn-secondary' }}" style="border-radius:8px;">{{ $p['label'] }}</a>
            @endforeach
            <a href="{{ route('depenses.index', ['date_debut' => $debut, 'date_fin' => $fin, 'perso' => 1]) }}"
               class="btn btn-sm {{ request('perso') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius:8px;">Personnalisé</a>
        </div>

        @if(request('perso'))
        <form method="GET" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:12px; align-items:end; margin-bottom:16px; background:#f8fafc; padding:14px; border-radius:12px; border:1px solid #e2e8f0;">
            <div>
                <label class="form-label" style="margin-bottom:4px; font-size:0.8rem; font-weight:700;">Date Début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ $debut }}" style="width:100%;">
            </div>
            <div>
                <label class="form-label" style="margin-bottom:4px; font-size:0.8rem; font-weight:700;">Date Fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ $fin }}" style="width:100%;">
            </div>
            <div>
                <button type="submit" class="btn btn-primary btn-sm" style="width:100%;"><i class="bi bi-filter"></i> Filtrer</button>
            </div>
        </form>
        @endif

        <div class="table-wrap" style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Enregistré par</th>
                        <th style="text-align:right;">Montant</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($depenses as $d)
                        <tr>
                            <td style="white-space:nowrap; font-weight:600;">{{ $d->date_depense ? $d->date_depense->format('d/m/Y') : '—' }}</td>
                            <td>{!! $d->description ?: '<em style="color:#94a3b8;">Sans description</em>' !!}</td>
                            <td style="white-space:nowrap;">{{ $d->user->name ?? '—' }}</td>
                            <td style="text-align:right; color:#dc2626; font-weight:800; white-space:nowrap;">-{{ number_format($d->montant, 0, ',', ' ') }} FCFA</td>
                            <td style="text-align:right; white-space:nowrap;">
                                <form method="POST" action="{{ route('depenses.destroy', $d) }}" onsubmit="return confirm('Supprimer cette dépense ?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; color:#94a3b8; padding:30px;">
                                Aucune dépense enregistrée sur cette période.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($depenses->count() > 0)
                <tfoot>
                    <tr style="background:#f8fafc;">
                        <td colspan="3" style="text-align:right; font-weight:700;">Total Période</td>
                        <td style="text-align:right; color:#dc2626; font-weight:900; font-size:1.05rem;">-{{ number_format($total, 0, ',', ' ') }} FCFA</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@endsection
