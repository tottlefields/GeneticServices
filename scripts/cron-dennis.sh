#!/bin/bash

#cd /home/eschofield/GitHub/GeneticServices/scripts

#mysql -B -N -e "insert ignore into tmp_order_post_lookup (post_id,post_title) select distinct ID,post_title from wp_posts where ID>=25766 and post_type ='orders'" designaweb_ahtdnaDb;
mysql -B -N -e "insert ignore into tmp_order_post_lookup (post_id,post_title) select distinct ID,post_title from wp_posts where ID>=35580 and post_type ='orders'" designaweb_ahtdnaDb;
mysql -B -N -e "update tmp_order_post_lookup set OrderID=substring(post_title,8,5) where post_id>35580" designaweb_ahtdnaDb;

#cat ${HOME}/last_order
cat ${HOME}/last_order | /usr/local/bin/wp --path=/var/www/vhosts/ahtdnatesting.co.uk/httpdocs/ eval-file /home/eschofield/GitHub/GeneticServices/scripts/wp-dennis.php
#cat ${HOME}/last_order

for id in `mysql -B -N -e"select group_concat(post_id) from tmp_order_post_lookup inner join wp_postmeta using (post_id) where meta_key='paid-pm' and meta_value=1 and OrderID not in (select OrderID from orders) and OrderID > 0 group by OrderID order by OrderID desc;"`; 
do
	echo $id | /usr/local/bin/wp --path=/var/www/vhosts/ahtdnatesting.co.uk/httpdocs/ eval-file /home/eschofield/GitHub/GeneticServices/scripts/wp-dennis-checker.php ;
done

mysqldump designaweb_ahtdnaDb animal audit_trail client order_tests orders vet 2>/dev/null > ${HOME}/dennis.sql
