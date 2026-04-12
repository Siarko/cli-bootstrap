#!/bin/bash

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

MY_UID="$(id -u)" MY_GID="$(id -g)" docker compose -f $SCRIPT_DIR/docker-compose.yaml run --rm app
