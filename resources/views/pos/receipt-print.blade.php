<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            padding: 10px;
        }
        .receipt {
            max-width: 80mm;
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .mb-2 { margin-bottom: 8px; }
        .pb-2 { padding-bottom: 8px; }
        .border-b { border-bottom: 1px dashed #000; }
        .border-t { border-top: 1px dashed #000; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .space-y-2 > * + * { margin-top: 8px; }
        hr { border: none; border-top: 1px dashed #000; margin: 10px 0; }
    </style>
</head>
<body onload="window.print(); window.onafterprint = function() { window.close(); };">
    <div class="receipt">
        <!-- Header -->
        <div class="text-center pb-2 border-b">
            <div class="bold" style="font-size: 16px;">{{ $settings['store_name'] }}</div>
            @if($settings['store_address'])
                <div>{{ $settings['store_address'] }}</div>
            @endif
            @if($settings['store_phone'])
                <div>{{ $settings['store_phone'] }}</div>
            @endif
            @if($settings['store_email'])
                <div>{{ $settings['store_email'] }}</div>
            @endif
        </div>

        <!-- Receipt Info -->
        <div class="mt-4 pb-2 border-b">
            <div class="flex justify-between">
                <span>Receipt #:</span>
                <span>{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Date:</span>
                <span>{{ $order->created_at->format('Y-m-d H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Cashier:</span>
                <span>{{ $settings['show_cashier_name'] && $order->user ? $order->user->name : 'N/A' }}</span>
            </div>
            @if($order->customer_name)
                <div class="flex justify-between">
                    <span>Customer:</span>
                    <span>{{ $order->customer_name }}</span>
                </div>
            @endif
            @if($settings['show_customer_phone'] && $order->customer_phone)
                <div class="flex justify-between">
                    <span>Phone:</span>
                    <span>{{ $order->customer_phone }}</span>
                </div>
            @endif
            <div class="flex justify-between">
                <span>Payment:</span>
                <span class="bold">{{ ucfirst($order->payment_method) }}</span>
            </div>
        </div>

        <!-- Items -->
        <div class="mt-4">
            @foreach($order->items as $item)
                <div style="margin-bottom: 8px;">
                    <div class="flex justify-between">
                        <div>
                            <div class="bold">{{ $item->item->name }}</div>
                            <div>{{ $item->quantity }} x {{ $settings['currency_symbol'] }}{{ number_format($item->price, 2) }}</div>
                        </div>
                        <div class="bold">{{ $settings['currency_symbol'] }}{{ number_format($item->subtotal, 2) }}</div>
                    </div>
                    @if($item->note)
                        <div style="font-style: italic; font-size: 11px; padding-left: 0; margin-top: 2px;">* {{ $item->note }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="mt-4 pt-2 border-t">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>{{ $settings['currency_symbol'] }}{{ number_format($order->total_amount, 2) }}</span>
            </div>

            @if($settings['tax_rate'] > 0)
                @php
                    $taxAmount = $settings['tax_included']
                        ? $order->total_amount - ($order->total_amount / (1 + $settings['tax_rate'] / 100))
                        : $order->total_amount * ($settings['tax_rate'] / 100);
                @endphp
                <div class="flex justify-between">
                    <span>{{ $settings['tax_name'] }} ({{ $settings['tax_rate'] }}%):</span>
                    <span>{{ $settings['currency_symbol'] }}{{ number_format($taxAmount, 2) }}</span>
                </div>
            @endif

            <hr>
            <div class="flex justify-between bold" style="font-size: 14px;">
                <span>TOTAL:</span>
                <span>{{ $settings['currency_symbol'] }}{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-4 text-center">
            @if($settings['receipt_header'])
                <div>{{ $settings['receipt_header'] }}</div>
            @endif
            <hr>
            @if($settings['receipt_footer'])
                <div>{{ $settings['receipt_footer'] }}</div>
            @endif
            <div class="mt-2" style="font-size: 10px;">{{ $order->created_at->format('Y-m-d H:i:s') }}</div>
        </div>
    </div>
</body>
</html>
