/**
 * Seeded test user credentials and usernames.
 * See backend/database/seeders/UserSeeder.php
 */
export const SEEDED_USERS: Record<
  string,
  { password: string; username: string }
> = {
  "applicationadministrator@meshresearch.net": {
    password: "adminPassword!@#",
    username: "applicationAdminUser",
  },
  "publicationadministrator@meshresearch.net": {
    password: "publicationadminPassword!@#",
    username: "publicationAdministrator",
  },
  "publicationeditor@meshresearch.net": {
    password: "editorPassword!@#",
    username: "publicationEditor",
  },
  "reviewcoordinator@meshresearch.net": {
    password: "coordinatorPassword!@#",
    username: "reviewCoordinator",
  },
  "reviewer@meshresearch.net": {
    password: "reviewerPassword!@#",
    username: "reviewer",
  },
  "regularuser@meshresearch.net": {
    password: "regularPassword!@#",
    username: "regularUser",
  },
  "settingsuser@meshresearch.net": {
    password: "settingsPassword!@#",
    username: "settingsUser",
  },
};

export type SeededEmail = keyof typeof SEEDED_USERS;
