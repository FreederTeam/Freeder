FROM debian:stable
MAINTAINER Niols <niols@niols.net>

# Install packages
RUN apt-get update
RUN apt-get install apache2 apache2-utils php5 php5-dev php5-sqlite php5-curl

# Configure the database
ADD init.sh /tmp/init.sh
RUN chmod 775 /tmp/init.sh
RUN rm /tmp/init.sh

# Get Freeder
RUN apt-get install git
RUN git clone https://github.com/Niols/Freeder /var/www/Freeder

# apache user has UID 1000 and GID 100
# You may have to change these values to match with your current "host" user
RUN usermod -u 1000 -g 100 apache

# Launch unit test
RUN /var/www/Freeder/unit/check.sh

ADD start.sh /start.sh

ENTRYPOINT ["/bin/bash", "-e", "/start.sh"]

EXPOSE 80
