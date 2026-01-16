<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Order #{{ $order->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            line-height: 1.5;
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
        .uppercase { text-transform: uppercase; }
        .large { font-size: 18px; }
        .extra-large { font-size: 24px; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .mb-2 { margin-bottom: 8px; }
        .pb-2 { padding-bottom: 8px; }
        .border-b { border-bottom: 2px dashed #000; }
        .border-t { border-top: 2px dashed #000; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .space-y-2 > * + * { margin-top: 8px; }
        .item-row { margin-bottom: 12px; }
        .qty { font-weight: bold; font-size: 20px; }
        .item-name { font-weight: bold; font-size: 16px; }
        .note { font-style: italic; font-size: 12px; margin-top: 4px; }
        hr { border: none; border-top: 2px dashed #000; margin: 10px 0; }
    </style>
</head>
<body onload="window.print(); window.onafterprint = function() { window.close(); };">
    <div class="receipt">
        <!-- Header -->
        <div class="text-center pb-2 border-b">
            <div class="extra-large bold uppercase">{{ $settings['store_name'] }}</div>
            <div class="large bold mt-2">KITCHEN ORDER</div>
        </div>

        <!-- Order Info -->
        <div class="mt-4 pb-2 border-b">
            <div class="flex justify-between large bold">
                <span>Order #:</span>
                <span>{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="mt-2 flex justify-between bold">
                <span>Time:</span>
                <span>{{ $order->created_at->format('H:i') }}</span>
            </div>
            @if($order->order_type === 'dine_in' && $order->table_number)
                <div class="mt-2 flex justify-between extra-large bold">
                    <span>TABLE:</span>
                    <span>{{ $order->table_number }}</span>
                </div>
            @endif
            @if($order->order_type === 'takeaway')
                <div class="mt-2 text-center extra-large bold uppercase">TAKEAWAY</div>
            @endif
        </div>

        <!-- Items -->
        <div class="mt-4">
            @foreach($order->items as $item)
                <div class="item-row">
                    <div class="flex justify-between">
                        <div class="qty">{{ $item->quantity }}</div>
                        <div class="item-name">{{ $item->item->name }}</div>
                    </div>
                    @if($item->note)
                        <div class="note">* {{ $item->note }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Footer -->
        <div class="mt-4 pt-2 border-t text-center">
            <div class="bold">{{ $order->created_at->format('Y-m-d H:i') }}</div>
            @if($order->customer_name)
                <div class="mt-2">{{ $order->customer_name }}</div>
            @endif
        </div>
    </div>
</body>
</html>
