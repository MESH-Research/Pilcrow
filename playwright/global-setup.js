import { resetDb, setupResetDb } from "./helpers";

async function globalSetup(config) {
  const baseURL = config.projects[0].use.baseURL
  await setupResetDb(baseURL);
  await resetDb(baseURL);

}

export default globalSetup;