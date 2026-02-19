<?php

/**
 * Copyright © - LiquidLab Agency - All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Liquidlab\HyvaMagePrincePaymentFee\Model\Total;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Mageprince\Paymentfee\Helper\Data as FeeHelper;

class PaymentFeeTax extends AbstractTotal
{
    /**
     * @var FeeHelper
     */
    private $helper;

    /**
     * @param FeeHelper $helper
     */
    public function __construct(FeeHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Collect payment fee tax total
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): static {
        parent::collect($quote, $shippingAssignment, $total);

        // We don't need to collect anything here, MagePrince already does the calculation
        // This is just a display total collector to expose the tax in the fetch() method

        return $this;
    }

    /**
     * Fetch payment fee tax for display
     *
     * @param Quote $quote
     * @param Total $total
     */
    public function fetch(Quote $quote, Total $total): array
    {
        // Only show if payment fee tax is enabled and module is enabled
        if (!$this->helper->isEnable() || !$this->helper->isTaxEnabled()) {
            return [];
        }

        $taxAmount = (float)$total->getPaymentFee() + (float)$total->getPaymentFeeTax();

        if ($taxAmount <= 0) {
            return [];
        }

        return [
            'code' => 'payment_fee_tax',
            'title' => __('Payment Fee'),
            'value' => $taxAmount
        ];
    }

    /**
     * Get label
     *
     */
    public function getLabel(): string
    {
        return (string)__('Payment Fee');
    }
}
