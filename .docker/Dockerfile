FROM ubuntu:24.04

ENV DEBICANONICAL_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    php-cli \
    php-curl \
    php-mbstring \
    php-xml \
    php-xdebug \
    unzip \
    zip \
    curl \
    ca-certificates \
    jq \
    curl \
    less \
    tree \
    net-tools \
    && rm -rf /var/lib/apt/lists/*

COPY ./xdebug.ini /etc/php/8.3/cli/conf.d/99-xdebug.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER ubuntu

ENV XDEBUG_TRIGGER=1

WORKDIR /app

CMD ["bash"]
