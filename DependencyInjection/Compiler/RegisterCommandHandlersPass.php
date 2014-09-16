<?php

/*
 * This file is part of the HeltheCQRSBundle package.
 *
 * (c) Carl Alexander <carlalexander@helthe.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Helthe\Bundle\CQRSBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Compiler pass for registering tagged services for a command handler locator.
 *
 * @author Carl Alexander <carlalexander@helthe.co>
 */
class RegisterCommandHandlersPass implements CompilerPassInterface
{
    /**
     * Tag used to find command handler services.
     *
     * @var string
     */
    private $handlerTag;

    /**
     * Command handler locator service id.
     *
     * @var string
     */
    private $locatorService;

    /**
     * Constructor.
     *
     * @param string $locatorService
     * @param string $handlerTag
     */
    public function __construct($locatorService = 'helthe_cqrs.command_handler_locator', $handlerTag = 'helthe_cqrs.command_handler')
    {
        $this->handlerTag = $handlerTag;
        $this->locatorService = $locatorService;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->locatorService) && !$container->hasAlias($this->locatorService)) {
            return;
        }

        $locator = $container->getDefinition($this->locatorService);

        foreach ($container->findTaggedServiceIds($this->handlerTag) as $handlerId => $commands) {
            $this->processTaggedService($container, $locator, $handlerId, $commands);
        }
    }

    /**
     * Processes a command handler tagged service.
     *
     * @param ContainerBuilder $container
     * @param Definition       $locator
     * @param string           $handlerId
     * @param array            $commands
     *
     * @throws \InvalidArgumentException
     */
    private function processTaggedService(ContainerBuilder $container, Definition $locator, $handlerId, array $commands)
    {
        $handler = $container->getDefinition($handlerId);

        if (!$handler->isPublic()) {
            throw new \InvalidArgumentException(sprintf('The service "%s" must be public as command handlers are lazy-loaded.', $handlerId));
        }

        if ($handler->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('The service "%s" must not be abstract as command handlers are lazy-loaded.', $handlerId));
        }

        foreach ($commands as $command) {
            $this->registerCommandHandler($locator, $handlerId, $command);
        }
    }

    /**
     * Registers a command handler service with the command handler locator definition.
     *
     * @param Definition $locator
     * @param string     $handlerId
     * @param array      $command
     *
     * @throws \InvalidArgumentException
     */
    private function registerCommandHandler(Definition $locator, $handlerId, array $command)
    {
        if (!isset($command['command'])) {
            throw new \InvalidArgumentException(sprintf('Service "%s" must define the "command" attribute on "%s" tags.', $handlerId, $this->handlerTag));
        }

        $locator->addMethodCall('register', array($command['command'], $handlerId));
    }
}
