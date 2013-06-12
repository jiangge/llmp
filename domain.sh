#!/bin/bash

# Copyright (c) 2013, Jiang Jilin. All rights reserved.
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

OP=$1
DOMAIN=$2

MYUID=`id -u`
CWD=`pwd`
RC=$CWD/rc
SH=$CWD/sh
APP=$CWD/app

# BEGIN

LIGHTTPD_CONF=/etc/lighttpd/lighttpd.conf
WEB_ROOT_DIR=/srv/www/vhosts

if [ $# -lt 2 ]; then 
  echo "Usage: $0 <add/rm> <domain>"
  exit 1
fi

if [ $MYUID != "0" ]; then
    echo "Captain: No root, no running"
    exit 1
fi

case $OP in
  "add")
    $0 rm $DOMAIN 
    mkdir -p $WEB_ROOT_DIR/$DOMAIN 
    cat  >> $LIGHTTPD_CONF <<EOF 
\$HTTP["host"] =~ "(^|www\.)$DOMAIN" {
server.document-root = "$WEB_ROOT_DIR/$DOMAIN" 
}
#END
EOF
    ;;
  "rm")
    sed -i "/^\$HTTP\[\"host\"\] =~ .*$DOMAIN\"/,/\#END/c\ " $LIGHTTPD_CONF
    ;;
  *)
    echo "Usage: $0 <add/rm> <domain>"
    exit 1
    ;;
esac

#/etc/init.d/lighttpd reload
echo "OK" 

