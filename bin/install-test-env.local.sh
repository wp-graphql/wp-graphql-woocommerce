#!/usr/bin/env bash

set +u

source .env

print_usage_instruction() {
	echo "Ensure that .env file exist in project root directory exists."
	echo "And run the following 'composer $1' in the project root directory"
	exit 1
}

BASEDIR=$(dirname "$0");
source ${BASEDIR}/_env.sh
source ${BASEDIR}/_lib.sh

install_wordpress
install_local_test_library
install_db
configure_wordpress
setup_plugin
