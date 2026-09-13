<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function csv(Request $request): StreamedResponse
    {
        $user = Auth::user();

        $query = Transaction::where('user_id', $user->id)->with('category');

        if ($dateFrom = $request->get('date_from')) {
            $query->where('date', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->where('date', '<=', $dateTo);
        }

        $transactions = $query->orderByDesc('date')->get();

        $filename = 'transactions-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Date',
                'Type',
                'Category',
                'Amount',
                'Payment Method',
                'Notes',
                'Source',
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->date->format('Y-m-d'),
                    $transaction->type->value,
                    $transaction->category?->name_ar ?? '',
                    $transaction->amount / 100,
                    $transaction->payment_method->value,
                    $transaction->notes ?? '',
                    $transaction->source->value,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
