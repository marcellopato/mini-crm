@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Editar Produto</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('products.update', $product) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nome do Produto</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Preço</label>
                    <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Estoque</label>
                    @if($product->variations->count() > 0)
                        @foreach($product->variations as $variation)
                            <div class="row mb-2">
                                <div class="col">
                                    <input type="text" class="form-control" value="{{ $variation->name }}" readonly>
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" 
                                           name="stock_updates[{{ $variation->stock->id }}][quantity]" 
                                           value="{{ $variation->stock->quantity }}" 
                                           min="0">
                                </div>
                            </div>
                        @endforeach
                    @else
                        <input type="number" class="form-control" 
                               name="stock_updates[{{ $product->stocks->first()->id }}][quantity]" 
                               value="{{ $product->stocks->first()->quantity }}" 
                               min="0">
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Atualizar Produto</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection
