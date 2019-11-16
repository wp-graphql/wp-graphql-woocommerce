#!/bin/bash

# Move to WordPress root folder
workdir="$PWD"
echo "Moving to WordPress root directory."
cd ${WP_ROOT_FOLDER}

# Run app entrypoint script.
. app-entrypoint.sh

# Return to PWD.
echo "Moving back to project working directory."
cd ${workdir}

# Ensure Apache is running
service apache2 start

# Ensure everything is loaded
dockerize \
    -wait tcp://${DB_HOST}:${DB_HOST_PORT:-3306} \
    -wait ${WP_URL} \
    -timeout 1m

# Download c3 for testing.
if [ ! -f "${PROJECT_DIR}/c3.php" ]; then
    echo 'Downloading c3.php'
    curl -L 'https://raw.github.com/Codeception/c3/2.0/c3.php' > "${PROJECT_DIR}/c3.php"
fi

# Install dependencies
COMPOSER_MEMORY_LIMIT=-1 composer install --prefer-source --no-interaction

if [ "$DESIRED_PHP_VERSION" != "5.6" ] || [ "$DESIRED_PHP_VERSION" != "7.0" ] && [ "$COVERAGE" == "1" ]; then
    COMPOSER_MEMORY_LIMIT=-1 composer require --dev pcov/clobber
    vendor/bin/pcov clobber
fi

# Set output permission
echo "Setting Codeception output directory permissions"
chmod 777 ${TESTS_OUTPUT}

run_tests() {
    local suites=${1:-" ;"}
    IFS=';' read -ra target_suites <<< "$suites"
    for suite in "${target_suites[@]}"; do
        if [ "$COVERAGE" == "1" -a "$DEBUG" == "1" ]; then
            php -d pcov.enabled=1 vendor/bin/codecept run \
                -c codeception.dist.yml ${suite} --debug --coverage --coverage-xml
        elif [ "$COVERAGE" == "1" ]; then
            php -d pcov.enabled=1 vendor/bin/codecept run \
                -c codeception.dist.yml ${suite} --coverage --coverage-xml
        elif [ "$DEBUG" == "1" ]; then
            vendor/bin/codecept run -c codeception.dist.yml ${suite} --debug
        else
            vendor/bin/codecept run -c codeception.dist.yml ${suite}
        fi
    done
}

run_tests ${SUITES}


if [ -f "${TESTS_OUTPUT}" ]; then
    echo 'Setting "coverage.xml" permissions'.
    chmod 777 -R ${TESTS_OUTPUT}/coverage.xml
fi

if [ "$COVERAGE" == "1" ]; then
    echo 'Removing pcov/clobber.'
    vendor/bin/pcov unclobber
    COMPOSER_MEMORY_LIMIT=-1 composer remove --dev pcov/clobber
fi