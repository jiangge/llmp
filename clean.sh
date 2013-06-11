#!/bin/bash

/etc/init.d/lighttpd stop
/etc/init.d/php-fpm stop
/etc/init.d/mysql stop

cd lighttpd*
make distclean || make clean
cd ../php-*
make distclean || make clean
cd ../mysql-*
make distclean || make clean



