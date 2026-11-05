import {
    test, expect, addToCart, openCheckout, fillCheckoutAddress, chooseShipping, choosePayment, placeOrder, totals, idle,
    uniqueEmail,
} from '@liquidlab-agency/e2e-kit';

/* The cash-on-delivery fee in the Hyvä checkout: MagePrince adds it to the total, and this module shows it as a line
   (fee with its tax, segment `payment_fee_tax`). Read from the totals' segment classes and amounts, never from labels:
   the store's language varies. */
const FEE = 'cashondelivery';
const feeLine = (page) => totals(page).locator('.payment_fee_tax .value');
const grandTotalLine = (page) => totals(page).locator('.grand_total .value').first();

/** An amount as shown, in any format: "82,97 RON", "1.234,56 lei", "$54.00". */
function amount(shown) {
    const digits = shown.replace(/\s/g, '').match(/\d[\d.,]*/)?.[0] ?? '';
    const last = Math.max(digits.lastIndexOf(','), digits.lastIndexOf('.'));
    if (last >= 0 && digits.length - last - 1 === 2) {
        return Number(`${digits.slice(0, last).replace(/[.,]/g, '')}.${digits.slice(last + 1)}`);
    }
    return Number(digits.replace(/[.,]/g, ''));
}

const cents = (value) => Math.round(value * 100);

async function shownTotals(page) {
    await idle(page, 1200);
    return {
        fee: (await feeLine(page).count()) ? amount(await feeLine(page).innerText()) : null,
        grandTotal: amount(await grandTotalLine(page).innerText()),
    };
}

/** Fills the cart with the profile's product and takes the checkout as far as payment. */
async function toPayment(page, store, email) {
    await addToCart(page, store.product);
    await openCheckout(page);
    await fillCheckoutAddress(page, store.address, email);
    await chooseShipping(page, store.shipping[0]);
}

/** The profile's other payment method: the one compared against, with no fee. */
const otherPayment = (store) => (store.payment ?? []).find((code) => code !== FEE);

function skipUnlessOffered(store) {
    test.skip(!(store.payment ?? []).includes(FEE) || !otherPayment(store), `the profile doesn't list ${FEE} and another payment method`);
}

test('cash on delivery shows its fee, and the fee is the whole difference in the total', async ({ page, store }) => {
    skipUnlessOffered(store);
    await toPayment(page, store, uniqueEmail('payment-fee'));
    test.skip(await page.locator(`#payment-method-${FEE}`).count() === 0,
        `${FEE} isn't offered for this cart (its minimum order total, or it is switched off)`);

    await choosePayment(page, otherPayment(store));
    const without = await shownTotals(page);
    expect(without.fee, 'no fee line without cash on delivery').toBeNull();

    await choosePayment(page, FEE);
    const withFee = await shownTotals(page);
    test.skip(withFee.fee === null && cents(withFee.grandTotal) === cents(without.grandTotal),
        `the store charges no fee for ${FEE}`);
    /* A total that rises with no line saying why is the "invisible fee" this module exists to fix. */
    expect(withFee.fee, 'a fee line').not.toBeNull();
    expect(withFee.fee).toBeGreaterThan(0);
    expect(cents(withFee.grandTotal) - cents(without.grandTotal), 'the fee line is the whole difference').toBe(cents(withFee.fee));

    await choosePayment(page, otherPayment(store));
    const back = await shownTotals(page);
    expect(back.fee, 'the fee line goes away again').toBeNull();
    expect(cents(back.grandTotal)).toBe(cents(without.grandTotal));
});

test('a cash-on-delivery order is charged the total the checkout showed, fee included @orders', async ({ page, store }) => {
    skipUnlessOffered(store);
    const email = uniqueEmail('payment-fee-order');
    await toPayment(page, store, email);
    test.skip(await page.locator(`#payment-method-${FEE}`).count() === 0, `${FEE} isn't offered for this cart`);
    await choosePayment(page, FEE);
    const shown = await shownTotals(page);
    test.skip(shown.fee === null, `the store charges no fee for ${FEE}`);

    const order = await placeOrder(page);

    /* The guest order lookup, by its form's action and field names. */
    await page.goto('sales/guest/form/');
    const form = page.locator('form[action*="sales/guest/view"]');
    await form.locator('[name="oar_order_id"]').fill(order);
    await form.locator('[name="oar_billing_lastname"]').fill(store.address.lastname);
    if (await form.locator('[name="oar_type"]').count()) await form.locator('[name="oar_type"]').selectOption('email');
    await form.locator('[name="oar_email"]').fill(email);
    await Promise.all([page.waitForURL(/sales\/guest\/view/), form.locator('button[type="submit"]').first().click()]);

    const prices = (await page.locator('#maincontent .price').allInnerTexts()).map((text) => cents(amount(text)));
    expect(prices, 'the fee on the order').toContain(cents(shown.fee));
    expect(prices, 'the grand total the checkout showed').toContain(cents(shown.grandTotal));
});
