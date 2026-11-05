# hyva-mageprince-paymentfee end-to-end tests

Collected by a project's E2E run when the project installs this module (see `liquidlab-agency/e2e-kit` 1.2.0 or
later). MagePrince Payment Fee adds the cash-on-delivery fee to the total; this module shows it in the Hyvä checkout
as a line (the fee with its tax, total segment `payment_fee_tax`). The spec reads the totals by their segment classes
and amounts, never by label, so it runs on stores in any language and number format. It uses the store profile's
`product` (with its `qty`), `address`, first `shipping` method and `payment` methods.

`payment-fee.spec.mjs` (desktop, as a guest):
- with the profile's other payment method there is no fee line; with `cashondelivery` a fee line appears, and the
  grand total rises by exactly that line. A total that rises with no line saying why is the "invisible fee" the module
  exists to fix. Switching back removes the line and restores the total;
- `@orders`: a cash-on-delivery order, looked up through the guest order form, shows the fee and the grand total the
  checkout showed.

A test skips, with a reason, where the profile doesn't list `cashondelivery` and another method, the cart doesn't
qualify for cash on delivery (its minimum order total: the profile's `qty` must reach it), or the store charges no fee
for it.

Not covered: the fee's order-total limits (they live in each store's config), fees on other payment methods, and the
REST extension attributes (reading a cart or an order needs a token the storefront doesn't have).
