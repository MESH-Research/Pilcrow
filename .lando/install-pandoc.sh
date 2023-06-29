#!/bin/sh
arch=$(uname -m)
if [ "$arch" = "aarch64" ]; then #M1 Mac
  echo Installing ARM64 version of Pandoc
  build="arm64"
else
  echo Installing AMD64 version of Pandoc
  build="amd64"
fi
wget https://github.com/jgm/pandoc/releases/download/2.18/pandoc-2.18-1-${build}.deb -O /tmp/pandoc.deb && \
dpkg -i /tmp/pandoc.deb
rm /tmp/pandoc.deb
