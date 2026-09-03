<?php

namespace App\Http\Controllers;

use App\Services\TotpService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function setup(Request $request, TotpService $totp)
    {
        $user = $request->user();

        if (!$this->requiresTwoFactor($user)) {
            return redirect()->route('redirect.role');
        }

        if (!$user->two_factor_secret) {
            $user->forceFill([
                'two_factor_secret' => $totp->generateSecret(),
            ])->save();
        }

        if ($user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.challenge');
        }

        return view('auth.two_factor_setup', [
            'secret' => $user->two_factor_secret,
        ]);
    }

    public function qr(Request $request, TotpService $totp)
    {
        $user = $request->user();

        abort_unless($this->requiresTwoFactor($user) && $user->two_factor_secret, 404);

        $uri = $totp->provisioningUri('Comercializadora Alimentos', $user->email, $user->two_factor_secret);

        $result = (new Builder(
            writer: new SvgWriter(),
            data: $uri,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 280,
            margin: 12,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            validateResult: false
        ))->build();

        return response($result->getString(), 200)->header('Content-Type', $result->getMimeType());
    }

    public function challenge(Request $request)
    {
        $user = $request->user();

        if (!$this->requiresTwoFactor($user)) {
            return redirect()->route('redirect.role');
        }

        if (!$user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.setup');
        }

        return view('auth.two_factor_challenge');
    }

    public function confirm(Request $request, TotpService $totp)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:12'],
        ]);

        $user = $request->user();

        abort_unless($this->requiresTwoFactor($user) && $user->two_factor_secret, 403);

        if (!$totp->verify($user->two_factor_secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Codigo 2FA invalido o vencido.'])->onlyInput('code');
        }

        $user->forceFill([
            'two_factor_confirmed_at' => $user->two_factor_confirmed_at ?? now(),
            'two_factor_last_verified_at' => now(),
        ])->save();

        $request->session()->put('two_factor_verified', true);
        $request->session()->put('two_factor_verified_at', now()->timestamp);

        return redirect()->intended(route('redirect.role'));
    }

    private function requiresTwoFactor($user): bool
    {
        return $user
            && $user->tieneRol('admin', 'auditor')
            && !in_array(mb_strtolower($user->email), config('two_factor.bypass_emails', []), true);
    }
}
