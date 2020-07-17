#!/bin/bash
# CHeck if certificate exists
if [ ! -f "$NGINX_DHPARAM" ]; then
  echo "Generating new DHPARAM FILE"
  openssl dhparam -out /etc/nginx/ssl/dhparam.pem ${DHPARAM_SIZE}
fi

if [ "$SSL" != "SNAKEOIL" ]; then
  exit
fi

SNAKEOIL_COMMAND="openssl req -x509 -nodes -days 365 -newkey rsa:2048 -subj \"/C=US/ST=MI/O=CCR/CN=${APP_HOSTNAME}\" -keyout \"${NGINX_SSL_KEY}\" -out \"${NGINX_SSL_CERT}\""

if [ ! -f "$NGINX_SSL_KEY" ] || [ ! -f "$NGINX_SSL_CERT" ]; then
  echo "Generating new snakeoil certificate for ${APP_HOSTNAME}"
  eval $SNAKEOIL_COMMAND
else
  #Check if certificate is still valid
  expirationdate=$(date -d "$(: | cat ${NGINX_SSL_CERT} \
                              | openssl x509 -text \
                              | grep 'Not After' \
                              | awk '{print $4,$5,$7}')" '+%s'); 
  in7days=$(($(date +%s) + 604800));
  if [ $in7days -gt $expirationdate ]; then
    echo "Snakeoil expiring soon... regenerating."
    eval $SNAKEOIL_COMMAND
  else 
    echo "Snakeoil still valid."  
fi
    
fi








