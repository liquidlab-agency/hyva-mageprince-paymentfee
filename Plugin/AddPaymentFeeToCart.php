<?php

/**
 * Copyright © - LiquidLab Agency - All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Liquidlab\HyvaMagePrincePaymentFee\Plugin;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

/**
 * Plugin to add MagePrince payment fee attributes to Cart extension attributes for REST API
 */
class AddPaymentFeeToCart
{
    /**
     * @var CartExtensionInterfaceFactory
     */
    private $factory;

    /**
     * @var int[]
     */
    private $cartsProcessed = [];

    /**
     * @param CartExtensionInterfaceFactory $factory
     */
    public function __construct(
        CartExtensionInterfaceFactory $factory
    ) {
        $this->factory = $factory;
    }

    /**
     * Add payment fee to single cart
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $result
     * @return CartInterface
     */
    public function afterGet(CartRepositoryInterface $subject, CartInterface $result)
    {
        $result = $this->processCart($result);

        return $result;
    }

    /**
     * Add payment fee to cart list
     *
     * @param CartRepositoryInterface $subject
     * @param SearchResultsInterface $result
     * @return SearchResultsInterface
     */
    public function afterGetList(CartRepositoryInterface $subject, SearchResultsInterface $result)
    {
        $items = $result->getItems();
        foreach ($items as $id => $item) {
            if ($item instanceof CartInterface) {
                $items[$id] = $this->processCart($item);
            }
        }

        $result->setItems($items);

        return $result;
    }

    /**
     * Process cart and add payment fee to extension attributes
     *
     * This code sometimes collides with other extensions. When the cart is loaded during the totals processing,
     * it will overwrite the calculated values with the values from the database. This code will prevent this.
     *
     * @param CartInterface $cart
     * @return CartInterface
     */
    private function processCart(CartInterface $cart)
    {
        if (in_array($cart->getId(), $this->cartsProcessed)) {
            return $cart;
        }

        $extensionAttributes = $cart->getExtensionAttributes()
            ? $cart->getExtensionAttributes()
            : $this->factory->create();

        // Cast to Quote to access getData() method
        if ($cart instanceof Quote) {
            $extensionAttributes->setPaymentFee((float)$cart->getData('payment_fee'));
            $extensionAttributes->setBasePaymentFee((float)$cart->getData('base_payment_fee'));
            $extensionAttributes->setPaymentFeeTax((float)$cart->getData('payment_fee_tax'));
            $extensionAttributes->setBasePaymentFeeTax((float)$cart->getData('base_payment_fee_tax'));
        }

        $cart->setExtensionAttributes($extensionAttributes);

        $this->cartsProcessed[] = $cart->getId();

        return $cart;
    }
}
