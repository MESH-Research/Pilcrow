/* eslint-env node */
module.exports = {
  useI18n: () => ({
    te: jest.fn(() => true),
    t: jest.fn((t) => t),
  }),
}
