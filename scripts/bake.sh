#!/bin/bash
# Run docker buildx bake targets locally with the correct environment.
# Handles LOCAL_PLATFORM detection and BUILDSTAMP cache-busting automatically.
#
# Usage: ./scripts/bake.sh [--no-cache] <target> [target...]
#
# Examples:
#   ./scripts/bake.sh fpm-lint
#   ./scripts/bake.sh web-test web-lint
#   ./scripts/bake.sh --no-cache fpm-lint
#   ./scripts/bake.sh fpm-test          # warns about MySQL requirement

set -e

NO_CACHE=""
TARGETS=()

# Parse arguments
for arg in "$@"; do
    case $arg in
        --no-cache)
            NO_CACHE="--no-cache"
            ;;
        -*)
            echo "Unknown option: $arg" >&2
            echo "Usage: $0 [--no-cache] <target> [target...]" >&2
            exit 1
            ;;
        *)
            TARGETS+=("$arg")
            ;;
    esac
done

if [ ${#TARGETS[@]} -eq 0 ]; then
    echo "Usage: $0 [--no-cache] <target> [target...]" >&2
    echo "" >&2
    echo "Available local targets: fpm-test, fpm-lint, web-test, web-lint" >&2
    exit 1
fi

# Detect platform for macOS compatibility
if [[ "$(uname -s)" == "Darwin" ]]; then
    ARCH="$(uname -m)"
    if [[ "$ARCH" == "arm64" ]]; then
        export LOCAL_PLATFORM="linux/arm64"
    else
        export LOCAL_PLATFORM="linux/amd64"
    fi
    echo "Detected macOS — using LOCAL_PLATFORM=$LOCAL_PLATFORM"
fi

# Check if fpm-test is among the targets and warn about MySQL requirement
for target in "${TARGETS[@]}"; do
    if [[ "$target" == "fpm-test" ]]; then
        echo "NOTE: The fpm-test target requires a running MySQL 5.7 instance."
        echo "For automatic MySQL setup and teardown, use bake-fpm-test.sh instead:"
        echo "  ./scripts/bake-fpm-test.sh"
        echo ""
        read -r -p "Continue with fpm-test anyway? [y/N] " response
        if [[ ! "$response" =~ ^[Yy]$ ]]; then
            echo "Aborted."
            exit 0
        fi
        break
    fi
done

# Set BUILDSTAMP to bust the cache for test/lint layers
export BUILDSTAMP
BUILDSTAMP=$(date +%s)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
cd "$PROJECT_ROOT"

echo "Running: docker buildx bake ${NO_CACHE:+$NO_CACHE }--allow=network.host ${TARGETS[*]}"
docker buildx bake \
    --allow=network.host \
    $NO_CACHE \
    --progress=plain \
    "${TARGETS[@]}"
