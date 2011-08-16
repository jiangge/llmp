#!/bin/sh

mkdir -p /srv/www/vhosts/$DOMAIN/htdocs
mkdir -p /srv/www/htdocs
mkdir -p /etc/lighttpd 
mkdir -p /usr/local/lighttpd

./configure --prefix=/usr/local/lighttpd --enable-fast-install --with-libev --with-mysql=/usr/local/mysql/bin/mysql_config --with-openssl --with-pcre --with-zlib --with-bzip2 --with-fam --with-memcache 
make && make install 

cp doc/config/lighttpd.conf /etc/lighttpd/

groupadd lighttpd
useradd -s /bin/false -r -g lighttpd lighttpd

sed -i "s:default.example.com:$DOMAIN/g" /etc/lighttpd/conf.d/simple_vhost.conf


