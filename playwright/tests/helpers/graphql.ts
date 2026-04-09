import type { APIRequestContext, Locator, Page, Response } from "@playwright/test";

interface GqlOperation {
  operationName?: string;
  variables?: Record<string, unknown>;
}

/**
 * Wait for a GraphQL response matching the given operation name.
 * Handles both single and batched GraphQL requests.
 */
export function waitForGQLOperation(
  page: Page,
  operationName: string,
): Promise<Response> {
  return page.waitForResponse((response) => {
    if (!response.url().endsWith("/graphql")) return false;
    const request = response.request();
    const postData = request.postData();
    if (!postData) return false;

    try {
      const body = JSON.parse(postData) as GqlOperation | GqlOperation[];
      if (Array.isArray(body)) {
        return body.some((op) => op.operationName === operationName);
      }
      return body.operationName === operationName;
    } catch {
      return false;
    }
  });
}

/**
 * Type a search term into a locator and wait for results to appear.
 * The search may be served from Apollo's cache (no network request),
 * so we don't wait for a specific GQL response.
 */
export async function userSearch(
  locator: Locator,
  searchTerm: string,
): Promise<void> {
  await locator.click();
  await locator.page().keyboard.type(searchTerm, { delay: 50 });
}

/**
 * Seed a test submission via GraphQL. The submission is tracked by the
 * change tracker and rolled back automatically after the test.
 *
 * @param status - GraphQL SubmissionStatus enum value, e.g. "UNDER_REVIEW", "REJECTED"
 * @returns The created submission's ID
 */
export async function seedSubmission(
  apiContext: APIRequestContext,
  options?: {
    title?: string;
    status?: string;
    publicationId?: number;
    withContent?: boolean;
  },
): Promise<string> {
  const response = await apiContext.post("/graphql", {
    data: {
      query: `mutation SeedSubmission(
        $title: String
        $status: SubmissionStatus
        $publication_id: ID
        $with_content: Boolean
      ) {
        seedSubmission(
          title: $title
          status: $status
          publication_id: $publication_id
          with_content: $with_content
        ) {
          id
        }
      }`,
      variables: {
        title: options?.title,
        status: options?.status,
        publication_id: options?.publicationId,
        with_content: options?.withContent,
      },
    },
  });

  const json = await response.json();
  if (json.errors) {
    throw new Error(
      `seedSubmission failed: ${JSON.stringify(json.errors).substring(0, 300)}`,
    );
  }

  return json.data.seedSubmission.id;
}
