#!/bin/sh

./buildconf --force
./configure --prefix=/usr/local/php --with-config-file-path=/usr/local/php/etc --with-mysql=/usr/local/mysql --with-mysqli=/usr/local/mysql/bin/mysql_config --with-iconv-dir --with-freetype-dir --with-jpeg-dir --with-png-dir --with-zlib --with-libxml-dir=/usr --enable-xml --enable-discard-path --enable-magic-quotes --enable-safe-mode --enable-bcmath --enable-shmop --enable-sysvsem --enable-inline-optimization --with-curl --with-curlwrappers --enable-mbregex --enable-fastcgi --enable-fpm --enable-force-cgi-redirect --enable-mbstring --with-mcrypt --enable-ftp --with-gd --enable-gd-native-ttf --with-openssl --with-mhash --enable-pcntl --enable-sockets --with-xmlrpc --enable-zip --enable-soap --without-pear --with-gettext --with-mime-magic --with-pdo-mysql=/usr/local/mysql --with-readline --with-pcre-dir
make ZEND_EXTRA_LIBS='-liconv'
make install

cp php.ini-production /usr/local/php/etc/php.ini 

strip /usr/local/php/bin/php-cgi


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

# ZendOptimizer
if [ `getconf WORD_BIT` = '32' ] && [ `getconf LONG_BIT` = '64' ] ; then
  wget -c http://soft.vpser.net/web/zend/ZendOptimizer-3.3.9-linux-glibc23-x86_64.tar.gz
  tar zxvf ZendOptimizer-3.3.9-linux-glibc23-x86_64.tar.gz
  mkdir -p /usr/local/zend/
  cp ZendOptimizer-3.3.9-linux-glibc23-x86_64/data/5_2_x_comp/ZendOptimizer.so /usr/local/zend/
else
  wget -c http://soft.vpser.net/web/zend/ZendOptimizer-3.3.9-linux-glibc23-i386.tar.gz
  tar zxvf ZendOptimizer-3.3.9-linux-glibc23-i386.tar.gz
  mkdir -p /usr/local/zend/
  cp ZendOptimizer-3.3.9-linux-glibc23-i386/data/5_2_x_comp/ZendOptimizer.so /usr/local/zend/
fi

cat >>/usr/local/php/etc/php.ini<<EOF
;eaccelerator

;ionCube

[Zend Optimizer] 
zend_optimizer.optimization_level=1 
zend_extension="/usr/local/zend/ZendOptimizer.so" 
EOF

 
