variable "VERSION" {
    default = ""
}

variable "VERSION_URL" {
    default = ""
}

variable "VERSION_DATE" {
    default = ""
}

variable "PUSH" {
    default = false
}

variable "BUILDSTAMP" {
    default = ""
}

target "fpm" {
    context = "backend"
    target = "fpm"
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
        BUILDSTAMP = BUILDSTAMP
    }
    labels = {
        for k, v in target.default-labels.labels : k => replace(v, "__service__", "web")
    }
}

target "ci-actions" {
    matrix = {
        svc = ["web", "fpm"]
        action = ["test", "lint"]
    }
    name = "${svc}-${action}-ci"
    inherits = ["${svc}-${action}"]
    cache-from = [ for v in target.docker-build-cache-config-action.cache-from : cacheReplace(v, svc)]
}



target "web-lint" {
    inherits = ["web"]
    target = "lint"
    platforms = [ "local" ]
    output = ["type=cacheonly"]
}

target "fpm-test" {
    inherits = ["fpm"]
    target = "unit-test"
    platforms = ["local"]
    output = ["type=cacheonly"]
    args = {
        BUILDSTAMP = BUILDSTAMP
    }
}

target "fpm-lint" {
    inherits = ["fpm"]
    target = "lint"
    platforms = ["local"]
    output = ["type=cacheonly"]
}

target "web-test" {
    inherits = ["web"]
    target = "unit-test"
    platforms = ["local"]
    output = ["type=cacheonly"]
}

target "web-bundle" {
    context = "client"
    target = "bundle"
    args = {
        VERSION = VERSION
        VERSION_URL = VERSION_URL
        VERSION_DATE = VERSION_DATE
    }
    platforms = ["local"]
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

group "ci" {
    targets = ["ci-fpm", "ci-web"]
}

target "ci-images" {
    matrix = {
        tgt = ["fpm", "web"]
    }
    name = "ci-${tgt}"
    inherits = [tgt]

    #Metadata action supllies us the tags to use.
    tags = [for tag in target.docker-metadata-action.tags : replace(tag, "__service__", tgt)]

    #Merge labels from metadata action and default-labels
    #Replace __service__ with the target name
    labels = merge(
        { for k,v in target.docker-metadata-action.labels :
            k => replace(v, "__service__", tgt)
        },
        { for k,v  in target.default-labels.labels :
            k => replace(v, "__service__", tgt)
        }
    )

    #Set cache-from and cache-to based on the cache-config action
    #Replace __service__ with the target name
    cache-from = [ for v in target.docker-build-cache-config-action.cache-from : cacheReplace(v, tgt)]
    cache-to = [ for v in target.docker-build-cache-config-action.cache-to : cacheReplace(v, tgt)]

}
target "docker-metadata-action" {
    tags = []
    labels = {}
}

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
        },
        ]
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
