<?php

namespace Flouci\SymfonyBundle\Tests;

use Flouci\SymfonyBundle\Service\FlouciService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FlouciServiceTest extends TestCase
{
    private $httpClient;
    private $eventDispatcher;
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->service = new FlouciService(
            $this->httpClient,
            'test_token',
            'test_secret',
            'https://api.test',
            $this->eventDispatcher
        );
    }

    public function testVerifyPaymentDispatchesEvent(): void
    {
        $paymentData = ['id' => '123', 'status' => 'SUCCESS', 'developer_tracking_id' => 'order_1'];
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['result' => $paymentData]);

        $this->httpClient->method('request')->willReturn($response);

        // Verification: The dispatcher MUST be called with the right event
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch');

        $result = $this->service->verifyPayment('123');

        $this->assertEquals($paymentData, $result);
    }
}
