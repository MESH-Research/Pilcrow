import { request } from "@playwright/test";

/**
 * Runs once before all workers start.
 * Fully resets the database with migrate:fresh --seed and
 * cleans up any leftover shadow tables from previous runs.
 */
async function globalSetup() {
  const baseURL =
    process.env.PLAYWRIGHT_BASE_URL ?? "https://pilcrow.lndo.site";

  const apiContext = await request.newContext({
    baseURL,
    ignoreHTTPSErrors: true,
  });

  try {
    const response = await apiContext.post("/graphql", {
      data: { query: "mutation { resetDatabase }" },
    });
    const json = await response.json();
    if (json.errors) {
      throw new Error(
        `Global setup: resetDatabase failed: ${JSON.stringify(json.errors)}`,
      );
    }
  } finally {
    await apiContext.dispose();
  }
}

export default globalSetup;
