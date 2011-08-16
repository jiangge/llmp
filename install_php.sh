#!/bin/sh

./buildconf --force
./configure --prefix=/usr/local/php --with-config-file-path=/usr/local/php/etc --with-mysql=/usr/local/mysql --with-mysqli=/usr/local/mysql/bin/mysql_config --with-iconv-dir --with-freetype-dir --with-jpeg-dir --with-png-dir --with-zlib --with-libxml-dir=/usr --enable-xml --enable-discard-path --enable-magic-quotes --enable-safe-mode --enable-bcmath --enable-shmop --enable-sysvsem --enable-inline-optimization --with-curl --with-curlwrappers --enable-mbregex --enable-fastcgi --enable-fpm --enable-force-cgi-redirect --enable-mbstring --with-mcrypt --enable-ftp --with-gd --enable-gd-native-ttf --with-openssl --with-mhash --enable-pcntl --enable-sockets --with-xmlrpc --enable-zip --enable-soap --without-pear --with-gettext --with-mime-magic --with-pdo-mysql=/usr/local/mysql --with-readline --with-pcre-dir
make ZEND_EXTRA_LIBS='-liconv'
make install

cp php.ini-production /usr/local/php/etc/php.ini 

strip /usr/local/php/bin/php-cgi

if [ ! -f xcache-1.3.2.tar.bz2 ]; then 
  wget -c http://xcache.lighttpd.net/pub/Releases/1.3.2/xcache-1.3.2.tar.bz2
fi

# xcache
tar -zxf xcache-*.tar.bz2
cd xcache
/usr/local/php/bin/phpize
./configure --enable-xcache
make && make install 
cat xcache.ini >> /etc/php.ini 


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


