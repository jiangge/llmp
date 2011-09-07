#!/bin/bash

PATH=/usr/local/mysql/bin/:$PATH
MYSQLCMD="mysql -uroot -puser22"

for db in `echo show databases | $MYSQLCMD | grep -v Database`; do
  for table in `echo show tables | $MYSQLCMD $db | grep -v Tables_in_`; do
    TABLE_TYPE=`echo show create table $table | $MYSQLCMD $db | sed -e's/.*ENGINE=\([[:alnum:]\]\+\)[[:space:]].*/\1/'|grep -v 'Create Table'`
    if [ $TABLE_TYPE = "InnoDB" ] ; then
      mysqldump $db $table > $db.$table.sql
      echo "ALTER TABLE $table ENGINE = MyISAM" | $MYSQLCMD $db
    fi
  done
done

