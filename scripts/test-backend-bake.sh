#!/bin/bash
# Run backend unit tests using docker buildx bake (same as CI)
# Usage: ./scripts/test-backend-bake.sh [--no-cache]

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

MYSQL_CONTAINER="pilcrow-test-mysql"
NO_CACHE=""

# Parse arguments
for arg in "$@"; do
    case $arg in
        --no-cache)
            NO_CACHE="--no-cache"
            ;;
    esac
done

cleanup() {
    echo -e "Cleaning up..."
    docker stop "$MYSQL_CONTAINER" 2>/dev/null || true
    docker rm "$MYSQL_CONTAINER" 2>/dev/null || true
}

trap cleanup EXIT

echo "Setting up test environment..."

# Start MySQL 5.7 container (same version as CI)
echo "Starting MySQL 5.7 container..."
docker run -d \
    --name "$MYSQL_CONTAINER" \
    -p 3306:3306 \
    -e MYSQL_ROOT_PASSWORD=pilcrow \
    -e MYSQL_DATABASE=pilcrow \
    mysql:5.7

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

# Run the tests
echo "Running backend unit tests..."
cd "$PROJECT_ROOT"
BUILDSTAMP=$(date +%s) docker buildx bake \
    --allow=network.host \
    $NO_CACHE \
    --progress=plain \
    fpm-test

echo "Tests completed successfully!"
