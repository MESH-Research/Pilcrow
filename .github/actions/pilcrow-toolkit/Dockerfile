FROM gcc:15.1.0 as builder

WORKDIR /src

ADD https://github.com/theZiz/aha.git .

RUN make && make install


FROM alpine:3.10

COPY --from=builder /usr/local/bin/aha /usr/local/bin/aha

# Copies your code file from your action repository to the filesystem path `/` of the container
COPY . .

# Code file to execute when the docker container starts up (`entrypoint.sh`)
ENTRYPOINT ["/entrypoint.sh"]
