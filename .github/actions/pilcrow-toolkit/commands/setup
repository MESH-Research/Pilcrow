#!/bin/bash


VERSION=$(git describe --tags --match "v*")
VERSION_URL=https://github.com/${GITHUB_REPOSITORY}/commits/${GITHUB_SHA}
VERSION_DATE=$(git show -s --format=%cI ${GITHUB_SHA})

REPO=${GITHUB_REPOSITORY,,}
DOCKER_REGISTRY_CACHE=ghcr.io/${REPO}/cache/__service__

echo "source.version=${VERSION}" >> "$GITHUB_OUTPUTS"
echo "source.version-url=${VERSION_URL}" >> "$GITHUB_OUTPUTS"
echo "source.version-date=${VERSION_DATE}" >> "$GITHUB_OUTPUTS"
echo "repository=${GITHUB_REPOSITORY,,}" >> "$GITHUB_OUTPUTS"
echo "docker-registry-cache=${DOCKER_REGISTRY_CACHE}" >> "$GITHUB_OUTPUTS"
if [ "${TARGET}" == "release" ]; then
    echo "image-template=ghcr.io/${REPO}/__service__" >> "$GITHUB_ENV"
else
    echo "image-template=${DOCKER_REGISTRY_CACHE}" >> "$GITHUB_ENV"
fi
