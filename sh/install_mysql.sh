#!/bin/sh

echo "mysql install: pwd=`pwd`"

rm -f CMakeCache.txt 

cmake . -DWITH_SSL=system -DENABLED_LOCAL_INFILE=ON -DWITH_EXTRA_CHARSETS=all -DENABLED_PROFILING=OFF -DWITH_DEBUG=OFF -DWITH_READLINE=1 -DCMAKE_BUILD_TYPE=MinSizeRel -DWITH_BLACKHOLE_STORAGE_ENGINE=OFF -DWITH_PARTITION_STORAGE_ENGINE=OFF -DWITH_PERFSCHEMA_STORAGE_ENGINE=OFF 

make && make install

strip /usr/local/mysql/bin/*

groupadd mysql
useradd -s /bin/false -r -g mysql mysql

cd /usr/local/mysql
chown -R mysql .
chgrp -R mysql .
scripts/mysql_install_db --user=mysql
chown -R root .
chown -R mysql data
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

#bin/mysqld_safe --user=mysql &
cp support-files/mysql.server /etc/init.d/mysql
chmod +x /etc/init.d/mysql

/etc/init.d/mysql start

bin/mysqladmin -u root password "$MYSQL_PASSWORD"
#./bin/mysql_secure_installation
/etc/init.d/mysql stop



