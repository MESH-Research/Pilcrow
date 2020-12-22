#!/bin/bash

if [ -f "/app/backend/.env" ]; then
    echo "Backend .env exists, skipping initial setup"
    exit
fi

echo "No backend .env file found. Copying defaults."

cp /app/.lando/backend.default.env /app/backend/.env




