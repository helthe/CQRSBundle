<?php

/*
 * This file is part of the HeltheCQRSBundle package.
 *
 * (c) Carl Alexander <carlalexander@helthe.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Helthe\Bundle\CQRSBundle\Tests\DependencyInjection\Compiler;

use Helthe\Bundle\CQRSBundle\DependencyInjection\Compiler\RegisterCommandHandlersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterCommandHandlersPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The service "foo" must not be abstract as command handlers are lazy-loaded.
     */
    public function testRegisterAbstractCommandHandlerListener()
    {
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass')->setAbstract(true)->addTag('helthe_cqrs.command_handler', array());
        $container->register('helthe_cqrs.command_handler_locator', 'stdClass');

        $registerCommandHandlersPass = new RegisterCommandHandlersPass();
        $registerCommandHandlersPass->process($container);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service "foo" must define the "command" attribute on "helthe_cqrs.command_handler" tags.
     */
    public function testRegisterCommandHandlerWithoutCommandAttributeListener()
    {
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass')->addTag('helthe_cqrs.command_handler', array());
        $container->register('helthe_cqrs.command_handler_locator', 'stdClass');

        $registerCommandHandlersPass = new RegisterCommandHandlersPass();
        $registerCommandHandlersPass->process($container);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The service "foo" must be public as command handlers are lazy-loaded.
     */
    public function testRegisterPrivateCommandHandlerListener()
    {
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass')->setPublic(false)->addTag('helthe_cqrs.command_handler', array());
        $container->register('helthe_cqrs.command_handler_locator', 'stdClass');

        $registerCommandHandlersPass = new RegisterCommandHandlersPass();
        $registerCommandHandlersPass->process($container);
    }

    public function testRegisterValidCommandHandler()
    {
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass')->addTag('helthe_cqrs.command_handler', array('command' => 'bar'));
        $container->register('helthe_cqrs.command_handler_locator', 'stdClass');

        $registerCommandHandlersPass = new RegisterCommandHandlersPass();
        $registerCommandHandlersPass->process($container);

        $definition = $container->getDefinition('helthe_cqrs.command_handler_locator');

        $this->assertSame(array(array('register', array('bar', 'foo'))), $definition->getMethodCalls());
    }
}
