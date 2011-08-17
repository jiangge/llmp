#!/bin/sh
#
# php     Startup script for the php server
#
# chkconfig: - 85 15
# description: Lightning fast webserver with light system requirements
#
# processname: php
# config: /etc/php/php.conf
# config: /etc/sysconfig/php
# pidfile: /var/run/php.pid
#
# Note: pidfile is assumed to be created
# by php (config: server.pid-file).
# If not, uncomment 'pidof' line.

# Source function library
#. /etc/rc.d/init.d/functions

if [ -f /etc/sysconfig/php ]; then
	. /etc/sysconfig/php
fi

if [ -z "$LIGHTTPD_CONF_PATH" ]; then
	LIGHTTPD_CONF_PATH="/etc/php/php.conf"
fi

prog="php"
php="/usr/local/php/bin/php"
RETVAL=0

start() {
	echo -n $"Starting $prog: "
	$php -f $LIGHTTPD_CONF_PATH
	RETVAL=$?
	echo
	[ $RETVAL -eq 0 ] && touch /var/lock/$prog
	return $RETVAL
}

stop() {
	echo -n $"Stopping $prog: "
	killall $php
	RETVAL=$?
	echo
	[ $RETVAL -eq 0 ] && rm -f /var/lock/$prog
	return $RETVAL
}

reload() {
	echo -n $"Reloading $prog: "
	killall $php -HUP
	RETVAL=$?
	echo
	return $RETVAL
}

case "$1" in
	start)
		start
		;;
	stop)
		stop
		;;
	restart)
		stop
		start
		;;
	condrestart)
		if [ -f /var/lock/$prog ]; then
			stop
			start
		fi
		;;
	reload)
		reload
		;;
	status)
		status $php
		RETVAL=$?
		;;
	*)
		echo $"Usage: $0 {start|stop|restart|condrestart|reload|status}"
		RETVAL=1
esac

exit $RETVAL
