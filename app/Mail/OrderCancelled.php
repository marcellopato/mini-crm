<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCancelled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $reason = 'Pedido cancelado via administração'
    ) {}

    public function build()
    {
        return $this->subject('Pedido #' . $this->order->id . ' Cancelado')
                    ->view('emails.order-cancelled');
    }
}
