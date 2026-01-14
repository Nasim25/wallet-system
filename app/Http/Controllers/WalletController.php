<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('wallet');

        return response()->json([
            'id' => $user->wallet?->id,
            'balance' => $user->wallet?->balance ?? 0,
            'agreement_token' => !blank($user->wallet?->agreement_token),
            'masked_number' => $user->wallet?->masked_number ?? "",
        ]);
    }
}
