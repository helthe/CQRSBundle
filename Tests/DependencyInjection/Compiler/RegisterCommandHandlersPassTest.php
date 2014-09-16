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
use Helthe\Component\CQRS\Command\CommandInterface;
use Helthe\Component\CQRS\CommandHandler\CommandHandlerInterface;
use Helthe\Component\CQRS\Exception\InvalidCommandException;
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
     * @expectedExceptionMessage Service "my_command_handler" must define the "command" attribute on "helthe_cqrs.command_handler" tags.
     */
    public function testRegisterCommandHandlerWithoutCommandAttributeListener()
    {
        $handlerDefinition = $this->getDefinitionMock();
        $handlerDefinition->expects($this->once())
                          ->method('isPublic')
                          ->will($this->returnValue(true));
        $handlerDefinition->expects($this->once())
                          ->method('isAbstract')
                          ->will($this->returnValue(false));
        $handlerDefinition->expects($this->once())
                          ->method('getClass')
                          ->will($this->returnValue('Helthe\Bundle\CQRSBundle\Tests\DependencyInjection\Compiler\CommandHandler'));

        $locatorDefinition = $this->getDefinitionMock();
        $locatorDefinition->expects($this->never())
                          ->method('addMethodCall');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->once())
                  ->method('hasDefinition')
                  ->with($this->equalTo('helthe_cqrs.command_handler_locator'))
                  ->will($this->returnValue(true));
        $container->expects($this->once())
                  ->method('findDefinition')
                  ->with($this->equalTo('helthe_cqrs.command_handler_locator'))
                  ->will($this->returnValue($locatorDefinition));
        $container->expects($this->once())
                  ->method('getDefinition')
                  ->with($this->equalTo('my_command_handler'))
                  ->will($this->returnValue($handlerDefinition));
        $container->expects($this->once())
                  ->method('findTaggedServiceIds')
                  ->with($this->equalTo('helthe_cqrs.command_handler'))
                  ->will($this->returnValue(array('my_command_handler' => array(array()))));

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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The service "foo" must implement interface "Helthe\Component\CQRS\CommandHandler\CommandHandlerInterface".
     */
    public function testRegisterCommandHandlerWithoutInterface()
    {
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass')->addTag('helthe_cqrs.command_handler', array('command' => 'bar'));
        $container->register('helthe_cqrs.command_handler_locator', 'stdClass');

        $registerCommandHandlersPass = new RegisterCommandHandlersPass();
        $registerCommandHandlersPass->process($container);

        $definition = $container->getDefinition('helthe_cqrs.command_handler_locator');

        $this->assertSame(array(array('register', array('bar', 'foo'))), $definition->getMethodCalls());
    }

    public function testRegisterValidCommandHandler()
    {
        $handlerDefinition = $this->getDefinitionMock();
        $handlerDefinition->expects($this->once())
                          ->method('isPublic')
                          ->will($this->returnValue(true));
        $handlerDefinition->expects($this->once())
                          ->method('isAbstract')
                          ->will($this->returnValue(false));
        $handlerDefinition->expects($this->once())
                          ->method('getClass')
                          ->will($this->returnValue('Helthe\Bundle\CQRSBundle\Tests\DependencyInjection\Compiler\CommandHandler'));

        $locatorDefinition = $this->getDefinitionMock();
        $locatorDefinition->expects($this->once())
                          ->method('addMethodCall')
                          ->with($this->equalTo('register'), $this->equalTo(array('bar', 'my_command_handler')));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->once())
                  ->method('hasDefinition')
                  ->with($this->equalTo('helthe_cqrs.command_handler_locator'))
                  ->will($this->returnValue(true));
        $container->expects($this->once())
                  ->method('findDefinition')
                  ->with($this->equalTo('helthe_cqrs.command_handler_locator'))
                  ->will($this->returnValue($locatorDefinition));
        $container->expects($this->once())
                  ->method('getDefinition')
                  ->with($this->equalTo('my_command_handler'))
                  ->will($this->returnValue($handlerDefinition));
        $container->expects($this->once())
                  ->method('findTaggedServiceIds')
                  ->with($this->equalTo('helthe_cqrs.command_handler'))
                  ->will($this->returnValue(array('my_command_handler' => array(array('command' => 'bar')))));

        $registerCommandHandlersPass = new RegisterCommandHandlersPass();
        $registerCommandHandlersPass->process($container);
    }

    /**
     * Get a mock of a dependency injection definition.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDefinitionMock()
    {
        return $this->getMock('Symfony\Component\DependencyInjection\Definition');
    }
}

class CommandHandler implements CommandHandlerInterface
{
    public function execute(CommandInterface $command) {}

    public function supports(CommandInterface $command) {}
}