<?php

namespace Flouci\SymfonyBundle\Controller;

use Flouci\SymfonyBundle\Service\FlouciManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Default webhook handler for Flouci notifications.
 */
class FlouciWebhookController extends AbstractController
{
    private FlouciManager $flouciManager;

    public function __construct(FlouciManager $flouciManager)
    {
        $this->flouciManager = $flouciManager;
    }

    /**
     * Endpoint to handle Flouci payment verification.
     * 
     * You can point Flouci's webhook to this route.
     * It will automatically verify the payment and dispatch a PaymentVerifiedEvent.
     */
    #[Route('/flouci/webhook/{account}', name: 'flouci_webhook', methods: ['GET', 'POST'])]
    public function handle(Request $request, string $account = 'default'): JsonResponse
    {
        $paymentId = $request->get('payment_id');

        if (!$paymentId) {
            return new JsonResponse(['error' => 'Missing payment_id'], 400);
        }

        try {
            $flouci = $this->flouciManager->get($account);
            $result = $flouci->verifyPayment($paymentId);

            return new JsonResponse([
                'success' => true,
                'status' => $result['status'] ?? 'UNKNOWN'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
