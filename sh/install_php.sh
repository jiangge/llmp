#!/bin/sh

export PHP_AUTOCONF=/usr/local/autoconf-2.13/bin/autoconf
export PHP_AUTOHEADER=/usr/local/autoconf-2.13/bin/autoheader 

groupadd nobody
useradd -s /bin/false -r -g nobody nobody

make uninstall
./buildconf --force
./configure --prefix=/usr/local/php --with-config-file-path=/usr/local/php/etc --with-mysql=/usr/local/mysql --with-mysqli=/usr/local/mysql/bin/mysql_config --with-iconv-dir --with-freetype-dir --with-jpeg-dir --with-png-dir --with-zlib --with-libxml-dir=/usr --enable-xml --enable-magic-quotes --enable-safe-mode --enable-bcmath --enable-shmop --enable-inline-optimization --with-curl --with-curlwrappers --enable-mbregex --enable-fpm --enable-mbstring --with-mcrypt --enable-ftp --with-gd --enable-gd-native-ttf --with-openssl --with-mhash --enable-pcntl --enable-sockets --with-xmlrpc --enable-zip --enable-soap --without-pear --with-gettext --with-pdo-mysql=/usr/local/mysql --with-readline --with-pcre-dir --without-sqlite3 --without-cdb --without-pdo-sqlite --without-sqlite --disable-fileinfo --disable-debug --disable-rpath --with-bz2 --without-gdbm --disable-dba --without-sqlite  --disable-phar --without-pspell --disable-wddx --disable-sysvmsg --disable-sysvshm --disable-sysvsem 
make 
make install

cp php.ini-production /usr/local/php/etc/php.ini 
cp /usr/local/php/etc/php-fpm.conf.default /usr/local/php/etc/php-fpm.conf

strip /usr/local/php/bin/* /usr/local/php/sbin/*

if [ ! -f xcache-1.3.2.tar.bz2 ]; then 
  wget -c http://xcache.lighttpd.net/pub/Releases/1.3.2/xcache-1.3.2.tar.bz2
fi

# xcache
tar xvf xcache-1.3.2.tar.bz2
cd xcache-1.3.2
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

PHPINI=/usr/local/php/etc/php.ini

sed -i 's/cgi\.fix_pathinfo=.*$/cgi\.fix_pathinfo=1/g' $PHPINI
sed -i 's:^zend.*xcache\.so:zend_extension = /usr/local/php/lib/php/extensions/no-debug-non-zts-20090626/xcache.so:g' $PHPINI
sed -i 's:^\(xcache\.size\).*$:\1 = 10M :g' $PHPINI

cat  >> $PHPINI <<EOF 
extension_dir = "/usr/local/php/lib/php/extensions/no-debug-non-zts-20090626/"
extension = "memcache.so"
EOF

FPMCONF=/usr/local/php/etc/php-fpm.conf

sed -i 's:^listen.*$:listen = /tmp/php-fastcgi.socket:g' $FPMCONF
sed -i 's:^;\(pm.max_children\).*$:\1 = 10 :g' $FPMCONF
sed -i 's:^;\(pm.start_servers\).*$:\1 = 1 :g' $FPMCONF
sed -i 's:^;\(pm.min_spare_servers\).*$:\1 = 1 :g' $FPMCONF
sed -i 's:^;\(pm.max_spare_servers\).*$:\1 = 9 :g' $FPMCONF
sed -i 's:^;\(pm.max_requests\).*$:\1 = 500 :g' $FPMCONF

