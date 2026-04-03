<?php

namespace Flouci\SymfonyBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PaymentVerifiedEvent extends Event
{
    private array $paymentData;

    public function __construct(array $paymentData)
    {
        $this->paymentData = $paymentData;
    }

    public function getPaymentData(): array
    {
        return $this->paymentData;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentData['id'] ?? null;
    }

    public function getStatus(): ?string
    {
        return $this->paymentData['status'] ?? null;
    }

    public function getTrackingId(): ?string
    {
        return $this->paymentData['developer_tracking_id'] ?? null;
    }
}
