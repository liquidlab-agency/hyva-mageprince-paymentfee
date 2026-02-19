<!--
/**
 * Copyright © - LiquidLab Agency - All rights reserved.
 * See LICENSE.txt for license details.
 */
-->

# Liquidlab_HyvaMagePrincePaymentFee

Hyvä Checkout compatibility module for **MagePrince Payment Fee** (`mageprince/magento2-paymentfee`).

## Problem

The MagePrince Payment Fee module adds payment fees with tax to orders, but the tax component is "invisible" to customers in the Hyvä Checkout UI. The module calculates and stores the payment fee tax in the quote, but doesn't expose it as a separate line item in the checkout summary.

## Solution

This module makes the **Payment Fee Tax** visible as a dedicated line item in the Hyvä Checkout price summary, ensuring transparency for customers.

## Features

- ✅ Displays Payment Fee Tax as a separate line item under the subtotal in Hyvä Checkout
- ✅ Only shows when payment fee tax is enabled and has a value > 0
- ✅ Fully integrated with MagePrince Payment Fee module
- ✅ Multi-language support (English & Romanian included)
- ✅ No modifications to vendor code - clean, upgrade-safe implementation

## Requirements

- PHP >= 8.1
- Magento 2.4.x
- **mageprince/magento2-paymentfee** - The base payment fee module
- **hyva-themes/magento2-hyva-checkout** - Hyvä Checkout

## Installation
### Composer Installation

```bash
composer require liquidlab-agency/hyva-mageprince-paymentfee
php bin/magento module:enable Liquidlab_HyvaMagePrincePaymentFee
php bin/magento setup:upgrade
php bin/magento cache:clean
```

## How It Works

1. **MagePrince Payment Fee** calculates the payment fee and tax during quote totals collection
2. It stores the tax amount in `$total->setPaymentFeeTax($tax)` but doesn't return it as a segment
3. **This module** creates a custom total collector that:
   - Doesn't recalculate anything (MagePrince already did that)
   - Simply exposes the `payment_fee_tax` segment via the `fetch()` method
   - Returns the tax amount stored by MagePrince
4. **Hyvä Checkout** picks up this segment and renders it using our custom template
5. The template displays "Payment Fee Tax" as a line item with the formatted tax amount

## Technical Details

### Module Structure

```
app/code/Liquidlab/HyvaMagePrincePaymentFee/
├── composer.json
├── README.md
├── registration.php
├── etc/
│   ├── module.xml
│   └── sales.xml
├── i18n/
│   ├── en_US.csv
│   └── ro_RO.csv
├── Model/
│   └── Total/
│       └── PaymentFeeTax.php
└── view/
    └── frontend/
        ├── layout/
        │   └── hyva_checkout_components.xml
        └── templates/
            └── checkout/
                └── price-summary/
                    └── total-segments/
                        └── payment_fee_tax.phtml
```

### Key Components

- **Model/Total/PaymentFeeTax.php** - Total collector that exposes the tax segment
- **etc/sales.xml** - Registers the total collector (sort_order: 501, after MagePrince's 500)
- **view/frontend/layout/hyva_checkout_components.xml** - Injects the template into Hyvä Checkout
- **view/frontend/templates/.../payment_fee_tax.phtml** - Displays the tax line item

### Dependencies

The module loads in this order:
1. `Magento_Sales` - Core sales functionality
2. `Magento_Quote` - Quote management
3. `Mageprince_Paymentfee` - Calculates the payment fee tax
4. `Hyva_Checkout` - UI framework
5. `Liquidlab_HyvaMagePrincePaymentFee` - Displays the tax (this module)

## Compatibility

- **Magento**: 2.4.x
- **PHP**: 8.1+
- **Hyvä Themes**: Compatible with Hyvä Checkout
- **MagePrince Payment Fee**: All versions that support tax calculation

## Support

For issues, questions, or contributions:
- **Developer**: LiquidLab Agency
- **Website**: https://liquidlab.ro
- **Email**: contact@liquidlab.ro

## License

Proprietary - Copyright © LiquidLab Agency. All rights reserved.

## Credits

- Developed by **LiquidLab Agency**
- Compatible with **MagePrince Payment Fee** module
- Built for **Hyvä Checkout**
