#!/bin/sh
POPPLER_VERSION="25.03.0-5+deb13u4"
CACHE_DIR="/app/.lando/apt-cache"

arch=$(uname -m)
if [ "$arch" = "aarch64" ]; then #Apple Silicon
  echo Installing ARM64 version of Poppler
  build="arm64"
else
  echo Installing AMD64 version of Poppler
  build="amd64"
fi

deb_file="poppler-utils_${POPPLER_VERSION}_${build}.deb"
cached_deb="${CACHE_DIR}/${deb_file}"

mkdir -p "$CACHE_DIR"

if [ -f "$cached_deb" ]; then
  echo "Installing poppler-utils and dependencies from cache..."
  dpkg -i "$CACHE_DIR"/*.deb
else
  echo "Downloading poppler-utils and dependencies to cache..."
  apt-get update
  apt-get install -y -d -o Dir::Cache::Archives="$CACHE_DIR" poppler-utils:${build}=${POPPLER_VERSION}
  echo "Installing poppler-utils and dependencies..."
  dpkg -i "$CACHE_DIR"/*.deb
fi
