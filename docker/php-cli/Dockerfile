FROM php:8.0-cli
RUN apt-get update \
    && apt-get install -y git sshpass iputils-ping

# Install Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN docker-php-ext-install pdo_mysql

RUN mkdir /root/.ssh && touch /root/.ssh/known_hosts
RUN #ssh-keyscan -H preview-ssh-server >> ~/.ssh/known_hosts

WORKDIR /app
EXPOSE 9000