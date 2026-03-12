#!/bin/bash
# Run backend unit tests using docker buildx bake (same as CI)
# Usage: ./scripts/test-backend-bake.sh [--no-cache] [--docker-network]
#
# On Linux, uses network=host to connect the build to MySQL on localhost.
# On macOS (auto-detected) or with --docker-network, creates a Docker network
# and a dedicated buildx builder so the build container can reach MySQL by
# container name.

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

MYSQL_CONTAINER="pilcrow-test-mysql"
DOCKER_NETWORK="pilcrow-test-net"
BUILDER_NAME="pilcrow-builder"
NO_CACHE=""
USE_DOCKER_NETWORK=false

IS_MACOS=false
if [[ "$(uname -s)" == "Darwin" ]]; then
    IS_MACOS=true
    USE_DOCKER_NETWORK=true
fi

# Parse arguments
for arg in "$@"; do
    case $arg in
        --no-cache)
            NO_CACHE="--no-cache"
            ;;
        --docker-network)
            USE_DOCKER_NETWORK=true
            ;;
    esac
done

# Warn macOS users about known issues
if $IS_MACOS; then
    echo "WARNING: This script does not currently work reliably on macOS."
    echo "See: https://github.com/MESH-Research/Pilcrow/issues/2224"
    echo ""
    echo "You can still run tests via Lando (lando yarn test:unit), which is"
    echo "unaffected. This script simulates the CI environment using Docker bake."
    echo ""
    read -r -p "Continue anyway? [y/N] " response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        echo "Aborted."
        exit 0
    fi
fi

cleanup() {
    echo -e "Cleaning up..."
    docker stop "$MYSQL_CONTAINER" 2>/dev/null || true
    docker rm "$MYSQL_CONTAINER" 2>/dev/null || true
    if $USE_DOCKER_NETWORK; then
        docker network rm "$DOCKER_NETWORK" 2>/dev/null || true
    fi
}

trap cleanup EXIT

echo "Setting up test environment..."

MYSQL_RUN_ARGS=(
    -d
    --name "$MYSQL_CONTAINER"
    -e MYSQL_ROOT_PASSWORD=pilcrow
    -e MYSQL_DATABASE=pilcrow
)

if $USE_DOCKER_NETWORK; then
    echo "Using Docker network for build connectivity."

    # Create a dedicated network for the build to reach MySQL
    docker network create "$DOCKER_NETWORK" 2>/dev/null || true

    MYSQL_RUN_ARGS+=(--network "$DOCKER_NETWORK")

    # Ensure a buildx builder exists that can access the network.
    # The docker-container driver is also required on Apple Silicon
    # for proper multi-platform image resolution.
    if ! docker buildx inspect "$BUILDER_NAME" >/dev/null 2>&1; then
        echo "Creating buildx builder '$BUILDER_NAME'..."
        docker buildx create \
            --name "$BUILDER_NAME" \
            --driver docker-container \
            --driver-opt network="$DOCKER_NETWORK"
    else
        # Builder exists — make sure it's connected to our network.
        # Recreate to update driver options (no update-in-place supported).
        docker buildx rm "$BUILDER_NAME" 2>/dev/null || true
        docker buildx create \
            --name "$BUILDER_NAME" \
            --driver docker-container \
            --driver-opt network="$DOCKER_NETWORK"
    fi
else
    MYSQL_RUN_ARGS+=(-p 3306:3306)
fi

# Start MySQL 5.7 container (same version as CI)
echo "Starting MySQL 5.7 container..."
docker run "${MYSQL_RUN_ARGS[@]}" mysql:5.7

# Wait for MySQL to be ready to accept connections
echo -n "Waiting for MySQL to be ready..."
MYSQL_READY=0
for i in {1..60}; do
    if docker exec "$MYSQL_CONTAINER" mysql -uroot -ppilcrow -e "SELECT 1" >/dev/null 2>&1; then
        echo -e "\nMySQL is ready"
        MYSQL_READY=1
        break
    fi
    echo -n "."
    sleep 2
done

if [ "$MYSQL_READY" -eq 0 ]; then
    echo -e "\nError: MySQL failed to start within 120 seconds"
    exit 1
fi

# Copy client schema snapshot into backend stubs (same as CI)
echo "Copying client schema for drift detection..."
mkdir -p "$PROJECT_ROOT/backend/tests/stubs"
cp "$PROJECT_ROOT/client/src/graphql/schema.graphql" "$PROJECT_ROOT/backend/tests/stubs/schema.graphql"

# Run the tests
echo "Running backend unit tests..."
cd "$PROJECT_ROOT"

if $USE_DOCKER_NETWORK; then
    # Detect architecture for LOCAL_PLATFORM override
    ARCH="$(uname -m)"
    if [[ "$ARCH" == "arm64" ]]; then
        LOCAL_PLATFORM="linux/arm64"
    else
        LOCAL_PLATFORM="linux/amd64"
    fi

    LOCAL_PLATFORM="$LOCAL_PLATFORM" \
    DB_HOST="$MYSQL_CONTAINER" \
    BUILDSTAMP=$(date +%s) \
    docker buildx bake \
        --builder "$BUILDER_NAME" \
        $NO_CACHE \
        --progress=plain \
        fpm-test
else
    BUILDSTAMP=$(date +%s) docker buildx bake \
        --allow=network.host \
        $NO_CACHE \
        --progress=plain \
        fpm-test
fi

echo "Tests completed successfully!"
