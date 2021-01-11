# CCR Documentation

## Testing / Development

Our documentation is a [VuePress](https://vuepress.vuejs.org/) based static site.  Using [Lando](https://lando.dev) is probably the simplest way to spin up and test the documentation site.

1. Change directory to the `docs/` folder.
2. Start the Lando environment: `lando start`
3. Visit `https://docs_dev.lndo.site` to preview the built documentation site.

The development server supports hot-reloading of most page and content changes.

## Building Locally

1. Change directory to the `docs/` folder.
2. Run: `lando yarn build`
3. Compiled site will be located in `docs/.vuepress/dist`

## Environment
There are a few environment variables available to control the output of the built site:

- `CURRENT_BRANCH`: Adds a badge to the page header to indicate the source/version of the documentation.
- `BASE_URL`: The base URL of the hosted documentation version.  Note this should always start and end with a `/`.  e.g. `/CCR/`.