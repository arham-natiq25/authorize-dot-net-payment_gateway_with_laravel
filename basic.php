 {
        // Set Authorize.Net credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(env('AUTHORIZENET_API_LOGIN_ID'));
        $merchantAuthentication->setTransactionKey(env('AUTHORIZENET_TRANSACTION_KEY'));

        // Use the stored customerId for subsequent transactions
        $customerId = '99999456654'; // Replace with the actual customerId from your database

        // Create a payment transaction
        $transactionRequest = new AnetAPI\TransactionRequestType();
        $transactionRequest->setTransactionType("authCaptureTransaction");
        $transactionRequest->setAmount(100);

        // Include the customerId in the transaction request
        $customerProfile = new AnetAPI\CustomerProfilePaymentType();
        $customerProfile->setCustomerProfileId($customerId);
        $transactionRequest->setProfile($customerProfile);

        // Create a request to create the customer profile
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setTransactionRequest($transactionRequest);

        // Execute the request
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        dd($response);

        // Handle the response...

    }
