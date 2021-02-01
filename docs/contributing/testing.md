# Code Quality & Testing

## Code Reviews

The CCR project enforces a mandatory code review for every pull request on the project, including from core developers and maintainers.  When you create a pull request, be sure to document:

- What features/fixes the PR addresses
- How to test the implementation

Reviewers should be able to follow your steps to confirm that the change works as expected.  **Reviewers should also review each changed file to:**

- point out improvements to the submission
- ask questions about unclear areas and
- catch bugs.

Reviews must be a good-faith effort on the part of both the reviewer and the submitter.  Reviewers should presume that mistakes are simple oversights, and submitters should assume that reviewers are looking out for the project.

## Client Unit Tests

The client side of the application uses [Jest](https://jestjs.io), [vue-test-utils](https://vue-test-utils.vuejs.org/) and [@quasar/testing-unit-jest](https://testing.quasar.dev/packages/unit-jest/).

To run the client-side unit tests, you can run the following **from the `/client` directory**:

```sh
lando yarn test:unit
```
Each package's documentation is an excellent source of information on testing best practices and examples.  The [Vue Testing Handbook](https://lmiller1990.github.io/vue-testing-handbook/) is also an excellent resource for unit testing a Vue application.
### Lando config for Majestic UI

The [Majestic](https://github.com/Raathigesh/majestic) GUI for jest is a useful tool for running unit tests in a browser and watching code and tests for changes.  You can add the following config to your `.lando.local.yml` file to enable a container for Majestic.

::: warning Heads Up
 If you already have configuration `.lando.local.yml`, be sure to merge the services and proxy keys, or Lando will fail to rebuild/start.
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
    - ccr_test.lndo.site:4000
```

Then, once you run `lando rebuild` the majestic interface will be available at <https://ccr_test.lndo.site>

## Server Unit Tests

On the PHP side, we use [PHPUnit](https://phpunit.de/) to run backend tests.  To run the backend unit tests:

To run the PHP unit tests execute the following command **from the `/backend` directory**:
```sh
lando artisan test
```

Be sure to read the [Laravel testing documentation](https://laravel.com/docs/8.x/testing) and [Laravel Lighthouse testing documentation](https://lighthouse-php.com/master/testing/phpunit.html).  Both provide essential information about writing unit tests for our application.

## Integration Tests (E2E)

We use [Cypress](https://www.cypress.io/) for our integration testing.  Cypress runs integration tests in a browser (Chrome, Firefox, Electron, or Edge) and allows controlling the browser and testing the responses of the application programmatically.

::: tip
These instructions focus on installing Cypress under Lando.  Cypress can bit a bit of a resource hog and, as such, might be better run directly in your host environment.
:::

Add the following configuration to your `.lando.local.yml`.  

::: warning Heads Up
NOTE: If you already have configuration in `.lando.local.yml`, be sure to merge the services and tooling keys, or Lando will fail to start/rebuild.
:::

```yaml
#FILE: .lando.local.yml
services:
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