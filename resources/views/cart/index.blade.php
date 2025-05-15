@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Carrinho de Compras</h1>

    @if(session()->has('cart') && count(session('cart')) > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php $subtotal = 0 @endphp
                    @foreach(session('cart') as $key => $item)
                        @php 
                            $subtotal += $item['price'] * $item['quantity'];
                            $productId = explode('-', $key)[0]; // Extrair o ID do produto da chave
                        @endphp
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>R$ {{ number_format($item['price'], 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</td>
                            <td>
                                <form action="{{ route('cart.remove', $productId) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Remover</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5>Finalizar Compra</h5>
                <form action="{{ route('cart.checkout') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="customer_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="cep" class="form-label">CEP</label>
                        <input type="text" class="form-control" id="cep" name="cep" required maxlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary">Finalizar Compra</button>
                </form>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            Seu carrinho está vazio.
            <a href="{{ route('products.index') }}" class="alert-link">Continue comprando</a>
        </div>
    @endif
</div>
@endsection
