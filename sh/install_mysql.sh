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


rm -f CMakeCache.txt 

make uninstall

cmake . -DWITH_SSL=system -DENABLED_LOCAL_INFILE=ON -DWITH_EXTRA_CHARSETS=all -DENABLED_PROFILING=OFF -DWITH_DEBUG=OFF -DCMAKE_BUILD_TYPE=MinSizeRel -DWITH_BLACKHOLE_STORAGE_ENGINE=OFF -DWITH_PARTITION_STORAGE_ENGINE=OFF -DWITH_PERFSCHEMA_STORAGE_ENGINE=OFF -DMYSQL_MAINTAINER_MODE=OFF -DNO_ALARM=1 -DWITH_ARCHIVE_STORAGE_ENGINE=OFF -DWITH_EMBEDDED_SERVER=OFF -DWITH_FAST_MUTEXES=OFF -DWITH_FEDERATED_STORAGE_ENGINE=OFF -DWITH_INNOBASE_STORAGE_ENGINE=ON -DWITH_LIBEDIT=ON -DWITH_LIBWRAP=OFF -DWITH_PIC=OFF -DWITH_READLINE=OFF -DWITH_UNIT_TESTS=OFF -DWITH_VALGRIND=OFF -DWITH_ZLIB=system

make && make install

strip /usr/local/mysql/bin/* /usr/local/mysql/lib/* /usr/local/mysql/lib/plugin/*

groupadd mysql
useradd -s /bin/false -r -g mysql mysql

cd /usr/local/mysql
chown -R mysql .
chgrp -R mysql .
./scripts/mysql_install_db --user=mysql
chown -R root .
chown -R mysql data
mem=`free -m|grep Mem |awk '{ print $2 }' `

if [ $mem -le 128 ]; then
  cp support-files/my-small.cnf /etc/my.cnf 
  sed -i 's/^\[mysqld\]/\[mysqld\]\ndefault-storage-engine = MyISAM\nskip-innodb/g' /etc/my.cnf
elif [ $mem -le 512 ]; then
  cp support-files/my-small.cnf /etc/my.cnf

  sed -i 's/^#\(innodb_data_home_dir\)/\1/g' /etc/my.cnf
  sed -i 's/^#\(innodb_data_file_path\)/\1/g' /etc/my.cnf
  sed -i 's/^#\(innodb_log_group_home_dir\)/\1/g' /etc/my.cnf
  sed -i 's/^#\(innodb_buffer_pool_size\).*$/\1 = 8M /g' /etc/my.cnf
  sed -i 's/^#\(innodb_additional_mem_pool_size\).*$/\1 = 1M /g' /etc/my.cnf
  sed -i 's/^#\(innodb_log_file_size\).*$/\1 = 2M /g' /etc/my.cnf
  sed -i 's/^#\(innodb_log_buffer_size\).*$/\1 = 1M /g' /etc/my.cnf
  sed -i 's/^#\(innodb_flush_log_at_trx_commit\)/\1/g' /etc/my.cnf
  sed -i 's/^#\(innodb_lock_wait_timeout\)/\1/g' /etc/my.cnf
  sed -i 's/^#\(skip-networking\)/\1/g' /etc/my.cnf 
elif [ $mem -le 1024 ]; then
  cp support-files/my-large.cnf /etc/my.cnf
  sed -i 's/^#\(innodb_.*$\)/\1/g' /etc/my.cnf
elif [ $mem -le 2048 ]; then
  cp support-files/my-huge.cnf /etc/my.cnf 
  sed -i 's/^#\(innodb_.*$\)/\1/g' /etc/my.cnf
else
  cp support-files/my-innodb-heavy-4G.cnf /etc/my.cnf
  sed -i 's/^#\(innodb_.*$\)/\1/g' /etc/my.cnf 
fi 


cp support-files/mysql.server /etc/init.d/mysql
chmod +x /etc/init.d/mysql

#/etc/init.d/mysql start

#./bin/mysqladmin -u root -p $MYSQL_PASSWORD
#../app/mysql_secure_installation $MYSQL_PASSWORD
#/etc/init.d/mysql stop



