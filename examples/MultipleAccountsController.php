<?php

namespace App\Controller;

use Flouci\SymfonyBundle\Service\FlouciManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MultipleAccountsController extends AbstractController
{
    /**
     * Example showing how to use multiple Flouci accounts via the FlouciManager.
     */
    #[Route('/payment/multiple', name: 'payment_multiple')]
    public function index(FlouciManager $flouciManager): Response
    {
        // 1. Get the default account service
        $defaultService = $flouciManager->getDefault();
        // Equivalent to injecting FlouciServiceInterface directly
        
        // 2. Get a specific named account (as defined in config)
        try {
            $businessService = $flouciManager->get('business');
            
            // Example payment link generation for business account
            $businessPayment = $businessService->generatePaymentLink(
                100.0,
                'order_123',
                'https://example.com/success',
                'https://example.com/fail'
            );
        } catch (\InvalidArgumentException $e) {
            // 'business' account not configured
        }

        // 3. Iterate over all accounts
        foreach ($flouciManager->all() as $name => $service) {
            // Do something with each account
        }

        return new Response('Demonstration of multiple accounts usage.');
    }

    /**
     * Example showing how to use the FlouciServiceFactory for dynamic accounts.
     * This is useful when client credentials are stored in a database.
     */
    #[Route('/payment/dynamic/{clientId}', name: 'payment_dynamic')]
    public function dynamic(int $clientId, \Flouci\SymfonyBundle\Service\FlouciServiceFactory $flouciFactory): Response
    {
        // 1. Imagine fetching the client from your database
        // $client = $this->clientRepository->find($clientId);
        // $appToken = $client->getFlouciAppToken();
        // $appSecret = $client->getFlouciAppSecret();

        // 2. Dummy data for demonstration
        $appToken = 'client_token_' . $clientId;
        $appSecret = 'client_secret_' . $clientId;

        // 3. Create the service on the fly
        $clientFlouciService = $flouciFactory->create($appToken, $appSecret);

        // 4. Use the service normally
        $payment = $clientFlouciService->generatePaymentLink(
            50.0,
            'client_order_' . $clientId,
            'https://example.com/success',
            'https://example.com/fail'
        );

        return new Response('Demonstration of dynamic account creation for client ' . $clientId);
    }

    /**
     * THE BEST WAY: Simple, one-liner handling of both Default and Client accounts.
     * This is the "no-dev" approach you asked for.
     */
    #[Route('/payment/simple/{clientId}', name: 'payment_simple')]
    public function simple(int $clientId, \Flouci\SymfonyBundle\Service\FlouciManager $flouciManager): Response
    {
        // 1. Fetch your client from the database
        // $client = $this->clientRepository->find($clientId);
        $client = null; // DUMMY: Simulate client not having custom keys
        
        // 2. Just call getService(). 
        // If you pass nulls, it uses the DEFAULT from config automatically!
        $flouci = $flouciManager->getService(
            $client ? $client->getFlouciToken() : null, 
            $client ? $client->getFlouciSecret() : null
        );

        // 3. One single flow for everyone
        $payment = $flouci->generatePaymentLink(100.0, 'order_abc', 'https://...', 'https://...');

        return new Response('Handled automatically!');
    }
}
