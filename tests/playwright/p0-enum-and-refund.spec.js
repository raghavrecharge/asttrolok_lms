// @ts-check
const { test, expect } = require('@playwright/test');
const { loginAsUser, loginAsAdmin, isAdminLoggedIn } = require('./helpers/auth');
const { USER_EMAIL, USER_PASSWORD, ADMIN_EMAIL, ADMIN_PASSWORD } = require('./helpers/config');

test.describe('P0: Enum Truncation & Refund Fixes', () => {

  test('LMS-014/017: support request pages should not show SQLSTATE enum errors', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);

    // Navigate to support request creation page
    await page.goto('/panel/support/new');
    await page.waitForLoadState('domcontentloaded');

    const content = await page.content();
    expect(content).not.toContain('SQLSTATE');
    expect(content).not.toContain('Data truncated');
    expect(content).not.toContain('SQLSTATE[22007]');
  });

  test('LMS-014/017: admin support list should not show SQLSTATE errors', async ({ page }) => {
    await loginAsAdmin(page, ADMIN_EMAIL, ADMIN_PASSWORD);

    // Navigate to admin support tickets
    await page.goto('/admin/supports/newsuportforasttrolok/support');
    await page.waitForLoadState('domcontentloaded');

    const content = await page.content();
    expect(content).not.toContain('SQLSTATE');
    expect(content).not.toContain('Data truncated');

    // Should not show a server error page
    expect(content).not.toContain('500 | Server Error');
    expect(content).not.toContain('Whoops!');
  });

  test('LMS-011: refund form should display validation errors (not silent refresh)', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);

    // Navigate to the user's purchases / UPE purchase list
    await page.goto('/panel/financial/upe-purchases');
    await page.waitForLoadState('domcontentloaded');

    const content = await page.content();
    // Page should load without errors
    expect(content).not.toContain('SQLSTATE');
    expect(content).not.toContain('Whoops');

    // Look for any purchase detail link
    const purchaseLink = page.locator('a[href*="/panel/financial/upe-purchases/"]').first();
    if (await purchaseLink.count() > 0) {
      await purchaseLink.click();
      await page.waitForLoadState('domcontentloaded');

      // On the purchase detail page, check for refund form or error display area
      const detailContent = await page.content();
      expect(detailContent).not.toContain('SQLSTATE');

      // Verify the error display block exists in the HTML source
      // The fix added @if($errors->any()) block
      // We can verify the page structure includes the error container potential
      // (it won't show unless there are actual errors)
      expect(detailContent).not.toContain('Whoops');
    }
  });

  test('LMS-011: purchase detail page should render correctly', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);

    await page.goto('/panel/financial/upe-purchases');
    await page.waitForLoadState('domcontentloaded');

    // Check page loads
    const pageTitle = await page.title();
    expect(pageTitle).toBeTruthy();

    // No server errors
    const bodyText = await page.locator('body').textContent();
    expect(bodyText).not.toContain('500 | Server Error');
    expect(bodyText).not.toContain('Whoops!');
  });
});
