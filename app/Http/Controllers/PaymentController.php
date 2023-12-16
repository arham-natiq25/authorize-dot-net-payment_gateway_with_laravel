<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        // Set Authorize.Net credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(env('AUTHORIZENET_API_LOGIN_ID'));
        $merchantAuthentication->setTransactionKey(env('AUTHORIZENET_TRANSACTION_KEY'));

        $refId = 'ref' . time();

        // Add credit card information to the transaction request


        // Create a payment transaction
        $transactionRequest = new AnetAPI\TransactionRequestType();
        $transactionRequest->setTransactionType("authCaptureTransaction");
        $transactionRequest->setAmount(1000);
        // Add other transaction details...

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber('4111111111111111');
        $creditCard->setExpirationDate('2023-12'); // Use a valid expiration date


        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setCreditCard($creditCard);

        $transactionRequest->setPayment($paymentType);


        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setTransactionRequest($transactionRequest);

        // Execute the request
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            // Payment successful
            $transactionResponse = $response->getTransactionResponse();
            if ($transactionResponse != null) {
                dd($transactionResponse);
                // Handle the response...
            } else {
                // Handle the case where getTransactionResponse() returns null

            }
            // Handle the response...
        } else {
            // Payment failed
            $errorMessages = $response->getMessages()->getMessage();
            dd($errorMessages); // Log or handle the error...
            // Handle the error...
        }
    }
}
