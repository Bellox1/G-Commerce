<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionRule;
use Illuminate\Http\Request;

class OffreController extends Controller
{
    /**
     * Liste les offres pour que le super admin définisse les prix et commissions.
     */
    public function index(Request $request)
    {
        $regles = CommissionRule::where('code', '!=', 'locale')->orderBy('id')->get();
        $paliers = \App\Models\Setting::get('prime_paliers', [5, 10, 15]);

        if ($this->isApi($request)) {
            return response()->json(['success' => true, 'data' => $regles, 'paliers' => $paliers]);
        }

        return view('admin.offres.index', compact('regles', 'paliers'));
    }

    /**
     * Met à jour le prix d'abonnement et/ou la commission d'une offre.
     */
    public function update(Request $request, string $code)
    {
        $regle = CommissionRule::where('code', $code)->firstOrFail();
        $paliers = \App\Models\Setting::get('prime_paliers', [5, 10, 15]);

        $rules = [
            'prix'       => 'required|numeric|min:0',
            'commission' => 'required|numeric|min:0',
        ];
        foreach ($paliers as $seuil) {
            $rules['prime_' . $seuil] = 'required|numeric|min:0';
        }
        $request->validate($rules);

        $primes = [];
        foreach ($paliers as $seuil) {
            $primes[$seuil] = $request->input('prime_' . $seuil);
        }

        $regle->update([
            'prix'       => $request->prix,
            'commission' => $request->commission,
            'primes'     => $primes,
        ]);

        if ($this->isApi($request)) {
            return response()->json(['success' => true, 'message' => 'Offre mise à jour.', 'data' => $regle]);
        }

        return back()->with('success', "L'offre « {$regle->nom} » a été mise à jour.");
    }

    /**
     * Met à jour les paliers (nombre de ventes) déclenchant les primes.
     */
    public function updatePaliers(Request $request)
    {
        $data = $request->validate([
            'paliers'   => 'required|array|min:1',
            'paliers.*' => 'required|integer|min:1',
        ]);

        $paliers = array_map('intval', $data['paliers']);
        sort($paliers);

        \App\Models\Setting::set('prime_paliers', $paliers);

        if ($this->isApi($request)) {
            return response()->json(['success' => true, 'message' => 'Paliers de primes mis à jour.', 'data' => $paliers]);
        }

        return back()->with('success', 'Paliers de primes mis à jour : ' . implode(', ', $paliers) . ' ventes.');
    }

    protected function isApi(Request $request): bool
    {
        return $request->expectsJson()
            || $request->is('api/*')
            || ($request->header('X-Requested-With') === 'XMLHttpRequest' && ! $request->header('X-No-Api'));
    }
}
