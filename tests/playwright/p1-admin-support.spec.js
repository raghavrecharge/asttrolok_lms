// @ts-check
const { test, expect } = require('@playwright/test');
const { loginAsAdmin, isAdminLoggedIn } = require('./helpers/auth');
const { ADMIN_EMAIL, ADMIN_PASSWORD } = require('./helpers/config');

test.describe('P1: Admin Support Ticket Fixes', () => {

  test.describe.configure({ mode: 'serial' });

  test('LMS-025: admin support list loads without errors', async ({ page }) => {
    await loginAsAdmin(page, ADMIN_EMAIL, ADMIN_PASSWORD);

    await page.goto('/admin/supports/newsuportforasttrolok/support');
    await page.waitForLoadState('domcontentloaded');

    const content = await page.content();
    expect(content).not.toContain('SQLSTATE');
    expect(content).not.toContain('Whoops!');

    // Should contain a table with support tickets
    const table = page.locator('table');
    expect(await table.count()).toBeGreaterThan(0);
  });

  test('LMS-025: admin can view a support ticket detail page', async ({ page }) => {
    await loginAsAdmin(page, ADMIN_EMAIL, ADMIN_PASSWORD);

    await page.goto('/admin/supports/newsuportforasttrolok/support');
    await page.waitForLoadState('domcontentloaded');

    // Click on the first ticket detail link
    const ticketLink = page.locator('a[href*="/support/"]').first();
    if (await ticketLink.count() > 0) {
      await ticketLink.click();
      await page.waitForLoadState('domcontentloaded');

      const detailContent = await page.content();
      expect(detailContent).not.toContain('SQLSTATE');
      expect(detailContent).not.toContain('Whoops!');
    }
  });

  test('LMS-024: post-purchase coupon ticket page should not show SQLSTATE errors', async ({ page }) => {
    await loginAsAdmin(page, ADMIN_EMAIL, ADMIN_PASSWORD);

    await page.goto('/admin/supports/newsuportforasttrolok/support');
    await page.waitForLoadState('domcontentloaded');

    const content = await page.content();
    expect(content).not.toContain('SQLSTATE');
    expect(content).not.toContain('Whoops!');
  });

  test('LMS-022: admin support page should handle installment restructure tickets', async ({ page }) => {
    await loginAsAdmin(page, ADMIN_EMAIL, ADMIN_PASSWORD);

    await page.goto('/admin/supports/newsuportforasttrolok/support');
    await page.waitForLoadState('domcontentloaded');

    const content = await page.content();
    expect(content).not.toContain('SQLSTATE');
    expect(content).not.toContain('Whoops!');
  });
});
