import { defineConfig, devices } from "@playwright/test";

export default defineConfig({
  globalSetup: "./tests/global-setup.ts",
  testDir: "./tests/specs",
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 5,
  reporter: process.env.CI
    ? [["list"], ["html", { open: "never" }], ["blob"]]
    : [["html", { open: "on-failure" }]],
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL ?? "https://pilcrow.lndo.site",
    testIdAttribute: "data-cy",
    trace: "retain-on-failure",
    screenshot: "only-on-failure",
    ignoreHTTPSErrors: true,
    actionTimeout: 7_500,
    navigationTimeout: 10_000,
  },
  projects: [
    {
      name: "chromium",
      use: {
        ...devices["Desktop Chrome"],
        viewport: { width: 1280, height: 720 },
        contextOptions: {
          reducedMotion: "reduce",
        },
      },
    },
    {
      name: "firefox",
      use: {
        ...devices["Desktop Firefox"],
        viewport: { width: 1280, height: 720 },
        contextOptions: {
          reducedMotion: "reduce",
        },
      },
    },
    {
      name: "webkit",
      timeout: 60_000,
      use: {
        ...devices["Desktop Safari"],
        viewport: { width: 1280, height: 720 },
        contextOptions: {
          reducedMotion: "reduce",
        },
      },
    },
  ],
  timeout: 30_000,
});
