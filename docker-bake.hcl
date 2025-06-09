variable "VERSION" {
    default = ""
}

variable "VERSION_URL" {
    default = ""
}

variable "VERSION_DATE" {
    default = ""
}

variable "CI_TMP_DIR" {
    default = "/tmp/webbuild"
}

target "fpm" {
    context = "backend"
    args = {
        VERSION = VERSION
        VERSION_URL = VERSION_URL
        VERSION_DATE = VERSION_DATE
    }
    labels = {
        for k, v in target.default-labels.labels : k => replace(v, "__service__", "fpm")
    }
}


target "web" {
    context = "client"
    args = {
        VERSION = VERSION
        VERSION_URL = VERSION_URL
        VERSION_DATE = VERSION_DATE
    }
    labels = {
        for k, v in target.default-labels.labels : k => replace(v, "__service__", "web")
    }
}

target "default-labels" {
    labels = {
        "net.mesh-research.pilcrow.service" = "__service__"
        "net.mesh-research.pilcrow.version" = "${VERSION}"

    }
}

group "default" {
    targets = ["fpm", "web"]
}

target "ci" {
    matrix = {
        item = [
            {
                tgt = "fpm"
                output = ["type=image,push=true"]
            },
            {
                tgt = "web"
                output = ["type=image,push=true", "type=local,dest=${CI_TMP_DIR}"]
            }
        ]
    }
    name = "ci-${item.tgt}"
    inherits = [item.tgt]

    #Metadata action supllies us the tags to use.
    tags = [for tag in target.docker-metadata-action.tags : replace(tag, "__service__", item.tgt)]

    #Merge labels from metadata action and default-labels
    #Replace __service__ with the target name
    labels = merge(
        { for k,v in target.docker-metadata-action.labels :
            k => replace(v, "__service__", item.tgt)
        },
        { for k,v  in target.default-labels.labels :
            k => replace(v, "__service__", item.tgt)
        }
    )

    #Set cache-from and cache-to based on the cache-config action
    #Replace __service__ with the target name
    cache-from = [ for v in target.docker-build-cache-config-action.cache-from : cacheReplace(v, item.tgt)]
    cache-to = [ for v in target.docker-build-cache-config-action.cache-to : cacheReplace(v, item.tgt)]

    output = item.output
}
target "docker-metadata-action" {}

target "docker-build-cache-config-action" {}

target "release" {
    matrix = {
        item = [ {
            tgt= "fpm"
            platforms = ["linux/amd64"]
        },
        {
            tgt = "web"
            platforms = ["linux/amd64", "linux/arm64"]
        }]
    }
    name = "release-${item.tgt}"
    inherits = ["ci-${item.tgt}"]

    #For release targets, we want to build multi-platform images.
    platforms = item.platforms
}

function "cacheReplace" {
    params = [config, service]
    result = {
        for k, v in config : k => replace(v, "__service__", service)
    }
}
