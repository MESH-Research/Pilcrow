# Collaborative Community Review

## Development

### Getting Started

CCR uses [Lando](https://lando.dev) to manage its development environment.

1. [Install Lando](https://docs.lando.dev/basics/installation.html)
2. Clone this repository to your local machine: `git clone https://github.com/MESH-Research/CCR`
3. Head into the CCR directory and run: `lando start`
4. Coffee. Downloading. Building. Patience.
5. Migrate database: `lando artisan migrate`
6. Once the bootstraping process is finished, open a browser to <https://ccr.lndo.site/>

The stack is running `yarn dev` in the node container so editing source files should result in HMR / recompiling as needed.  PHP files are served with phpfpm and should not require restarting the container to load changes.

### Tips / Troubleshooting

* Lando generates it's own CA cert which [you can add to your OS certificate store](https://docs.lando.dev/config/security.html#trusting-the-ca) (optional, but super nice).
* Node and composer packages are updated on *rebuild* only.  To update dependencies without rebuilding use `lando composer install` and/or `lando yarn install`. You may have to run `lando restart` if node dependencies have changed.
* Database migrations are *not* automatically applied, so you'll need to run `lando artisan migrate` to apply them as needed.
