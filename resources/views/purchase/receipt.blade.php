<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Receipt #{{ $purchase->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            background: #f0f0f0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.4;
        }

        .receipt {
            background: white;
            width: 80mm;
            margin: 0 auto;
            padding: 10mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 11px;
            margin: 2px 0;
        }

        .info {
            margin-bottom: 15px;
            font-size: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .items {
            margin-bottom: 15px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
        }

        .item {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 12px;
        }

        .item-name {
            flex: 1;
        }

        .item-qty {
            text-align: center;
            min-width: 40px;
        }

        .item-price {
            text-align: right;
            min-width: 60px;
        }

        .totals {
            margin-bottom: 15px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 13px;
        }

        .total-row.final {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px dashed #000;
            padding-top: 5px;
            margin-top: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 11px;
        }

        .supplier-info {
            background: #f5f5f5;
            padding: 5px;
            margin: 10px 0;
            font-size: 11px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt {
                box-shadow: none;
                margin: 0;
                width: 100%;
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <h1>{{ $settings['store_name'] ?? 'Cycle Store' }}</h1>
            <p>{{ $settings['store_address'] ?? '' }}</p>
            <p>Tel: {{ $settings['store_phone'] ?? '-' }}</p>
            <p style="margin-top: 10px;">*** PURCHASE ORDER RECEIPT ***</p>
        </div>

        <!-- Info -->
        <div class="info">
            <div class="info-row">
                <span>PO #:</span>
                <span>{{ str_pad($purchase->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-row">
                <span>Date:</span>
                <span>{{ $purchase->created_at->format('Y-m-d H:i') }}</span>
            </div>
            <div class="info-row">
                <span>Cashier:</span>
                <span>{{ $purchase->user->name ?? 'System' }}</span>
            </div>
        </div>

        <!-- Supplier Info -->
        @if($purchase->supplier_name)
            <div class="supplier-info">
                <div class="info-row">
                    <span>Supplier:</span>
                    <span>{{ $purchase->supplier_name }}</span>
                </div>
                @if($purchase->supplier_phone)
                    <div class="info-row">
                        <span>Phone:</span>
                        <span>{{ $purchase->supplier_phone }}</span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Items -->
        <div class="items">
            <div class="item" style="font-weight: bold; border-bottom: 1px dashed #000; padding-bottom: 5px;">
                <span class="item-name">ITEM</span>
                <span class="item-qty">QTY</span>
                <span class="item-price">PRICE</span>
            </div>
            @foreach($purchase->items as $item)
                <div style="margin-top: 5px;">
                    <div class="item">
                        <span class="item-name">{{ $item->item->name }}</span>
                        <span class="item-qty">{{ number_format($item->quantity, 2) }}</span>
                        <span class="item-price">{{ $settings['currency_symbol'] ?? 'Rp' }} {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row final">
                <span>TOTAL</span>
                <span>{{ $settings['currency_symbol'] ?? 'Rp' }} {{ number_format($purchase->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="info-row" style="margin-top: 10px;">
                <span>Payment:</span>
                <span style="text-transform: uppercase;">{{ $purchase->payment_method }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>{{ $settings['receipt_header'] ?? 'Thank you for your purchase!' }}</p>
            @if($purchase->notes)
                <p style="margin-top: 5px; font-style: italic;">Note: {{ $purchase->notes }}</p>
            @endif
            <p style="margin-top: 10px;">{{ $settings['receipt_footer'] ?? 'Please come again!' }}</p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
            Print Receipt
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>
</body>

</html>
