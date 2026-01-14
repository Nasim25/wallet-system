<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $wallet = auth()->user()->wallet;

        if (!$wallet) {
            return response()->json(['transactions' => []]);
        }

        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($transactions);
    }

    // Download PDF Statement
    public function downloadStatement(Request $request)
    {
        $wallet = auth()->user()->wallet;

        if (!$wallet) {
            abort(404, 'Wallet not found');
        }

        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->get();

        $html = view('pdf.statement', [
            'user' => auth()->user(),
            'wallet' => auth()->user()->wallet,
            'transactions' => $transactions,
        ])->render();

        // Call Gotenberg to generate PDF
        $response = Http::asMultipart()->post( env('GOTENBERG_URL').'/forms/chromium/convert/html', [
            [
                'name' => 'files',
                'contents' => $html,
                'filename' => 'index.html',
            ]
        ]);

        return response($response->body())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="statement.pdf"');
    }
}
