# Collaborative Community Review

## Development

### Getting Started
CCR uses docker-compose to manage our development environment.  

1. Make sure you have docker installed on your development machine.
1. Clone this repository to your local machine: `git clone https://github.com/MESH-Research/CCR`
1. Head into the CCR directory and run: `./develop start`
1. Open your hosts file and map `ccr.local` to your docker host's IP address (usually 127.0.0.1 for local development)
1. Once the bootstraping process is finished, open a browser to https://ccr.local/

The stack is running `quasar dev` in the node container so editing source files should result in HMR / recompiling as needed.  PHP files are served with phpfpm and should not require restarting the container to load changes.

### Tips / Troubleshooting

* The develop script is a bash script so you'll need bash installed.  On Windows, installing [WSL](https://docs.microsoft.com/en-us/windows/wsl/about) is highly recommended.  
* The nginx container expects port 443 to be open.  Be sure to stop other daemons or containers that are running on port 443.
* The nginx container generates a snakeoil certificate.  Add that certificate to your browser's trusted certificates to avoid content and clickthrough warnings.
* Run `./develop` alone to view the usage list.  There are commands to run `quasar`, `yarn`, `composer`, and `artisan` inside a container in case those commands aren't available on the host system.
* Under the hood, this is all docker-compose so feel free to use docker-compose commands directly to manipulate or stop/start containers as needed.
