# Introduction

::: tip
CCR is in the beginning phases of its development process. As such, much of this documentation is evolving quickly. Please check back frequently to see what has changed!
:::

## Project Organization

Most of the work of the project is handled on our [GitHub repo](https://github.com/MESH-Research/CCR). The core team operates using an agile model, and we organize our sprints using [ZenHub](https://www.zenhub.com/). It's highly advisable to install the [ZenHub browser extension](https://www.zenhub.com/sign-up#). Once installed, you'll see a new tab in GitHub for our ZenHub board.

![zenhub screenshot](./images/zenhub_screenshot.jpg)


### Team Members
#### Core Developers

<TeamList filter="core"/>


## Code of Conduct

All contributors to the project must agree to adhere to our project's [Code of Conduct](https://github.com/MESH-Research/CCR/blob/master/CODE_OF_CONDUCT.md)

::: tip In summary
In the interest of fostering an open and welcoming environment, we as contributors and maintainers pledge to making participation in our project and our community a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, sex characteristics, gender identity and expression, level of experience, education, socio-economic status, nationality, personal appearance, race, religion, or sexual identity and orientation.
:::

The full text of our Code of Conduct is available in our repository: <https://github.com/MESH-Research/CCR/blob/master/CODE_OF_CONDUCT.md>

## Development Environments

We use a Docker-based container management system called [Lando](https://lando.dev/) to manage reusable development environments to ensure everyone is working with the same dependencies. To get started spinning up a development environment, you'll roughly need to follow these steps:
::: warning Minimum Version
CCR requires Lando version &ge; 3.0.25
:::

1. [Download and Install Lando and its dependencies.](https://docs.lando.dev/basics/installation.html)
2. Checkout the CCR repository on your local machine.
3. From the project root run, `lando start`.

Lando will then download the appropriate containers and get everything spun up. Once everything is installed and running, you should see:

![lando start container screenshot](./images/lando_screenshot.jpg)

You can then open a browser to <https://ccr.lndo.site/> and view the project running on your local machine.

### Database Migration and Seeding

By default, the database of the application will be empty. To log in as a sample user, the database tables must be [migrated](https://laravel.com/docs/master/migrations) and [seeded](https://laravel.com/docs/master/seeding#main-content).

#### Migrate and Seed

`lando artisan migrate:fresh --seed`

#### Migrate Only

`lando artisan migrate:fresh`

#### Seed Only

`lando artisan db:seed`


Once seeding is complete, you can log in at <https://ccr.lndo.site/login> as any one of the sample users defined in `backend/database/seeders/UserSeeder.php` in the repository.

### Lando tooling commands

Lando has built-in tooling commands that allow a developer to run commands inside a container from their terminal.

- `lando artisan`: Run Laravel's artisan command in the appserver container.
- `lando composer`: Run composer in the appserver container.
- `lando yarn`: Run yarn in the client container.
- `lando mysql`: Start a MySQL client session (TIP: use `lando mysql laravel` to start with the CCR database selected).
- `lando quasar`: Run the quasar cli inside the client container.
- `lando extras`: Manage and install tools into `.lando.local.yml`.
- `lando pandoc`: Run pandoc inside the appserver container

There are other useful tooling commands available. To view a list of all commands available, run `lando` at your command prompt with no arguments.

::: tip
Lando tooling commands will run inside the container in your current working directory. Therefore, you should be careful to run tools (composer, yarn, etc.) inside the appropriate directory, or you may end up inadvertently creating a new composer.json or package.json in a different part of the project.
The only exception to this is the `lando quasar` command, which always runs in the `/client` directory.
:::

### Additional Local Configuration

You can create local containers or additional tooling commands by creating a `.lando.local.yml` file.

Some containers are useful for specific development tasks but would unnecessarily bloat the development stack for day-to-day usage.  Our `lando extras` tooling command eases the friction associated with managing your `.lando.local.yml` file.  Run `lando extras` for a list of templates and instructions to enable and disable extras.

#### Testing Mail

[MailHog](https://github.com/mailhog/MailHog) can capture mail to aid debugging of email messages. MailHog is integrated as an extra in our Lando config.  To enable MailHog:

```sh
lando extras enable mailhog

lando rebuild
```

The MailHog interface will be available at <http://mailhog.ccr.lndo.site/> once the rebuild has finished.  CCR's development environment will automatically route outgoing mail to MailHog's SMTP interface.


### Lando Tips and Tricks

- [Trusting the Lando CA Certificate](https://docs.lando.dev/config/security.html#trusting-the-ca)
- [ZSH Plugin](https://github.com/JoshuaBedford/lando-zsh)

## Preview Environments

It can be helpful to deploy pull requests for team members or stakeholders to preview and provide feedback.  `CCR-Droid` is our custom Github App dedicated to creating these environments (and other small tasks from time to time).

### Creating a Preview Deployment

To create a preview environment, add the `pr-preview` label to a pull request in the CCR GitHub repository that meets the following criteria:

- Must be open (not closed, not merged)
- Must be targeted to either `master` or `development` branches
- Must be a branch on the CCR repo (forks aren't allowed).
- CI/CD checks must be passing (`CCR-Droid` will wait until checks fail or pass)

If the pull request does not meet the above rules, `CCR-Droid` will comment on the pull request and remove the `pr-preview` label.  If the pull request meets the criteria, `CCR-Droid` will request the deployment for you.

![pending deployment screenshot](./images/preview_in_progress.jpg)

After `CCR-Droid` creates the deployment, a link will appear above the checks section.

![deployment ready screenshot](./images/preview_ready.jpg)

#### Mail

Mailhog captures mail from preview deployments to prevent abuse.  Visit <https://mail.gh.ccrproject.dev> to access the Mailhog server and view any email sent by preview deployments.

### Destroying a Preview Environment

To destroy a preview deployment, remove the `pr-preview` label from the pull request.  Closing or merging an issue will also automatically destroy any associated environments.

::: tip Dangling Previews
Preview builds cost hosting money. It's not a lot of money, but it adds up if a build is left running.  If you don't need an environment, go ahead and destroy it.  Creating it again is simply a matter of re-adding the `pr-preview` label.
:::

### Errors During Build

Currently, `CCR-Droid` is not great about spelling out specific build errors.  If you run into a problem, mention `@wreality`. He can look up the error messages and help with debugging the build.

## Contributor Workflow

1. Create a new branch based on the `development` branch
2. (Optional) Ensure your locally installed versions of the client dependencies match the `development` branch
    - Delete the `/client/node_modules` folder
    - Run `lando rebuild`
3. Work on your feature/fix
4. Open a pull request on GitHub to merge your branch into the `development` branch
5. Respond to feedback from the subsequent code review(s)
6. The pull request can be merged by a reviewer or a maintainer once approved

## Tagging a release

1. Check to make sure that tests on master are passing.
2. Run: `lando yarn release` to tag the release and update the changelog.
3. Push with tags: `git push --follow-tags`
4. Run `lando yarn release:details` to generate detailed changelog for MS Teams.
