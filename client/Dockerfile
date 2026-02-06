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

ARG BUILDSTAMP
RUN --mount=type=cache,target=/usr/local/share/.cache/yarn/v6 \
    yarn quasar build

RUN sed -i 's#"version": ""#"version": "${VERSION}"#g' /app/dist/spa/version.json && \
    sed -i 's#"versionUrl": ""#"versionUrl": "${VERSION_URL}"#g' /app/dist/spa/version.json && \
    sed -i 's#"versionDate": ""#"versionDate": "${VERSION_DATE}"#g' /app/dist/spa/version.json

FROM scratch AS bundle
COPY --from=build-stage /app/dist/spa/ /dist/spa/

FROM base AS unit-test
RUN --mount=type=cache,target=/usr/local/share/.cache/yarn/v6 \
    yarn test:unit:ci \
      --reporter=basic \
      --reporter=github-actions

FROM base AS lint

RUN --mount=type=cache,target=/usr/local/share/.cache/yarn/v6 \
    yarn lint


FROM nginx:${NGINX_VERSION} AS nginx

COPY --from=build-stage /app/dist/spa /var/www/html
COPY .docker/default.conf.template /etc/nginx/templates/default.conf.template
