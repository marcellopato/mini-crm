@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col">
            <h1>Cupons de Desconto</h1>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCouponModal">
                Novo Cupom
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Desconto</th>
                    <th>Valor Mínimo</th>
                    <th>Válido Até</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($coupons as $coupon)
                <tr>
                    <td>{{ $coupon->code }}</td>
                    <td>R$ {{ number_format($coupon->discount, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($coupon->min_value, 2, ',', '.') }}</td>
                    <td>{{ $coupon->valid_until->format('d/m/Y') }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary" 
                                onclick="editCoupon({{ json_encode($coupon) }})"
                                data-bs-toggle="modal" 
                                data-bs-target="#editCouponModal">
                            Editar
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="newCouponModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('coupons.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Cupom</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="code" class="form-label">Código</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label for="discount" class="form-label">Desconto (R$)</label>
                            <input type="number" class="form-control" id="discount" name="discount" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="min_value" class="form-label">Valor Mínimo (R$)</label>
                            <input type="number" class="form-control" id="min_value" name="min_value" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="valid_until" class="form-label">Válido Até</label>
                            <input type="date" class="form-control" id="valid_until" name="valid_until" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div class="modal fade" id="editCouponModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCouponForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Cupom</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_code" class="form-label">Código</label>
                            <input type="text" class="form-control" id="edit_code" name="code" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_discount" class="form-label">Desconto (R$)</label>
                            <input type="number" class="form-control" id="edit_discount" name="discount" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_min_value" class="form-label">Valor Mínimo (R$)</label>
                            <input type="number" class="form-control" id="edit_min_value" name="min_value" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_valid_until" class="form-label">Válido Até</label>
                            <input type="date" class="form-control" id="edit_valid_until" name="valid_until" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function editCoupon(coupon) {
        const form = document.getElementById('editCouponForm');
        form.action = `/coupons/${coupon.id}`;
        
        document.getElementById('edit_code').value = coupon.code;
        document.getElementById('edit_discount').value = coupon.discount;
        document.getElementById('edit_min_value').value = coupon.min_value;
        document.getElementById('edit_valid_until').value = coupon.valid_until.split('T')[0];
    }
    </script>
</div>
@endsection
