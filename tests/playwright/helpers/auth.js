// Authentication helpers for Playwright tests
const { expect } = require('@playwright/test');

/**
 * Log in as a regular user via the login form.
 * @param {import('@playwright/test').Page} page
 * @param {string} email
 * @param {string} password
 */
async function loginAsUser(page, email, password) {
  await page.goto('/login');
  await page.waitForLoadState('domcontentloaded');
  // Fill the login form — the username field uses name="username"
  const usernameInput = page.locator('form[action="/login"] input[id="username"], form[action="/login"] input[name="username"]');
  if (await usernameInput.count() > 0) {
    await usernameInput.fill(email);
  } else {
    // Fallback: first text input in login form
    await page.locator('form[action="/login"] input[type="text"]').first().fill(email);
  }
  await page.locator('form[action="/login"] input[name="password"]').fill(password);
  await page.locator('form[action="/login"] button[type="submit"]').click();
  await page.waitForLoadState('domcontentloaded');
  await page.waitForTimeout(1000);
}

/**
 * Log in as admin via the admin login form.
 * @param {import('@playwright/test').Page} page
 * @param {string} email
 * @param {string} password
 */
async function loginAsAdmin(page, email, password) {
  // Use /login (no captcha) instead of /admin/login (has captcha)
  await page.goto('/login');
  await page.waitForLoadState('domcontentloaded');
  const usernameInput = page.locator('form[action="/login"] input[id="username"], form[action="/login"] input[name="username"]');
  if (await usernameInput.count() > 0) {
    await usernameInput.fill(email);
  } else {
    await page.locator('form[action="/login"] input[type="text"]').first().fill(email);
  }
  await page.locator('form[action="/login"] input[name="password"]').fill(password);
  await page.locator('form[action="/login"] button[type="submit"]').click();
  await page.waitForLoadState('domcontentloaded');
  await page.waitForTimeout(1000);
}

/**
 * Check if we are on the admin dashboard (login succeeded).
 * @param {import('@playwright/test').Page} page
 * @returns {Promise<boolean>}
 */
async function isAdminLoggedIn(page) {
  const url = page.url();
  // After login via /login, admin is redirected away from /login
  return !url.includes('/login');
}

/**
 * Check if we are on a user panel page (login succeeded).
 * @param {import('@playwright/test').Page} page
 * @returns {Promise<boolean>}
 */
async function isUserLoggedIn(page) {
  const url = page.url();
  // After login, user is typically redirected away from /login
  return !url.includes('/login');
}

module.exports = { loginAsUser, loginAsAdmin, isAdminLoggedIn, isUserLoggedIn };
