#!/bin/bash
touch /usr/share/nginx/logs/error.log
cp /etc/nginx/sites/application.conf /etc/nginx/sites-available/default.conf
cp /usr/local/etc/php/conf/php-fpm.conf /usr/local/etc/php-fpm.conf
cp /usr/local/etc/php/conf/www.conf  /usr/local/etc/php-fpm.d/www.conf
cp /usr/local/etc/php/conf/docker-vars.ini /usr/local/etc/php/conf.d/docker-vars.ini

####### NGINX CONFIG
# Set custom webroot
if [ ! -z "$WEBROOT" ]; then
 sed -i "s#root {env_webroot};#root ${WEBROOT};#g" /etc/nginx/sites-available/default.conf
else
 sed -i "s#root {env_webroot};#root /var/www/html;#g" /etc/nginx/sites-available/default.conf
fi

# Display Version Details or not
if [[ "$HIDE_NGINX_HEADERS" == "0" ]] ; then
 sed -i "s/server_tokens off;/server_tokens on;/g" /etc/nginx/nginx.conf
fi

# Pass real-ip to logs when behind ELB, etc
if [[ "$REAL_IP_HEADER" == "1" ]] ; then
 sed -i "s/#real_ip_header X-Forwarded-For;/real_ip_header X-Forwarded-For;/" /etc/nginx/sites-available/default.conf
 sed -i "s/#set_real_ip_from/set_real_ip_from/" /etc/nginx/sites-available/default.conf
 if [ ! -z "$REAL_IP_FROM" ]; then
  sed -i "s#172.16.0.0/12#$REAL_IP_FROM#" /etc/nginx/sites-available/default.conf
 fi
fi

# Set HTTP_HOST
if [ ! -z "$FAST_CGI_HTTP_HOST" ]; then
  sed -i "s/#{additional_params};/fastcgi_param HTTP_HOST $(echo $FAST_CGI_HTTP_HOST | sed -E 's/[\/&]/\\&/g');\n        #{additional_params};/g" /etc/nginx/sites-available/default.conf
fi

# Set server_name
if [ ! -z "$NGINX_SERVER_NAME" ]; then
  sed -i "s/server_name {env_server_name};/server_name ${NGINX_SERVER_NAME};/g" /etc/nginx/sites-available/default.conf
else
  sed -i "s/server_name {env_server_name};/server_name _;/g"
fi

# Share same permissions between container and host
if [ ! -z "$PUID" ]; then
  if [ -z "$PGID" ]; then
    PGID=${PUID}
  fi
  deluser nginx
  delgroup nginx
  addgroup --system --gid ${PGID} nginx
  adduser --system --ingroup nginx --disabled-password --home /var/cache/nginx --disabled-login --uid ${PUID} nginx
  chown -Rf nginx:nginx ${ROOT}
 else
  # Always chown webroot for better mounting
  chown -Rf nginx:nginx ${ROOT}
fi

ln -s /etc/nginx/sites-available/default.conf /etc/nginx/sites-enabled/default
mkdir /etc/nginx/logs
touch /etc/nginx/logs/error.log

# Start supervisord and services
exec /usr/bin/supervisord -n -c /etc/supervisord.conf