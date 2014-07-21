<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;


function ciparam_declarer_tables_principales($tables_principales){
	$tables_principales['spip_rubriques']['field']['ciforme'] = "varchar(50) DEFAULT '' NOT NULL";
	$tables_principales['spip_articles']['field']['ciforme'] = "varchar(50) DEFAULT '' NOT NULL";
	$tables_principales['spip_rubriques']['field']['citri'] = "varchar(50) DEFAULT '' NOT NULL";
	$tables_principales['spip_rubriques']['field']['citrinum'] = "varchar(50) DEFAULT 'titre' NOT NULL";
	$tables_principales['spip_rubriques']['field']['citriinverse'] = "varchar(50) DEFAULT '' NOT NULL";
	$tables_principales['spip_rubriques']['field']['cirangse'] = "bigint(21) DEFAULT '0' NOT NULL";
	$tables_principales['spip_rubriques']['field']['cirangspae'] = "bigint(21) DEFAULT '0' NOT NULL";
	return $tables_principales;
}


function ciparam_declarer_tables_interfaces($interface){
	$interface['table_des_tables']['ci_raccourcis_articles']='ci_raccourcis_articles';
	$interface['table_des_tables']['ci_raccourcis_rubriques']='ci_raccourcis_rubriques';
	$interface['table_des_tables']['ci_raccourcis_syndic']='ci_raccourcis_syndic';
	
	$interface['tables_jointures']['spip_articles'][] = 'ci_raccourcis_articles';
	$interface['tables_jointures']['spip_rubriques'][] = 'ci_raccourcis_rubriques';
	$interface['tables_jointures']['spip_syndic'][] = 'ci_raccourcis_syndic';
	
	return $interface;
}


function ciparam_declarer_tables_auxiliaires($tables_auxiliaires){
	
	$spip_ci_raccourcis_articles = array(
		"id_article"	=> "bigint(21) NOT NULL",
		"raccourci" 	=> "VARCHAR(100) NOT NULL");
	
	$spip_ci_raccourcis_articles_key = array(
		"PRIMARY KEY" 	=> "id_article, raccourci",
		"KEY id_article"	=> "id_article",
		"KEY raccourci" => "raccourci");
	
	$tables_auxiliaires['spip_ci_raccourcis_articles'] = array(
		'field' => &$spip_ci_raccourcis_articles,
		'key' => &$spip_ci_raccourcis_articles_key);

		
	$spip_ci_raccourcis_rubriques = array(
		"id_rubrique"	=> "bigint(21) NOT NULL",
		"raccourci" 	=> "VARCHAR(100) NOT NULL");
	
	$spip_ci_raccourcis_rubriques_key = array(
		"PRIMARY KEY" 	=> "id_rubrique, raccourci",
		"KEY id_rubrique"	=> "id_rubrique",
		"KEY raccourci" => "raccourci");
	
	$tables_auxiliaires['spip_ci_raccourcis_rubriques'] = array(
		'field' => &$spip_ci_raccourcis_rubriques,
		'key' => &$spip_ci_raccourcis_rubriques_key);


	$spip_ci_raccourcis_syndic = array(
		"id_syndic"	=> "bigint(21) NOT NULL",
		"raccourci" 	=> "VARCHAR(100) NOT NULL");
	
	$spip_ci_raccourcis_syndic_key = array(
		"PRIMARY KEY" 	=> "id_syndic, raccourci",
		"KEY id_syndic"	=> "id_syndic",
		"KEY raccourci" => "raccourci");
	
	$tables_auxiliaires['spip_ci_raccourcis_syndic'] = array(
		'field' => &$spip_ci_raccourcis_syndic,
		'key' => &$spip_ci_raccourcis_syndic_key);


	return $tables_auxiliaires;
}

?>