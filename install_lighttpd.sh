#!/bin/sh

./configure --enable-fast-install --with-libev --with-mysql=/usr/local/mysql/bin/mysql_config --with-openssl --with-pcre --with-zlib --with-bzip2 --with-fam --with-memcache CFLAGS=" -O3 " 
make && make install
mkdir -p /usr/local/lighttpd/etc

cp doc/config/lighttpd.conf /usr/local/lighttpd/etc/
