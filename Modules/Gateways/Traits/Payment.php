<?php

namespace Modules\Gateways\Traits;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Application;
use InvalidArgumentException;
use Modules\Gateways\Entities\PaymentRequest;
use Illuminate\Support\Facades\Log;

trait Payment
{
    public static function generate_link(object $payer, object $payment_info, object $receiver): Application|bool|string|UrlGenerator|\Illuminate\Contracts\Foundation\Application
    {
        if ($payment_info->getPaymentAmount() <= 0) {
            Log::error("Invalid payment amount: Amount cannot be 0 or negative.");
            throw new InvalidArgumentException('Payment amount cannot be 0');
        }

        if (!is_array($payment_info->getAdditionalData())) {
            Log::error("Invalid additional data: Must be a valid array.");
            throw new InvalidArgumentException('Additional data should be in a valid array');
        }

        $payment = new PaymentRequest();
        $payment->payment_amount = $payment_info->getPaymentAmount();
        $payment->success_hook = $payment_info->getSuccessHook();
        $payment->failure_hook = $payment_info->getFailureHook();
        $payment->payer_id = $payment_info->getPayerId();
        $payment->receiver_id = $payment_info->getReceiverId();
        $payment->currency_code = strtoupper($payment_info->getCurrencyCode());
        $payment->payment_method = $payment_info->getPaymentMethod();
        $payment->additional_data = json_encode($payment_info->getAdditionalData());
        $payment->payer_information = json_encode($payer->information());
        $payment->receiver_information = json_encode($receiver->information());
        $payment->external_redirect_link = $payment_info->getExternalRedirectLink();
        $payment->attribute = $payment_info->getAttribute();
        $payment->attribute_id = $payment_info->getAttributeId();
        $payment->payment_platform = $payment_info->getPaymentPlatForm();
        $payment->save();

        $routes = [
            'ssl_commerz' => 'payment/sslcommerz/pay',
            'stripe' => 'payment/stripe/pay',
            'paymob_accept' => 'payment/paymob/pay',
            'flutterwave' => 'payment/flutterwave-v3/pay',
            'paytm' => 'payment/paytm/pay',
            'paypal' => 'payment/paypal/pay',
            'paytabs' => 'payment/paytabs/pay',
            'liqpay' => 'payment/liqpay/pay',
            'razor_pay' => 'payment/razor-pay/pay',
            'senang_pay' => 'payment/senang-pay/pay',
            'mercadopago' => 'payment/mercadopago/pay',
            'bkash' => 'payment/bkash/make-payment',
            'paystack' => 'payment/paystack/pay',
            'placetoPay' => 'payment/placetoPay/pay',
            'digiWallet' => 'payment/digiWallet/pay',
            'oneLink' => 'payment/oneLink/pay',
        ];

        if (array_key_exists($payment->payment_method, $routes)) {
            Log::info("Generated payment link for method " . $payment->payment_method . " with payment ID " . $payment->id);
            return url("{$routes[$payment->payment_method]}/?payment_id={$payment->id}");
        } else {
            Log::error("Invalid payment method: " . $payment->payment_method);
            return false;
        }
    }
}
