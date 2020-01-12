#!/bin/bash

echo "RUN AS ROOT!"
sleep 4

# Install deps
apt-get update
apt-get -y install php-cli lighttpd php-cgi php-json wiringpi

# Enable PHP support in Lighttpd
lighttpd-enable-mod fastcgi
lighttpd-enable-mod fastcgi-php

# Restart HTTPD
/etc/init.d/lighttpd force-reload 


# Enable httpd at boot
systemctl enable lighttpd.service

# Register as a service
cp piot.service /etc/systemd/system/piot.service
systemctl enable piot.service
