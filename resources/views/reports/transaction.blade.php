{{-- resources/views/reports/transactions.blade.php --}}
    <!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>كشف حساب - {{ $account->account_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            direction: rtl;
            text-align: right;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 3px 0;
            font-size: 12px;
        }

        .account-info {
            margin-bottom: 15px;
        }

        .account-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .account-info td {
            padding: 4px 0;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .transactions-table th,
        .transactions-table td {
            border: 1px solid #ccc;
            padding: 6px;
            font-size: 11px;
        }

        .transactions-table th {
            background-color: #f0f0f0;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
        }

        .badge-deposit {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-withdraw {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-transfer {
            background-color: #cce5ff;
            color: #004085;
        }

        .footer {
            margin-top: 25px;
            font-size: 10px;
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>كشف حساب مالي</h1>
    <p>رقم الحساب: {{ $account->account_number }}</p>
    <p>
        الفترة من
        {{ $fromDate->format('Y-m-d') }}
        إلى
        {{ $toDate->format('Y-m-d') }}
    </p>
</div>

<div class="account-info">
    <table>
        <tr>
            <td><strong>اسم صاحب الحساب:</strong> {{ $account->user->name ?? '-' }}</td>
            <td><strong>نوع الحساب:</strong> {{ $account->type }}</td>
        </tr>
        <tr>
            <td><strong>الحالة الحالية:</strong> {{ $account->status }}</td>
            <td><strong>الرصيد الحالي:</strong> {{ number_format($account->balance, 2) }}</td>
        </tr>
    </table>
</div>

<table class="transactions-table">
    <thead>
    <tr>
        <th class="text-center">#</th>
        <th>نوع العملية</th>
        <th>المبلغ</th>
        <th>حساب المصدر</th>
        <th>حساب الوجهة</th>
        <th>تاريخ التنفيذ</th>
    </tr>
    </thead>
    <tbody>
    @foreach($transactions as $transaction)
        @php
            $typeLabel = match ($transaction->type) {
                'deposit'  => 'إيداع',
                'withdraw' => 'سحب',
                'transfer' => 'تحويل',
                default    => $transaction->type,
            };

            $badgeClass = match ($transaction->type) {
                'deposit'  => 'badge badge-deposit',
                'withdraw' => 'badge badge-withdraw',
                'transfer' => 'badge badge-transfer',
                default    => 'badge',
            };
        @endphp
        <tr>
            <td class="text-center">{{ $transaction->id }}</td>
            <td>
                <span class="{{ $badgeClass }}">{{ $typeLabel }}</span>
            </td>
            <td>{{ number_format($transaction->amount, 2) }}</td>
            <td>{{ $transaction->fromAccount->account_number ?? '-' }}</td>
            <td>{{ $transaction->toAccount->account_number ?? '-' }}</td>
            <td>{{ optional($transaction->executed_at)->format('Y-m-d H:i') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    تم توليد هذا التقرير بتاريخ {{ now()->format('Y-m-d H:i') }}
</div>

</body>
</html>

