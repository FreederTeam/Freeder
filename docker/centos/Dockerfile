FROM centos:centos6
MAINTAINER Phyks <phyks@phyks.me>

# Install packages
RUN yum update -y
RUN yum install -y httpd php php-pdo php-xml

# Configure the database
ADD init.sh /tmp/init.sh
RUN chmod 775 /tmp/init.sh
RUN rm /tmp/init.sh

# Get Freeder
RUN yum install -y git
RUN git clone https://github.com/FreederTeam/Freeder /var/www/Freeder

# apache user has UID 1000 and GID 100
# You may have to change these values to match with your current "host" user
RUN usermod -u 1000 -g 100 apache

# Launch unit test
RUN /var/www/Freeder/unit/check.sh

ADD start.sh /start.sh

ENTRYPOINT ["/bin/bash", "-e", "/start.sh"]

EXPOSE 80
