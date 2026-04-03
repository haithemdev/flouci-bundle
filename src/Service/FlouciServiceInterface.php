<?php

namespace Flouci\SymfonyBundle\Service;

interface FlouciServiceInterface
{
    /**
     * Generate a Flouci payment link (API v2)
     *
     * @param float  $amount               Amount in the base currency (e.g., TND)
     * @param string $trackingId           Unique identifier for the transaction
     * @param string $successUrl           URL to redirect to after successful payment
     * @param string $failUrl              URL to redirect to after failed payment
     * @param string|null $webhook          Webhook URL for server-to-server notifications
     * @param array  $destinations         Optional array of split payment destinations
     * @param int    $sessionTimeoutSecs   Session timeout in seconds
     * @return array|null                  Response containing 'payment_id' and 'link'
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
    ): ?array;

    /**
     * Verify a Flouci payment
     *
     * @param string $paymentId   The payment ID returned by Flouci
     * @return array|null         The verification result or null on failure
     */
    public function verifyPayment(string $paymentId): ?array;
}
