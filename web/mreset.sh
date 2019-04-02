#!/usr/bin/env bash

../vendor/bin/drush pmu gff_migrate
../vendor/bin/drush en gff_migrate
../vendor/bin/drush mrs gff_migrate_news
