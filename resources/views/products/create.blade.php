@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Novo Produto</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nome do Produto</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Preço</label>
                    <input type="number" class="form-control" id="price" name="price" value="{{ old('price') }}" step="0.01" min="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Variações</label>
                    <div id="variations">
                        <div class="row mb-2">
                            <div class="col">
                                <input type="text" class="form-control" name="variations[0][name]" placeholder="Nome da Variação">
                            </div>
                            <div class="col">
                                <input type="number" class="form-control" name="variations[0][stock]" placeholder="Quantidade" min="0">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addVariation()">+ Adicionar Variação</button>
                </div>

                <div class="mb-3" id="stockInput">
                    <label for="stock" class="form-label">Estoque (sem variações)</label>
                    <input type="number" class="form-control" id="stock" name="stock" value="{{ old('stock') }}" min="0">
                </div>

                <button type="submit" class="btn btn-primary">Salvar Produto</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<script>
let variationCount = 1;

function addVariation() {
    const container = document.getElementById('variations');
    const newRow = document.createElement('div');
    newRow.className = 'row mb-2';
    newRow.innerHTML = `
        <div class="col">
            <input type="text" class="form-control" name="variations[${variationCount}][name]" placeholder="Nome da Variação">
        </div>
        <div class="col">
            <input type="number" class="form-control" name="variations[${variationCount}][stock]" placeholder="Quantidade" min="0">
        </div>
    `;
    container.appendChild(newRow);
    variationCount++;
}
</script>
@endsection
