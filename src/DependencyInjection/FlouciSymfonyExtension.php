<?php

namespace Flouci\SymfonyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Flouci\SymfonyBundle\Service\FlouciService;
use Flouci\SymfonyBundle\Service\FlouciServiceInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FlouciSymfonyExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $accounts = $config['accounts'] ?? [];

        // Backward compatibility for root-level config
        if ($config['app_token'] && $config['app_secret']) {
            if (!isset($accounts['default'])) {
                $accounts['default'] = [
                    'app_token' => $config['app_token'],
                    'app_secret' => $config['app_secret'],
                    'api_base_url' => $config['api_base_url'],
                ];
            }
        }

        if (empty($accounts)) {
             // In a real bundle, we might want to allow empty config if not used,
             // but here we expect at least one account.
             return;
        }

        $managerDefinition = new Definition(\Flouci\SymfonyBundle\Service\FlouciManager::class, [
            new Reference(\Flouci\SymfonyBundle\Service\FlouciServiceFactory::class)
        ]);
        $container->setDefinition(\Flouci\SymfonyBundle\Service\FlouciManager::class, $managerDefinition);
        $container->setAlias('flouci.manager', \Flouci\SymfonyBundle\Service\FlouciManager::class);

        $factoryDefinition = new Definition(\Flouci\SymfonyBundle\Service\FlouciServiceFactory::class, [
            new Reference('http_client'),
            $config['api_base_url']
        ]);
        $container->setDefinition(\Flouci\SymfonyBundle\Service\FlouciServiceFactory::class, $factoryDefinition);
        $container->setAlias('flouci.factory', \Flouci\SymfonyBundle\Service\FlouciServiceFactory::class);

        $defaultAccountName = isset($accounts['default']) ? 'default' : array_key_first($accounts);
        $managerDefinition->setArgument(0, $defaultAccountName);

        foreach ($accounts as $name => $accountConfig) {
            $apiBaseUrl = $accountConfig['api_base_url'] ?? $config['api_base_url'];

            $serviceId = sprintf('flouci.service.%s', $name);
            $definition = new Definition(FlouciService::class, [
                new Reference('http_client'),
                $accountConfig['app_token'],
                $accountConfig['app_secret'],
                $apiBaseUrl,
                new Reference('event_dispatcher', ContainerBuilder::IGNORE_ON_INVALID_REFERENCE)
            ]);

            $container->setDefinition($serviceId, $definition);

            // Add to manager
            $managerDefinition->addMethodCall('addService', [$name, new Reference($serviceId)]);
        }

        // Set default service aliases
        $defaultServiceId = sprintf('flouci.service.%s', $defaultAccountName);
        $container->setAlias(FlouciService::class, $defaultServiceId);
        $container->setAlias(FlouciServiceInterface::class, $defaultServiceId);
        $container->setAlias('flouci.service', $defaultServiceId);
    }

    public function getAlias(): string
    {
        return 'flouci_symfony';
    }
}
