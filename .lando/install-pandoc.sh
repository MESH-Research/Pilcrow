#!/bin/sh
PANDOC_VERSION="2.18"
CACHE_DIR="/app/.lando/cache"

arch=$(uname -m)
if [ "$arch" = "aarch64" ]; then #M1 Mac
  echo Installing ARM64 version of Pandoc
  build="arm64"
else
  echo Installing AMD64 version of Pandoc
  build="amd64"
fi

deb_file="pandoc-${PANDOC_VERSION}-1-${build}.deb"
cached_deb="${CACHE_DIR}/${deb_file}"

mkdir -p "$CACHE_DIR"

if [ ! -f "$cached_deb" ]; then
  echo "Downloading pandoc ${PANDOC_VERSION}..."
  wget "https://github.com/jgm/pandoc/releases/download/${PANDOC_VERSION}/${deb_file}" -O "$cached_deb"
else
  echo "Using cached pandoc ${PANDOC_VERSION}"
fi

dpkg -i "$cached_deb"
