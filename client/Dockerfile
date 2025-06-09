FROM --platform=$BUILDPLATFORM node:20-alpine as build-stage
ARG VERSION
ARG VERSION_URL
ARG VERSION_DATE

WORKDIR /app

COPY package.json yarn.lock ./
RUN yarn
COPY . .
RUN yarn build

FROM nginx:latest

COPY --from=build-stage /app/dist/spa /var/www/html
COPY .docker/default.conf.template /etc/nginx/templates/default.conf.template

COPY --from=build-stage /app/public/version.json /var/www/html/version.json
RUN sed -i 's/"version": ""/"version": "${VERSION}"/g' /var/www/html/version.json && \
    sed -i 's/"versionUrl": ""/"versionUrl": "${VERSION_URL}"/g' /var/www/html/version.json && \
    sed -i 's/"versionDate": ""/"versionDate": "${VERSION_DATE}"/g' /var/www/html/version.json
