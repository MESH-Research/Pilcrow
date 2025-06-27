ARG NGINX_VERSION=1.29.0-bookworm
ARG NODE_VERSION=20
ARG VERSION
ARG VERSION_URL
ARG VERSION_DATE
FROM --platform=$BUILDPLATFORM node:${NODE_VERSION}-alpine AS base

WORKDIR /app
COPY package.json yarn.lock ./
RUN --mount=type=cache,target=/usr/local/share/.cache/yarn/v6 \
NO_POSTINSTALL=1 \
yarn --frozen-lockfile
COPY . .
RUN --mount=type=cache,target=/usr/local/share/.cache/yarn/v6 \
yarn quasar prepare

FROM base AS build-stage
ARG OUTPUT_PATH=/tmp/output/web-build
ARG STDOUT_FILE=${OUTPUT_PATH}/stdout.txt
ARG STDERR_FILE=${OUTPUT_PATH}/stderr.txt
ARG STEP_SUMMARY_FILE=${OUTPUT_PATH}/output.md


ARG BUILDSTAMP
RUN --mount=type=cache,target=/tmp/output \
    --mount=type=cache,target=/usr/local/share/.cache/yarn/v6 \
    mkdir -p "${OUTPUT_PATH}" && \
    yarn quasar build > >(tee ${STDOUT_FILE}) 2> >(tee ${STDERR_FILE})

RUN sed -i 's#"version": ""#"version": "${VERSION}"#g' /app/dist/spa/version.json && \
    sed -i 's#"versionUrl": ""#"versionUrl": "${VERSION_URL}"#g' /app/dist/spa/version.json && \
    sed -i 's#"versionDate": ""#"versionDate": "${VERSION_DATE}"#g' /app/dist/spa/version.json

# Generate a summary of the build step in markdown.
RUN --mount=type=cache,target=/tmp/output <<EOC
    echo "## Quasar Build Summary" > "${STEP_SUMMARY_FILE}"
    echo "${STEP_SUMMARY_FILE}"
    sed -r \
      -e 's/^ ║\s+([A-Za-z0-9\.\/\(\)\ -]+)║([A-Za-z0-9\. ]+)║([A-Za-z0-9\. -]+)║$/| \1 | \2 | \3 |/' \
      -e 's/^ ╔.*$/\n<details> \n<summary>Output Files<\/summary> \n /'  \
      -e 's/^ [╟].*$/| ------ | ------ | ------ |/g' \
      -e '/^ ╚.*$/d' \
      -e '4i ```' \
      -e '13i ```' \
      -e '15i | Arg | Value | ' \
      -e '15i | ---- | ----- | ' \
      -e '/^ Build succeeded$/a\ \n| Arg | Value |\n| ---- | ----- |' \
      -e '/^ Build succeeded$/{n;N;d;}' \
      -e 's/^ ([a-zA-Z\ \/@-]+)\.\.\.\.* ([A-Za-z0-9\.|\/]+)$/| \1 | `\2` | /' \
      -e '/^ =.*$/d' \
      -e 's/^ ╠.*$/<\/details>\n\n#### Total Sizes \n\n| Files | Size | - |\n| ----- | ----- | ----- |/' \
      "${STDOUT_FILE}" >> "${STEP_SUMMARY_FILE}"
EOC



RUN --mount=type=cache,target=/tmp/output tar -czvf /tmp/output/frontend-bundle.tar.gz -C /app/dist/spa .

FROM scratch AS bundle
COPY --from=build-stage /app/dist/spa/ /dist/spa/

FROM base AS unit-test
ARG OUTPUT_PATH=/tmp/output/web-unit
ARG STDOUT_FILE=${OUTPUT_PATH}/stdout.txt
ARG STDERR_FILE=${OUTPUT_PATH}/stderr.txt
RUN --mount=type=cache,target=/usr/local/share/.cache/yarn/v6 \
    --mount=type=cache,target=/tmp/output \
    mkdir -p "${OUTPUT_PATH}" && \
    yarn test:unit:ci \
      --reporter=basic \
      --reporter=github-actions \
      > >(tee ${STDOUT_FILE}) \
      2> >(tee ${STDERR_FILE})

FROM base AS lint
ARG OUTPUT_PATH=/tmp/web-lint/output
ARG STDOUT_FILE=${OUTPUT_PATH}/stdout.txt
ARG STDERR_FILE=${OUTPUT_PATH}/stderr.txt

RUN --mount=type=cache,target=/usr/local/share/.cache/yarn/v6 \
    --mount=type=cache,target=/tmp/output \
    mkdir -p "${OUTPUT_PATH}" && \
    yarn lint \
      > >(tee ${STDOUT_FILE}) \
      2> >(tee ${STDERR_FILE})


FROM nginx:${NGINX_VERSION} AS nginx

COPY --from=build-stage /app/dist/spa /var/www/html
COPY .docker/default.conf.template /etc/nginx/templates/default.conf.template
