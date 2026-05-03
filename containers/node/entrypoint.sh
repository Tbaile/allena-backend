#!/usr/bin/env sh

if [ "$1" = "npm" ] && [ "$2" = "run" ] && [ "$3" = "dev" ]; then
  npm i
fi

exec "$@"
