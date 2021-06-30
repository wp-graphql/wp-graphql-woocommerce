ARG PHP_VERSION
FROM wordpress:php${PHP_VERSION}-apache

ARG XDEBUG_VERSION=2.9.6

RUN apt-get update; \
	apt-get install -y --no-install-recommends \
	# WP-CLI dependencies.
	bash less default-mysql-client git \
	# MailHog dependencies.
	msmtp \
	# Dockerize dependencies.
	wget;

# Install XDebug 3
RUN echo "Installing XDebug 3 (in disabled state)" \
    && pecl install xdebug \
    && mkdir -p /usr/local/etc/php/conf.d/disabled \
    && echo "zend_extension=xdebug" > /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini \
    && echo "xdebug.mode=develop,debug,coverage" >> /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini \
    && echo "xdebug.max_nesting_level=512" >> /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini \
    ;

# Set xdebug configuration off by default. See the entrypoint.sh.
ENV USING_XDEBUG=0

# Install PDO MySQL driver.
RUN docker-php-ext-install pdo_mysql

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

# Add composer global binaries to PATH
ENV PATH "$PATH:~/.composer/vendor/bin"

# Set PHPUnit version.
ARG PHPUNIT_VERSION="<=8.1"
# Install wp-browser globally
RUN composer global require --optimize-autoloader \
	wp-cli/wp-cli-bundle \
    lucatume/wp-browser \
    codeception/module-asserts \
    codeception/module-cli \
    codeception/module-db \
    codeception/module-filesystem \
    codeception/module-phpbrowser \
    codeception/module-rest \
    codeception/module-webdriver \
    codeception/util-universalframework \
    league/factory-muffin \
    league/factory-muffin-faker \
	stripe/stripe-php \
	"phpunit/phpunit:${PHPUNIT_VERSION}"

# Remove exec statement from base entrypoint script.
RUN sed -i '$d' /usr/local/bin/docker-entrypoint.sh

COPY local/php.ini /usr/local/etc/php/php.ini
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf
RUN service apache2 restart

# Set project environmental variables
ENV WP_ROOT_FOLDER="/var/www/html"
ENV WORDPRESS_DB_HOST=${DB_HOST}
ENV WORDPRESS_DB_PORT=${DB_PORT}
ENV WORDPRESS_DB_USER=${DB_USER}
ENV WORDPRESS_DB_PASSWORD=${DB_PASSWORD}
ENV WORDPRESS_DB_NAME=${DB_NAME}
ENV PLUGINS_DIR="${WP_ROOT_FOLDER}/wp-content/plugins"
ENV PROJECT_DIR="${PLUGINS_DIR}/wp-graphql-woocommerce"

# Set up Apache
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf
RUN a2enmod rewrite


WORKDIR /var/www/html
# Set codecept wrapper
COPY bin/codecept /usr/local/bin/codecept
RUN chmod 755 /usr/local/bin/codecept

# Set stall script.
ADD https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh /usr/local/bin/wait-for-it
RUN chmod 755 /usr/local/bin/wait-for-it

# Set up entrypoint
COPY bin/entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN  chmod 755 /usr/local/bin/app-entrypoint.sh
ENTRYPOINT ["app-entrypoint.sh"]
CMD ["apache2-foreground"]
