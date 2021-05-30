# Code Quality & Testing

## Code Reviews

The CCR project enforces a mandatory code review for every pull request on the project, including those from core developers and maintainers.  When you create a pull request, be sure to document in the pull request description:

- What features/fixes the PR addresses
- How to test the implementation

Reviewers should be able to follow your steps to confirm that the change works as expected.  **Reviewers should also review each changed file to:**

- point out improvements to the submission
- ask questions about unclear areas and
- catch bugs.

Reviews must be a good-faith effort on the part of both the reviewer and the submitter.  Reviewers should presume that mistakes are simple oversights, and submitters should assume that reviewers are looking out for the project.

## Testing

The full test suite of CCR consists of the following:

* [Client Unit Testing](#client-unit-tests)
* [Client Integration Testing](#integration-tests-e2e)
* [Server Unit Testing](#server-unit-tests)

In a command line, run `yarn test` from the root project directory (`/`). This will initiate all tests to be run synchronously.

### Client Unit Tests

The client side of the application uses [Jest](https://jestjs.io), [vue-test-utils](https://vue-test-utils.vuejs.org/) and [@quasar/testing-unit-jest](https://testing.quasar.dev/packages/unit-jest/).

To run the client-side unit tests, you can run the following **from the `/client` directory**:

```sh
lando yarn test:unit
```
Each package's documentation is an excellent source of information on testing best practices and examples.  The [Vue Testing Handbook](https://lmiller1990.github.io/vue-testing-handbook/) is also an excellent resource for unit testing a Vue application.

### Lando config for Majestic UI

The [Majestic](https://github.com/Raathigesh/majestic) GUI for Jest is a useful tool for running unit tests in a browser and watching code and tests for changes.  You can add the following config to your `.lando.local.yml` file to enable a container for Majestic.

::: warning Heads Up
If you already have configuration `.lando.local.yml`, be sure to merge the services and proxy keys, or Lando will fail to rebuild/start.
:::

::: tip
The lando extras tooling command can set up Majestic for you.  Run: `lando extras enable majestic`
:::
```yaml
#FILE: .lando.local.yml
services:
  test:
    type: node
    build:
      - cd client && yarn
    port: 4000
    command: "cd client && yarn test:unit:ui"
    scanner: false
proxy:
  test:
    - majestic.ccr.lndo.site:4000
```

Then, once you run `lando rebuild` the majestic interface will be available at <https://majestic.ccr.lndo.site>

### Server Unit Tests

On the PHP side, we use [PHPUnit](https://phpunit.de/) to run backend tests.  To run the backend unit tests:

To run the PHP unit tests execute the following command **from the `/backend` directory**:
```sh
lando artisan test
```

Be sure to read the [Laravel testing documentation](https://laravel.com/docs/8.x/testing) and [Laravel Lighthouse testing documentation](https://lighthouse-php.com/master/testing/phpunit.html).  Both provide essential information about writing unit tests for our application.

### Integration Tests (E2E)

We use [Cypress](https://www.cypress.io/) for our integration testing.  Cypress runs integration tests in a browser (Chrome, Firefox, Electron, or Edge) and allows controlling the browser and testing the responses of the application programmatically.

::: tip
These instructions focus on installing Cypress under Lando.  Cypress can bit a bit of a resource hog and, as such, might be better run directly in your host environment.

Also you can use Lando Extras to set up this configuration for you.  Run: `lando extras enable cypress`
:::

Add the following configuration to your `.lando.local.yml`.

::: warning Heads Up
NOTE: If you already have configuration in `.lando.local.yml`, be sure to merge the services and tooling keys, or Lando will fail to start/rebuild.
:::

```yaml
#FILE: .lando.local.yml
services:
  cypress:
    type: node
    overrides:
      image: "cypress/included:6.3.0"
tooling:
  cypress:
    service: cypress
    dir: /app/client
    cmd: cypress
```

Rebuild your containers:
```sh
lando rebuild -y
```

After rebuilding your containers, you can run the headless tests with the command:

```sh
lando cypress run
```

::: tip Interactive Test Runner
It is possible to run the interactive test runner, although it does require some configuration.  The following links are useful resources for this:
- [Cypress Docker Blog Post](https://www.cypress.io/blog/2019/05/02/run-cypress-with-a-single-docker-command/)
- [Running GUI Applications in WSL2](https://dev.to/nickymeuleman/using-graphical-user-interfaces-like-cypress-in-wsl2-249j
)
:::

The [Cypress Docs](https://docs.cypress.io/guides/getting-started/writing-your-first-test.html#Add-a-test-file) are a thorough resource for writing tests using Cypress.

## Code Style & Linting

We use code style checking to help ensure consistency across our codebase.  Style checking is implemented using Github Actions on all pull requests, and each language's style/linting checker can also be run from the local environment.

### PHP

From the root of the project run:
```sh
lando composer lint #Check and report linting errors

lando composer lint-fix #Fix fixable linting errors
```

#### PHPCS in VScode

The [PHP Sniffer](https://marketplace.visualstudio.com/items?itemName=wongjn.php-sniffer) extension should pick up our phpcs config automatically.  You will need to have PHP installed locally, however.

### Markdown

From the root of the project run:
```sh
lando yarn lint:md
```
#### Markdownlint in VSCode

[Markdownlint](https://marketplace.visualstudio.com/items?itemName=DavidAnson.vscode-markdownlint) should pick up our configuration automatically.

### JavaScript

From the `/client` directory run:
```sh
lando yarn lint #Check and report linting errors

lando yarn lint --fix #Fix fixable linting errors
```

#### Eslint in VSCode

Instructions for integrating eslint into VSCode can be found [in the eslint-plugin-vue documentation](https://vuejs.github.io/eslint-plugin-vue/user-guide/#editor-integrations).  You will need to make an additional configuration change to what is outlined there, however:

- Install [dbaeumer.vscode-eslint](https://marketplace.visualstudio.com/items?itemName=dbaeumer.vscode-eslint)
- Edit your VSCode settings.json:

```json
{
  //...
  "eslint.format.enable": true, // Adds eslint to the formatter options in the right-click menu (Optional)
  "eslint.packageManager": "yarn", // CCR uses yarn
  "eslint.workingDirectories": ["./client"], // Point the eslint plugin at the client directory
  "eslint.validate": [
    "javascript",
    "javascriptreact",
    "vue"
  ],
  "vetur.validation.template": false // If you have vetur installed as well, disable the default validation functionality.
  //...
}
```
