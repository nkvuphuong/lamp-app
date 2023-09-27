FROM php:8.1-apache-buster

 # Surpresses debconf complaints of trying to install apt packages interactively
# https://github.com/moby/moby/issues/4032#issuecomment-192327844
ARG DEBIAN_FRONTEND=noninteractive
#ENV CONTAINER_DOMAIN=${CONTAINER_DOMAIN}
#RUN echo "CONTAINER_DOMAIN ${CONTAINER_DOMAIN}"

 # Update and install necessary packages
RUN apt-get update --fix-missing && \
    apt-get upgrade -y && \
    apt-get --no-install-recommends install -y \
        apt-utils \
        nano \
        wget \
        dialog \
        libsqlite3-dev \
        libsqlite3-0 \
        default-mysql-client \
        zlib1g-dev \
        libzip-dev \
        libicu-dev \
        build-essential \
        git \
        curl \
        libonig-dev \
        iputils-ping \
        libcurl4 \
        libcurl4-openssl-dev \
        zip \
        openssl \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        gettext-base \
        pkg-config \
        libssl-dev \
        libmagickwand-dev && \
    rm -rf /var/lib/apt/lists/*
 # Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
 # Install xdebug
RUN pecl install xdebug-3.1.4 && \
    docker-php-ext-enable xdebug && \
    mkdir /var/log/xdebug
 # Install redis
RUN pecl install redis-5.3.3 && \
    docker-php-ext-enable redis
 # Install imagick
RUN pecl install imagick && \
    docker-php-ext-enable imagick
 # Install other PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli pdo_sqlite bcmath curl zip intl mbstring gettext calendar exif gd sockets

RUN pecl install mongodb \
    &&  echo "extension=mongodb.so" > $PHP_INI_DIR/conf.d/mongo.ini

 # Insure an SSL directory exists
RUN mkdir -p /etc/apache2/ssl

#RUN echo "CONTAINER_DOMAIN ${CONTAINER_DOMAIN}"

#COPY ./.docker/apache2/conf/vhost.conf /etc/apache2/sites-available/vhost_new.conf
#RUN export CONTAINER_DOMAIN=$CONTAINER_DOMAIN && \
#    envsubst < "./.docker/apache2/conf/new-vhost.conf > "/etc/apache2/sites-available/000-default.conf"

#RUN envsubst < "/etc/apache2/sites-available/vhost_new.conf" > "/etc/apache2/sites-available/000-default.conf"

 # Enable SSL support
RUN a2enmod ssl && a2enmod rewrite

 # Enable apache modules
RUN a2enmod rewrite headers

 # Cleanup
RUN rm -rf /usr/src/*
