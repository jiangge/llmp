#!/bin/sh

echo "mysql install: pwd=`pwd`"

cmake . -DCMAKE_BUILD_TYPE:STRING=MinSizeRel -DWITH_SSL:STRING=system -DENABLED_LOCAL_INFILE:BOOL=ON -DWITH_EXTRA_CHARSETS:STRING=all 

make && make install

groupadd mysql
useradd -s /bin/false -r -g mysql mysql

cd /usr/local/mysql
chown -R mysql .
chgrp -R mysql .
scripts/mysql_install_db --user=mysql
chown -R root .
chown -R mysql data
cp support-files/my-medium.cnf /etc/my.cnf
bin/mysqld_safe --user=mysql &
cp support-files/mysql.server /etc/init.d/mysql
chmod +x /etc/init.d/mysql
bin/mysqladmin -u root password "$MYSQL_PASSWORD"
/etc/init.d/mysql restart



