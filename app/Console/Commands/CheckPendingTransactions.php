<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Modules\Gateways\Http\Controllers\PlacetoPayController;
use Modules\Gateways\Http\Controllers\DigiWalletController;
use Modules\Gateways\Entities\PaymentRequest;

class CheckPendingTransactions extends Command
{
    protected $signature = 'transactions:check-pending';
    protected $description = 'Check the status of pending transactions';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $batchSize = 50; // Number of orders to process at once
        $pendingOrders = Order::where('payment_status', 'pending')->take($batchSize)->get();

        foreach ($pendingOrders as $order) {
            $transactionId = $order->transaction_reference; // Assuming this is the transaction ID

            try {
                if ($order->payment_method === 'placetoPay') {
                    $controller = new PlacetoPayController(new PaymentRequest);
                } elseif ($order->payment_method === 'digiWallet') {
                    $controller = new DigiWalletController(new PaymentRequest);
                } else {
                    Log::warning("Order ID {$order->id}: Unknown payment method.");
                    continue; // Skip if it's an unknown payment method
                }

                $status = $controller->checkStatus($transactionId);

                if ($status === 'APPROVED') {
                    $order->payment_status = 'paid';
                    Log::info("Order ID {$order->id}: Payment approved.");
                } elseif ($status === 'REJECTED') {
                    $order->payment_status = 'failed';
                    Log::info("Order ID {$order->id}: Payment rejected.");
                } else {
                    Log::info("Order ID {$order->id}: Payment status is still pending.");
                }
                $order->save();
            } catch (\Exception $e) {
                Log::error("Order ID {$order->id}: Error checking payment status - " . $e->getMessage());
            }
        }

        return 0;
    }
}
