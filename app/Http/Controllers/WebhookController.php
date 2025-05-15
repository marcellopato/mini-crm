<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\OrderStatusChanged;
use App\Mail\OrderCancelled;

class WebhookController extends Controller
{
    protected $allowedStatuses = [
        'pending',    // Aguardando pagamento
        'paid',       // Pago
        'processing', // Em processamento
        'shipped',    // Enviado
        'delivered',  // Entregue
        'cancelled'   // Cancelado
    ];

    protected $statusTranslations = [
        'pending' => 'Pendente',
        'paid' => 'Pago',
        'processing' => 'Em Processamento',
        'shipped' => 'Enviado',
        'delivered' => 'Entregue',
        'cancelled' => 'Cancelado'
    ];

    public function handleOrderStatus(Request $request)
    {
        DB::beginTransaction();
        try {
            $payload = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'status' => ['required', 'string', 'in:' . implode(',', $this->allowedStatuses)]
            ]);

            $order = Order::findOrFail($payload['order_id']);
            
            // Evita atualização desnecessária
            if ($order->status === $payload['status']) {
                DB::commit();
                return response()->json([
                    'message' => 'Pedido já está com este status',
                    'status' => $this->statusTranslations[$order->status]
                ]);
            }

            $oldStatus = $order->status;
            
            if ($payload['status'] === 'cancelled') {
                // Enviar e-mail antes de deletar o pedido
                Mail::to($order->customer_email)
                    ->send(new OrderCancelled($order, $request->input('reason', 'Pedido cancelado via administração')));

                // Devolver estoque
                foreach ($order->items as $item) {
                    $stock = Stock::where('product_id', $item->product_id)
                        ->where('variation_id', $item->variation_id)
                        ->first();
                    
                    if ($stock) {
                        $stock->increment('quantity', $item->quantity);
                    }
                }
                
                $order->delete();
                DB::commit();
                
                Log::info("Pedido #{$order->id} cancelado e estoque devolvido via webhook");
                return response()->json(['message' => 'Pedido cancelado e estoque devolvido com sucesso']);
            }

            $order->update([
                'status' => $payload['status'],
                'updated_at' => now()
            ]);

            Log::info("Status do pedido #{$order->id} atualizado de {$this->statusTranslations[$oldStatus]} para {$this->statusTranslations[$payload['status']]} via webhook");
            
            // Envia email notificando a mudança de status
            Mail::to($order->customer_email)->send(new OrderStatusChanged($order, $oldStatus));
            
            DB::commit();
            return response()->json([
                'message' => 'Status atualizado com sucesso',
                'old_status' => $this->statusTranslations[$oldStatus],
                'new_status' => $this->statusTranslations[$payload['status']]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
