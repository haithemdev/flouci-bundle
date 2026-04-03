<?php

namespace Flouci\SymfonyBundle\Service;

use InvalidArgumentException;

class FlouciManager
{
    /**
     * @var array<string, FlouciServiceInterface>
     */
    private array $services = [];

    private FlouciServiceFactory $factory;
    private string $defaultAccount;

    public function __construct(FlouciServiceFactory $factory, string $defaultAccount = 'default')
    {
        $this->factory = $factory;
        $this->defaultAccount = $defaultAccount;
    }

    /**
     * The easiest way to get a Flouci service:
     * - If $token and $secret are provided, it returns a new service for that client.
     * - Otherwise, it returns the default service from configuration.
     */
    public function getService(?string $token = null, ?string $secret = null): FlouciServiceInterface
    {
        if ($token && $secret) {
            return $this->factory->create($token, $secret);
        }

        return $this->getDefault();
    }

    public function addService(string $name, FlouciServiceInterface $service): void
    {
        $this->services[$name] = $service;
    }

    public function get(string $name): FlouciServiceInterface
    {
        if (!isset($this->services[$name])) {
            throw new InvalidArgumentException(sprintf('Flouci account "%s" is not configured.', $name));
        }

        return $this->services[$name];
    }

    public function getDefault(): FlouciServiceInterface
    {
        return $this->get($this->defaultAccount);
    }

    /**
     * @return array<string, FlouciServiceInterface>
     */
    public function all(): array
    {
        return $this->services;
    }
}
