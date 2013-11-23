#!/bin/sh

# Copyright (c) 2011, Jiang Jilin. All rights reserved.
#
# This file is part of LLMP.
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


. ~/.profile

SERVER_ROOT=/srv/www/vhosts/$DOMAIN/htdocs
LLMPCONF=conf/llmp.conf
MYUID=`id -u`
CWD=`pwd`
RC=$CWD/rc
SH=$CWD/sh
APP=$CWD/app

# BEGIN

if [ $MYUID != "0" ]; then
    echo "Captain: No root, no running"
    exit 1
fi

/etc/init.d/lighttpd stop
/etc/init.d/mysql stop
/etc/init.d/php-fpm stop

if [ ! -f $LLMPCONF ]; then
  wget -c http://llmp.org/$LLMPCONF
fi

while read url cmdfile
do
  if [ -z $url ]; then
    continue
  fi

  furl=`echo "$url" | sed -e 's#\(.*tar\.[gb]z2\?\).*#\1#g'  `

  filename=`basename $furl`
  if [ ! -f $filename ]; then
    wget -c $url -O $filename
  fi

  tar xvf $filename

  DIR=`echo "$filename" | sed -e 's/\(.*\)\.tar\..*/\1/g' `
  cd $DIR 
  [ -f ../sh/$cmdfile.update ] && ../sh/$cmdfile.update 
  cd $CWD
done < $LLMPCONF

echo "====================  Update completed ===========================" 

cd $CWD

/etc/init.d/mysql start
/etc/init.d/php-fpm start
/etc/init.d/lighttpd start



echo "POWERED BY LLMP.ORG"

