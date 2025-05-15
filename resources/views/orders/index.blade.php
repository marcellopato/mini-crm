@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Pedidos</h1>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        Os pedidos iniciam com status "Pendente". Para simular uma mudança de status, use o comando:
        <pre class="mt-2"><code>curl -X POST http://localhost/api/webhooks/order-status \
    -H "Content-Type: application/json" \
    -d '{"order_id": 4, "status": "paid"}'</code></pre>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Data</th>
                    <th>Última Atualização</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td>
                        <span class="badge bg-{{ $order->status === 'paid' ? 'success' : 
                            ($order->status === 'pending' ? 'warning' : 'info') }}">
                            {{ [
                                'pending' => 'Pendente',
                                'paid' => 'Pago',
                                'processing' => 'Em Processamento',
                                'shipped' => 'Enviado',
                                'delivered' => 'Entregue',
                                'cancelled' => 'Cancelado'
                            ][$order->status] ?? $order->status }}
                        </span>
                    </td>
                    <td>R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
