FROM php:8.2-fpm

WORKDIR /app

RUN apt-get update

RUN apt-get -y install git zip libpq-dev

RUN apt-get -y install git zip libpq-dev default-mysql-client

RUN docker-php-ext-install pdo pdo_mysql

RUN curl -sL https://getcomposer.org/installer | php -- --install-dir /usr/bin --filename composer

RUN pecl install xdebug

CMD ["php-fpm"]