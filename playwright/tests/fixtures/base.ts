import { randomUUID } from "crypto";
import {
  test as base,
  expect,
  request as pwRequest,
  type APIRequestContext,
} from "@playwright/test";
import { seedSubmission as seedSubmissionHelper } from "../helpers/graphql";
import { SEEDED_USERS, type SeededEmail } from "../helpers/users";

type SeedSubmissionOptions = {
  title?: string;
  status?: string;
  publicationId?: number;
  withContent?: boolean;
};

type LoginAsOptions = {
  /**
   * Skip the shared session cache and run a fresh login. Use this for
   * tests that will log out, change passwords, or otherwise destroy the
   * session — caching across parallel workers would let one worker kill
   * another worker's session mid-test.
   */
  noCache?: boolean;
};

type PilcrowFixtures = {
  loginAs: (
    email: SeededEmail,
    goto: string,
    options?: LoginAsOptions,
  ) => Promise<void>;
  resetDatabase: () => Promise<void>;
  seedSubmission: (options?: SeedSubmissionOptions) => Promise<string>;
};

/**
 * Single token per worker process. All tests in this worker run sequentially
 * (fullyParallel: false), so there's no interleaving of changes/rollbacks.
 */
const WORKER_TOKEN = `pw-${randomUUID()}`;

/**
 * Cache of post-login cookies (and resolved username) keyed by email.
 * Laravel uses file-based sessions, so cookies are valid across all
 * workers and shadow tables. The first loginAs call for a user fills
 * the cache; subsequent calls skip CSRF + login and just inject cookies.
 */
type CachedSession = {
  cookies: Awaited<ReturnType<APIRequestContext["storageState"]>>["cookies"];
  username: string;
};
const sessionCache = new Map<string, CachedSession>();

/** Call a test infrastructure mutation (no X-Test-Token prefix needed). */
async function callTestMutation(
  baseURL: string,
  query: string,
  variables: Record<string, unknown>,
): Promise<void> {
  const apiContext = await pwRequest.newContext({
    baseURL,
    ignoreHTTPSErrors: true,
  });

  try {
    const response = await apiContext.post("/graphql", {
      data: { query, variables },
    });
    const json = await response.json();
    if (json.errors) {
      throw new Error(
        `GraphQL mutation failed: ${JSON.stringify(json.errors).substring(0, 300)}`,
      );
    }
  } finally {
    await apiContext.dispose();
  }
}

/** Create shadow tables for this worker's token. */
async function setupToken(baseURL: string): Promise<void> {
  await callTestMutation(
    baseURL,
    `mutation ($token: String!) { setupTestToken(token: $token) }`,
    { token: WORKER_TOKEN },
  );
}


