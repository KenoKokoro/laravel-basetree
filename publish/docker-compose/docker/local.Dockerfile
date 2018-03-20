FROM php:7.2.3-fpm-stretch

MAINTAINER Jimmy <stefan.brankovik@cosmicdevelopment.com>

### NGINX
ARG NGINX_VERSION=1.10.3-1+deb9u1
ARG FFMPEG_VERSION=7:3.2.10-1~deb9u1
ARG YARN_VERSION=1.5.1-1

RUN apt-get update && apt-get install -y --no-install-recommends apt-utils gnupg2 apt-transport-https
### Required repositories
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN curl -sL https://deb.nodesource.com/setup_8.x | bash -

### NGINX
RUN addgroup --system nginx \
    && adduser --system --ingroup nginx --disabled-password --home /var/cache/nginx --disabled-login nginx \
    && apt-get install -y nginx=${NGINX_VERSION}
# Sites folders and link configuration
RUN unlink /etc/nginx/sites-enabled/default \
    && mkdir -p /usr/share/nginx/logs \
    && touch /usr/share/nginx/logs/error.log

# Required packages ( supervisor and composer )
RUN apt-get install -y wget supervisor curl ca-certificates dialog git \
    musl-dev libpng-dev libffi-dev vim libsqlite3-dev libicu-dev libxml2-dev

### PHP
RUN docker-php-ext-install pdo_mysql pdo_sqlite mysqli gd exif intl json soap dom zip opcache bcmath \
    && docker-php-source delete \
    && mkdir -p /run/nginx \
    && mkdir -p /var/log/supervisor
RUN EXPECTED_COMPOSER_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig) \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '${EXPECTED_COMPOSER_SIGNATURE}') { echo 'Composer.phar Installer verified'; } else { echo 'Composer.phar Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

### NODEJS + YARN
RUN apt-get install -y yarn=${YARN_VERSION}
RUN apt-get install -y nodejs

## FFMPEG
RUN apt-get install -y ffmpeg=${FFMPEG_VERSION}

### CLEANUP
RUN rm -rf /var/cache/* \
    && apt-get purge -y gcc musl-dev linux-headers libffi-dev python-dev autoconf && apt-get autoremove -y

### Make scipt executable
ADD local/scripts/start.sh /entrypoint.sh

CMD ["/entrypoint.sh"]