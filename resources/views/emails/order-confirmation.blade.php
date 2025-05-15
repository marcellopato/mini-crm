<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pedido #{{ $order->id }} Confirmado!</h1>
        
        <h2>Dados do Cliente</h2>
        <p>Nome: {{ $order->customer_name }}</p>
        <p>Email: {{ $order->customer_email }}</p>
        <p>Endereço de Entrega: {{ $order->address }}</p>

        <h2>Itens do Pedido</h2>
        <table width="100%" style="border-collapse: collapse;">
            <tr>
                <th align="left">Produto</th>
                <th align="center">Qtd</th>
                <th align="right">Preço</th>
                <th align="right">Total</th>
            </tr>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td align="center">{{ $item->quantity }}</td>
                <td align="right">R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                <td align="right">R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>

        <h2>Resumo</h2>
        <p>Subtotal: R$ {{ number_format($order->subtotal, 2, ',', '.') }}</p>
        <p>Frete: R$ {{ number_format($order->shipping, 2, ',', '.') }}</p>
        @if($order->discount > 0)
            <p>Desconto: R$ {{ number_format($order->discount, 2, ',', '.') }}</p>
        @endif
        <p class="total">Total: R$ {{ number_format($order->total, 2, ',', '.') }}</p>
    </div>
</body>
</html>
