#!/bin/sh

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


PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH

DOMAIN=$1
MYSQL_PASSWORD=$2 
export DOMAIN
export MYSQL_PASSWORD

SERVER_ROOT=/srv/www
LTMPCONF=conf/ltmp.conf
MYUID=`id -u`
CWD=`pwd`
RC=$CWD/rc
SH=$CWD/sh
APP=$CWD/app

# BEGIN

if [ $# -lt 3 ]; then 
  echo "Usage: $0 <yourdomain.com> <mysql_password>"
  exit 1
fi

if [ $MYUID != "0" ]; then
    echo "Capton: No root, no running"
    exit 1
fi


apt-get update

REMOVED='libmysqlclient15off libmysqlclient15-dev libmysqlclient-dev mysql-common 
        apache2 apache2-doc apache2-mpm-prefork apache2-utils   
        apache2.2-common lighttpd php apache2-mpm-worker        
        mysql-client mysql-server lighttpd' 

apt-get remove -y `echo $REMOVED`

TO_INSTALL='build-essential gcc g++ make autoconf automake cmake libgamin-dev gamin
           wget cron bzip2 libzip-dev libc6-dev file rcconf flex libreadline-dev
           vim bison m4 gawk less make cpp binutils diffutils    
           unzip tar bzip2 libbz2-dev libncurses5 libncurses5-dev     
           libtool libevent-dev libpcre3 libpcre3-dev libpcrecpp0     
           libssl-dev zlibc openssl libsasl2-dev libxml2 libxml2-dev  
           libltdl3-dev libltdl-dev libmcrypt-dev zlib1g zlib1g-dev   
           libbz2-1.0 libbz2-dev libglib2.0-0 libglib2.0-dev libpng3  
           libfreetype6 libfreetype6-dev libjpeg62 libjpeg62-dev      
           libjpeg-dev libpng-dev libpng12-0 libpng12-dev curl        
           libcurl3 libmhash2 libmhash-dev libpq-dev libpq5 gettext   
           libncurses5-dev  libjpeg-dev            
           libpng12-dev libxml2-dev zlib1g-dev libfreetype6           
           libfreetype6-dev libssl-dev libcurl4-openssl-dev 
           libcurl4-gnutls-dev mcrypt memcached libev-dev libev3'

apt-get install -y `echo $TO_INSTALL` --force-yes
apt-get -fy install
apt-get -y autoremove 


if [ ! -f $LTMPCONF ]; then
  wget -c http://ltmp.net/$LTMPCONF
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
  [ -f ../sh/$cmdfile ] && ../sh/$cmdfile
  cd $CWD
done < $LTMPCONF

echo "====================  install completed ==========================="

cp $APP/probe.php $SERVER_ROOT/probe.php
cp $APP/php.php $SERVER_ROOT/php.php
cp $APP/index.html $SERVER_ROOT/index.html

cp $RC/rc.lighttpd /etc/init.d/lighttpd
cp $RC/rc.php-fpm /etc/init.d/php-fpm

chmod +x /etc/init.d/lighttpd 
chmod +x /etc/init.d/php

update-rc.d -f mysql defaults
update-rc.d -f php-fpm defaults
update-rc.d -f lighttpd defaults

/etc/init.d/mysql start
./app/mysql_secure_installation $MYSQL_PASSWORD
/etc/init.d/mysql stop

/etc/init.d/mysql start
/etc/init.d/php-fpm start
/etc/init.d/lighttpd start


echo "POWERED BY LTMP.NET"

