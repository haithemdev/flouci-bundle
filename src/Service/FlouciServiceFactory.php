<?php

namespace Flouci\SymfonyBundle\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FlouciServiceFactory
{
    private HttpClientInterface $httpClient;
    private string $defaultApiBaseUrl;

    public function __construct(HttpClientInterface $httpClient, string $defaultApiBaseUrl)
    {
        $this->httpClient = $httpClient;
        $this->defaultApiBaseUrl = $defaultApiBaseUrl;
    }

    /**
     * Create a new FlouciService instance on the fly with specific credentials.
     * Useful when accounts are stored in a database (e.g. multi-tenant apps).
     */
    public function create(string $appToken, string $appSecret, ?string $apiBaseUrl = null): FlouciServiceInterface
    {
        return new FlouciService(
            $this->httpClient,
            $appToken,
            $appSecret,
            $apiBaseUrl ?? $this->defaultApiBaseUrl
        );
    }
}
