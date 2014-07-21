<?php
/**
 * Plugin CILIEN
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
function cilien_declarer_tables_auxiliaires($tables_auxiliaires){
	
	// table des liens
	$spip_cilien = array(
			"url"	=> "VARCHAR(255) NOT NULL",
			"objet"	=> "VARCHAR (25) DEFAULT '' NOT NULL",
			"id_objet"	=> "bigint(21) DEFAULT '0' NOT NULL",
			"obsolete"	=> "ENUM('oui', 'non') NOT NULL DEFAULT 'non'",
			"maj"	=> "TIMESTAMP"
			);
	
	$spip_cilien_key = array(
			// pour la jointure de boucle
			"PRIMARY KEY"	=> "url,id_objet,objet",
			"KEY url"	=> "url",
			"KEY objet"	=> "objet",
			"KEY id_objet"	=> "id_objet"
			);

	$tables_auxiliaires['spip_cilien'] =
		array('field' => &$spip_cilien, 'key' => &$spip_cilien_key);


	// table des tranches 
	$spip_cilien_tranche = array(
			"table_objet"	=> "VARCHAR (25) DEFAULT '' NOT NULL",
			"objet"	=> "VARCHAR (25) DEFAULT '' NOT NULL",
			"date_tranche"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL"
			);

	$spip_cilien_tranche_key = array("PRIMARY KEY"	=> "table_objet");
			
	$tables_auxiliaires['spip_cilien_tranche'] =
		array('field' => &$spip_cilien_tranche, 'key' => &$spip_cilien_tranche_key);

	return $tables_auxiliaires;
}

function cilien_declarer_tables_interfaces($interface){
	
	$interface['table_des_tables']['cilien']='cilien';
	$interface['table_des_tables']['cilien_tranche']='cilien_tranche';
	
	$interface['tables_jointures']['spip_articles'][] = 'cilien';
	$interface['tables_jointures']['spip_rubriques'][] = 'cilien';

	return $interface;
}


?>