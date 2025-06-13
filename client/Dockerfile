FROM --platform=$BUILDPLATFORM node:20-alpine AS build-stage
ARG VERSION
ARG VERSION_URL
ARG VERSION_DATE

WORKDIR /app

COPY package.json yarn.lock ./
RUN NO_POSTINSTALL=1 yarn
COPY . .
RUN yarn quasar prepare && yarn build

RUN sed -i 's#"version": ""#"version": "${VERSION}"#g' /app/dist/spa/version.json && \
    sed -i 's#"versionUrl": ""#"versionUrl": "${VERSION_URL}"#g' /app/dist/spa/version.json && \
    sed -i 's#"versionDate": ""#"versionDate": "${VERSION_DATE}"#g' /app/dist/spa/version.json

RUN tar -czvf /app/dist/frontend-bundle.tgz -C /app/dist/spa .

FROM scratch AS bundle
COPY --from=build-stage /app/dist /


FROM nginx:latest

COPY --from=build-stage /app/dist/spa /var/www/html
COPY .docker/default.conf.template /etc/nginx/templates/default.conf.template
