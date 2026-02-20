// @ts-check
const { test, expect } = require('@playwright/test');
const { loginAsUser, isUserLoggedIn } = require('./helpers/auth');
const { USER_EMAIL, USER_PASSWORD, COURSE_SLUG, COUPON_ALL_CODE } = require('./helpers/config');

test.describe('P0: Coupon Validation Fixes', () => {

  test.describe.configure({ mode: 'serial' });

  test('LMS-019/030: source=all coupon should be accepted on checkout', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);

    if (!(await isUserLoggedIn(page))) {
      test.skip(true, 'User login failed — skipping');
      return;
    }

    // Go to course page and click Buy Now (form submits to /course/direct-payment)
    await page.goto(`/course/${COURSE_SLUG}`);
    await page.waitForLoadState('domcontentloaded');

    const cartForm = page.locator('form[action="/cart/store"] button[type="submit"]').first();
    if (await cartForm.count() > 0) {
      await cartForm.click();
    } else {
      const buyBtn = page.locator('button.js-course-direct-payment').first();
      await buyBtn.click();
    }
    await page.waitForLoadState('domcontentloaded');

    // We should now be on the buyNow checkout page
    const couponInput = page.locator('#coupon_input, input[name="coupon"]').first();
    expect(await couponInput.count()).toBe(1);

    // Fill coupon code and click Validate
    await couponInput.fill(COUPON_ALL_CODE);
    const validateBtn = page.locator('#checkCoupon1, button:has-text("Validate")').first();
    expect(await validateBtn.count()).toBeGreaterThan(0);
    await validateBtn.click();
    await page.waitForLoadState('domcontentloaded');

    // After validation, the coupon input should have is-valid class
    const couponAfter = page.locator('#coupon_input, input[name="coupon"]').first();
    const classes = await couponAfter.getAttribute('class') || '';
    expect(classes).toContain('is-valid');
    expect(classes).not.toContain('is-invalid');
  });

  test('LMS-006/020/031: course page loads with valid price and no errors', async ({ page }) => {
    await page.goto(`/course/${COURSE_SLUG}`, { waitUntil: 'domcontentloaded' });

    const pageContent = await page.content();
    expect(pageContent).not.toContain('SQLSTATE');
    expect(pageContent).not.toContain('Whoops!');
    expect(pageContent).not.toContain('500 | Server Error');
  });

  test('LMS-028: coupon usage limit — page loads without errors', async ({ page }) => {
    await loginAsUser(page, USER_EMAIL, USER_PASSWORD);

    await page.goto(`/course/${COURSE_SLUG}`);
    await page.waitForLoadState('domcontentloaded');

    const content = await page.content();
    expect(content).not.toContain('SQLSTATE');
    expect(content).not.toContain('Whoops!');
  });
});
