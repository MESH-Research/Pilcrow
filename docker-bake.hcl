variable "VERSION" {
    default = "source"
}

variable "VERSION_URL" {
    default = ""
}

variable "VERSION_DATE" {
    default = ""
}

variable "GITHUB_REF_NAME" {
    default = ""
}

variable "WEB_CACHE_FROM" {
    default = ""
}

variable "WEB_CACHE_TO" {
    default = ""
}

variable "FPM_CACHE_FROM" {
    default = ""
}

variable "FPM_CACHE_TO" {
    default = ""
}

target "fpm" {
    context = "backend"

    labels = {
        "org.opencontainers.image.description" = "Pilcrow FPM Container Image version: ${ VERSION }@${VERSION_DATE } (${ VERSION_URL })"
    }
    output = ["type=image,push=true,annotation-index.org.opencontainers.image.description=Pilcrow FPM Container Image version: ${ VERSION }@${VERSION_DATE } (${ VERSION_URL })"]
    cache-from = ["${FPM_CACHE_FROM}", "type=registry,ref=ghcr.io/mesh-research/pilcrow/fpm:edge"]
    cache-to = ["${FPM_CACHE_TO}"]
    tags = [ "wreality/pilcrow-fpm-debug:latest" ]
}


target "web" {
    context = "client"

    labels = {
        "org.opencontainers.image.description" = "Pilcrow WEB Container Image version: ${ VERSION }@${VERSION_DATE } (${ VERSION_URL })"
    }
    output = ["type=image,push=true,annotation-index.org.opencontainers.image.description=Pilcrow WEB Container Image version: ${ VERSION }@${VERSION_DATE } (${ VERSION_URL })"]
    cache-from = ["${WEB_CACHE_FROM}", "type=registry,ref=ghcr.io/mesh-research/pilcrow/web:edge"]
    cache-to = ["${WEB_CACHE_TO}"]
    tags = [ "wreality/pilcrow-web-debug:latest" ]
}


target "fpm-release" {
    inherits = ["fpm"]
}

target "web-release" {
    inherits = ["web"]
    platforms = ["linux/amd64", "linux/arm64"]
}

group "default" {
    targets = ["fpm", "web"]
}


group "release" {
    targets = ["fpm-release", "web-release"]

}
