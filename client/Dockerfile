FROM node:14-alpine as build-stage
WORKDIR /app
COPY . ./

RUN yarn && yarn build

FROM nginx:latest

COPY --from=build-stage /app/dist/spa /var/www/html
COPY .docker/default.conf.template /etc/nginx/templates/default.conf.template





