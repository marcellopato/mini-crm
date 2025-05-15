<?php

namespace App\Http\Controllers;

use App\Models\{Product, Stock, Order, OrderItem};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CartController extends Controller
{
    public function addToCart(Request $request, Product $product)
    {
        $validated = $request->validate([
            'variation_id' => 'nullable|exists:variations,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $stock = Stock::where('product_id', $product->id)
            ->where('variation_id', $validated['variation_id'])
            ->first();

        if (!$stock || $stock->quantity < $validated['quantity']) {
            return back()->with('error', 'Estoque insuficiente.');
        }

        $cart = session()->get('cart', []);
        $itemKey = $product->id . '-' . ($validated['variation_id'] ?? 'default');

        $cart[$itemKey] = [
            'product_id' => $product->id,
            'variation_id' => $validated['variation_id'],
            'name' => $product->name,
            'quantity' => ($cart[$itemKey]['quantity'] ?? 0) + $validated['quantity'],
            'price' => $product->price
        ];

        session()->put('cart', $cart);
        return back()->with('success', 'Produto adicionado ao carrinho.');
    }

    public function removeFromCart(Product $product)
    {
        $cart = session()->get('cart', []);
        $key = $product->id . '-' . (request('variation_id') ?? 'default');
        
        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
        }
        
        return redirect()->back()->with('success', 'Item removido do carrinho.');
    }

    public function cart()
    {
        return view('cart.index');
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'cep' => 'required|string|size:8',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email'
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'Carrinho vazio.');
        }

        // Consulta CEP via API
        $response = Http::get("https://viacep.com.br/ws/{$validated['cep']}/json/");
        if (!$response->successful()) {
            return back()->with('error', 'CEP inválido.');
        }

        $address = $response->json();
        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $shipping = $this->calculateShipping($subtotal);

        // ... lógica para criar pedido e itens
        
        session()->forget('cart');
        return redirect()->route('orders.show', $order)
            ->with('success', 'Pedido realizado com sucesso!');
    }

    private function calculateShipping($subtotal)
    {
        if ($subtotal >= 200) {
            return 0;
        } elseif ($subtotal >= 52 && $subtotal <= 166.59) {
            return 15;
        }
        return 20;
    }
}
