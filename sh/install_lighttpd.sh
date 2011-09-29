#!/bin/sh
#
# Copyright (c) 2011, Jiang Jilin. All rights reserved.
#
# This file is part of LTMP.
# 
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 3 of the License, or
#  (at your option) any later version.

#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.

#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.  


# INSTALL LIGHTTPD 

mkdir -p /srv/www/vhosts/$DOMAIN/htdocs
mkdir -p /srv/www/htdocs
mkdir -p /srv/www/cgi-bin/
mkdir -p /etc/lighttpd 
mkdir -p /usr/local/lighttpd
mkdir -p /var/cache/lighttpd/compress
mkdir -p /var/lib/lighttpd/sockets 
mkdir -p /var/log/lighttpd
chmod a+w /var/log/lighttpd
chmod a+w /var/lib/lighttpd/sockets


groupadd lighttpd
useradd -s /bin/false -r -g lighttpd lighttpd

make uninstall

./configure --prefix=/usr/local/lighttpd --enable-fast-install --with-libev --with-mysql=/usr/local/mysql/bin/mysql_config --with-openssl --with-pcre --with-zlib --with-bzip2 --with-fam --with-memcache 

make && make install 

strip /usr/local/lighttpd/sbin/*  /usr/local/lighttpd/lib/*

cp -r doc/config/* /etc/lighttpd/ 

sed -i "s:default\.example\.com:$DOMAIN/g" /etc/lighttpd/conf.d/simple_vhost.conf 
sed -i "s:"mod_access",:"mod_access","mod_accesslog":g" /etc/lighttpd/modules.conf
cat >> /etc/lighttpd/modules.conf <<EOF  
include "conf.d/compress.conf"
include "conf.d/status.conf"
include "conf.d/simple_vhost.conf"
#include "conf.d/trigger_b4_dl.conf"
include "conf.d/fastcgi.conf"
include "conf.d/secdownload.conf"
include "conf.d/expire.conf"
EOF

cat >> /etc/lighttpd/conf.d/fastcgi.conf <<EOF
fastcgi.server = ( ".php" =>
                   ( "php-local" =>
                     (
                       "socket" => "/tmp/php-fastcgi.socket",
                       "max-procs" => 1,
                       "broken-scriptfilename" => "enable",
                     )
                   ),
                   ( "php-tcp" =>
                     (
                       "host" => "127.0.0.1",
                       "port" => 9000,
                       "check-local" => "disable",
                       "broken-scriptfilename" => "enable",
                     )
                   ),

                   ( "php-num-procs" =>
                     (
                       "socket" => "/tmp/php-fastcgi.socket",
                       "bin-environment" => (
                         "PHP_FCGI_CHILDREN" => "6",
                         "PHP_FCGI_MAX_REQUESTS" => "500",
                       ),
                       "max-procs" => 1,
                       "broken-scriptfilename" => "enable",
                     )
                   ),
                )
EOF


