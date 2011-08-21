#!/bin/sh

./configure --prefix=/usr/local/autoconf-2.13
make && make install

strip /usr/local/autoconf-2.13/bin/*
