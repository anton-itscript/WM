#!/bin/bash


set MIGRATIONS="";
MIGRATIONS=$(ls -a /usr/share/nginx/html/protected/migrations | grep .php);
if [ -n "$MIGRATIONS" ] ; then
	echo $MIGRATIONS;
	
	cd /usr/share/nginx/html/protected/
	./yiic migrate  --interactive=0 
	
fi


