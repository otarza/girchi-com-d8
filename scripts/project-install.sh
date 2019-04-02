#!/bin/bash

# Load global configurations.
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source $DIR/config

SETTINGS_LOCAL="web/sites/default/settings.local.php"
SETTINGS_PRODUCTION="environments/settings.production.php"
SETTINGS_STAGING="environments/settings.staging.php"
SETTINGS_DEVELOP="environments/settings.develop.php"

SERVICES="web/sites/default/services.local.yml"
SERVICES_PRODUCTION="environments/services.production.yml"
SERVICES_STAGING="environments/services.staging.yml"
SERVICES_DEVELOP="environments/services.develop.yml"

cd $DOCROOT

chmod +w web/sites/default

# Put random salt in settings.php
HASHSTRING=$(LC_ALL=C tr -dc 'A-Za-z0-9-_' </dev/urandom | head -c 64)
# Different commands for string replace by OS (darwin* is MACOSX)
if [[ "$OSTYPE" == darwin* ]]; then
  sed -i '' "s/_HASH_SALT_HERE_REPLACED_BY_PROJECT_INSTALL_SH_/$HASHSTRING/g" "web/sites/default/settings.php"
else
  sed -i "s/_HASH_SALT_HERE_REPLACED_BY_PROJECT_INSTALL_SH_/$HASHSTRING/g" "web/sites/default/settings.php"
fi

# Copy environment config to web/sites/default/settings.local.php
if [ "$1" = 'prod' ]; then
  # Copy from .dist if settings.production.php doesn't exist
  if [ ! -f $SETTINGS_PRODUCTION ]; then
    cp $SETTINGS_PRODUCTION.dist $SETTINGS_LOCAL
  else
    cp $SETTINGS_PRODUCTION $SETTINGS_LOCAL
  fi

  # Copy from .dist if services.production.yml doesn't exist
  if [ ! -f $SERVICES_PRODUCTION ]; then
    cp $SERVICES_PRODUCTION.dist $SERVICES
  else
    cp $SERVICES_PRODUCTION $SERVICES
  fi
elif [ "$1" = 'staging' ]; then
  # Copy from .dist if settings.staging.php doesn't exist
  if [ ! -f $SETTINGS_STAGING ]; then
    cp $SETTINGS_STAGING.dist $SETTINGS_LOCAL
  else
    cp $SETTINGS_STAGING $SETTINGS_LOCAL
  fi

  # Copy from .dist if services.staging.yml doesn't exist
  if [ ! -f $SERVICES_STAGING ]; then
    cp $SERVICES_STAGING.dist $SERVICES
  else
    cp $SERVICES_STAGING $SERVICES
  fi
else
  # Copy from .dist if settings.develop.php doesn't exist
  if [ ! -f $SETTINGS_DEVELOP ]; then
    cp $SETTINGS_DEVELOP.dist $SETTINGS_LOCAL
  else
    cp $SETTINGS_DEVELOP $SETTINGS_LOCAL
  fi

  # Copy from .dist if services.develop.yml doesn't exist
  if [ ! -f $SERVICES_DEVELOP ]; then
    cp $SERVICES_DEVELOP.dist $SERVICES
  else
    cp $SERVICES_DEVELOP $SERVICES
  fi
fi

chmod 755 $SETTINGS_LOCAL
chmod -w web/sites/default

cd web/

/usr/bin/env PHP_OPTIONS="-d sendmail_path=`which true`" \
$DOCROOT/vendor/bin/drush site-install config_installer -y --config-dir=../config/sync \
--account-name=$ADMIN_USER --account-pass=$ADMIN_PASS --account-mail=$ADMIN_EMAIL

# Import initial content

## Import from temporary repo
### Import files first
#$DOCROOT/vendor/bin/drupal content-sync:import $DOCROOT/vendor/gdi/gdi-content/file/
### Import nodes after files are imported
#$DOCROOT/vendor/bin/drupal content-sync:import $DOCROOT/vendor/gdi/gdi-content/node/

## Import from sa_general_helper
#$DOCROOT/vendor/bin/drupal content-sync:import $DOCROOT/web/modules/custom/sa_general_helper/content/

## Clear cache
#$DOCROOT/vendor/bin/drush cr all
