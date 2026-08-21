@extends('layouts.app')

@section('title', 'Dépenses')
@section('page-title', 'Dépenses')

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
    <div class="card-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-cash-stack"></i> Toutes les dépenses
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ $depenses->count() }}</span>
        </h3>
        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <span style="font-weight:700; color:var(--danger);">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
            <a href="{{ route('dashboard', ['date' => $debut]) }}#depenses" class="btn btn-sm btn-secondary">
                <i class="bi bi-plus-circle"></i> Ajouter
            </a>
        </div>
    </div>

    {{-- Filtre de période --}}
    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; align-items:center;">
        @foreach($presets as $key => $p)
            <a href="{{ route('depenses.index', ['date_debut' => $p['debut'], 'date_fin' => $p['fin']]) }}"
               class="btn btn-sm {{ $activePreset === $key ? 'btn-primary' : 'btn-secondary' }}">{{ $p['label'] }}</a>
        @endforeach
        <a href="{{ route('depenses.index', ['date_debut' => $debut, 'date_fin' => $fin, 'perso' => 1]) }}"
           class="btn btn-sm {{ request('perso') ? 'btn-primary' : 'btn-secondary' }}">Personnalisé</a>
    </div>

    @if(request('perso'))
    <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; margin-bottom:12px;">
        <div>
            <label class="form-label" style="margin-bottom:4px;">Du</label>
            <input type="date" name="date_debut" class="form-control" value="{{ $debut }}">
        </div>
        <div>
            <label class="form-label" style="margin-bottom:4px;">Au</label>
            <input type="date" name="date_fin" class="form-control" value="{{ $fin }}">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
    </form>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Par</th>
                    <th style="text-align:right;">Montant</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($depenses as $d)
                    <tr>
                        <td>{{ $d->date_depense ? $d->date_depense->format('d/m/Y') : '—' }}</td>
                        <td>{!! $d->description ?: '<em style="color:#94a3b8;">Sans description</em>' !!}</td>
                        <td>{{ $d->user->name ?? '—' }}</td>
                        <td style="text-align:right; color:#dc2626; font-weight:700;">-{{ number_format($d->montant, 0, ',', ' ') }} FCFA</td>
                        <td style="text-align:right;">
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
                            Aucune dépense sur cette période.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($depenses->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right; font-weight:700;">Total période</td>
                    <td style="text-align:right; color:#dc2626; font-weight:800;">-{{ number_format($total, 0, ',', ' ') }} FCFA</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
