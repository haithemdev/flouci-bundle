<?php

namespace Flouci\SymfonyBundle\Service;

use Flouci\SymfonyBundle\Event\FlouciEvents;
use Flouci\SymfonyBundle\Event\PaymentVerifiedEvent;
use Flouci\SymfonyBundle\Exception\PaymentGenerationException;
use Flouci\SymfonyBundle\Exception\PaymentVerificationException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FlouciService implements FlouciServiceInterface
{
    private HttpClientInterface $httpClient;
    private ?EventDispatcherInterface $eventDispatcher;
    private string $appToken;
    private string $appSecret;
    private string $apiBaseUrl;

    public function __construct(
        HttpClientInterface $httpClient,
        string $appToken,
        string $appSecret,
        string $apiBaseUrl,
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->httpClient = $httpClient;
        $this->eventDispatcher = $eventDispatcher;
        $this->appToken = $appToken;
        $this->appSecret = $appSecret;
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/');
    }

    /**
     * Generate a Flouci payment link (API v2)
     */
    public function generatePaymentLink(
        float $amount,
        string $trackingId,
        string $successUrl,
        string $failUrl,
        ?string $webhook = null,
        array $destinations = [],
        bool $acceptCard = true,
        int $sessionTimeoutSecs = 1200
    ): ?array {
        $amountInMillimes = (int) ($amount * 1000);

        $body = [
            'amount' => $amountInMillimes,
            'success_link' => $successUrl,
            'fail_link' => $failUrl,
            'developer_tracking_id' => $trackingId,
            'accept_card' => $acceptCard,
            'session_timeout_secs' => $sessionTimeoutSecs,
        ];

        if ($webhook) {
            $body['webhook'] = $webhook;
        }

        if (!empty($destinations)) {
            $body['destination'] = $destinations;
        }

        try {
            $response = $this->httpClient->request('POST', $this->apiBaseUrl . '/generate_payment', [
                'headers' => [
                    'Authorization' => sprintf('Bearer %s:%s', $this->appToken, $this->appSecret),
                    'Content-Type' => 'application/json',
                ],
                'json' => $body
            ]);

            $content = $response->toArray();

            if (isset($content['result'])) {
                return $content['result']; // returns ['payment_id' => '...', 'link' => '...']
            }
        } catch (\Exception $e) {
            throw new PaymentGenerationException('Could not generate payment link: ' . $e->getMessage(), 0, $e);
        }

        throw new PaymentGenerationException('Invalid API response structure during payment generation.');
    }

    /**
     * Verify a Flouci payment (API v2)
     */
    public function verifyPayment(string $paymentId): ?array
    {
        try {
            $response = $this->httpClient->request('GET', $this->apiBaseUrl . '/verify_payment/' . $paymentId, [
                'headers' => [
                    'Authorization' => sprintf('Bearer %s:%s', $this->appToken, $this->appSecret),
                ]
            ]);

            $content = $response->toArray();

            if (isset($content['result'])) {
                $result = $content['result'];

                if ($this->eventDispatcher) {
                    $this->eventDispatcher->dispatch(new PaymentVerifiedEvent($result), FlouciEvents::PAYMENT_VERIFIED);
                }

                return $result;
            }
        } catch (\Exception $e) {
            throw new PaymentVerificationException('Could not verify payment: ' . $e->getMessage(), 0, $e);
        }

        throw new PaymentVerificationException('Invalid API response structure during payment verification.');
    }
}
