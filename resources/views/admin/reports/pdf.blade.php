<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #10b981;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 24px;
            color: #10b981;
        }
        .header p {
            margin: 0;
            color: #666;
            font-size: 11px;
        }
        .summary {
            margin: 20px 0;
            padding: 15px;
            background-color: #f0fdf4;
            border-radius: 8px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .summary-label {
            font-weight: bold;
            color: #065f46;
        }
        .summary-value {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        thead {
            background-color: #10b981;
            color: white;
        }
        th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .order-id {
            font-weight: bold;
            font-family: monospace;
        }
        .payment-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        .payment-cash {
            background-color: #dcfce7;
            color: #166534;
        }
        .payment-qris {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .payment-debit {
            background-color: #f3e8ff;
            color: #6b21a8;
        }
        .payment-credit {
            background-color: #ffedd5;
            color: #9a3412;
        }
        .payment-transfer {
            background-color: #f3f4f6;
            color: #374151;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
        }
        .page-number {
            text-align: right;
            margin-top: 20px;
            color: #9ca3af;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report</h1>
        <p>
            Period: {{ ucfirst($period) }}
            @if($period === 'custom' && $startDate && $endDate)
                ({{ $from->format('d M Y') }} - {{ $to->format('d M Y') }})
            @else
                ({{ $from->format('d M Y') }})
            @endif
        </p>
        <p>Generated on {{ now()->format('d M Y, H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Total Revenue:</span>
            <span class="summary-value">Rp {{ number_format($metrics['total_revenue'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Orders:</span>
            <span class="summary-value">{{ $metrics['total_orders'] }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Average Order Value:</span>
            <span class="summary-value">Rp {{ number_format($metrics['average_order_value'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Items Sold:</span>
            <span class="summary-value">{{ $metrics['total_items_sold'] }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date & Time</th>
                <th>Customer</th>
                <th>Cashier</th>
                <th class="text-center">Payment</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td class="order-id">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $order->created_at->format('d M Y, H:i') }}</td>
                    <td>{{ $order->customer_name ?? 'Walk-in' }}
                        @if($order->customer_phone)
                            <br><small style="color: #666;">{{ $order->customer_phone }}</small>
                        @endif
                    </td>
                    <td>{{ $order->user->name ?? '-' }}</td>
                    <td class="text-center">
                        <span class="payment-badge payment-{{ $order->payment_method }}">
                            {{ ucfirst($order->payment_method) }}
                        </span>
                    </td>
                    <td class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #9ca3af;">
                        No sales transactions found for this period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Cycle POS - Sales Report</p>
        <p>This is an auto-generated report. For questions, contact your system administrator.</p>
    </div>

    <div class="page-number">
        Page 1 of 1
    </div>
</body>
</html>
