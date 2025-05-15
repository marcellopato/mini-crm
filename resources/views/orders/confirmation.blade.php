@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <h2 class="card-title">Pedido #{{ $order->id }} Confirmado!</h2>
            
            <div class="mt-4">
                <h5>Dados do Cliente</h5>
                <p>Nome: {{ $order->customer_name }}</p>
                <p>Email: {{ $order->customer_email }}</p>
                <p>Endereço: {{ $order->address }}</p>
            </div>

            <div class="mt-4">
                <h5>Resumo do Pedido</h5>
                <p>Subtotal: R$ {{ number_format($order->subtotal, 2, ',', '.') }}</p>
                <p>Frete: R$ {{ number_format($order->shipping, 2, ',', '.') }}</p>
                @if($order->discount > 0)
                    <p>Desconto: R$ {{ number_format($order->discount, 2, ',', '.') }}</p>
                @endif
                <p><strong>Total: R$ {{ number_format($order->total, 2, ',', '.') }}</strong></p>
            </div>

            <a href="{{ route('products.index') }}" class="btn btn-primary mt-4">Continuar Comprando</a>
        </div>
    </div>
</div>
@endsection
