# Staging Server Guide

Welcome to the CCR Staging Server Documentation!

- [https://staging.ccrproject.dev](https://staging.ccrproject.dev): Automatically built from the `master` branch
- [https://mailhog.ccrproject.dev](https://mailhog.ccrproject.dev): Email notifications from the staging server are sent here, NOT to a real inbox.

::: warning
:warning: Data in the staging server is **not permanent** and will be reset **every** time the code base is updated.
:::

## Staging Server User Login Information

These users are permanent on the staging server, and will not be deleted. However, data associated with these users **may be reset at any time**.

Name | Username | Login email | Login Password
:---- | :---- | :---- | :----
Application Administrator | applicationAdminUser | applicationadministrator@ccrproject.dev | adminPassword!@#
Publication Administrator | publicationAdministrator | publicationAdministrator@ccrproject.dev | publicationadminPassword!@#
Publication Editor | publicationEditor | publicationEditor@ccrproject.dev | editorPassword!@#
Review Coordinator for Submission | reviewCoordinator | reviewCoordinator@ccrproject.dev | coordinatorPassword!@#
Reviewer for Submission | reviewer | reviewer@ccrproject.dev | reviewerPassword!@#
Regular User | regularUser | regularuser@ccrproject.dev | regularPassword!@#

Other users can be registered, but please note that these users and any data associated with them may be wiped any time the code base is updated.
