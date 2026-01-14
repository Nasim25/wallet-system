<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Transaction Statement</title>

    <style>
        @page {
            margin: 20mm 15mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0;
        }

        .meta {
            margin-bottom: 15px;
        }

        .meta p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 6px;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: left;
        }

        td.amount {
            text-align: right;
        }

        .status-completed {
            color: #16a34a;
            font-weight: bold;
        }

        .status-pending {
            color: #ca8a04;
            font-weight: bold;
        }

        .status-failed {
            color: #dc2626;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 2mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <h1>Transaction Statement</h1>
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    {{-- USER INFO --}}
    <div class="meta">
        <p><strong>Name:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Wallet ID:</strong> {{ $wallet->id }}</p>
    </div>

    {{-- TRANSACTION TABLE --}}
    <table>
        <thead>
            <tr>
                <th style="width: 18%">Date</th>
                <th style="width: 22%">Transaction ID</th>
                <th style="width: 12%">Type</th>
                <th style="width: 14%">Status</th>
                <th style="width: 14%; text-align:right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $tx)
                <tr>
                    <td>{{ $tx->created_at->format('d M Y') }}</td>
                    <td>{{ $tx->trx_id }}</td>
                    <td>{{ ucfirst($tx->type) }}</td>
                    <td
                        class="
                        {{ $tx->status === 'completed' ? 'status-completed' : '' }}
                        {{ $tx->status === 'pending' ? 'status-pending' : '' }}
                        {{ $tx->status === 'failed' ? 'status-failed' : '' }}
                    ">
                        {{ ucfirst($tx->status) }}
                    </td>
                    <td class="amount">৳ {{ number_format($tx->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding: 20px;">
                        No transactions found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        This is a system generated statement
    </div>

</body>

</html>
