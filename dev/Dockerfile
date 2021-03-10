FROM debian:stretch

#Install extra packages for PHP repo
RUN apt update && apt -y install apt-transport-https ca-certificates gnupg
#Register PHP repo
ADD ./packages.sury.org.list /etc/apt/sources.list.d/
#Download and install PHP repo key
ADD https://packages.sury.org/php/apt.gpg /tmp/php.gpg
RUN apt-key add /tmp/php.gpg && rm /tmp/php.gpg
#Install PHP packages - XML and ZIP extensions are required for Composer
RUN apt update && apt -y install php7.1-cli php7.1-xml php7.1-zip php-xdebug
#Add XDebug extension configuration
ADD ./xdebug.ini /etc/php/7.1/mods-available/
RUN chmod 644 /etc/php/7.1/mods-available/xdebug.ini
ENV PHP_IDE_CONFIG="serverName=Docker"
#Create folder for PHP App
RUN mkdir -p /var/php_app
WORKDIR /var/php_app
VOLUME /var/php_app
#Setup development server for PHP App
EXPOSE 80
CMD mkdir -p ./web && php -S 0.0.0.0:80 -t ./web

#It is possible to make image thinner with '&& apt clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*' after each apt install.
#It will remove unnecessary cache and documentation files after package installation but it will make installation of new packages during development longer.
