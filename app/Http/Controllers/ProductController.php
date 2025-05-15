<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['variations', 'stocks'])->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'variations' => 'nullable|array',
                'variations.*.name' => 'required|string',
                'variations.*.stock' => 'required|integer|min:0',
                'stock' => 'required_without:variations|integer|min:0'
            ]);

            $product = Product::create([
                'name' => $validated['name'],
                'price' => $validated['price']
            ]);

            if (isset($validated['variations'])) {
                foreach ($validated['variations'] as $variationData) {
                    $variation = $product->variations()->create([
                        'name' => $variationData['name']
                    ]);

                    Stock::create([
                        'product_id' => $product->id,
                        'variation_id' => $variation->id,
                        'quantity' => $variationData['stock']
                    ]);
                }
            } else {
                Stock::create([
                    'product_id' => $product->id,
                    'quantity' => $validated['stock']
                ]);
            }

            DB::commit();
            return redirect()->route('products.index')
                ->with('success', 'Produto criado com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Erro ao criar produto.']);
        }
    }

    public function edit(Product $product)
    {
        $product->load(['variations.stock', 'stocks']);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'stock_updates' => 'required|array',
                'stock_updates.*.quantity' => 'required|integer|min:0'
            ]);

            $product->update([
                'name' => $validated['name'],
                'price' => $validated['price']
            ]);

            foreach ($validated['stock_updates'] as $stockId => $data) {
                DB::table('stocks')
                    ->where('id', $stockId)
                    ->update(['quantity' => $data['quantity']]);
            }

            DB::commit();
            return redirect()
                ->route('products.index')
                ->with('success', 'Produto atualizado com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao atualizar produto.']);
        }
    }
}
