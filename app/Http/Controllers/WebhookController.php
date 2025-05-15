<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusChanged;

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
        $payload = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => ['required', 'string', 'in:' . implode(',', $this->allowedStatuses)]
        ]);

        $order = Order::findOrFail($payload['order_id']);
        
        // Evita atualização desnecessária
        if ($order->status === $payload['status']) {
            return response()->json([
                'message' => 'Pedido já está com este status',
                'status' => $this->statusTranslations[$order->status]
            ]);
        }

        $oldStatus = $order->status;
        
        if ($payload['status'] === 'cancelled') {
            // Ao cancelar, podemos devolver o estoque
            foreach ($order->items as $item) {
                $stock = $item->product->stocks()->first();
                if ($stock) {
                    $stock->increment('quantity', $item->quantity);
                }
            }
            $order->delete();
            Log::info("Pedido #{$order->id} cancelado e estoque devolvido via webhook");
            return response()->json(['message' => 'Pedido cancelado com sucesso']);
        }

        $order->update([
            'status' => $payload['status'],
            'updated_at' => now()
        ]);

        Log::info("Status do pedido #{$order->id} atualizado de {$this->statusTranslations[$oldStatus]} para {$this->statusTranslations[$payload['status']]} via webhook");
        
        // Envia email notificando a mudança de status
        Mail::to($order->customer_email)->send(new OrderStatusChanged($order, $oldStatus));
        
        return response()->json([
            'message' => 'Status atualizado com sucesso',
            'old_status' => $this->statusTranslations[$oldStatus],
            'new_status' => $this->statusTranslations[$payload['status']]
        ]);
    }
}
