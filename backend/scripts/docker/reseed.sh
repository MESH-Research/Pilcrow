#!/bin/sh
/usr/bin/composer install --no-ansi
/wait && ./artisan migrate:fresh --seed --force