export const test = base.extend<PilcrowFixtures>({
  loginAs: async ({ page, baseURL }, use) => {
    const fn = async (
      email: SeededEmail,
      goto: string,
      options?: LoginAsOptions,
    ) => {
      await base.step(`loginAs(${email}, ${goto})`, async () => {
        const user = SEEDED_USERS[email.toLowerCase()];
        expect(user, `seeded user exists for ${email}`).toBeTruthy();
        const { password } = user;

        const origin = baseURL!;
        const cacheKey = email.toLowerCase();

        const performLogin = async (): Promise<CachedSession> => {
          const apiCtx = await pwRequest.newContext({
            baseURL: origin,
            ignoreHTTPSErrors: true,
            extraHTTPHeaders: {
              "X-Test-Token": WORKER_TOKEN,
              Origin: origin,
              Referer: `${origin}/`,
            },
          });
          try {
            await apiCtx.get("/sanctum/csrf-cookie");
            const xsrfCookie = (await apiCtx.storageState()).cookies.find(
              (c) => c.name === "XSRF-TOKEN",
            );
            expect(xsrfCookie, `XSRF cookie exists`).toBeTruthy();
            const xsrfToken = decodeURIComponent(xsrfCookie!.value);

            const loginResp = await apiCtx.post("/graphql", {
              headers: { "X-XSRF-TOKEN": xsrfToken },
              data: {
                query: `mutation Login($email: String!, $password: String!) {
                  login(email: $email, password: $password) { id, email, username }
                }`,
                variables: { email, password },
              },
            });
            const loginJson = await loginResp.json();
            expect(
              loginJson.errors,
              `login succeeded without errors`,
            ).toBeUndefined();
            expect(
              loginJson.data?.login?.email,
              `logged in as ${email}`,
            ).toBe(email);

            return {
              cookies: (await apiCtx.storageState()).cookies,
              username: loginJson.data.login.username as string,
            };
          } finally {
            await apiCtx.dispose();
          }
        };

        const tryLoginAndNavigate = async (
          session: CachedSession,
        ): Promise<boolean> => {
          await page.goto("about:blank");
          await page.context().clearCookies();
          await page.context().addCookies(session.cookies);
          await page.goto(`${origin}${goto}`);
          // Use a short timeout — if the cached session is dead, the
          // dropdown_username will never show, and we want to fall back fast.
          try {
            await expect(
              page.getByTestId("dropdown_username"),
            ).toContainText(session.username, {
              ignoreCase: true,
              timeout: 2_000,
            });
            return true;
          } catch {
            return false;
          }
        };

        // eslint-disable-next-line playwright/no-conditional-in-test -- session cache lookup, not test logic
        let cached = options?.noCache ? undefined : sessionCache.get(cacheKey);
        // eslint-disable-next-line playwright/no-conditional-in-test -- session cache hit path
        if (cached && (await tryLoginAndNavigate(cached))) {
          return;
        }

        // Cache miss, stale, or noCache requested — fresh login.
        cached = await performLogin();
        // eslint-disable-next-line playwright/no-conditional-in-test -- cache write/clear based on noCache option
        if (!options?.noCache) {
          sessionCache.set(cacheKey, cached);
        } else {
          // Caller is going to invalidate this session; remove any
          // existing cached entry so other workers don't try to use it.
          sessionCache.delete(cacheKey);
        }
        await page.goto("about:blank");
        await page.context().clearCookies();
        await page.context().addCookies(cached.cookies);
        await page.goto(`${origin}${goto}`);
        await expect(
          page.getByTestId("dropdown_username"),
          `header shows ${cached.username}`,
        ).toContainText(cached.username, { ignoreCase: true });
      }); // end test.step
    };
    await use(fn);
  },

  /**
   * Create shadow tables before the test. Each test gets a fresh copy
   * of the seeded database.
   */
  resetDatabase: async ({ baseURL }, use) => {
    await setupToken(baseURL!);

    await use(async () => {});

    // No teardown needed — setupToken drops stale tables before creating new ones.
    // This avoids race conditions where in-flight browser requests hit dropped tables.
  },

  /**
   * Create an isolated test submission. Tracked by the change tracker
   * and rolled back automatically after the test via resetDatabase teardown.
   */
  seedSubmission: async ({ baseURL }, use) => {
    const fn = async (options?: SeedSubmissionOptions): Promise<string> => {
      const apiContext = await pwRequest.newContext({
        baseURL,
        ignoreHTTPSErrors: true,
        extraHTTPHeaders: { "X-Test-Token": WORKER_TOKEN },
      });
      try {
        return await seedSubmissionHelper(apiContext, options);
      } finally {
        await apiContext.dispose();
      }
    };
    await use(fn);
  },

  /**
   * Intercept ALL requests to the backend to include the X-Test-Token header.
   * Also disables CSS animations/transitions.
   */
  page: async ({ page, baseURL }, use) => {
    // Disable CSS animations and transitions
    await page.addInitScript(() => {
      const css = [
        "*, *::before, *::after {",
        "  animation-duration: 1ms !important;",
        "  animation-delay: 0s !important;",
        "  transition-duration: 1ms !important;",
        "  transition-delay: 0s !important;",
        "}",
      ].join("\n");

      const inject = () => {
        if (!document.head) return;
        if (!document.getElementById("pw-no-animations")) {
          const style = document.createElement("style");
          style.id = "pw-no-animations";
          style.textContent = css;
          document.head.appendChild(style);
        }
      };

      inject();
      document.addEventListener("DOMContentLoaded", inject);

      const startObserving = () => {
        if (document.documentElement) {
          new MutationObserver(inject).observe(document.documentElement, {
            childList: true,
            subtree: true,
          });
        }
      };
      if (document.documentElement) {
        startObserving();
      } else {
        document.addEventListener("DOMContentLoaded", startObserving);
      }
    });

    const backendOrigin = baseURL ?? "https://pilcrow.lndo.site";
    await page.route(`${backendOrigin}/**`, async (route) => {
      const headers = {
        ...route.request().headers(),
        "x-test-token": WORKER_TOKEN,
      };
      await route.continue({ headers });
    });
    await use(page);
  },
});

export { expect };
