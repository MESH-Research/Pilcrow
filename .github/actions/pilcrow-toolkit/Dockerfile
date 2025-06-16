ARG GCC_VERSION=15.1.0
FROM gcc:${GCC_VERSION} AS builder


WORKDIR /src

ADD https://github.com/theZiz/aha.git .

RUN make && make install


FROM node:22-alpine
ARG ORAS_VERSION=1.2.2
ARG TARGETOS
ARG TARGETARCH

COPY --from=builder /usr/local/bin/aha /usr/local/bin/aha

RUN apk add --no-cache \
    docker-cli \
    curl \
    git \
    bash \
    jq

RUN curl -Lo oras.tar.gz https://github.com/oras-project/oras/releases/download/v${ORAS_VERSION}/oras_${ORAS_VERSION}_${TARGETOS}_${TARGETARCH}.tar.gz && \
    tar -zxvf oras.tar.gz oras && \
    mv oras /usr/local/bin/ && \
    rm -rf oras.tar.gz

WORKDIR /github/workspace

# Copies your code file from your action repository to the filesystem path `/` of the container
COPY entrypoint.sh post.sh /

COPY commands /commands/

# Code file to execute when the docker container starts up (`entrypoint.sh`)
ENTRYPOINT ["/entrypoint.sh"]
