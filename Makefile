TAG ?= latest

# REGISTRY defines the registry where we store our images.
# To push to a specific registry,
# you can use the REGISTRY as an arg of the docker build command (e.g make docker REGISTRY=my_registry.com/username)
# You may also change the default value if you are using a different registry as a default
REGISTRY ?= ghcr.io/wreality/pilcrow


# Commands
docker: docker-build docker-push

docker-build:
	docker build backend -t ${REGISTRY}/fpm:${VERSION}
	docker build backend --build-arg COMPOSER_NO_DEV=0 -t ${REGISTRY}/fpm-dev:${VERSION}
	docker build client -t ${REGISTRY}/web:${VERSION}

docker-push:
	docker push ${REGISTRY}/fpm:${VERSION}
	docker push ${REGISTRY}/fpm-dev:${VERSION}
	docker push ${REGISTRY}/web:${VERSION}