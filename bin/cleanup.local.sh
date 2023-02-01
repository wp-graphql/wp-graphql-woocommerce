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

# Remove any local Composer instances that could
# potentially get in the way of any file removals.
remove_local_composer_instance

# Uninstall WordPress from project and Composer.
remove_wordpress

# Delete symlink to
remove_project_symlink

# Remove testing dependencies from Composer.
remove_local_test_library

# Clean composer.json.
cleanup_composer_file

# Delete any missed files in the removal of the local installation.
# And rebuild composer.lock
cleanup_local_files
