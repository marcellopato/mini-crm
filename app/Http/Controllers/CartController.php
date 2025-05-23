<?php

namespace App\Http\Controllers;

use App\Models\{Product, Stock, Order, OrderItem, Coupon};
use App\Services\ShippingCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;

class CartController extends Controller
{
    private $shippingCalculator;

    public function __construct(ShippingCalculator $shippingCalculator)
    {
        $this->shippingCalculator = $shippingCalculator;
    }

    public function addToCart(Request $request, Product $product)
    {
        $validated = $request->validate([
            'variation_id' => 'nullable|exists:variations,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validated['variation_id']) {
            $stock = Stock::where('product_id', $product->id)
                         ->where('variation_id', $validated['variation_id'])
                         ->first();
        } else {
            $stock = $product->stocks()->first();
        }

        if (!$stock || $stock->quantity < $validated['quantity']) {
            return back()->with('error', 'Quantidade solicitada não disponível em estoque.');
        }

        // Verifica se já existe no carrinho
        $cart = session()->get('cart', []);
        $itemKey = $product->id . '-' . ($validated['variation_id'] ?? 'default');
        $currentQty = isset($cart[$itemKey]) ? $cart[$itemKey]['quantity'] : 0;
        
        if (($currentQty + $validated['quantity']) > $stock->quantity) {
            return back()->with('error', 'Quantidade total excede o estoque disponível.');
        }

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
        $cart = session()->get('cart', []);
        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $shipping = $this->shippingCalculator->calculate($subtotal);

        return view('cart.index', compact('cart', 'subtotal', 'shipping'));
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'cep' => 'required|string|size:8',
            'coupon_id' => 'nullable|exists:coupons,id'
        ]);

        DB::beginTransaction();
        try {
            $cart = session()->get('cart', []);
            if (empty($cart)) {
                return back()->with('error', 'Carrinho vazio');
            }

            // Verificar estoque antes de finalizar
            foreach ($cart as $key => $item) {
                $productId = explode('-', $key)[0];
                $variationId = explode('-', $key)[1] ?? null;
                
                $stock = Stock::where('product_id', $productId)
                    ->where('variation_id', $variationId !== 'default' ? $variationId : null)
                    ->first();
                
                if (!$stock || $stock->quantity < $item['quantity']) {
                    throw new \Exception('Produto sem estoque suficiente.');
                }
            }

            // Consulta CEP
            $response = Http::get("https://viacep.com.br/ws/{$validated['cep']}/json/");
            if (!$response->successful()) {
                throw new \Exception('CEP inválido');
            }
            $address = $response->json();

            // Calcula valores
            $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
            $shipping = $this->shippingCalculator->calculate($subtotal);
            $discount = 0;

            // Aplica desconto do cupom se existir
            if (!empty($validated['coupon_id'])) {
                $coupon = Coupon::find($validated['coupon_id']);
                if ($coupon && $coupon->valid_until->endOfDay() >= now() && $subtotal >= $coupon->min_value) {
                    $discount = $coupon->discount;
                }
            }

            // Cria pedido com desconto
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'cep' => $validated['cep'],
                'address' => $address['logradouro'] . ', ' . $address['bairro'] . ' - ' . $address['localidade'] . '/' . $address['uf'],
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => $subtotal + $shipping - $discount,
                'status' => 'pending',
                'coupon_id' => $validated['coupon_id']
            ]);

            // Atualizar estoque
            foreach ($cart as $key => $item) {
                $productId = explode('-', $key)[0];
                $variationId = explode('-', $key)[1] ?? 'default';
                
                $stock = Stock::where('product_id', $productId)
                    ->where('variation_id', $variationId !== 'default' ? $variationId : null)
                    ->first();
                
                if (!$stock) {
                    throw new \Exception('Erro ao atualizar estoque: produto não encontrado.');
                }
                
                $stock->decrement('quantity', $item['quantity']);
            }
            
            // Cria itens do pedido após confirmar estoque
            foreach ($cart as $key => $item) {
                $productId = explode('-', $key)[0];
                $variationId = explode('-', $key)[1] ?? 'default';
                
                $order->items()->create([
                    'product_id' => $productId,
                    'variation_id' => $variationId !== 'default' ? $variationId : null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
            }

            // Após criar o pedido, enviar e-mail
            Mail::to($order->customer_email)->send(new OrderConfirmation($order));

            DB::commit();
            session()->forget('cart');
            
            return view('orders.confirmation', compact('order'));

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
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
