#!/bin/bash

set -e

if [[ ! -f .env ]]; then
    echo -e "\n\033[0;32mRunning\033[0m cp .env.template .env"
    cp .env.template .env
fi

echo -e "\n\033[0;32mOptional background mode\033[0m"
read -p "Enter 'bg' to start your stack in background mode: " PARAM

if [[ "$PARAM" == "bg" ]]; then
  echo -e "\033[0;32mOk starting stack in background!\033[0m\n"
  docker-compose up -d
else
  echo -e "\033[0;32mAlright, starting stack in foreground!\033[0m\n"
  docker-compose up
fi
