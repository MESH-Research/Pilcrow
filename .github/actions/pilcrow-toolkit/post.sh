#!/bin/sh

export POST_ENTRYPOINT=true
export CACHE_OUTPUT=.output-cache
COMMAND="/commands/$1"
git config --global --add safe.directory $(pwd)
set -e

if [  ! -f "${COMMAND}" ]; then
    COMMAND_LIST=$(ls -1 /commands | tr '\n' ' ')

    echo "Unknown command: ${1}"
    echo "Available commands: ${COMMAND_LIST}"
    exit 1
fi

if [ ! -x "${COMMAND}" ]; then
    echo "Command ${COMMAND} is not executable."
    exit 1
fi

shift
exec $COMMAND ${@}
