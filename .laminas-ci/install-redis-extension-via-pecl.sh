#!/bin/bash

PHP_VERSION="$1"

if ! [[ "${PHP_VERSION}" =~ 8\.3 ]]; then
  echo "redis is only installed from pecl for PHP 8.3, ${PHP_VERSION} detected."
  exit 0;
fi

set +e
apt install make

pecl install redis
echo "extension=redis.so" > /etc/php/${PHP_VERSION}/mods-available/redis.ini
phpenmod -v ${PHP} -s cli redis