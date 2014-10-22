#!/bin/bash

# Configure timezone for PHP
sed -i.bak s/';date.timezone ='/'date.timezone = "Europe\/Paris"'/ /etc/php.ini
sed -i.bak s/'#ServerName www.example.com:80'/'ServerName localhost:80'/ /etc/httpd/conf/httpd.conf
