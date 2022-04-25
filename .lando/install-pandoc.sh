#!/bin/sh

wget https://github.com/jgm/pandoc/releases/download/2.18/pandoc-2.18-1-amd64.deb -O /tmp/pandoc.deb && \
dpkg -i /tmp/pandoc.deb
rm /tmp/pandoc.deb
