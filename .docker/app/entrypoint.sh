#!/usr/bin/env bash

vendorFolder=/app/vendor

if ! [ -f "$vendorFolder" ]; then
    printf "\n- Composer install \n"
    composer install && composer dumpautoload
    chown 1000:1000 -R /app/vendor
else
  printf "\n- Composer já instalado, seguindo... \n"
fi

/usr/local/bin/rr serve -c /app/.rr.yaml