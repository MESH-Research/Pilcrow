variable "VERSION" {
  default = "source"
}

variable "VERSION_URL" {
  default = ""
}

variable "VERSION_DATE" {
  default = ""
}

target "fpm" {
  inherits =  ["_defaults"]
  context = "backend"
  tags = ["pilcrow/fpm:latest"]
  args = {
    VERSION = "${VERSION}"
    VERSION_URL = "${VERSION_URL}"
    VERSION_DATE = "${VERSION_DATE}"
  }
}

target "fpm-ci" {
  inherits =  ["fpm", "_ci"]
  tags = ["ci.local/pilcrow/fpm:latest"]
  output = ["type=docker,dest=/tmp/fpm.tar"]
}

target "web" {
  inherits =  ["_defaults"]
  context = "client"
  tags = ["pilcrow/web:latest"]
  args = {
    VERSION = "${VERSION}"
    VERSION_URL = "${VERSION_URL}"
    VERSION_DATE = "${VERSION_DATE}"
  }
}

target "web-ci" {
  inherits = ["web", "_ci"]
  tags = ["ci.local/pilcrow/web:latest"]
  output = ["type=docker,dest=/tmp/web.tar"]
}

target "fpm-release" {
  inherits = ["fpm", "_release"]
}

target "web-release" {
  inherits = ["web", "_release"]
  platforms = ["linux/amd64", "linux/arm64"]
}

target "_release" {
  output = ["type=image,push=true"]
}


target "_defaults" {
  dockerfile = "Dockerfile"
}

target "_ci" {
  cache-to = ["type=gha,mode=max"]
  cache-from = ["type=gha"]
}

group "default" {
  targets = ["fpm", "web"]
}

group "ci" {
  targets = ["fpm-ci", "web-ci"]
}

group "release" {
  targets = ["fpm-release", "web-release"]

}
