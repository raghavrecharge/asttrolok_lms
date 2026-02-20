// Test configuration — update these values to match your environment
module.exports = {
  // Regular test user credentials
  USER_EMAIL: process.env.TEST_USER_EMAIL || '007rajatgupta@gmail.com',
  USER_PASSWORD: process.env.TEST_USER_PASSWORD || 'Test@1234',

  // Admin credentials
  ADMIN_EMAIL: process.env.TEST_ADMIN_EMAIL || 'astrolok.vedic@gmail.com',
  ADMIN_PASSWORD: process.env.TEST_ADMIN_PASSWORD || 'AstroVedic$#@!2026',

  // Test data — courses
  COURSE_SLUG: process.env.TEST_COURSE_SLUG || 'Asttroveda-2024',
  COURSE_ID: process.env.TEST_COURSE_ID || '2092',

  // A non-private course the test user has NOT purchased (for coupon test)
  CHEAP_COURSE_SLUG: process.env.TEST_CHEAP_COURSE_SLUG || 'Asttroveda-2024',
  CHEAP_COURSE_ID: process.env.TEST_CHEAP_COURSE_ID || '2092',

  // Coupon with source='all' (valid, not expired)
  COUPON_ALL_CODE: process.env.TEST_COUPON_ALL || 'Shiromani20k',
  COUPON_ALL_PERCENT: 53.45,

  // Installment test data
  INST_ORDER_USER_EMAIL: process.env.TEST_INST_USER || 'madhavp1239@gmail.com',
  INST_ORDER_USER_PASSWORD: process.env.TEST_INST_USER_PASSWORD || 'Test@1234',
  INST_ORDER_ID: process.env.TEST_INST_ORDER_ID || '32',
  INST_COURSE_SLUG: process.env.TEST_INST_COURSE_SLUG || 'astromani-2023',

  // Subscription
  SUBSCRIPTION_SLUG: process.env.TEST_SUB_SLUG || 'asttrolok-pathshala',
};
