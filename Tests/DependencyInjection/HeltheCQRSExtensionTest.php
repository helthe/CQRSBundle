<?php

/*
 * This file is part of the HeltheCQRSBundle package.
 *
 * (c) Carl Alexander <carlalexander@helthe.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Helthe\Bundle\CQRSBundle\Tests\DependencyInjection;

use Helthe\Bundle\CQRSBundle\DependencyInjection\HeltheCQRSExtension;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HeltheCQRSExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder();
        $loader = new HeltheCQRSExtension();
        $loader->load(array(), $container);

        // Command Bus
        $this->assertTrue($container->hasParameter('helthe_cqrs.command_bus.sequential.class'));
        $this->assertEquals('Helthe\Component\CQRS\Bus\SequentialCommandBus', $container->getParameter('helthe_cqrs.command_bus.sequential.class'));
        $this->assertTrue($container->hasDefinition('helthe_cqrs.command_bus.sequential'));

        $this->assertTrue($container->hasAlias('helthe_cqrs.command_bus'));
        $this->assertEquals(new Alias('helthe_cqrs.command_bus.sequential'), $container->getAlias('helthe_cqrs.command_bus'));

        // Command Handler Locator
        $this->assertTrue($container->hasParameter('helthe_cqrs.command_handler_locator.container_aware.class'));
        $this->assertEquals('Helthe\Bundle\CQRSBundle\CommandHandler\ContainerAwareCommandHandlerLocator', $container->getParameter('helthe_cqrs.command_handler_locator.container_aware.class'));
        $this->assertTrue($container->hasDefinition('helthe_cqrs.command_handler_locator.container_aware'));

        $this->assertTrue($container->hasAlias('helthe_cqrs.command_handler_locator'));
        $this->assertEquals(new Alias('helthe_cqrs.command_handler_locator.container_aware'), $container->getAlias('helthe_cqrs.command_handler_locator'));
    }
}
