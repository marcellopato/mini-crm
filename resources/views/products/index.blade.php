@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Produtos</h1>
    <a href="{{ route('products.create') }}" class="btn btn-primary">Novo Produto</a>
</div>

<div class="row">
    @foreach($products as $product)
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $product->name }}</h5>
                    <p class="card-text">R$ {{ number_format($product->price, 2, ',', '.') }}</p>
                    
                    @if($product->variations->count() > 0)
                        <form action="{{ route('cart.add', $product) }}" method="POST">
                            @csrf
                            <select name="variation_id" class="form-select mb-2">
                                @foreach($product->variations as $variation)
                                    @php
                                        $stock = $variation->stock?->quantity ?? 0;
                                    @endphp
                                    <option value="{{ $variation->id }}">
                                        {{ $variation->name }} ({{ $stock }} disponíveis)
                                    </option>
                                @endforeach
                            </select>
                            <input type="number" name="quantity" value="1" min="1" class="form-control mb-2">
                            <button type="submit" class="btn btn-success" {{ $stock == 0 ? 'disabled' : '' }}>
                                {{ $stock > 0 ? 'Comprar' : 'Indisponível' }}
                            </button>
                        </form>
                    @else
                        @php
                            $stock = $product->stocks->first()?->quantity ?? 0;
                        @endphp
                        <p class="text-muted">{{ $stock }} unidades disponíveis</p>
                        <form action="{{ route('cart.add', $product) }}" method="POST">
                            @csrf
                            <input type="number" name="quantity" value="1" min="1" max="{{ $stock }}" class="form-control mb-2">
                            <button type="submit" class="btn btn-success" {{ $stock == 0 ? 'disabled' : '' }}>
                                {{ $stock > 0 ? 'Comprar' : 'Indisponível' }}
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary mt-2">Editar</a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
