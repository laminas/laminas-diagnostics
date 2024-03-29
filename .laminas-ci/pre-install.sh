#!/bin/bash

WORKING_DIRECTORY=$2
JOB=$3
PHP_VERSION=$(echo "${JOB}" | jq -r '.php')


if [ ! -z "$GITHUB_BASE_REF" ] && [[ "$GITHUB_BASE_REF" =~ ^[0-9]+\.[0-9] ]]; then
  readarray -td. TARGET_BRANCH_VERSION_PARTS <<<"${GITHUB_BASE_REF}.";
  unset 'TARGET_BRANCH_VERSION_PARTS[-1]';
  declare -a TARGET_BRANCH_VERSION_PARTS
  MAJOR_OF_TARGET_BRANCH=${TARGET_BRANCH_VERSION_PARTS[0]}
  MINOR_OF_TARGET_BRANCH=${TARGET_BRANCH_VERSION_PARTS[1]}

  export COMPOSER_ROOT_VERISON="${MAJOR_OF_TARGET_BRANCH}.${MINOR_OF_TARGET_BRANCH}.99"
  echo "Exported COMPOSER_ROOT_VERISON as ${COMPOSER_ROOT_VERISON}"
fi

${WORKING_DIRECTORY}/.laminas-ci/install-mongodb-extension-via-pecl.sh "${PHP_VERSION}" || exit 1
${WORKING_DIRECTORY}/.laminas-ci/install-apcu-extension-via-pecl.sh "${PHP_VERSION}" || exit 1
${WORKING_DIRECTORY}/.laminas-ci/install-memcached-extension-via-pecl.sh "${PHP_VERSION}" || exit 1
${WORKING_DIRECTORY}/.laminas-ci/install-redis-extension-via-pecl.sh "${PHP_VERSION}" || exit 1
