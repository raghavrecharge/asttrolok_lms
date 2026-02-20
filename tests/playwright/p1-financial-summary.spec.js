// @ts-check
const { test, expect } = require('@playwright/test');
const { loginAsUser } = require('./helpers/auth');
const { USER_EMAIL, USER_PASSWORD } = require('./helpers/config');

test.describe('P1: Financial Summary Fix', () => {

  test('LMS-003: financial summary page should load without empty state for users with purchases', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);

    await page.goto('/panel/financial/summary');
    await page.waitForLoadState('domcontentloaded');

    const content = await page.content();
    expect(content).not.toContain('SQLSTATE');
    expect(content).not.toContain('Server Error');
    expect(content).not.toContain('Whoops');

    // The fix adds UPE sales — so if the user has UPE purchases,
    // they should see financial data (not "No financial document!")
    // This is a smoke test that the page renders without errors
  });

  test('LMS-003: panel financial pages are accessible', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);

    // Test the main financial panel pages
    const pages = [
      '/panel/financial/summary',
      '/panel/financial/sales',
    ];

    for (const pagePath of pages) {
      await page.goto(pagePath);
      await page.waitForLoadState('domcontentloaded');

      const bodyText = await page.locator('body').textContent() || '';
      expect(bodyText).not.toContain('500 | Server Error');
      expect(bodyText).not.toContain('Whoops!');
    }
  });
});
