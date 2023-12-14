# Code Quality & Testing

## Testing

The full test suite of Pilcrow consists of the following:

* [Backend Unit Testing](#backend-unit-tests)
* [Client Unit Testing](#client-unit-tests)
* [Integration Testing](#integration-tests-e2e)

From the root project directory (`/`), run the following in a command line:
```sh
yarn test
```

This will initiate a synchronous run of all tests.

### Backend Unit Tests

On the PHP side, we use [PHPUnit](https://phpunit.de/) to run backend tests.

To run the PHP unit tests execute the following command **from the `/backend` directory**:
```sh
lando artisan test
```

Be sure to read the [Laravel testing documentation](https://laravel.com/docs/8.x/testing) and [Laravel Lighthouse testing documentation](https://lighthouse-php.com/master/testing/phpunit.html).  Both provide essential information about writing unit tests for our application.

### Client Unit Tests

The client side of the application uses [Vitest](https://vitest.dev), [vue-test-utils](https://vue-test-utils.vuejs.org/) and [@quasar/testing-unit-vitest](https://testing.quasar.dev/packages/unit-vitest/).

To run the client-side unit tests, you can run the following **from the `/client` directory**:

```sh
lando yarn test:unit
```
Each package's documentation is an excellent source of information on testing best practices and examples.  The [Vue Testing Handbook](https://lmiller1990.github.io/vue-testing-handbook/) is also an excellent resource for unit testing a Vue application.

Vitest also has a web interface for browsing and running tests.  To launch it, run:
```sh
lando yarn test:unit:ui
```


### Integration Tests (E2E)

We use [Cypress](https://www.cypress.io/) for our integration or end-to-end testing.  Cypress runs integration tests in a browser (Chrome, Firefox, Electron, or Edge) and allows controlling the browser and testing the responses of the application programmatically.

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
    type: node:14
    overrides:
      image: "cypress/base:14"
tooling:
  cypress:
    service: cypress
    dir: /app/client
    cmd: cypress
```

Rebuild your containers and install cypress binaries:
```sh
lando rebuild -y
lando cypress install
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
  "eslint.packageManager": "yarn", // Pilcrow uses yarn
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
