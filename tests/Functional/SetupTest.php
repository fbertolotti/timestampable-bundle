<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Functional;

use Andante\TimestampableBundle\EventSubscriber\TimestampableEventSubscriber;
use Andante\TimestampableBundle\Tests\HttpKernel\AndanteTimestampableKernel;
use Andante\TimestampableBundle\Tests\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class SetupTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    protected static function createKernel(array $options = []): AndanteTimestampableKernel
    {
        /** @var AndanteTimestampableKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addConfig('/config/basic.yaml');

        return $kernel;
    }

    public function testSubscriberSetup(): void
    {
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = self::getContainer()->get('doctrine');
        /** @var EntityManagerInterface $em */
        foreach ($managerRegistry->getManagers() as $em) {
            $evm = $em->getEventManager();
            /** @var array<object> $listeners */
            $listeners = $evm->getListeners('loadClassMetadata');
            $listenerRegistered = \array_reduce($listeners, static fn(
                bool $carry,
                     $service
            ) => $carry ? $carry : $service instanceof TimestampableEventSubscriber, false);

            self::assertTrue($listenerRegistered);
        }
    }
}
