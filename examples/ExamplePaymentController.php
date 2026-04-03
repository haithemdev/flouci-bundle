<?php

namespace App\Controller; // This is an example, it should be in the main app

use Flouci\SymfonyBundle\Service\FlouciService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExamplePaymentController extends AbstractController
{
    private FlouciService $flouciService;

    public function __construct(FlouciService $flouciService)
    {
        $this->flouciService = $flouciService;
    }

    #[Route('/payment/checkout', name: 'payment_checkout')]
    public function checkout(): Response
    {
        $amount = 10.500; // in TND
        $trackingId = 'ORDER_12345';
        $successUrl = $this->generateUrl('payment_verify', [], 0); // Need absolute URL
        $failUrl = $this->generateUrl('payment_checkout', [], 0);

        $paymentLink = $this->flouciService->generatePaymentLink(
            $amount,
            $trackingId,
            $successUrl,
            $failUrl
        );

        if ($paymentLink) {
            return $this->redirect($paymentLink);
        }

        return $this->render('payment/error.html.twig', [
            'message' => 'Error generating Flouci payment link'
        ]);
    }

    #[Route('/payment/verify', name: 'payment_verify')]
    public function verify(Request $request): Response
    {
        $paymentId = $request->query->get('payment_id');

        if (!$paymentId) {
            return $this->render('payment/error.html.twig', [
                'message' => 'Missing payment ID'
            ]);
        }

        $result = $this->flouciService->verifyPayment($paymentId);

        if ($result && isset($result['status']) && $result['status'] === 'SUCCESS') {
            // Payment successful, update your database
            $orderId = str_replace('ORDER_', '', $result['developer_tracking_id']);

            return $this->render('payment/success.html.twig', [
                'order_id' => $orderId,
                'transaction_id' => $paymentId
            ]);
        }

        return $this->render('payment/error.html.twig', [
            'message' => 'Payment verification failed'
        ]);
    }
}
