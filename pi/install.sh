#!/usr/bin/bash

# Install deps
apt-get update
apt-get -y install php5-cli lighttpd php5-cgi

# Enable PHP support in Lighttpd
lighttpd-enable-mod fastcgi
lighttpd-enable-mod fastcgi-php

# Restart HTTPD
/etc/init.d/lighttpd force-reload

