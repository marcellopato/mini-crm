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
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="coupon_code" class="form-control" placeholder="Código do cupom">
                            <button class="btn btn-secondary" type="button" onclick="applyCoupon()">Aplicar Cupom</button>
                        </div>
                        <small id="coupon-message" class="text-danger"></small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <p>Subtotal: R$ <span id="subtotal">{{ number_format($subtotal, 2, ',', '.') }}</span></p>
                        <p>Frete: R$ <span id="shipping">{{ number_format($shipping, 2, ',', '.') }}</span></p>
                        <p id="discount-row" style="display: none">Desconto: R$ <span id="discount">0,00</span></p>
                        <p><strong>Total: R$ <span id="total">{{ number_format($subtotal + $shipping, 2, ',', '.') }}</span></strong></p>
                    </div>
                </div>

                <form action="{{ route('cart.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="coupon_id" id="coupon_id">
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

        <script>
        function applyCoupon() {
            const code = document.getElementById('coupon_code').value;
            const subtotal = {{ $subtotal }};

            fetch('{{ route('coupons.apply') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ coupon_code: code, subtotal: subtotal })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('coupon-message').textContent = data.error;
                    document.getElementById('discount-row').style.display = 'none';
                    document.getElementById('coupon_id').value = '';
                } else {
                    document.getElementById('coupon-message').textContent = '';
                    document.getElementById('discount-row').style.display = 'block';
                    document.getElementById('discount').textContent = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2 }).format(data.discount);
                    document.getElementById('coupon_id').value = data.coupon_id;
                    
                    // Atualiza o total
                    const total = subtotal + {{ $shipping }} - data.discount;
                    document.getElementById('total').textContent = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2 }).format(total);
                }
            });
        }
        </script>
    @else
        <div class="alert alert-info">
            Seu carrinho está vazio.
            <a href="{{ route('products.index') }}" class="alert-link">Continue comprando</a>
        </div>
    @endif
</div>
@endsection
