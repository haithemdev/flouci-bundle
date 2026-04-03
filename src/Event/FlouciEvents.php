<?php

namespace Flouci\SymfonyBundle\Event;

/**
 * Lists all events dispatched by the Flouci Symfony Bundle.
 */
final class FlouciEvents
{
    /**
     * Dispatched after a payment link is successfully generated.
     * Event instance: Flouci\SymfonyBundle\Event\PaymentGeneratedEvent
     */
    public const PAYMENT_GENERATED = 'flouci.payment_generated';

    /**
     * Dispatched after a payment is successfully verified.
     * Event instance: Flouci\SymfonyBundle\Event\PaymentVerifiedEvent
     */
    public const PAYMENT_VERIFIED = 'flouci.payment_verified';

    /**
     * Dispatched if a payment verification fails.
     * Event instance: Flouci\SymfonyBundle\Event\PaymentFailedEvent
     */
    public const PAYMENT_FAILED = 'flouci.payment_failed';
}
