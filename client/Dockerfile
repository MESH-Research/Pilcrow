ARG NGINX_VERSION=1.27.5-bookworm
ARG NODE_VERSION=20
ARG VERSION
ARG VERSION_URL
ARG VERSION_DATE
ARG STDOUT_FILE=output.txt
ARG STDERR_FILE=errors.txt
ARG STEP_SUMMARY_FILE=output.md
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

RUN --mount=type=cache,target=/usr/local/share/.cache/yarn/v6 \
    yarn quasar build > >(tee output.txt) 2> >(tee errors.txt)

RUN sed -i 's#"version": ""#"version": "${VERSION}"#g' /app/dist/spa/version.json && \
    sed -i 's#"versionUrl": ""#"versionUrl": "${VERSION_URL}"#g' /app/dist/spa/version.json && \
    sed -i 's#"versionDate": ""#"versionDate": "${VERSION_DATE}"#g' /app/dist/spa/version.json

# Generate a summary of the build step in markdown.
RUN <<EOC
    echo "## Quasar Build Summary" > "${STEP_SUMMARY_FILE}"
    if [[ -s "${STDERR_FILE}" ]]; then
      echo "| :warning:     | The build wrote to stderr during execution.  Check below for errors/warnings. |" >> "${STEP_SUMMARY_FILE}"
      echo "|---------------|:------------------------------------------------------------------------------|" >> "${STEP_SUMMARY_FILE}"
      echo "### STDERR Output" >> "${STEP_SUMMARY_FILE}"
      cat "${STDERR_FILE}" | aha --no-header >> "${STEP_SUMMARY_FILE}"
    fi
    echo "### STDOUT Output" >> "${STEP_SUMMARY_FILE}"
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

RUN tar -czvf /app/dist/frontend-bundle.tgz -C /app/dist/spa .

FROM scratch AS bundle
COPY --from=build-stage /app/dist /app/output.txt /app/errors.txt /app/output.md /


FROM base AS unit-test

RUN --mount=type=cache,target=/usr/local/share/.cache/yarn/v6 \
    yarn test:unit:ci \
      --reporter=basic \
      --reporter=github-actions \
      > >(tee /output.txt) 2> >(tee /errors.txt)

FROM scratch AS test-results
COPY --from=unit-test /output.txt /errors.txt /

FROM nginx:${NGINX_VERSION} AS nginx

COPY --from=build-stage /app/dist/spa /var/www/html
COPY .docker/default.conf.template /etc/nginx/templates/default.conf.template
