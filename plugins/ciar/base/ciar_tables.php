<?php
/**
 * Plugin Acces restreints Giseh
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */


function ciar_declarer_tables_principales($tables_principales){

	// ajout d'un champ a la table des auteurs pour les statuts additionnels (eccma, etc.)
	$tables_principales['spip_auteurs']['field']['cistatut'] = "VARCHAR(20) DEFAULT '' NOT NULL";

	// ajout d'un champ a la table des auteurs pour les options (ecadminsite, etc.)
	$tables_principales['spip_auteurs']['field']['cioption'] = "VARCHAR(20) DEFAULT '' NOT NULL";

	return $tables_principales;
}


function ciar_declarer_tables_interfaces($interface){
	
	$interface['table_des_tables']['ciar_rubriques_protection']='ciar_rubriques_protection';
	$interface['table_des_tables']['ciar_auteurs_acces_rubriques']='ciar_auteurs_acces_rubriques';
	
	$interface['tables_jointures']['spip_auteurs'][] = 'ciar_auteurs_acces_rubriques';
	$interface['tables_jointures']['spip_rubriques'][] = 'ciar_auteurs_acces_rubriques';
	$interface['tables_jointures']['spip_rubriques'][] = 'ciar_rubriques_protection';
	
	// reservation
	$interface['table_des_tables']['ciar_auteurs_acces_articles']='ciar_auteurs_acces_articles';	
	$interface['table_des_tables']['ciar_auteurs_acces_documents']='ciar_auteurs_acces_documents';
	$interface['tables_jointures']['spip_auteurs'][] = 'ciar_auteurs_acces_articles';
	$interface['tables_jointures']['spip_articles'][] = 'ciar_auteurs_acces_articles';
	$interface['tables_jointures']['spip_auteurs'][] = 'ciar_auteurs_acces_documents';
	$interface['tables_jointures']['spip_documents'][] = 'ciar_auteurs_acces_documents';
	
	return $interface;
}


function ciar_declarer_tables_auxiliaires($tables_auxiliaires){

	// table pour définir la protection d'un rubrique
	// pour des questions de performance (au lieu de l'ajout d'un champ a la table des rubriques)
	$spip_ciar_rubriques_protection = array(
		"id_rubrique"	=> "bigint(21) NOT NULL",
		"acces_restreint"	=> "VARCHAR(20) DEFAULT '' NOT NULL");
	
	$spip_ciar_rubriques_protection_key = array(
		"KEY id_rubrique"	=> "id_rubrique",
		"KEY acces_restreint"	=> "acces_restreint");

	$tables_auxiliaires['spip_ciar_rubriques_protection'] = array(
		'field' => &$spip_ciar_rubriques_protection,
		'key' => &$spip_ciar_rubriques_protection_key);


	// table pour l'acces des auteurs aux rubriques
	// et la surcharge du statut d’un auteur dans une rubrique 
	$spip_ciar_auteurs_acces_rubriques = array(
		"id_rubrique"	=> "bigint(21) NOT NULL",
		"id_auteur"	=> "bigint(21) NOT NULL",
		"cistatut_auteur_rub"	=> "VARCHAR(255) DEFAULT '' NOT NULL");
	
	$spip_ciar_auteurs_acces_rubriques_key = array(
		"KEY id_rubrique"	=> "id_rubrique",
		"KEY id_auteur"	=> "id_auteur");

	$tables_auxiliaires['spip_ciar_auteurs_acces_rubriques'] = array(
		'field' => &$spip_ciar_auteurs_acces_rubriques,
		'key' => &$spip_ciar_auteurs_acces_rubriques_key);


	// reservation : table pour l'acces des auteurs aux articles
	// et la surcharge du statut d’un auteur pour un article 
	$spip_ciar_auteurs_acces_articles = array(
		"id_article"	=> "bigint(21) NOT NULL",
		"id_auteur"	=> "bigint(21) NOT NULL",
		"cistatut_auteur_art"	=> "VARCHAR(255) DEFAULT '' NOT NULL");
	
	$spip_ciar_auteurs_acces_articles_key = array(
		"KEY id_article"	=> "id_article",
		"KEY id_auteur"	=> "id_auteur");

	$tables_auxiliaires['spip_ciar_auteurs_acces_articles'] = array(
		'field' => &$spip_ciar_auteurs_acces_articles,
		'key' => &$spip_ciar_auteurs_acces_articles_key);


	// reservation : table pour l'acces des auteurs aux documents
	// et la surcharge du statut d’un auteur pour un document 
	$spip_ciar_auteurs_acces_documents = array(
		"id_document"	=> "bigint(21) NOT NULL",
		"id_auteur"	=> "bigint(21) NOT NULL",
		"cistatut_auteur_doc"	=> "VARCHAR(255) DEFAULT '' NOT NULL");
	
	$spip_ciar_auteurs_acces_documents_key = array(
		"KEY id_document"	=> "id_document",
		"KEY id_auteur"	=> "id_auteur");

	$tables_auxiliaires['spip_ciar_auteurs_acces_documents'] = array(
		'field' => &$spip_ciar_auteurs_acces_documents,
		'key' => &$spip_ciar_auteurs_acces_documents_key);
		
	return $tables_auxiliaires;
}

?>