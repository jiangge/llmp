#!/bin/sh

usage()
{
  echo "Usage: $0 <yourdomain.com> <mysql_username> <mysql_password>"
}

if [ $# -lt 3 ]; then 
  usage
  exit 1
fi

PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH

DOMAIN=$1
MYSQL_USER=$2
MYSQL_PASSWORD=$3 

UID=`id -u`
CWD=`pwd`

if [ $UID != "0" ]; then
    echo "Capton: No root, no running"
    exit 1
fi


apt-get update

REMOVED="libmysqlclient15off libmysqlclient15-dev mysql-common \
        apache2 apache2-doc apache2-mpm-prefork apache2-utils   \
        apache2.2-common lighttpd php apache2-mpm-worker        \
        mysql-client mysql-server" 


for PACK in $REMOVED
do
  apt-get remove -y $PACK
done

TO_INSTALL="build-essential gcc g++ make autoconf automake \
           wget cron bzip2 libzip-dev libc6-dev file rcconf flex \
           vim bison m4 gawk less make cpp binutils diffutils    \
           unzip tar bzip2 libbz2-dev libncurses5 libncurses5-dev     \
           libtool libevent-dev libpcre3 libpcre3-dev libpcrecpp0     \
           libssl-dev zlibc openssl libsasl2-dev libxml2 libxml2-dev  \
           libltdl3-dev libltdl-dev libmcrypt-dev zlib1g zlib1g-dev   \
           libbz2-1.0 libbz2-dev libglib2.0-0 libglib2.0-dev libpng3  \
           libfreetype6 libfreetype6-dev libjpeg62 libjpeg62-dev      \
           libjpeg-dev libpng-dev libpng12-0 libpng12-dev curl        \
           libcurl3 libmhash2 libmhash-dev libpq-dev libpq5 gettext   \
           libncurses5-dev libcurl4-gnutls-dev libjpeg-dev            \
           libpng12-dev libxml2-dev zlib1g-dev libfreetype6           \
           libfreetype6-dev libssl-dev libcurl3 libcurl4-openssl-dev  \
           libcurl4-gnutls-dev mcrypt"

for packages in $TO_INSTALL 
do 
  apt-get install -y $packages --force-yes
  apt-get -fy install
  apt-get -y autoremove 
done

LTMPCONF="ltmp.conf"

if [ ! -f ltmp.conf ]; then
  wget -c http://ltmp.net/$LTMPCONF

while read url cmdfile
do
  if [ -z $url ]; then
    continue
  fi

  filename=`basename url`
  if [ ! -f $filename ]; then
    wget -c $url
  fi

  tar xvf $filename

  DIR=`echo "$filename" | sed -n 's/\(.*\)\.tar\.*$' `
  cd $DIR 
  chmod 0755 $cmdfile
  $cmdfile 
  CD $CWD
done < $LTMPCONF

echo "====================  install completed ==========================="
#phpinfo
cat >/home/wwwroot/phpinfo.php<<eof
<?
phpinfo();
?>
eof

#prober
tar zxvf p.tar.gz
cp p.php /home/wwwroot/p.php

cp conf/index.html /home/wwwroot/index.html

#start up
echo "Download new nginx init.d file......"
wget -c http://soft.vpser.net/lnmp/ext/init.d.nginx
cp init.d.nginx /etc/init.d/nginx
chmod +x /etc/init.d/nginx
update-rc.d -f mysql defaults
update-rc.d -f nginx defaults
update-rc.d -f php-fpm defaults

cd $CWD
cp lnmp /root/lnmp
chmod +x /root/lnmp
cp vhost.sh /root/vhost.sh
chmod +x /root/vhost.sh
/etc/init.d/mysql start
/etc/init.d/php-fpm start
/etc/init.d/nginx start
echo "===================================== Check install ==================================="
clear
if [ -s /usr/local/nginx ]; then
  echo "/usr/local/nginx [found]"
  else
  echo "Error: /usr/local/nginx not found!!!"
fi

if [ -s /usr/local/php ]; then
  echo "/usr/local/php [found]"
  else
  echo "Error: /usr/local/php not found!!!"
fi

if [ -s /usr/local/mysql ]; then
  echo "/usr/local/mysql [found]"
  else
  echo "Error: /usr/local/mysql not found!!!"
fi

echo "========================== Check install ================================"

echo "Install LTMP V0.7 completed! enjoy it."
echo "========================================================================="
echo "LTMP V0.7 for Debian VPS , Written by Jiang "
echo "========================================================================="
echo ""
echo "For more information please visit http://www.ltmp.net/"
echo ""
echo "lnmp status manage: /root/lnmp {start|stop|reload|restart|kill|status}"
echo "default mysql root password:$mysqlrootpwd"
echo "phpinfo : http://$domain/phpinfo.php"
echo "phpMyAdmin : http://$domain/phpmyadmin/"
echo "Prober : http://$domain/p.php"
echo ""
echo "The path of some dirs:"
echo "mysql dir:   /usr/local/mysql"
echo "php dir:     /usr/local/php"
echo "nginx dir:   /usr/local/nginx"
echo "web dir :     /home/wwwroot"
echo ""
echo "========================================================================="
fi
/root/lnmp status
netstat -ntl
