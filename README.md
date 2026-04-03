# Flouci Symfony Bundle

[![Tests](https://github.com/haithemdev/flouci-bundle/actions/workflows/tests.yml/badge.svg)](https://github.com/haithemdev/flouci-bundle/actions)
[![Latest Stable Version](https://img.shields.io/packagist/v/haithemdev/flouci-bundle.svg)](https://packagist.org/packages/haithemdev/flouci-bundle)
[![License](https://img.shields.io/github/license/haithemdev/flouci-bundle.svg)](https://github.com/haithemdev/flouci-bundle/blob/main/LICENSE)

A robust Symfony bundle for integrating the **Flouci Payment Gateway API v2**. This bundle is designed for modern Symfony applications, offering support for multiple API accounts (static or dynamic), event-driven architecture, and built-in webhook handling.

## Features

- ✅ **Simple & Advanced Configuration**: Single account or multiple account support.
- ✅ **Dynamic Accounts**: Create Flouci services on-the-fly for different clients (multi-tenancy).
- ✅ **Event-Driven**: Listen to `FlouciEvents::PAYMENT_VERIFIED` to handle post-payment logic.
- ✅ **Built-in Webhook**: Ready-to-use controller for payment notifications.
- ✅ **Developer Friendly**: Strong typing and custom exceptions for easy debugging.

## Installation

```bash
composer require haithemdev/flouci-bundle
```

## Configuration

### Simple Mode (Standard)

```yaml
# config/packages/flouci_symfony.yaml
flouci_symfony:
    app_token: '%env(FLOUCI_APP_TOKEN)%'
    app_secret: '%env(FLOUCI_APP_SECRET)%'
```

### Advanced Mode (Multiple Accounts)

```yaml
# config/packages/flouci_symfony.yaml
flouci_symfony:
    accounts:
        main:
            app_token: '...'
            app_secret: '...'
        business:
            app_token: '...'
            app_secret: '...'
```

## Usage

### 1. Generating a Payment Link

```php
use Flouci\SymfonyBundle\Service\FlouciServiceInterface;

public function checkout(FlouciServiceInterface $flouci)
{
    $result = $flouci->generatePaymentLink(
        10.500, // Amount in TND
        'order_123',
        'https://your-app.com/success',
        'https://your-app.com/fail'
    );

    return $this->redirect($result['link']);
}
```

### 2. Handling Payments (Events)

Create a listener to handle successful payments:

```php
use Flouci\SymfonyBundle\Event\FlouciEvents;
use Flouci\SymfonyBundle\Event\PaymentVerifiedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: FlouciEvents::PAYMENT_VERIFIED)]
public function onPaymentVerified(PaymentVerifiedEvent $event): void
{
    $data = $event->getPaymentData();
    $orderId = $event->getTrackingId();
    
    if ($event->getStatus() === 'SUCCESS') {
        // Update your order in database
    }
}
```

### 3. Dynamic Client Accounts (No configuration needed)

If your API keys are stored in a database (per client):

```php
use Flouci\SymfonyBundle\Service\FlouciManager;

public function pay(Client $client, FlouciManager $manager)
{
    // Automatically uses default if $client keys are null, or creates a new service if they exist.
    $flouci = $manager->getService($client->getToken(), $client->getSecret());
    
    $flouci->generatePaymentLink(...);
}
```

## Built-in Webhook

This bundle includes a ready-to-use webhook route. Just point Flouci's webhook settings to:
`https://your-app.com/flouci/webhook/default`

## Testing

```bash
vendor/bin/phpunit
```

## License

MIT
