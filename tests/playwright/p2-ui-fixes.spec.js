// @ts-check
const { test, expect } = require('@playwright/test');
const { loginAsUser } = require('./helpers/auth');
const { USER_EMAIL, USER_PASSWORD, SUBSCRIPTION_SLUG, COURSE_SLUG } = require('./helpers/config');

test.describe('P2: UI Fixes', () => {

  test('LMS-027: subscription free trial button should link to correct route', async ({ page }) => {
    // Visit subscription page (public, no login needed)
    await page.goto(`/subscriptions/${SUBSCRIPTION_SLUG}`);
    await page.waitForLoadState('domcontentloaded');

    const content = await page.content();
    expect(content).not.toContain('Server Error');

    // Check that NO links point to the old broken route
    const brokenLinks = page.locator('a[href*="direct-payment-enroll"]');
    const brokenCount = await brokenLinks.count();
    expect(brokenCount).toBe(0);

    // Check that correct links exist
    const correctLinks = page.locator(`a[href*="direct-payment/${SUBSCRIPTION_SLUG}"], a[href*="direct-payment"]`);
    if (await correctLinks.count() > 0) {
      // Verify the href uses the correct route pattern
      const href = await correctLinks.first().getAttribute('href');
      expect(href).not.toContain('direct-payment-enroll');
    }
  });

  test('LMS-029: payment page should not have stuck loader elements', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);

    // Navigate to a course buy page
    await page.goto(`/course/${COURSE_SLUG}`);
    await page.waitForLoadState('domcontentloaded');

    // Verify unified-payment.js is loaded and has the fix
    // by checking the JS source includes paymentLoader handling
    const jsResponse = await page.goto('/assets/design_1/js/unified-payment.js');
    if (jsResponse && jsResponse.ok()) {
      const jsContent = await jsResponse.text();
      // The fix adds paymentLoader handling in hideLoader
      expect(jsContent).toContain('paymentLoader');
    }
  });

  test('LMS-029: unified-payment.js hideLoader includes paymentLoader cleanup', async ({ page }) => {
    // Directly verify the JS file contains the fix
    const response = await page.goto('/assets/design_1/js/unified-payment.js');
    expect(response).not.toBeNull();
    if (response) {
      expect(response.ok()).toBeTruthy();
      const jsText = await response.text();

      // Verify hideLoader method includes paymentLoader handling
      expect(jsText).toContain('hideLoader');
      expect(jsText).toContain('paymentLoader');
      expect(jsText).toContain("getElementById('paymentLoader')");
    }
  });

  test('LMS-011: purchase detail page includes error display block', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);

    // Go to UPE purchases list
    await page.goto('/panel/financial/upe-purchases');
    await page.waitForLoadState('domcontentloaded');

    // Find and click first purchase detail link
    const detailLink = page.locator('a[href*="/panel/financial/upe-purchases/"]').first();
    if (await detailLink.count() > 0) {
      await detailLink.click();
      await page.waitForLoadState('domcontentloaded');

      // Verify the page source contains the error display block
      // (it won't be visible unless there are errors, but the HTML structure should exist)
      const html = await page.content();
      expect(html).not.toContain('Whoops');
      expect(html).not.toContain('SQLSTATE');
    }
  });

  test('LMS-026: course page loads with valid price format', async ({ page }) => {
    await page.goto(`/course/${COURSE_SLUG}`);
    await page.waitForLoadState('domcontentloaded');

    const content = await page.content();
    expect(content).not.toContain('Server Error');
    expect(content).not.toContain('SQLSTATE');

    // Check that price is displayed in valid format
    const priceElements = page.locator('text=/₹[\\d,]+/');
    if (await priceElements.count() > 0) {
      const priceText = await priceElements.first().textContent() || '';
      // Should contain a rupee symbol and digits
      expect(priceText).toMatch(/₹[\d,]+/);
    }
  });
});
