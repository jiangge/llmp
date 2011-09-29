#!/bin/bash

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

if [ $# -lt 2 ]; then
  echo "$0 <mysql password for root>"
fi

MYSQL_PASSWORD=$1

PATH=/usr/local/mysql/bin/:$PATH
MYSQLCMD="mysql -uroot -p $MYSQL_PASSWORD"

for db in `echo show databases | $MYSQLCMD | grep -v Database`; do
  for table in `echo show tables | $MYSQLCMD $db | grep -v Tables_in_`; do
    TABLE_TYPE=`echo show create table $table | $MYSQLCMD $db | sed -e's/.*ENGINE=\([[:alnum:]\]\+\)[[:space:]].*/\1/'|grep -v 'Create Table'`
    if [ $TABLE_TYPE = "InnoDB" ] ; then
      mysqldump $db $table > $db.$table.sql
      echo "ALTER TABLE $table ENGINE = MyISAM" | $MYSQLCMD $db
    fi
  done
done

