############################################################################
# Container for running Codeception tests on a WooGraphQL Docker instance. #
############################################################################

# Using the 'DESIRED_' prefix to avoid confusion with environment variables of the same name.
ARG DESIRED_WP_VERSION
ARG DESIRED_PHP_VERSION

FROM kidunot89/woographql-app:wp${DESIRED_WP_VERSION:-5.2.2}-php${DESIRED_PHP_VERSION:-7.3}

LABEL author=kidunot89
LABEL author_uri=https://github.com/kidunot89

SHELL [ "/bin/bash", "-c" ]

# Install php extensions
RUN docker-php-ext-install pdo_mysql

# Install Xdebug
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

# Add composer global binaries to PATH
ENV PATH "$PATH:~/.composer/vendor/bin"

# Remove exec statement from base entrypoint script.
RUN sed -i '$d' /usr/local/bin/app-entrypoint.sh

# Set up entrypoint
WORKDIR    /var/www/html
COPY       bin/testing-entrypoint.sh /usr/local/bin/testing-entrypoint.sh
RUN        chmod 755 /usr/local/bin/testing-entrypoint.sh
ENTRYPOINT ["testing-entrypoint.sh"]