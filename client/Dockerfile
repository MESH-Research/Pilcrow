FROM --platform=$BUILDPLATFORM node:20-alpine as build-stage
WORKDIR /app

COPY package.json yarn.lock ./
RUN yarn
COPY . .
RUN yarn build

FROM nginx:latest

COPY --from=build-stage /app/dist/spa /var/www/html
COPY .docker/default.conf.template /etc/nginx/templates/default.conf.template





