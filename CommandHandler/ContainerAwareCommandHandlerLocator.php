<?php

/*
 * This file is part of the HeltheCQRSBundle package.
 *
 * (c) Carl Alexander <carlalexander@helthe.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Helthe\Bundle\CQRSBundle\CommandHandler;

use Helthe\Component\CQRS\Command\CommandInterface;
use Helthe\Component\CQRS\CommandHandler\CommandHandlerInterface;
use Helthe\Component\CQRS\CommandHandler\CommandHandlerLocatorInterface;
use Helthe\Component\CQRS\Exception\CommandHandlerNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Command handler locator that uses the Symfony dependency injection container.
 *
 * @author Carl Alexander <carlalexander@helthe.co>
 */
class ContainerAwareCommandHandlerLocator implements CommandHandlerLocatorInterface
{
    /**
     * Dependency injection container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Services IDs of registered command handlers.
     *
     * @var array
     */
    private $handlerIds = array();

    /**
     * Loaded command handler services.
     *
     * @var CommandHandlerInterface[]
     */
    private $handlers = array();

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Locates the command handler for the given command.
     *
     * @param CommandInterface $command
     *
     * @return CommandHandlerInterface
     *
     * @throws CommandHandlerNotFoundException
     */
    public function locate(CommandInterface $command)
    {
        $commandName = get_class($command);

        if (!isset($this->handlerIds[$commandName])) {
            throw new CommandHandlerNotFoundException(sprintf('No command handler registered for "%s"', get_class($command)));
        }

        if (!isset($this->handlers[$commandName])) {
            $this->handlers[$commandName] = $this->getCommandHandler($this->handlerIds[$commandName]);
        }

        return $this->handlers[$commandName];
    }

    /**
     * Registers a command handler service id for the given command name.
     *
     * The command name should be the full class name of the command.
     *
     * @param string $commandName
     * @param string $handlerId
     */
    public function register($commandName, $handlerId)
    {
        $this->handlerIds[$commandName] = $handlerId;
    }

    /**
     * Get the command handler service with the given service id.
     *
     * @param string $id
     *
     * @return CommandHandlerInterface
     */
    private function getCommandHandler($id)
    {
        return $this->container->get($id);
    }
}
