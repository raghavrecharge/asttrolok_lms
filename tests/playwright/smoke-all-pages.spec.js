// @ts-check
const { test, expect } = require('@playwright/test');
const { loginAsUser, loginAsAdmin, isAdminLoggedIn } = require('./helpers/auth');
const { USER_EMAIL, USER_PASSWORD, ADMIN_EMAIL, ADMIN_PASSWORD, COURSE_SLUG, SUBSCRIPTION_SLUG } = require('./helpers/config');

test.describe('Smoke Tests: All affected pages load without errors', () => {

  test('Public: course page loads', async ({ page }) => {
    await page.goto(`/course/${COURSE_SLUG}`);
    await page.waitForLoadState('domcontentloaded');
    const body = await page.locator('body').textContent() || '';
    expect(body).not.toContain('500 | Server Error');
    expect(body).not.toContain('Whoops!');
    expect(body).not.toContain('SQLSTATE');
  });

  test('Public: subscription page loads', async ({ page }) => {
    await page.goto(`/subscriptions/${SUBSCRIPTION_SLUG}`);
    await page.waitForLoadState('domcontentloaded');
    const body = await page.locator('body').textContent() || '';
    expect(body).not.toContain('500 | Server Error');
    expect(body).not.toContain('Whoops!');
  });

  test('Public: login page loads', async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('domcontentloaded');
    const body = await page.locator('body').textContent() || '';
    expect(body).not.toContain('500 | Server Error');
    expect(body).toContain('Login');
  });

  test('User: panel dashboard loads after login', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);
    await page.goto('/panel');
    await page.waitForLoadState('domcontentloaded');
    const body = await page.locator('body').textContent() || '';
    expect(body).not.toContain('500 | Server Error');
    expect(body).not.toContain('SQLSTATE');
  });

  test('User: support creation page loads', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);
    await page.goto('/panel/support/new');
    await page.waitForLoadState('domcontentloaded');
    const body = await page.locator('body').textContent() || '';
    expect(body).not.toContain('500 | Server Error');
    expect(body).not.toContain('SQLSTATE');
  });

  test('User: financial summary page loads', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);
    await page.goto('/panel/financial/summary');
    await page.waitForLoadState('domcontentloaded');
    const body = await page.locator('body').textContent() || '';
    expect(body).not.toContain('500 | Server Error');
    expect(body).not.toContain('SQLSTATE');
  });

  test('User: UPE purchases page loads', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);
    await page.goto('/panel/financial/upe-purchases');
    await page.waitForLoadState('domcontentloaded');
    const body = await page.locator('body').textContent() || '';
    expect(body).not.toContain('500 | Server Error');
    expect(body).not.toContain('SQLSTATE');
  });

  test('User: my-requests page loads', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);
    await page.goto('/panel/financial/upe-requests');
    await page.waitForLoadState('domcontentloaded');
    const body = await page.locator('body').textContent() || '';
    expect(body).not.toContain('500 | Server Error');
    expect(body).not.toContain('SQLSTATE');
  });

  test('Admin: dashboard loads after login', async ({ page }) => {
    await loginAsAdmin(page, ADMIN_EMAIL, ADMIN_PASSWORD);
    await page.goto('/admin');
    await page.waitForLoadState('domcontentloaded');
    const body = await page.locator('body').textContent() || '';
    expect(body).not.toContain('500 | Server Error');
    expect(body).not.toContain('SQLSTATE');
  });

  test('Admin: support tickets page loads', async ({ page }) => {
    await loginAsAdmin(page, ADMIN_EMAIL, ADMIN_PASSWORD);
    await page.goto('/admin/supports/newsuportforasttrolok/support');
    await page.waitForLoadState('domcontentloaded');
    const body = await page.locator('body').textContent() || '';
    expect(body).not.toContain('500 | Server Error');
    expect(body).not.toContain('SQLSTATE');
  });

  test('Admin: discount codes page loads', async ({ page }) => {
    await loginAsAdmin(page, ADMIN_EMAIL, ADMIN_PASSWORD);
    await page.goto('/admin/financial/discounts');
    await page.waitForLoadState('domcontentloaded');
    const body = await page.locator('body').textContent() || '';
    expect(body).not.toContain('500 | Server Error');
    expect(body).not.toContain('SQLSTATE');
  });

  test('Static asset: unified-payment.js is accessible', async ({ page }) => {
    const response = await page.goto('/assets/design_1/js/unified-payment.js');
    expect(response).not.toBeNull();
    if (response) {
      expect(response.status()).toBe(200);
    }
  });
});
