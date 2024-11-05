<?php
use Modules\Gateways\Traits\PaymentGatewayTrait;

class CheckPendingPayments extends Command
{
    use PaymentGatewayTrait;

    protected $signature = 'payments:check-pending';
    protected $description = 'Check pending payments and update status if necessary';

    public function handle()
    {
        $pendingPayments = PaymentRequest::where('is_paid', 0)->get();

        foreach ($pendingPayments as $payment) {
            $status = $this->checkPlacetoPayStatus($payment->transaction_id);

            if ($status == 'APPROVED') {
                $payment->update(['is_paid' => 1]);

                if (function_exists($payment->success_hook)) {
                    call_user_func($payment->success_hook, $payment);
                }
                \Log::info("Payment approved for ID: {$payment->id}, Transaction ID: {$payment->transaction_id}");
            } else {
                \Log::error("Payment status check failed for Payment ID: {$payment->id}, Transaction ID: {$payment->transaction_id}, Status: {$status}");
            }
        }

        $this->info('Pending payments checked and updated.');
    }
}
