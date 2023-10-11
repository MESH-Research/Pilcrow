#!/bin/bash

if [ -f "/app/.env" ]; then
    echo "Backend .env exists, skipping initial setup"
    exit
fi

echo "No backend .env file found. Copying defaults."

cp /app/.lando/default.env /app/.env




