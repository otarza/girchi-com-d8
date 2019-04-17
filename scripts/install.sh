#!/bin/bash

#links
WEBSITE="http://girchi.docker.localhost"
MAILHOG="http://mailhog.girchi.docker.localhost"
PMA="http://pma.girchi.docker.localhost"

#colors 
NONE='\033[00m'
BOLD='\033[1m'
GREEN='\033[01;32m'

clear
echo -e "${BOLD}Starting up containers\n\v${NONE}"
docker-compose pull
docker-compose up -d --remove-orphans
sleep 5
clear
echo  -e "${BOLD}Running pre-install script\n\v${NONE}"
sleep 1
./scripts/pre-install.sh
clear
echo  -e "${BOLD}Running composer json\n\v${NONE}"
sleep 1
docker-compose exec php composer install
sleep 2
clear
echo  -e "${BOLD}Installing drupal\n\v${NONE}"
sleep 1
docker-compose exec php drush si --existing-config --account-pass=1234  -vvv -y
sleep 2
clear
echo  -e "${BOLD}Importing translations\n\v${NONE}"
sleep 1
docker-compose exec php drush language-import
sleep 2
clear
echo  -e "${BOLD}Importing config\n\v${NONE}"
sleep 1
docker-compose exec php drush cim -y -vvv
sleep 2
clear
echo  -e "${BOLD}Project is ready${NONE} ${RED}\n\vWebsite${NONE}  - " $GREEN$WEBSITE$NONE"${RED}\n\vMailHog${NONE} - " $GREEN$MAILHOG$NONE"${RED}\n\vphpMyAdmin${NONE} - " $GREEN$PMA$NONE"\n"
sleep 3
