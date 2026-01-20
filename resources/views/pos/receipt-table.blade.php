<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Order #{{ $order->id }}</title>
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
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .large { font-size: 16px; }
        .extra-large { font-size: 20px; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .mb-2 { margin-bottom: 8px; }
        .pb-2 { padding-bottom: 8px; }
        .border-b { border-bottom: 1px dashed #000; }
        .border-t { border-top: 1px dashed #000; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        hr { border: none; border-top: 1px dashed #000; margin: 10px 0; }
        .pending { background: #fff3cd; padding: 8px; border: 2px solid #ffc107; }
    </style>
</head>
<body onload="window.print(); window.onafterprint = function() { window.close(); };">
    <div class="receipt">
        <!-- Header -->
        <div class="text-center pb-2 border-b">
            <div class="extra-large bold uppercase">{{ $settings['store_name'] }}</div>
            @if($settings['store_address'])
                <div>{{ $settings['store_address'] }}</div>
            @endif
            @if($settings['store_phone'])
                <div>{{ $settings['store_phone'] }}</div>
            @endif
        </div>

        <!-- Order Info -->
        <div class="mt-4">
            @if($order->order_type === 'dine_in')
                <div class="text-center pending mb-2">
                    <div class="large bold uppercase">DINE IN</div>
                    @if($order->table_number)
                        <div class="extra-large bold mt-2">TABLE {{ $order->table_number }}</div>
                    @endif
                </div>
            @elseif($order->order_type === 'takeaway')
                <div class="text-center pending mb-2">
                    <div class="large bold uppercase">TAKEAWAY</div>
                    <div class="mt-2">Please wait at counter</div>
                </div>
            @endif

            <div class="flex justify-between bold mt-2">
                <span>Order #:</span>
                <span>{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Time:</span>
                <span>{{ $order->created_at->format('H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Date:</span>
                <span>{{ $order->created_at->format('Y-m-d') }}</span>
            </div>
        </div>

        <!-- Items -->
        <div class="mt-4 pb-2 border-b">
            @foreach($order->items as $item)
                <div style="margin-bottom: 8px;">
                    <div class="flex justify-between">
                        <div>
                            <div class="bold">{{ $item->quantity }} x {{ $item->item->name }}</div>
                            @if($item->note)
                                <div style="font-style: italic; font-size: 11px;">* {{ $item->note }}</div>
                            @endif
                        </div>
                        <div class="bold">{{ $settings['currency_symbol'] }} {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Total -->
        <div class="mt-4 pt-2 border-t">
            <div class="flex justify-between bold large">
                <span>TOTAL:</span>
                <span>{{ $settings['currency_symbol'] }} {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-4 text-center">
            <div class="bold">PENDING PAYMENT</div>
            <div class="mt-2">Please order at counter when ready</div>
            @if($settings['receipt_footer'])
                <hr>
                <div>{{ $settings['receipt_footer'] }}</div>
            @endif
        </div>
    </div>
</body>
</html>
