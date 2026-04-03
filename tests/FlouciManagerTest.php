<?php

namespace Flouci\SymfonyBundle\Tests;

use Flouci\SymfonyBundle\Service\FlouciManager;
use Flouci\SymfonyBundle\Service\FlouciServiceInterface;
use Flouci\SymfonyBundle\Service\FlouciServiceFactory;
use PHPUnit\Framework\TestCase;

class FlouciManagerTest extends TestCase
{
    public function testManagerCanRegisterAndRetrieveServices(): void
    {
        $factory = $this->createMock(FlouciServiceFactory::class);
        $manager = new FlouciManager($factory, 'default');
        
        $service = $this->createMock(FlouciServiceInterface::class);
        $manager->addService('default', $service);
        
        $this->assertSame($service, $manager->get('default'));
        $this->assertSame($service, $manager->getDefault());
    }

    public function testGetServiceCreatesDynamicInstance(): void
    {
        $factory = $this->createMock(FlouciServiceFactory::class);
        $manager = new FlouciManager($factory, 'default');
        
        $customService = $this->createMock(FlouciServiceInterface::class);
        
        $factory->expects($this->once())
            ->method('create')
            ->with('token', 'secret')
            ->willReturn($customService);

        $result = $manager->getService('token', 'secret');
        
        $this->assertSame($customService, $result);
    }

    public function testGetServiceReturnsDefaultWhenNoCredentials(): void
    {
        $factory = $this->createMock(FlouciServiceFactory::class);
        $manager = new FlouciManager($factory, 'default');
        
        $defaultService = $this->createMock(FlouciServiceInterface::class);
        $manager->addService('default', $defaultService);

        $result = $manager->getService(null, null);
        
        $this->assertSame($defaultService, $result);
    }
}
