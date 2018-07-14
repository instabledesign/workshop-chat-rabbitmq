FROM php:7.2-fpm

# Update Repository
RUN apt-get update

# Install requirement lib
RUN apt-get install -y libzip-dev \
    librabbitmq-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev

# Install needed packages
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN pecl install mongodb && docker-php-ext-enable mongodb
RUN pecl install zip && docker-php-ext-enable zip
RUN pecl install amqp && docker-php-ext-enable amqp

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"
