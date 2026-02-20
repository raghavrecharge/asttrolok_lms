// @ts-check
const { test, expect } = require('@playwright/test');
const { loginAsUser } = require('./helpers/auth');
const { INST_ORDER_USER_EMAIL, INST_ORDER_USER_PASSWORD, INST_ORDER_ID, INST_COURSE_SLUG } = require('./helpers/config');

test.describe('P0: Installment Status Fixes', () => {

  test.describe.configure({ mode: 'serial' });

  test('LMS-001/004: upfront installment should show "Paid" when payment exists', async ({ page }) => {
    await loginAsUser(page, INST_ORDER_USER_EMAIL, INST_ORDER_USER_PASSWORD);

    // Navigate to installment details
    await page.goto(`/panel/financial/installments/${INST_ORDER_ID}/details`);
    await page.waitForLoadState('domcontentloaded');

    // Page should load without errors
    const content = await page.content();
    expect(content).not.toContain('SQLSTATE');
    expect(content).not.toContain('Server Error');
    expect(content).not.toContain('Whoops');

    // Check that the page has an installment table
    const table = page.locator('table');
    if (await table.count() > 0) {
      // Look for the Upfront row
      const upfrontRow = page.locator('tr:has-text("Upfront"), tr:has-text("upfront")').first();
      if (await upfrontRow.count() > 0) {
        const rowText = await upfrontRow.textContent();
        // If a payment date is shown, status should be "Paid" not "Unpaid"
        // The fix ensures reconciliation marks upfront as paid
        if (rowText.includes('202')) {
          // Has a date → should show Paid
          expect(rowText.toLowerCase()).toContain('paid');
          expect(rowText.toLowerCase()).not.toMatch(/unpaid/);
        }
      }
    }
  });

  test('LMS-002: Pay Upcoming Part link should include step amount', async ({ page }) => {
    await loginAsUser(page, INST_ORDER_USER_EMAIL, INST_ORDER_USER_PASSWORD);

    await page.goto(`/panel/financial/installments/${INST_ORDER_ID}/details`);
    await page.waitForLoadState('domcontentloaded');

    // Find "Pay Upcoming Part" or "Pay This Part" links
    const payLinks = page.locator('a:has-text("Pay Upcomming Part"), a:has-text("Pay This Part"), a:has-text("Pay Upcoming Part")');
    const count = await payLinks.count();

    if (count > 0) {
      for (let i = 0; i < count; i++) {
        const href = await payLinks.nth(i).getAttribute('href');
        // The fix ensures these links include ?amount= parameter
        expect(href).toContain('amount=');
        // The amount should be a positive number, not the full course price
        const amountMatch = href.match(/amount=([0-9.]+)/);
        expect(amountMatch).not.toBeNull();
        if (amountMatch) {
          const amount = parseFloat(amountMatch[1]);
          expect(amount).toBeGreaterThan(0);
        }
      }
    }
  });
});
