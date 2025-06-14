#!/bin/bash

CMD="commands/$1"

set -e

if [ ! -x "$CMD" ]; then
    echo "Unknown command: ${1}"
    echo "Available commands: $(ls commands/)"
    exit 1
fi

shift
exec $CMD ${@}
