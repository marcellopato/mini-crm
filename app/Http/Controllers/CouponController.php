<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->get();
        return view('coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $messages = [
            'code.required' => 'O código do cupom é obrigatório',
            'code.unique' => 'Este código de cupom já está em uso',
            'discount.required' => 'O valor do desconto é obrigatório',
            'discount.numeric' => 'O desconto deve ser um valor numérico',
            'min_value.required' => 'O valor mínimo é obrigatório',
            'min_value.numeric' => 'O valor mínimo deve ser um valor numérico',
            'valid_until.required' => 'A data de validade é obrigatória',
            'valid_until.date' => 'A data de validade deve ser uma data válida',
            'valid_until.after_or_equal' => 'A data de validade deve ser hoje ou uma data futura'
        ];

        $validated = $request->validate([
            'code' => 'required|unique:coupons,code',
            'discount' => 'required|numeric|min:0',
            'min_value' => 'required|numeric|min:0',
            'valid_until' => 'required|date|after_or_equal:today'
        ], $messages);

        Coupon::create($validated);
        return redirect()->route('coupons.index')->with('success', 'Cupom criado com sucesso');
    }

    public function apply(Request $request)
    {
        $code = $request->input('coupon_code');
        $subtotal = $request->input('subtotal');

        $coupon = Coupon::where('code', $code)->first();
        
        if (!$coupon) {
            return response()->json(['error' => 'Cupom não encontrado'], 422);
        }

        // Ajusta a data de validade para o final do dia (23:59:59)
        $expirationDate = $coupon->valid_until->endOfDay();
        
        if ($expirationDate < now()) {
            return response()->json(['error' => 'Cupom expirado'], 422);
        }

        if ($subtotal < $coupon->min_value) {
            return response()->json([
                'error' => "Valor mínimo para este cupom: R$ " . number_format($coupon->min_value, 2, ',', '.')
            ], 422);
        }

        return response()->json([
            'discount' => $coupon->discount,
            'coupon_id' => $coupon->id
        ]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $messages = [
            'discount.required' => 'O valor do desconto é obrigatório',
            'discount.numeric' => 'O desconto deve ser um valor numérico',
            'min_value.required' => 'O valor mínimo é obrigatório',
            'min_value.numeric' => 'O valor mínimo deve ser um valor numérico',
            'valid_until.required' => 'A data de validade é obrigatória',
            'valid_until.date' => 'A data de validade deve ser uma data válida',
            'valid_until.after_or_equal' => 'A data de validade deve ser hoje ou uma data futura'
        ];

        $validated = $request->validate([
            'discount' => 'required|numeric|min:0',
            'min_value' => 'required|numeric|min:0',
            'valid_until' => 'required|date|after_or_equal:today'
        ], $messages);

        $coupon->update($validated);
        return redirect()->route('coupons.index')->with('success', 'Cupom atualizado com sucesso');
    }
}
