#!/usr/bin/env bash

set +u

source .env

print_usage_instruction() {
	echo "Ensure that .env file exist in project root directory exists."
	echo "And run the following 'composer $@' in the project root directory"
	exit 1
}

BASEDIR=$(dirname "$0");
source ${BASEDIR}/_env.sh
source ${BASEDIR}/_lib.sh

remove_local_test_library

composer install --no-dev
