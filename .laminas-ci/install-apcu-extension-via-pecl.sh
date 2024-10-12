#!/bin/bash

PHP_VERSION="$1"

if ! [[ "${PHP_VERSION}" =~ 8\.4 ]]; then
  echo "mongodb is only installed from pecl for PHP 8.4, ${PHP_VERSION} detected."
  exit 0;
fi

set +e
apt install make

pecl install apcu
echo "extension=mongodb.so" > /etc/php/${PHP_VERSION}/mods-available/apcu.ini
phpenmod -v ${PHP} -s cli apcu