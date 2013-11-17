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


PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH

DOMAIN=$1
MYSQL_PASSWORD=$2 
export DOMAIN
export MYSQL_PASSWORD

SERVER_ROOT=/srv/www/vhosts/$DOMAIN/htdocs
LLMPCONF=conf/llmp.conf
MYUID=`id -u`
CWD=`pwd`
RC=$CWD/rc
SH=$CWD/sh
APP=$CWD/app

# BEGIN

if [ $# -lt 2 ]; then 
  echo "Usage: $0 <yourdomain.com> <mysql_password for root>"
  exit 1
fi

if [ $MYUID != "0" ]; then
    echo "Captain: No root, no running"
    exit 1
fi


REMOVED='libmysqlclient15 libmysqlclient15-dev libmysqlclient-dev mysql-common 
        apache2 apache2-doc apache2-mpm-prefork apache2-utils  apache2.2-bin
        apache2.2-common lighttpd php-fpm apache2-mpm-worker        
        mysql-client mysql-server lighttpd libcurl-dev ' 



#aptitude install -y apt-spy
#cp /etc/apt/sources.list /etc/apt/sources.list.bak
#apt-spy update
#apt-spy -d stable -a america -t 5 
#cp /etc/apt/sources.list.d/apt-spy.list /etc/apt/sources.list

TO_INSTALL='build-essential psmisc gcc g++ make autoconf automake cmake libgamin-dev gamin  
           wget cron bzip2 libzip-dev libc6-dev file rcconf flex libreadline-dev  
           vim bison m4 gawk less make cpp binutils diffutils  sendmail
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

which aptitude
if [ $? -eq 0 ]; then 
  aptitude remove -y `echo $REMOVED` 
  aptitude safe-upgrade -y
  aptitude update -y

  aptitude install -y `echo $TO_INSTALL` 
  aptitude -fy install
  aptitude -y autoremove 
else
  which yum
  if [ $? -eq 0 ]; then 
    yum -y remove `echo $REMOVED` 
    #yum -y install `echo $TO_INSTALL` 
    for packages in patch make gcc gcc-c++ gcc-g77 flex bison file libtool libtool-libs autoconf kernel-devel libjpeg libjpeg-devel libpng libpng-devel libpng10 libpng10-devel gd gd-devel freetype freetype-devel libxml2 libxml2-devel zlib zlib-devel glib2 glib2-devel bzip2 bzip2-devel libevent libevent-devel ncurses ncurses-devel curl curl-devel e2fsprogs e2fsprogs-devel krb5 krb5-devel libidn libidn-devel openssl openssl-devel vim-minimal nano fonts-chinese gettext gettext-devel ncurses-devel gmp-devel pspell-devel unzip libmcrypt libmcrypt-devel readline readline-devel pcre-devel gamin gamin-devel;
    do yum -y install $packages; done 
  else
    echo "The OS not supported"
    exit 1
  fi
fi 

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
  [ -f ../sh/$cmdfile ] && ../sh/$cmdfile
  cd $CWD
done < $LLMPCONF

echo "====================  install completed ==========================="

cat >> ~/.profile <<EOF
export PATH=/usr/local/mysql/bin:/usr/local/mysql/sbin/:$PATH
EOF

. ~/.profile

sed -i 's!^2\(:23:respawn:/sbin/getty 38400 tty2\)!#2\1!g' /etc/inittab
sed -i 's!^3\(:23:respawn:/sbin/getty 38400 tty3\)!#3\1!g' /etc/inittab
sed -i 's!^4\(:23:respawn:/sbin/getty 38400 tty4\)!#4\1!g' /etc/inittab
sed -i 's!^5\(:23:respawn:/sbin/getty 38400 tty5\)!#5\1!g' /etc/inittab
sed -i 's!^6\(:23:respawn:/sbin/getty 38400 tty6\)!#6\1!g' /etc/inittab
init q

#cp $APP/probe.php $SERVER_ROOT/probe.php
cp $APP/php.php $SERVER_ROOT/php.php
cp $APP/index.html $SERVER_ROOT/index.html
cp -a $APP/images $SERVER_ROOT/

cp $RC/rc.lighttpd /etc/init.d/lighttpd
cp $RC/rc.php-fpm /etc/init.d/php-fpm

chmod +x /etc/init.d/lighttpd 
chmod +x /etc/init.d/php-fpm

update-rc.d -f mysql defaults
update-rc.d -f php-fpm defaults
update-rc.d -f lighttpd defaults

/etc/init.d/mysql start
cd $CWD
mysqladmin -u root password $MYSQL_PASSWORD
./app/mysql_secure_installation $MYSQL_PASSWORD
/etc/init.d/mysql stop

/etc/init.d/mysql start
/etc/init.d/php-fpm start
/etc/init.d/lighttpd start



echo "POWERED BY LLMP.ORG"

