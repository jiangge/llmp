#!/bin/sh

# INSTALL LIGHTTPD 

mkdir -p /srv/www/vhosts/$DOMAIN/htdocs
mkdir -p /srv/www/htdocs
mkdir -p /etc/lighttpd 
mkdir -p /usr/local/lighttpd

groupadd lighttpd
useradd -s /bin/false -r -g lighttpd lighttpd

./configure --prefix=/usr/local/lighttpd --enable-fast-install --with-libev --with-mysql=/usr/local/mysql/bin/mysql_config --with-openssl --with-pcre --with-zlib --with-bzip2 --with-fam --with-memcache 

make && make install 

strip /usr/local/lighttpd/sbin/* 

cp -r doc/config/* /etc/lighttpd/ 

sed -i "s:default.example.com:$DOMAIN/g" /etc/lighttpd/conf.d/simple_vhost.conf 
sed -i "s:"mod_access",:"mod_access","mod_accesslog"/g" /etc/lighttpd/modules.conf
cat >> /etc/lighttpd/modules.conf <<EOF  
include "conf.d/compress.conf"
include "conf.d/status.conf"
include "conf.d/simple_vhost.conf"
include "conf.d/trigger_b4_dl.conf.conf"
include "conf.d/fastcgi.conf"
include "conf.d/secdownload.conf"
include "conf.d/expire.conf"
EOF



