#!/bin/bash

crontab /root/crontab
cron -f &
redis-server --daemonize yes
php /var/www/html/cron/getCallStatus.php & > /proc/1/fd/1
php /var/www/html/cron/getQueueData.php & > /proc/1/fd/1
apache2-foreground
