#!/bin/bash

echo "RUN AS ROOT!"
sleep 4

# Install deps
apt-get update
apt-get -y install php5-cli lighttpd php5-cgi php5-json wiringpi

# Enable PHP support in Lighttpd
lighttpd-enable-mod fastcgi
lighttpd-enable-mod fastcgi-php

# Restart HTTPD
/etc/init.d/lighttpd force-reload 


# Enable httpd at boot
systemctl enable lighttpd.service

# Register as a service
systemctl enable /home/pi/piot/pi/piot.service
