<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', sans-serif; padding: 30px; color: #1e293b; max-width: 800px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 2px solid #1e293b; padding-bottom: 20px; }
        .header h1 { margin: 0; font-size: 28px; }
        .header .info { text-align: right; font-size: 14px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
        .info-grid h3 { font-size: 12px; text-transform: uppercase; color: #64748b; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f1f5f9; padding: 10px; text-align: left; font-size: 12px; text-transform: uppercase; }
        td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .totals { margin-left: auto; width: 300px; }
        .totals td { padding: 5px 10px; }
        .totals .grand { font-weight: bold; background: #1e293b; color: #fff; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 12px; }
        .badge-success { background: #10b981; color: #fff; }
        .badge-warning { background: #f59e0b; color: #fff; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="float:right; padding:8px 16px; background:#3b82f6; color:#fff; border:none; border-radius:4px; cursor:pointer">
        🖨 Print
    </button>

    <div class="header">
        <div>
            <h1>INVOICE</h1>
            <p style="margin: 5px 0; color: #64748b;">{{ config('app.name') }}</p>
        </div>
        <div class="info">
            <strong>Order #:</strong> {{ $order->order_number }}<br>
            <strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}<br>
            <strong>Status:</strong> {{ ucfirst($order->status) }}
        </div>
    </div>

    <div class="info-grid">
        <div>
            <h3>Bill To</h3>
            <strong>{{ $order->user->name ?? 'Guest' }}</strong><br>
            {{ $order->user->email ?? '' }}<br>
            {{ $order->user->phone ?? '' }}
        </div>
        <div>
            <h3>Ship To</h3>
            {{ $order->shipping_address }}<br>
            <strong>Phone:</strong> {{ $order->phone }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:right">Price</th>
                <th style="text-align:right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->product_sku }}</td>
                    <td style="text-align:center">{{ $item->quantity }}</td>
                    <td style="text-align:right">${{ number_format($item->price, 2) }}</td>
                    <td style="text-align:right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td style="text-align:right">${{ number_format($order->subtotal, 2) }}</td></tr>
        <tr><td>Tax</td><td style="text-align:right">${{ number_format($order->tax, 2) }}</td></tr>
        <tr><td>Shipping</td><td style="text-align:right">${{ number_format($order->shipping_fee, 2) }}</td></tr>
        <tr><td>Discount</td><td style="text-align:right">-${{ number_format($order->discount, 2) }}</td></tr>
        <tr class="grand"><td>TOTAL</td><td style="text-align:right">${{ number_format($order->total, 2) }}</td></tr>
    </table>

    @if ($order->notes)
        <p style="margin-top: 30px;"><strong>Notes:</strong> {{ $order->notes }}</p>
    @endif

    <p style="margin-top: 50px; text-align: center; color: #64748b; font-size: 12px;">
        Thank you for your business!
    </p>
</body>
</html>
