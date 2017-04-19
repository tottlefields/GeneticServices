#!/bin/bash

if [ -e "${HOME}/downloaded_check" ]
then
	echo "exports have been downloaded"
	rm -rf ${HOME}/exported_orders.out
	touch ${HOME}/exported_orders.out
	rm -rf ${HOME}/downloaded_check
else
	echo "exports file not downloaded yet"
fi

/usr/local/bin/wp --path=/var/www/vhosts/ahtdnatesting.co.uk/httpdocs/ eval-file /home/eschofield/GitHub/GeneticServices/scripts/export-orders.php >> /home/eschofield/exported_orders.out

