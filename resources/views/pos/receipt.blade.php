<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .receipt-font { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .receipt-container {
                box-shadow: none !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-md mx-auto px-4">
        <!-- Receipt Container -->
        <div class="receipt-container bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="text-center py-6 border-b border-dashed border-gray-300">
                <div class="mb-2">
                    <span class="material-symbols-outlined text-4xl text-emerald-600">local_cafe</span>
                </div>
                <h1 class="text-xl font-bold text-gray-900">{{ $settings['store_name'] }}</h1>
                @if($settings['store_address'])
                    <p class="text-sm text-gray-600 mt-1">{{ $settings['store_address'] }}</p>
                @endif
                @if($settings['store_phone'])
                    <p class="text-sm text-gray-600">{{ $settings['store_phone'] }}</p>
                @endif
                @if($settings['store_email'])
                    <p class="text-sm text-gray-600">{{ $settings['store_email'] }}</p>
                @endif
            </div>

            <!-- Receipt Info -->
            <div class="px-6 py-4 bg-gray-50 border-b border-dashed border-gray-300">
                <div class="receipt-font text-sm space-y-1">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Receipt #:</span>
                        <span class="font-semibold">{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Date:</span>
                        <span>{{ $order->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cashier:</span>
                        <span>{{ $settings['show_cashier_name'] && $order->user ? $order->user->name : 'N/A' }}</span>
                    </div>
                    @if($order->customer_name)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Customer:</span>
                            <span>{{ $order->customer_name }}</span>
                        </div>
                    @endif
                    @if($settings['show_customer_phone'] && $order->customer_phone)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phone:</span>
                            <span>{{ $order->customer_phone }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-600">Payment:</span>
                        <span class="capitalize">{{ $order->payment_method }}</span>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="px-6 py-4">
                <div class="space-y-3">
                    @foreach($order->items as $item)
                        <div>
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $item->item->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $item->quantity }} x {{ $settings['currency_symbol'] }} {{ number_format($item->price, 0, ',', '.') }}</p>
                                </div>
                                <p class="receipt-font font-semibold text-gray-900">{{ $settings['currency_symbol'] }} {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                            </div>
                            @if($item->note)
                                <p class="text-xs text-amber-600 italic mt-1">* {{ $item->note }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Totals -->
            <div class="px-6 py-4 border-t border-dashed border-gray-300 border-b">
                <div class="receipt-font space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal:</span>
                        <span>{{ $settings['currency_symbol'] }} {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>

                    @if($settings['tax_rate'] > 0)
                        @php
                            $taxAmount = $settings['tax_included']
                                ? $order->total_amount - ($order->total_amount / (1 + $settings['tax_rate'] / 100))
                                : $order->total_amount * ($settings['tax_rate'] / 100);
                        @endphp
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ $settings['tax_name'] }} ({{ $settings['tax_rate'] }}%):</span>
                            <span>{{ $settings['currency_symbol'] }} {{ number_format($taxAmount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                        <span>Total:</span>
                        <span class="text-emerald-600">{{ $settings['currency_symbol'] }} {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="py-6 text-center">
                @if($settings['receipt_header'])
                    <p class="text-sm text-gray-600 mb-2">{{ $settings['receipt_header'] }}</p>
                @endif
                <div class="flex justify-center gap-1 my-3">
                    @for($i = 1; $i <= 30; $i++)
                        <span class="w-1 h-0.5 bg-gray-300"></span>
                    @endfor
                </div>
                @if($settings['receipt_footer'])
                    <p class="text-sm text-gray-600">{{ $settings['receipt_footer'] }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-4 receipt-font">{{ $order->created_at->format('Y-m-d H:i:s') }}</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex gap-3 no-print">
            <button onclick="window.print()" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-lg transition-colors">
                <span class="material-symbols-outlined">print</span>
                Print Receipt
            </button>
            <a href="{{ route('pos.index') }}" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg shadow-lg transition-colors">
                <span class="material-symbols-outlined">add_shopping_cart</span>
                New Sale
            </a>
        </div>
    </div>

    <script>
        // Auto-print on load (optional)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
