#!/usr/bin/env bash

vendorFolder=/app/vendor

if [ ! -d "$vendorFolder" ]; then
    mkdir /app/vendor
    printf "\n- Composer install \n"
    cd /app && composer install && composer dump-autoload
    chown 1000:1000 -R /app/vendor
    touch /app/composer-installed && chown 1000:1000 /app/composer-installed
else
    printf "\n- Diretório vendor criado, seguindo... \n"
fi

while [ ! -f "/app/composer-installed" ]
do
  printf "\n- Aguardando instalação das dependências do composer... \n"
  sleep 2s
done

/usr/local/bin/rr serve -c /app/.rr.yaml