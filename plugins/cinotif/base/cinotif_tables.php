<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

function cinotif_declarer_tables_principales($tables_principales){
	
	// table des evenements
	$spip_cinotif_evenements = array(
		"id_evenement"	=> "bigint(21) NOT NULL",
		"quoi"			=> "varchar(50) DEFAULT '' NOT NULL",
		"objet"			=> "varchar (25) DEFAULT '' NOT NULL",
		"id_objet"		=> "bigint(21) DEFAULT '0' NOT NULL",
		"multisite"		=> "varchar(100) DEFAULT '' NOT NULL",
		"url_multisite"	=> "text DEFAULT '' NOT NULL",
		"titre"			=> "text DEFAULT '' NOT NULL",
		"adresse_liste_diffusion"	=> "text DEFAULT '' NOT NULL",
		"statut"		=> "VARCHAR(10) DEFAULT '' NOT NULL",
		"maj"			=> "TIMESTAMP");

	$spip_cinotif_evenements_key = array(
		"PRIMARY KEY"	=> "id_evenement",
		"KEY quoi"		=> "quoi",
		"KEY objet"		=> "objet",
		"KEY id_objet"	=> "id_objet");

	$tables_principales['spip_cinotif_evenements'] = array(
		'field' => &$spip_cinotif_evenements,
		'key' 	=> &$spip_cinotif_evenements_key);


	// table des abonnes
	$spip_cinotif_abonnes = array(
		"id_abonne"		=> "bigint(21) NOT NULL",
		"hash_email"	=> "CHAR(32) DEFAULT '' NOT NULL",
		"id_auteur"		=> "bigint(21) DEFAULT '0' NOT NULL",
		"email"			=> "text DEFAULT '' NOT NULL",
		"maj"			=> "TIMESTAMP");

	$spip_cinotif_abonnes_key = array(
		"PRIMARY KEY"		=> "id_abonne",
		"KEY hash_email"	=> "hash_email",
		"KEY id_auteur"		=> "id_auteur");

	$tables_principales['spip_cinotif_abonnes'] = array(
		'field' => &$spip_cinotif_abonnes,
		'key' 	=> &$spip_cinotif_abonnes_key);


	// table des courriers
	$spip_cinotif_courriers = array(
		"id_courrier"			=> "bigint(21) NOT NULL",
		"quoi"					=> "varchar(50) DEFAULT '' NOT NULL",
		"objet"					=> "varchar(25) DEFAULT '' NOT NULL",
		"id_objet"				=> "bigint(21) DEFAULT '0' NOT NULL",
		"id_version"			=> "bigint(21) DEFAULT 0 NOT NULL",
		"parent"				=> "varchar(25) DEFAULT '' NOT NULL",
		"id_parent"				=> "bigint(21) DEFAULT '0' NOT NULL",
		"sujet"					=> "text NOT NULL",
		"texte"					=> "text NOT NULL",
		"url_site"				=> "varchar(255) DEFAULT '' NOT NULL",
		"date"					=> "datetime NOT NULL default '0000-00-00 00:00:00'",
		"statut"				=> "varchar(10) NOT NULL",
		"destinataires"			=> "longtext DEFAULT '' NOT NULL",
		"nb_emails"				=> "bigint(21) NOT NULL default '0'",
		"nb_emails_envoyes"		=> "bigint(21) NOT NULL default '0'",
		"nb_emails_non_envoyes"	=> "bigint(21) NOT NULL default '0'",
		"nb_emails_echec"		=> "bigint(21) NOT NULL default '0'",
		"date_debut_envoi"		=> "datetime NOT NULL default '0000-00-00 00:00:00'",
		"date_fin_envoi"		=> "datetime NOT NULL default '0000-00-00 00:00:00'");

	$spip_cinotif_courriers_key = array(
		"PRIMARY KEY"		=> "id_courrier",
		"KEY quoi"			=> "quoi",
		"KEY objet"			=> "objet",
		"KEY id_objet"		=> "id_objet",
		"KEY statut"		=> "statut");

	$tables_principales['spip_cinotif_courriers'] = array(
		'field' => &$spip_cinotif_courriers,
		'key' 	=> &$spip_cinotif_courriers_key);

	return $tables_principales;
}

function cinotif_declarer_tables_auxiliaires($tables_auxiliaires){

	// table des abonnements
	$spip_cinotif_abonnements = array(
		"id_abonne"		=> "bigint(21) NOT NULL",
		"id_evenement"	=> "bigint(21) NOT NULL",
		"statut"		=> "VARCHAR(10) DEFAULT '' NOT NULL",
		"jeton"			=> "VARCHAR(32) DEFAULT '' NOT NULL",
		"maj"			=> "TIMESTAMP");

	$spip_cinotif_abonnements_key = array(
		"PRIMARY KEY"		=> "id_abonne,id_evenement",
		"KEY id_abonne"		=> "id_abonne",
		"KEY id_evenement"	=> "id_evenement",
		"KEY statut"		=> "statut",
		"KEY jeton"			=> "jeton");

	$tables_auxiliaires['spip_cinotif_abonnements'] = array(
		'field' => &$spip_cinotif_abonnements,
		'key' 	=> &$spip_cinotif_abonnements_key);

	
	// table des envois
	$spip_cinotif_tmp = array(
		"id_courrier"	=> "bigint(21) NOT NULL",
		"id_abonne"		=> "bigint(21) NOT NULL",
		"email"			=> "text DEFAULT '' NOT NULL",
		"multisite"		=> "varchar(100) DEFAULT '' NOT NULL",
		"url_multisite"	=> "text DEFAULT '' NOT NULL",
		"statut"		=> "varchar(10) DEFAULT '' NOT NULL",
		"pas_avant"		=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
		"maj"			=> "TIMESTAMP");

	$spip_cinotif_tmp_key = array(
		"KEY id_courrier" => "id_courrier",
		"KEY id_abonne" => "id_abonne",
		"KEY statut" => "statut",
		"KEY pas_avant" => "pas_avant");

	$tables_auxiliaires['spip_cinotif_tmp'] = array(
		'field' => &$spip_cinotif_tmp,
		'key' 	=> &$spip_cinotif_tmp_key);

	return $tables_auxiliaires;
}
		
function cinotif_declarer_tables_interfaces($interface){
	
	$interface['table_des_tables']['cinotif_evenements']='cinotif_evenements';
	$interface['table_des_tables']['cinotif_abonnements']='cinotif_abonnements';
	$interface['table_des_tables']['cinotif_courriers']='cinotif_courriers';
	$interface['table_des_tables']['cinotif_abonnes']='cinotif_abonnes';
	$interface['table_des_tables']['cinotif_tmp']='cinotif_tmp';
	
	$interface['tables_jointures']['spip_cinotif_abonnes']['id_abonne'] = 'cinotif_abonnements';
	$interface['tables_jointures']['spip_cinotif_evenements']['id_evenement'] = 'cinotif_abonnements';
	$interface['tables_jointures']['spip_cinotif_courriers']['id_courrier'] = 'cinotif_envois';
	
	return $interface;
}

?>