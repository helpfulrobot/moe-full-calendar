#!/bin/bash

# NPM Version
NPM_VERSION="4.0.0"

# Formatting variables
BOLD=$(tput bold)
NORMAL=$(tput sgr0)
GREEN=$(tput setaf 2)
LBLUE=$(tput setaf 6)
RED=$(tput setaf 1)
PURPLE=$(tput setaf 5)

#==============
# Initiate and clone submodule
#==============
INSTALL_SUBMODULE() {

	# Make sure you're in a git directory before trying to create a submodule
	if [ -d ".git" ]; then
		git submodule init
		git submodule add https://gitlab.cwp.govt.nz/build-tools/grunt-base.git
	else
		git init
		submodule;
	fi

}

#==============
# Remove any old files and import new ones to project root
#==============
INSTALL_GRUNT() {

	# remove Gruntfile.js if exist
	if [ -f Gruntfile.js ]
	then
		rm Gruntfile.js
	fi

	# remove package.json if exist
	if [ -f package.json ]
	then
		rm package.json
	fi

	# Create syslinks
	ln -s grunt-base/Gruntfile.js ./
	ln -s grunt-base/package.json ./

	# Install package.json dependencies
	npm install

	clear;

	echo 'COMPLETE: Type "grunt" to start watching!'

}

UPDATE_PROJECT() {

	# Update node
	npm cache clean -f
	npm install -g n
	n stable
	ln -sf /usr/local/n/versions/node/${NPM_VERSION}/bin/node /usr/bin/node

	# Update npm
	npm install -g npm

	# Update local project
	grunt package-update

	# Remove current node files
	rm -rf node_modules/

	# Install new local packages
	npm install

}

#==============
# Menu
#==============
main() {

	clear

	until [ "$REPLY" = "q" ]
	do
		echo "${BOLD}#-----------------------------------------------#${NORMAL}"
		echo
		echo "${GREEN}1. Project setup ${NORMAL}"
		echo "Install grunt and required modules for a clean project"
		echo
		echo "${GREEN}2. Project update ${NORMAL}"
		echo "Update system npm/node versions along with packages for this project"
		echo
		echo '#-----------------------------------------------#'
		echo 'q.  Quit'
		echo
		read -p 'Command : ' REPLY
		case $REPLY in

			1) INSTALL_SUBMODULE && INSTALL_GRUNT
			;;

			2) UPDATE_PROJECT
			;;

			[Qq]*) clear && exit
			;;
		esac
	done

}

#==============
# Call the menu
#==============
main
