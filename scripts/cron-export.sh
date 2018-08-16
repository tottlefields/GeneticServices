#!/bin/bash

if [ -e "${HOME}/downloaded_check" ]
then
	echo "exports have been downloaded"
	rm -rf ${HOME}/exported_orders.out
	rm -rf ${HOME}/wp_animals_updates.tsv
	touch ${HOME}/exported_orders.out
	rm -rf ${HOME}/downloaded_check
else
	echo "exports file not downloaded yet"
fi

/usr/local/bin/wp --path=/var/www/vhosts/ahtdnatesting.co.uk/httpdocs/ eval-file /home/eschofield/GitHub/GeneticServices/scripts/export-orders.php >> /home/eschofield/exported_orders.out

mysql -B -N -e 'select distinct id as AnimalID, `user-id` as ClientID, name as Breed, `pet-name` as PetName, `registered-name` as RegisteredName, `registration-number` as Registration, STR_TO_DATE(`birth-date`, "%d/%m/%Y") as BirthDate, sex, colour, `tattoo-chip` as TattooChip from wp_animals left outer join wp_terms on breed=term_id inner join wp_postmeta where last_updated >=DATE_SUB(NOW(),INTERVAL 1 HOUR) and meta_key="animal-id-pm" and meta_value=id' > ${HOME}/wp_animals_updates.tsv
