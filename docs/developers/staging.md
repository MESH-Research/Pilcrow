# Staging Environments

Two environments are automatically built in order to help testers and collaborators provide feedback on development features.  Release also serves as the demo environment for prospective users.

- [https://staging.pilcrow.dev](https://staging.pilcrow.dev): Automatically built from the `master` branch.  This is the "bleeding edge" of development.
- [https://release.pilcrow.dev](https://release.pilcrow.dev): Automatially built from the most recent versioned release. Releases happen after each development sprint (usually about once a month).
- [https://mailhog.pilcrow.dev](https://mailhog.pilcrow.dev): Email notifications from both environments are sent here, NOT to a real inbox.

::: warning
:warning: Data in the test environments is **not permanent** and will be reset **every** time their code base is updated.
:::

## User Login Information

The users below are permanent on the test servers, and will not be deleted. However, data associated with these users **may be reset at any time**.

Name | Username | Login email | Login Password
:---- | :---- | :---- | :----
Application Administrator | applicationAdminUser | `applicationadministrator@meshresearch.net` | `adminPassword!@#`
Publication Administrator | publicationAdministrator | `publicationAdministrator@meshresearch.net` | `publicationadminPassword!@#`
Publication Editor | publicationEditor | `publicationEditor@meshresearch.net` | `editorPassword!@#`
Review Coordinator for Submission | reviewCoordinator | `reviewCoordinator@meshresearch.net` | `coordinatorPassword!@#`
Reviewer for Submission | reviewer | `reviewer@meshresearch.net` | `reviewerPassword!@#`
Regular User | regularUser | `regularuser@meshresearch.net` | `regularPassword!@#`

Other users can be registered to the staging environment, but please note that these users and any data associated with them **may be wiped any time the code base is updated**.


