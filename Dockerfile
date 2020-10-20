FROM php:7.4-cli

RUN apt-get update && apt-get install -y \
    git zip unzip

WORKDIR /usr/src/jaeger2zipkin

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === trim(file_get_contents('https://composer.github.io/installer.sig'))) { echo 'Installer verified'; } else { echo 'Installer invalid'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \ 
    && php -r "unlink('composer-setup.php');"

COPY composer.json composer.json
COPY composer.lock composer.lock
COPY main.php main.php

RUN php composer.phar install

ENTRYPOINT [ "php", "main.php" ]