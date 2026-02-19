<?php

/**
 * Copyright © - LiquidLab Agency - All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Copyright © - Liquidlab - All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Liquidlab\HyvaMagePrincePaymentFee\Observer;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Event\Observer as Event;
use Magento\Framework\Event\ObserverInterface;

class RegisterModuleForHyvaConfig implements ObserverInterface
{
    private ComponentRegistrar $componentRegistrar;

    public function __construct(ComponentRegistrar $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    public function execute(Event $event): void
    {
        $config = $event->getData('config');
        $extensions = $config->hasData('extensions') ? $config->getData('extensions') : [];

        $path = $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            'Liquidlab_HyvaMagePrincePaymentFee'
        );

        // Only use the path relative to the Magento base dir
        if ($path !== null) {
            $extensions[] = ['src' => substr($path, strlen(BP) + 1)];
        }

        $config->setData('extensions', $extensions);
    }
}
