# Documentation

CCR's documentation (the site you're currently reading) is a [VuePress](https://vuepress.vuejs.org) site located in the `/docs` directory of the repo.  Our goal is to iterate the documentation as features are added to the application.  All of the documentation files are [Markdown](https://www.markdownguide.org/getting-started/) with some minor Vue additions sprinkled in here and there.

::: tip Help CCR Grow
![edit this page screenshot](./images/edit_this_page.jpg)

Helping improve documentation is a great way to help CCR that doesn't require programming experience.

At the bottom of each page, you should find a link to "Edit this Page."  Follow this link straight to the GitHub editing interface!
:::
## Automated Builds

Github Actions powered by [Netlify](https://netlify.com) automatically build and deploy the documentation sites.  There are two automatic builds configured:

- **<https://docs.ccrproject.dev>**: Automatically built from the `master` branch.
- **<https://development.docs.ccrproject.dev>**: Automatically built from the `development` branch.

### Deploy Previews

Any pull request that contains changes to documentation will have a preview build deployed.  These previews are a great way to share proposed documentation changes for feedback and review.  

Look in the automated checks section of the pull request for the action with the text "Deploy Preview Ready!".  Clicking the "Details" link will open a new tab for the deploy preview website.

![deploy previews screenshot](./images/deploy_previews.jpg)

## Serving test Docs using Lando

It can be helpful to have a locally rendered version to see the results of your changes while editing documentation.  The `/docs` directory has its own [Lando](https://lando.dev) configuration file just for starting up a documentation development environment.

Inside the `/docs` directory, run `lando start` to start the container and proxy.  Once everything is up and running, visit <https://docs_dev.lndo.site> to view your documentation build.  The container uses a development server, so updates should happen automatically in the browser without requiring a refresh.
