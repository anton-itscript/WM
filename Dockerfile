FROM ubuntu:14.04.2

# Surpress Upstart errors/warning
RUN dpkg-divert --local --rename --add /sbin/initctl
RUN ln -sf /bin/true /sbin/initctl

# Let the conatiner know that there is no tty
ENV DEBIAN_FRONTEND noninteractive

# Update base image
# Add sources for latest nginx
# Install software requirements
RUN apt-get update && \
apt-get install -y software-properties-common && \
nginx=stable && \
add-apt-repository ppa:nginx/$nginx && \
apt-get update && \
apt-get upgrade -y && \
BUILD_PACKAGES="supervisor nginx php5-fpm git php5-mysql php-apc php5-curl php5-gd php5-intl php5-mcrypt php5-memcache php5-sqlite php5-tidy php5-xmlrpc php5-xsl php5-pgsql php5-mongo pwgen nano mc rsyslog cron screen mysql-server-5.6 pwgen setserial sendmail" && \
apt-get -y install $BUILD_PACKAGES && \
apt-get remove --purge -y software-properties-common && \
apt-get autoremove -y && \
apt-get clean && \
apt-get autoclean && \
echo -n > /var/lib/apt/extended_states && \
rm -rf /var/lib/apt/lists/* && \
rm -rf /usr/share/man/?? && \
rm -rf /usr/share/man/??_*

# tweak nginx config
RUN sed -i -e"s/worker_processes  1/worker_processes 5/" /etc/nginx/nginx.conf && \
sed -i -e"s/keepalive_timeout\s*65/keepalive_timeout 2/" /etc/nginx/nginx.conf && \
sed -i -e"s/keepalive_timeout 2/keepalive_timeout 2;\n\tclient_max_body_size 100m/" /etc/nginx/nginx.conf && \
echo "daemon off;" >> /etc/nginx/nginx.conf

# tweak php-fpm config
RUN sed -i -e "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g" /etc/php5/fpm/php.ini && \
sed -i -e "s/upload_max_filesize\s*=\s*2M/upload_max_filesize = 100M/g" /etc/php5/fpm/php.ini && \
sed -i -e "s/post_max_size\s*=\s*8M/post_max_size = 100M/g" /etc/php5/fpm/php.ini && \
sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /etc/php5/fpm/php-fpm.conf && \
sed -i -e "s/;catch_workers_output\s*=\s*yes/catch_workers_output = yes/g" /etc/php5/fpm/pool.d/www.conf && \
sed -i -e "s/pm.max_children = 5/pm.max_children = 9/g" /etc/php5/fpm/pool.d/www.conf && \
sed -i -e "s/pm.start_servers = 2/pm.start_servers = 3/g" /etc/php5/fpm/pool.d/www.conf && \
sed -i -e "s/pm.min_spare_servers = 1/pm.min_spare_servers = 2/g" /etc/php5/fpm/pool.d/www.conf && \
sed -i -e "s/pm.max_spare_servers = 3/pm.max_spare_servers = 4/g" /etc/php5/fpm/pool.d/www.conf && \
sed -i -e "s/pm.max_requests = 500/pm.max_requests = 200/g" /etc/php5/fpm/pool.d/www.conf

# fix ownership of sock file for php-fpm
RUN sed -i -e "s/;listen.mode = 0660/listen.mode = 0750/g" /etc/php5/fpm/pool.d/www.conf && \
find /etc/php5/cli/conf.d/ -name "*.ini" -exec sed -i -re 's/^(\s*)#(.*)/\1;\2/g' {} \;

# nginx site conf
RUN rm -Rf /etc/nginx/conf.d/* && \
rm -Rf /etc/nginx/sites-available/default && \
mkdir -p /etc/nginx/ssl/
ADD ./nginx-wm.conf /etc/nginx/sites-available/default.conf
RUN ln -s /etc/nginx/sites-available/default.conf /etc/nginx/sites-enabled/default.conf

# Supervisor Config
ADD ./supervisord.conf /etc/supervisord.conf

# Start Supervisord
ADD ./start.sh /start.sh
RUN chmod 755 /start.sh

# Setup Volume fo logs and runtimes


# Add application code

ADD ./application /usr/share/nginx/html/
ADD ./application_config_params /usr/share/nginx/html/protected/config/params/
RUN touch  /usr/share/nginx/html/protected/docker
RUN mkdir -p \
	/usr/share/nginx/html/protected/runtime \
	/usr/share/nginx/html/protected/locks \
	/usr/share/nginx/html/log \
	/usr/share/nginx/html/www/files/backups \
	/usr/share/nginx/html/www/files/hbr \
	/usr/share/nginx/html/www/files/schedule_reports \
	/usr/share/nginx/html/www/files/schedule_type_reports \
	/usr/share/nginx/html/www/assets \
	/usr/share/nginx/html/www/files/temp \
	/usr/share/nginx/html/www/files/xml_messages \
	/usr/share/nginx/html/www/files/weather_monitor_reports


ADD ./cron-www-data /var/spool/cron/crontabs/www-data

###########################################################################################################################################
### MYSQL


# Add MySQL configuration
ADD my.cnf /etc/mysql/conf.d/my.cnf
ADD mysqld_charset.cnf /etc/mysql/conf.d/mysqld_charset.cnf

RUN  rm -rf /var/lib/apt/lists/* && \
    rm /etc/mysql/conf.d/mysqld_safe_syslog.cnf && \
    if [ ! -f /usr/share/mysql/my-default.cnf ] ; then cp /etc/mysql/my.cnf /usr/share/mysql/my-default.cnf; fi && \
    mysql_install_db > /dev/null 2>&1 && \
    touch /var/lib/mysql/.EMPTY_DB


ENV MYSQL_USER=admin \
    MYSQL_PASS=**False** \
    REPLICATION_MASTER=**False** \
    REPLICATION_SLAVE=**False** \
    REPLICATION_USER=replica \
    REPLICATION_PASS=replica

# Add MySQL scripts
ADD import_sql.sh /import_sql.sh
ADD run.sh /run.sh

ADD migrations.sh /migrations.sh
RUN chmod 755 /migrations.sh


RUN mkdir /tmp/wm_clear_databases
RUN mkdir /var/wm_backups

ADD ./application/www/files/install/db.sql 		/tmp/wm_clear_databases/wm_docker.sql
ADD ./application/www/files/install/long_db.sql		/tmp/wm_clear_databases/wm_docker_long.sql

		#	ADD clearDb/wm_docker.sql 		/tmp/wm_clear_databases/wm_docker.sql
		#	ADD clearDb/wm_docker_long.sql		/tmp/wm_clear_databases/wm_docker_long.sql

ADD clearDb/wm_docker_old.sql   	/tmp/wm_clear_databases/wm_docker_old.sql
ADD clearDb/wm_docker_long_old.sql	/tmp/wm_clear_databases/wm_docker_long_old.sql



### ADD SH BACKUP CREATERS

ADD ./backup_creater.sh /backup_creater.sh
RUN chmod 755 /backup_creater.sh
########################################################

### ADD CRONS
ADD ./cron-mysql /var/spool/cron/crontabs/mysql
RUN chown  mysql.crontab /var/spool/cron/crontabs/mysql &&  chmod  0600 /var/spool/cron/crontabs/mysql
########################################################


# Add VOLUMEs to allow backup of config and databases

VOLUME  ["/etc/mysql", "/var/lib/mysql", "/var/wm_backups"]
########################################################################################################################################






##################################################

RUN chown -Rf www-data.www-data /usr/share/nginx/html/ &&  chmod -R 0777 /usr/share/nginx/html/ && chown  www-data.crontab /var/spool/cron/crontabs/www-data &&  chmod  0600 /var/spool/cron/crontabs/www-data

run uname -mrs


VOLUME ["/usr/share/nginx/html/log"]
VOLUME ["/usr/share/nginx/html/protected/runtime"]
VOLUME ["/usr/share/nginx/html/protected/nosqlvars"]
VOLUME ["/usr/share/nginx/html/protected/migrations"]
VOLUME ["/usr/share/nginx/html/www/files/backups"]
VOLUME ["/usr/share/nginx/html/www/files/schedule_reports"]
VOLUME ["/usr/share/nginx/html/www/files/schedule_type_reports"]
VOLUME ["/usr/share/nginx/html/www/files/xml_messages"]
VOLUME ["/usr/share/nginx/html/www/files/weather_monitor_reports"]

# Set access rights
RUN chown -Rf www-data.www-data /usr/share/nginx/html/  &&  chmod -R 0777 /usr/share/nginx/html/

# Expose Ports

EXPOSE 5001-5011

CMD ["/bin/bash", "/start.sh"]
