FROM --platform=$BUILDPLATFORM node:20-alpine AS build-stage
ARG VERSION
ARG VERSION_URL
ARG VERSION_DATE

WORKDIR /app

COPY package.json yarn.lock ./
RUN NO_POSTINSTALL=1 yarn
COPY . .
RUN yarn quasar prepare && yarn build

FROM nginx:latest

COPY --from=build-stage /app/dist/spa /var/www/html
COPY .docker/default.conf.template /etc/nginx/templates/default.conf.template

COPY --from=build-stage /app/public/version.json /var/www/html/version.json
RUN sed -i 's#"version": ""#"version": "${VERSION}"#g' /var/www/html/version.json && \
    sed -i 's#"versionUrl": ""#"versionUrl": "${VERSION_URL}"#g' /var/www/html/version.json && \
    sed -i 's#"versionDate": ""#"versionDate": "${VERSION_DATE}"#g' /var/www/html/version.json
