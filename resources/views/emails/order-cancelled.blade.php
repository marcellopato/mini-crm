<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .alert { color: #721c24; background: #f8d7da; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pedido #{{ $order->id }} Cancelado</h1>
        
        <div class="alert">
            <strong>Motivo do cancelamento:</strong><br>
            {{ $reason }}
        </div>

        <h2>Detalhes do Pedido Cancelado</h2>
        <p>Total do Pedido: R$ {{ number_format($order->total, 2, ',', '.') }}</p>
        <p>Data do Pedido: {{ $order->created_at->format('d/m/Y H:i') }}</p>

        <p>O estoque dos produtos foi restaurado automaticamente.</p>
        
        <p>Se tiver alguma dúvida, entre em contato conosco.</p>
    </div>
</body>
</html>
