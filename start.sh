#!/bin/bash

# Disable Strict Host checking for non interactive git clones

chown -Rf www-data.www-data /usr/share/nginx/html/ &&  chmod -R 0777 /usr/share/nginx/html/

mkdir -p -m 0700 /root/.ssh
echo -e "Host *\n\tStrictHostKeyChecking no\n" >> /root/.ssh/config

# Tweak nginx to match the workers to cpu's

procs=$(cat /proc/cpuinfo |grep processor | wc -l)
sed -i -e "s/worker_processes 5/worker_processes $procs/" /etc/nginx/nginx.conf

set MIGRATIONS="";
MIGRATIONS=$(ls -a /usr/share/nginx/html/protected/migrations | grep .php);
if [ -n "$MIGRATIONS" ] ; then
	echo $MIGRATIONS;
	
	cd /usr/share/nginx/html/protected/
	./yiic migrate  --interactive=0 
	
fi



# Start supervisord and services
/usr/bin/supervisord -n -c /etc/supervisord.conf



