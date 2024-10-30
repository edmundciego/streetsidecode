<?php

use Illuminate\Support\Facades\Route;
use Modules\Gateways\Http\Controllers\PaymentConfigController;
use Modules\Gateways\Http\Controllers\OneLinkController;
use Modules\Gateways\Http\Controllers\SSLCommerzPaymentController;
use Modules\Gateways\Http\Controllers\StripePaymentController;
use Modules\Gateways\Http\Controllers\RazorPayController;
use Modules\Gateways\Http\Controllers\PaypalPaymentController;
use Modules\Gateways\Http\Controllers\SenangPayController;
use Modules\Gateways\Http\Controllers\PaytmController;
use Modules\Gateways\Http\Controllers\FlutterwaveV3Controller;
use Modules\Gateways\Http\Controllers\PaystackController;
use Modules\Gateways\Http\Controllers\BkashPaymentController;
use Modules\Gateways\Http\Controllers\LiqPayController;
use Modules\Gateways\Http\Controllers\MercadoPagoController;
use Modules\Gateways\Http\Controllers\PaymobController;
use Modules\Gateways\Http\Controllers\PaytabsController;
use Modules\Gateways\Http\Controllers\PlacetoPayController;
use Modules\Gateways\Http\Controllers\DigiWalletController;
use Modules\Gateways\Http\Controllers\SMSConfigController;

// Define the is_published flag based on your logic
$is_published = 0;
try {
    $full_data = include('Modules/Gateways/Addon/info.php');
    $is_published = $full_data['is_published'] == 1 ? 1 : 0;
} catch (\Exception $exception) {
}

if ($is_published) {
    Route::group(['prefix' => 'payment'], function () {

        // SSLCOMMERZ
        Route::group(['prefix' => 'sslcommerz', 'as' => 'sslcommerz.'], function () {
            Route::get('pay', [SSLCommerzPaymentController::class, 'index'])->name('pay');
            Route::post('success', [SSLCommerzPaymentController::class, 'success'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('failed', [SSLCommerzPaymentController::class, 'failed'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('canceled', [SSLCommerzPaymentController::class, 'canceled'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        // STRIPE
        Route::group(['prefix' => 'stripe', 'as' => 'stripe.'], function () {
            Route::get('pay', [StripePaymentController::class, 'index'])->name('pay');
            Route::get('token', [StripePaymentController::class, 'payment_process_3d'])->name('token');
            Route::get('success', [StripePaymentController::class, 'success'])->name('success');
        });

        // RAZOR-PAY
        Route::group(['prefix' => 'razor-pay', 'as' => 'razor-pay.'], function () {
            Route::get('pay', [RazorPayController::class, 'index'])->name('pay');
            Route::post('payment', [RazorPayController::class, 'payment'])->name('payment')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        // PAYPAL
        Route::group(['prefix' => 'paypal', 'as' => 'paypal.'], function () {
            Route::get('pay', [PaypalPaymentController::class, 'payment'])->name('pay');
            Route::any('success', [PaypalPaymentController::class, 'success'])->name('success')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::any('cancel', [PaypalPaymentController::class, 'cancel'])->name('cancel')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        // SENANG-PAY
        Route::group(['prefix' => 'senang-pay', 'as' => 'senang-pay.'], function () {
            Route::get('pay', [SenangPayController::class, 'index'])->name('pay');
            Route::any('callback', [SenangPayController::class, 'return_senang_pay'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        // PAYTM
        Route::group(['prefix' => 'paytm', 'as' => 'paytm.'], function () {
            Route::get('pay', [PaytmController::class, 'payment'])->name('pay');
            Route::any('response', [PaytmController::class, 'callback'])->name('response')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        // FLUTTERWAVE
        Route::group(['prefix' => 'flutterwave-v3', 'as' => 'flutterwave-v3.'], function () {
            Route::get('pay', [FlutterwaveV3Controller::class, 'initialize'])->name('pay');
            Route::get('callback', [FlutterwaveV3Controller::class, 'callback'])->name('callback');
        });

        // PAYSTACK
        Route::group(['prefix' => 'paystack', 'as' => 'paystack.'], function () {
            Route::get('pay', [PaystackController::class, 'index'])->name('pay');
            Route::post('payment', [PaystackController::class, 'redirectToGateway'])->name('payment');
            Route::get('callback', [PaystackController::class, 'handleGatewayCallback'])->name('callback');
        });

        // BKASH
        Route::group(['prefix' => 'bkash', 'as' => 'bkash.'], function () {
            Route::get('make-payment', [BkashPaymentController::class, 'make_tokenize_payment'])->name('make-payment');
            Route::any('callback', [BkashPaymentController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        // Liqpay
        Route::group(['prefix' => 'liqpay', 'as' => 'liqpay.'], function () {
            Route::get('payment', [LiqPayController::class, 'payment'])->name('payment');
            Route::any('callback', [LiqPayController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        // MERCADOPAGO
        Route::group(['prefix' => 'mercadopago', 'as' => 'mercadopago.'], function () {
            Route::get('pay', [MercadoPagoController::class, 'index'])->name('index');
            Route::post('make-payment', [MercadoPagoController::class, 'make_payment'])->name('make_payment');
        });

        // PAYMOB
        Route::group(['prefix' => 'paymob', 'as' => 'paymob.'], function () {
            Route::any('pay', [PaymobController::class, 'credit'])->name('pay');
            Route::any('callback', [PaymobController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        // PAYTABS
        Route::group(['prefix' => 'paytabs', 'as' => 'paytabs.'], function () {
            Route::any('pay', [PaytabsController::class, 'payment'])->name('pay');
            Route::any('callback', [PaytabsController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::any('response', [PaytabsController::class, 'response'])->name('response')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        // PlaceToPay
        Route::group(['prefix' => 'placetoPay', 'as' => 'placetoPay.'], function () {
            Route::get('pay', [PlacetoPayController::class, 'payment'])->name('pay');
            Route::any('callback', [PlacetoPayController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        // DigiWallet
        Route::group(['prefix' => 'digiWallet', 'as' => 'digiWallet.'], function () {
            Route::get('pay', [DigiWalletController::class, 'payment'])->name('pay');
            Route::any('callback', [DigiWalletController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        // OneLink
        Route::group(['prefix' => 'oneLink', 'as' => 'oneLink.'], function () {
            Route::get('pay', [OneLinkController::class, 'payment'])->name('pay');
            Route::any('callback', [OneLinkController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });
    });
}

Route::group(['prefix' => 'admin/payment'], function () {
    Route::group(['prefix' => 'configuration', 'as' => 'configuration.', 'middleware' => ['admin']], function () {
        Route::get('addon-payment-get', [PaymentConfigController::class, 'payment_config_get'])->name('addon-payment-get');
        Route::put('addon-payment-set', [PaymentConfigController::class, 'payment_config_set'])->name('addon-payment-set');
    });
});

Route::group(['prefix' => 'admin/sms'], function () {
    Route::group(['prefix' => 'configuration', 'as' => 'configuration.', 'middleware' => ['admin']], function () {
        Route::get('addon-sms-get', [SMSConfigController::class, 'sms_config_get'])->name('addon-sms-get');
        Route::put('addon-sms-set', [SMSConfigController::class, 'sms_config_set'])->name('addon-sms-set');
        Route::post('send-test-sms', [SMSConfigController::class, 'send_test_sms'])->name('send-test-sms');
    });
});

