<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'L\'adresse e-mail est requise.',
            'email.email' => 'L\'adresse e-mail doit être valide.',
            'email.exists' => 'Aucun utilisateur n\'est enregistré avec cette adresse e-mail.',
        ]);

        $email = $request->email;
        $otp = rand(100000, 999999);

        // Enregistrer le code OTP dans la base de données
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $otp, // Stocke l'OTP directement
                'created_at' => Carbon::now()
            ]
        );

        $resetUrl = route('password.reset', ['email' => $email, 'code' => $otp]);

        // Envoyer l'email
        try {
            Mail::send([], [], function ($message) use ($email, $otp, $resetUrl) {
                $message->to($email)
                    ->subject('G-STOCK — Réinitialisation de votre mot de passe')
                    ->html("
                        <div style=\"font-family: 'Inter', sans-serif; max-width: 550px; margin: 0 auto; padding: 30px; border: 1px solid #e5e7eb; border-radius: 12px; background-color: #ffffff;\">
                            <div style=\"text-align: center; margin-bottom: 24px;\">
                                <h2 style=\"color: #105e49; font-weight: 800; font-size: 24px; margin: 0 0 8px 0;\">G-STOCK</h2>
                                <p style=\"color: #6b7280; font-size: 14px; margin: 0;\">Gestion commerciale & stock</p>
                            </div>
                            <div style=\"border-bottom: 1px solid #f3f4f6; margin-bottom: 24px;\"></div>
                            <h3 style=\"color: #1f2937; font-weight: 700; font-size: 18px; margin: 0 0 12px 0;\">Demande de réinitialisation</h3>
                            <p style=\"color: #4b5563; font-size: 15px; line-height: 1.5; margin: 0 0 20px 0;\">
                                Bonjour,<br><br>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte G-STOCK. Voici votre code de validation à usage unique (OTP) :
                            </p>
                            <div style=\"background-color: #f4faf8; border: 1px dashed #167e65; border-radius: 8px; text-align: center; padding: 15px 0; margin-bottom: 20px;\">
                                <span style=\"font-size: 32px; font-weight: 800; letter-spacing: 6px; color: #105e49;\">{$otp}</span>
                            </div>
                            <p style=\"color: #4b5563; font-size: 15px; line-height: 1.5; margin: 0 0 20px 0;\">
                                Vous pouvez également réinitialiser directement votre mot de passe en cliquant sur le bouton ci-dessous :
                            </p>
                            <div style=\"text-align: center; margin-bottom: 24px;\">
                                <a href=\"{$resetUrl}\" style=\"display: inline-block; background-color: #105e49; color: #ffffff; text-decoration: none; padding: 12px 30px; font-weight: 700; border-radius: 6px; font-size: 15px;\">Réinitialiser mon mot de passe</a>
                            </div>
                            <p style=\"color: #6b7280; font-size: 13px; line-height: 1.4; margin: 0;\">
                                Ce code et ce lien expireront dans 15 minutes. Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail en toute sécurité.
                            </p>
                        </div>
                    ");
            });
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible d\'envoyer le mail. Erreur: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['email' => 'Impossible d\'envoyer le mail. Veuillez vérifier la configuration de messagerie dans votre fichier .env.'])->withInput();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Un code OTP et un lien de réinitialisation vous ont été envoyés par email.'
            ]);
        }

        return redirect()->route('password.reset', ['email' => $email])
            ->with('status', 'Un code OTP et un lien de réinitialisation vous ont été envoyés par email.');
    }

    public function showResetPassword(Request $request)
    {
        return view('auth.reset-password', [
            'email' => $request->query('email'),
            'code' => $request->query('code'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'code'     => 'required|numeric',
            'password' => [
                'required',
                'confirmed',
                'min:6',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            ],
        ], [
            'email.required'    => "L'adresse e-mail est requise.",
            'email.exists'      => "Cette adresse e-mail n'est pas enregistrée.",
            'code.required'     => 'Le code OTP est requis.',
            'code.numeric'      => 'Le code OTP doit être numérique.',
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.min'      => 'Le mot de passe doit faire au moins 6 caractères.',
            'password.confirmed'=> 'La confirmation du mot de passe ne correspond pas.',
            'password.regex'    => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Aucune demande en cours pour cet email.'], 400);
            }
            return back()->withErrors(['code' => 'Aucune demande en cours pour cet email.'])->withInput();
        }

        // Vérification de validité (15 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Le code OTP a expiré.'], 400);
            }
            return back()->withErrors(['code' => 'Le code de validation/OTP a expiré. Veuillez refaire une demande.'])->withInput();
        }

        // Vérification de l'OTP
        if ($record->token !== $request->code) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Code OTP incorrect.'], 400);
            }
            return back()->withErrors(['code' => 'Le code OTP saisi est incorrect.'])->withInput();
        }

        // Mettre à jour le mot de passe de l'utilisateur
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Supprimer le token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Votre mot de passe a été réinitialisé avec succès.'
            ]);
        }

        return redirect()->route('login')->with('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    }
}
