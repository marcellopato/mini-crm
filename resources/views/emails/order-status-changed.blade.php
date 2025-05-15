<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .status { font-weight: bold; }
        .old-status { color: #856404; }
        .new-status { color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Status do Pedido Atualizado</h1>
        
        <p>Olá {{ $order->customer_name }},</p>
        
        <p>O status do seu pedido #{{ $order->id }} foi atualizado:</p>
        
        <p>
            De: <span class="status old-status">{{ $oldStatus }}</span><br>
            Para: <span class="status new-status">{{ $order->status }}</span>
        </p>

        <h2>Detalhes do Pedido</h2>
        <p>Total: R$ {{ number_format($order->total, 2, ',', '.') }}</p>
        <p>Data do Pedido: {{ $order->created_at->format('d/m/Y H:i') }}</p>

        <p>Se tiver alguma dúvida, entre em contato conosco.</p>
    </div>
</body>
</html>
