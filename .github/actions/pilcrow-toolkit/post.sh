#!/bin/bash
git config --global --add safe.directory $(pwd)
set -e

if [[ -z "${STATE_frontendBundle}" ]]; then
    echo "No frontend bundle to upload."
    exit 0
fi
if [[ -z "${STATE_frontendImage}" ]]; then
    echo "No frontend image to upload."
    exit 0
fi

ARTIFACT_PATH="${STATE_frontendBundle}"
ARTIFACT_PARENT="${STATE_frontendImage}"

if [[ ! -r "${ARTIFACT_PATH}" ]]; then
    echo "Artifact path ${ARTIFACT_PATH} does not exist or is not readable."
    exit 0
fi

$ORAS_ACTOR="${ORAS_ACTOR:-$GITHUB_ACTOR}"
$ORAS_TOKEN="${STATE_orasToken:-$GITHUB_TOKEN}"
$ARTIFACT_TYPE="${ARTIFACT_TYPE:-application/vnd.pilcrow.toolkit.bundle.v1+json}"

docker manifest inspect "${ARTIFACT_PARENT}" > /dev/null 2>&1
if [[ $? -ne 0 ]]; then
    echo "Artifact parent ${ARTIFACT_PARENT} does not exist in the registry."
    exit 0
fi

oras login --username "${ORAS_ACTOR}" --password "${ ORAS_TOKEN }" ghcr.io
oras attach "${ARTIFACT_PARENT} \
    --disable-path-validation \
    --artifact-type ${ARTIFACT_TYPE} \
        "${ARTIFACT_PATH}"
