<?php

namespace App\Http\Controllers;

use App\Models\CustomerProfile;
use App\Models\TransactionRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class PaymentController extends Controller
{
    public function index(Request $request)
    {


        $user = Auth::user();
        /* Create a merchantAuthenticationType object with authentication details
           retrieved from the constants file */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(env('AUTHORIZENET_API_LOGIN_ID'));
        $merchantAuthentication->setTransactionKey(env('AUTHORIZENET_TRANSACTION_KEY'));

        // Set the transaction's refId
        $refId = 'ref' . time();
        try {
             // Create the payment data for a credit card
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($request->cardNumber);
        $creditCard->setExpirationDate($request->expiryYear."-".$request->expiryMonth);
        $creditCard->setCardCode($request->cvv);

        // Add the payment data to a paymentType object
        $paymentOne = new AnetAPI\PaymentType();
        $paymentOne->setCreditCard($creditCard);


        // Set the customer's Bill To address
        $customerAddress = new AnetAPI\CustomerAddressType();
        $customerAddress->setFirstName($user->name);
        $customerAddress->setLastName($user->name);
        // $customerAddress->setCompany("Souveniropolis");
        // $customerAddress->setAddress("14 Main Street");
        // $customerAddress->setCity("Pecan Springs");
        // $customerAddress->setState("TX");
        // $customerAddress->setZip("44628");
        // $customerAddress->setCountry("USA");

        // Set the customer's identifying information
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId($user->id); // give own user id
        $customerData->setEmail($user->email); // user emial

        // Add values for transaction settings
        $duplicateWindowSetting = new AnetAPI\SettingType();
        $duplicateWindowSetting->setSettingName("duplicateWindow");
        $duplicateWindowSetting->setSettingValue("60");

        $billTo = new AnetAPI\CustomerAddressType();
        $billTo->setFirstName($user->name);
        $billTo->setLastName($user->name);
        // $billTo->setCompany("Souveniropolis");
        // $billTo->setAddress("14 Main Street");
        // $billTo->setCity("Pecan Springs");
        // $billTo->setState("TX");
        // $billTo->setZip("44628");
        // $billTo->setCountry("USA");
        // $billTo->setPhoneNumber("888-888-8888");
        // $billTo->setFaxNumber("999-999-9999");

        // Add some merchant defined fields. These fields won't be stored with the transaction,
        // but will be echoed back in the response.
        // $merchantDefinedField1 = new AnetAPI\UserFieldType();
        // $merchantDefinedField1->setName($user->name);
        // $merchantDefinedField1->setValue("1128836273");

        // $merchantDefinedField2 = new AnetAPI\UserFieldType();
        // $merchantDefinedField2->setName("favoriteColor");
        // $merchantDefinedField2->setValue("blue");

        // Create a TransactionRequestType object and add the previous objects to it



        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType('individual');
        $paymentProfile->setBillTo($billTo);
        $paymentProfile->setPayment($paymentOne);
        $paymentProfiles[] = $paymentProfile;

        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setDescription("Payment Using laravel");
        $customerProfile->setMerchantCustomerId("M_" . time());
        $customerProfile->setEmail($user->email); // Set the email for the customer
        $customerProfile->setPaymentProfiles($paymentProfiles);

        $request = new AnetAPI\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setProfile($customerProfile);
        $controller = new AnetController\CreateCustomerProfileController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);



        if ($response != null && $response->getMessages()->getResultCode() == "Ok") {
            // here successfully get customer profile id
            $customerProfileIDFromAuthorize = $response->getCustomerProfileId();
            $paymentProfileId = $response->getCustomerPaymentProfileIdList()[0];
        } else {
            // Handle the case where the API request was not successful
            $errorMessages = $response->getMessages()->getMessage();
        }
        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($customerProfileIDFromAuthorize);
        $paymentProfile = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($paymentProfileId);
        $profileToCharge->setPaymentProfile($paymentProfile);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount(150);
        $transactionRequestType->setProfile($profileToCharge);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

        if ($response != null) {
            if ($response->getMessages()->getResultCode() == "Ok") {
                $tresponse = $response->getTransactionResponse();
                $lastFourDigits = $tresponse->getAccountNumber();
                $cardType = $tresponse->getAccountType();
                $trx_id =  $tresponse->getTransId() . "\n";

                TransactionRecords::create([
                    'user_id' => $user->id,
                    'payment' => 100,
                    'trx_id' => $trx_id
                ]);
                CustomerProfile::create([
                    'user_id' => $user->id,
                    'last_four_digit' => $lastFourDigits,
                    'card_type' => $cardType,
                    'customer_profile_id' => $customerProfileIDFromAuthorize,
                    'payment_profile_id' => $paymentProfileId

                ]);

                return response()->json(['message' => 'Payment successful'], 200);
            }else {
                $errorMessages = $response->getMessages()->getMessage();
                return response()->json(['error' => $errorMessages], 400);
            }
        }
        } catch (\Exception $e) {
            // Handle any exceptions here
            // You might want to log or handle the exception appropriately
            // For example, you can return the exception message to the frontend
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function payWithCard(Request $request)
    {

        $user = Auth::user();


        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(env('AUTHORIZENET_API_LOGIN_ID'));
        $merchantAuthentication->setTransactionKey(env('AUTHORIZENET_TRANSACTION_KEY'));

        $customerProfileId = $request->card['customer_profile_id'];
        $lastFourDigits = $request->card['last_four_digit'];
        $user_id = $request->card['user_id'];
        $card_type = $request->card['card_type'];
        $paymentProfileId = $request->card['payment_profile_id'];

        $refId = 'ref' . time();
            try {
                $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
                $profileToCharge->setCustomerProfileId($customerProfileId);
                $paymentProfile = new AnetAPI\PaymentProfileType();
                $paymentProfile->setPaymentProfileId($paymentProfileId);
                $profileToCharge->setPaymentProfile($paymentProfile);

                $transactionRequestType = new AnetAPI\TransactionRequestType();
                $transactionRequestType->setTransactionType("authCaptureTransaction");
                $transactionRequestType->setAmount(240);
                $transactionRequestType->setProfile($profileToCharge);

                $request = new AnetAPI\CreateTransactionRequest();
                $request->setMerchantAuthentication($merchantAuthentication);
                $request->setRefId($refId);
                $request->setTransactionRequest($transactionRequestType);
                $controller = new AnetController\CreateTransactionController($request);
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

                if ($response != null) {
                    if ($response->getMessages()->getResultCode() == "Ok") {
                        $tresponse = $response->getTransactionResponse();
                        $trx_id =  $tresponse->getTransId() . "\n";

                    $trans =  TransactionRecords::create([
                        'user_id' => $user->id,
                        'payment' => 240,
                        'trx_id' => $trx_id
                    ]);
                    return response()->json(['message' => 'Payment successful'], 200);
                }
                else {
                    $errorMessages = $response->getMessages()->getMessage();
                    return response()->json(['error' => $errorMessages], 400);
                }
                }

            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
         }
}
