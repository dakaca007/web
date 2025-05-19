#!/bin/bash

service php8.1-fpm start
cd /var/www/html/flaskapp && gunicorn -b 0.0.0.0:8000 app:app &
su - appuser -c "gotty --permit-write --port 3000 --key /home/appuser/.gotty.key --cert /home/appuser/.gotty.crt bash" &
nginx -g "daemon off;"