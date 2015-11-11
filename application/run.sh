#!/bin/bash
##########
echo "RUN"
##########

##############
# EMPTY FOLDER
##############

rm -rf \
	protected/runtime \
	protected/locks \
	log \
	www/files/backups \
	www/files/hbr \
	www/files/schedule_reports \
	www/files/schedule_type_reports \


mkdir -p \
	protected/runtime \
	protected/locks \
	log \
	www/files/backups \
	www/files/hbr \
	www/files/schedule_reports \
	www/files/schedule_type_reports \
	www/assets \



chmod -R 755 ./
chmod -R 777 \
	protected/runtime \
	protected/locks \
	log \
	www/assets \
	www/files/backups \
	www/files/hbr \
	www/files/schedule_reports \
	www/files/schedule_type_reports \



 
########                           
# CONFIG                          
########                          


if [ ! -f www/sendmail/sendmail.ini ]; then
	cp www/sendmail/def-sendmail.ini www/sendmail/sendmail.ini
fi

	
chmod 777 \
	www/sendmail/sendmail.ini \

#######
# CHOWN
#######

#chown -R webuser:webuser ./

###########
echo "DONE"
###########