<?php

/**
 * Copyright © - LiquidLab Agency - All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Liquidlab\HyvaMagePrincePaymentFee\Plugin;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Plugin to add MagePrince payment fee attributes to Order extension attributes for REST API
 */
class AddPaymentFeeToOrder
{
    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @param OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * Add payment fee to order list
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchCriteria
     * @return OrderSearchResultInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchCriteria
    ) {
        foreach ($searchCriteria->getItems() as $entity) {
            $this->addPaymentFeeToOrder($entity);
        }

        return $searchCriteria;
    }

    /**
     * Add payment fee to single order
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $this->addPaymentFeeToOrder($order);

        return $order;
    }

    /**
     * Add payment fee data to order extension attributes
     *
     * @param OrderInterface $entity
     */
    private function addPaymentFeeToOrder(OrderInterface $entity): void
    {
        $extensionAttributes = $this->getExtensionAttributes($entity);
        if ($entity instanceof \Magento\Framework\DataObject) {
            $extensionAttributes->setPaymentFee((float)$entity->getData('payment_fee'));
            $extensionAttributes->setBasePaymentFee((float)$entity->getData('base_payment_fee'));
            $extensionAttributes->setPaymentFeeTax((float)$entity->getData('payment_fee_tax'));
            $extensionAttributes->setBasePaymentFeeTax((float)$entity->getData('base_payment_fee_tax'));
        }
        $entity->setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get extension attributes from entity or create new instance
     *
     * @param OrderInterface $entity
     * @return \Magento\Sales\Api\Data\OrderExtension|\Magento\Sales\Api\Data\OrderExtensionInterface
     */
    private function getExtensionAttributes(OrderInterface $entity)
    {
        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes) {
            return $extensionAttributes;
        }

        return $this->orderExtensionFactory->create();
    }
}
