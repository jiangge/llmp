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


#export PHP_AUTOCONF=/usr/local/autoconf-2.13/bin/autoconf
#export PHP_AUTOHEADER=/usr/local/autoconf-2.13/bin/autoheader 
groupadd nobody
useradd -s /bin/false -r -g nobody nobody

./buildconf --force
./configure --prefix=/usr/local/php --with-config-file-path=/usr/local/php/etc --with-mysql=/usr/local/mysql --with-mysqli=/usr/local/mysql/bin/mysql_config --with-iconv-dir --with-freetype-dir --with-jpeg-dir --with-png-dir --with-zlib --with-libxml-dir=/usr --enable-xml --enable-magic-quotes --enable-safe-mode --enable-bcmath --enable-shmop --enable-inline-optimization --with-curl --with-curlwrappers --enable-mbregex --enable-fpm --enable-mbstring --with-mcrypt --enable-ftp --with-gd --enable-gd-native-ttf --with-openssl --with-mhash --enable-pcntl --enable-sockets --with-xmlrpc --enable-zip --enable-soap --without-pear --with-gettext --with-pdo-mysql=/usr/local/mysql --with-readline --with-pcre-dir --without-sqlite3 --without-cdb --without-pdo-sqlite --without-sqlite --disable-fileinfo --disable-debug --with-bz2 --without-gdbm --disable-dba --without-sqlite  --disable-phar --without-pspell --disable-wddx --disable-sysvmsg --disable-sysvshm --disable-sysvsem 
make 
make install

cp php.ini-production /usr/local/php/etc/php.ini 
cp /usr/local/php/etc/php-fpm.conf.default /usr/local/php/etc/php-fpm.conf

strip /usr/local/php/bin/* /usr/local/php/sbin/*
cd ..

if [ ! -f xcache-3.0.1.tar.bz2 ]; then 
  wget -c http://xcache.lighttpd.net/pub/Releases/3.0.1/xcache-3.0.1.tar.bz2
fi

# xcache
tar xvf xcache-3.0.1.tar.bz2
cd xcache-3.0.1
/usr/local/php/bin/phpize
./configure --enable-xcache --with-php-config=/usr/local/php/bin/php-config
make && make install 
cat xcache.ini >> /usr/local/php/etc/php.ini 
cd ..


# memcache
if [ ! -f memcache-3.0.5.tgz ]; then
  wget -c http://pecl.php.net/get/memcache-3.0.5.tgz
fi

tar zxvf memcache-3.0.5.tgz
cd memcache-3.0.5/
/usr/local/php/bin/phpize
./configure --with-php-config=/usr/local/php/bin/php-config
make && make install 
cd ../ 

strip /usr/local/php/lib/php/extensions/no-debug-non-zts-20100525/*

PHPINI=/usr/local/php/etc/php.ini

sed -i 's/cgi\.fix_pathinfo=.*$/cgi\.fix_pathinfo=1/g' $PHPINI
#sed -i 's:^zend.*xcache\.so:zend_extension = /usr/local/php/lib/php/extensions/no-debug-non-zts-20100525/xcache.so:g' $PHPINI
sed -i 's:^\(xcache\.size\).*$:\1 = 32M :g' $PHPINI

cat  >> $PHPINI <<EOF 
extension_dir = "/usr/local/php/lib/php/extensions/no-debug-non-zts-20100525/"
extension = "memcache.so"
EOF

FPMCONF=/usr/local/php/etc/php-fpm.conf

sed -i 's:^listen.*$:listen = /tmp/php-fastcgi.socket:g' $FPMCONF
sed -i 's:^;\(pm.max_children\).*$:\1 = 10 :g' $FPMCONF
sed -i 's:^;\(pm.start_servers\).*$:\1 = 1 :g' $FPMCONF
sed -i 's:^;\(pm.min_spare_servers\).*$:\1 = 1 :g' $FPMCONF
sed -i 's:^;\(pm.max_spare_servers\).*$:\1 = 9 :g' $FPMCONF
sed -i 's:^;\(pm.max_requests\).*$:\1 = 500 :g' $FPMCONF

