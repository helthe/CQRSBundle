<?php

/*
 * This file is part of the HeltheCQRSBundle package.
 *
 * (c) Carl Alexander <carlalexander@helthe.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Helthe\Bundle\CQRSBundle;

use Helthe\Bundle\CQRSBundle\DependencyInjection\Compiler\RegisterCommandHandlersPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * CQRSBundle
 *
 * @author Carl Alexander <carlalexander@helthe.co>
 */
class HeltheCQRSBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterCommandHandlersPass(), PassConfig::TYPE_AFTER_REMOVING);
    }
}
