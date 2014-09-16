<?php

/*
 * This file is part of the HeltheCQRSBundle package.
 *
 * (c) Carl Alexander <carlalexander@helthe.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Helthe\Bundle\CQRSBundle\Tests\CommandHandler;

use Helthe\Bundle\CQRSBundle\CommandHandler\ContainerAwareCommandHandlerLocator;

class ContainerAwareCommandHandlerLocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testLocateLoadedCommandHandler()
    {
        $command = $this->getCommandMock();
        $commandName = get_class($command);

        $handler = $this->getCommandHandlerMock();

        $container = $this->getContainerMock();
        $container->expects($this->never())
                  ->method('get');

        $locator = new ContainerAwareCommandHandlerLocator($container);

        $locator->register($commandName, 'foo');

        $reflection = new \ReflectionClass('Helthe\Bundle\CQRSBundle\CommandHandler\ContainerAwareCommandHandlerLocator');
        $handlersProperty = $reflection->getProperty('handlers');
        $handlersProperty->setAccessible(true);
        $handlersProperty->setValue($locator, array($commandName => $handler));

        $this->assertSame($handler, $locator->locate($command));
    }

    public function testLocateUnloadedCommandHandler()
    {
        $command = $this->getCommandMock();

        $handler = $this->getCommandHandlerMock();

        $container = $this->getContainerMock();
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->equalTo('foo'))
                  ->will($this->returnValue($handler));

        $locator = new ContainerAwareCommandHandlerLocator($container);

        $locator->register(get_class($command), 'foo');

        $this->assertSame($handler, $locator->locate($command));
    }

    /**
     * @expectedException Helthe\Component\CQRS\Exception\CommandHandlerNotFoundException
     */
    public function testLocateNonexistentCommandHandler()
    {
        $command = $this->getCommandMock();
        $container = $this->getContainerMock();
        $locator = new ContainerAwareCommandHandlerLocator($container);

        $locator->locate($command);
    }

    public function testRegister()
    {
        $container = $this->getContainerMock();
        $locator = new ContainerAwareCommandHandlerLocator($container);

        $locator->register('foo', 'bar');

        $reflection = new \ReflectionClass('Helthe\Bundle\CQRSBundle\CommandHandler\ContainerAwareCommandHandlerLocator');
        $handlerIdsProperty = $reflection->getProperty('handlerIds');
        $handlerIdsProperty->setAccessible(true);

        $this->assertSame(array('foo' => 'bar'), $handlerIdsProperty->getValue($locator));
    }

    /**
     * Get a mock of a command.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getCommandMock()
    {
        return $this->getMock('\Helthe\Component\CQRS\Command\CommandInterface');
    }

    /**
     * Get a mock of a command handler.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getCommandHandlerMock()
    {
        return $this->getMock('\Helthe\Component\CQRS\CommandHandler\CommandHandlerInterface');
    }

    /**
     * Get a mock of a container.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getContainerMock()
    {
        return $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
    }
}
