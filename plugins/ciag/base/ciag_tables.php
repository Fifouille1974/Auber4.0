<?php
/**
 * Plugin Groupes d'auteurs
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */


function ciag_declarer_tables_principales($tables_principales){
	
	// table des groupes d’auteurs
	$spip_ciag_grpauteurs = array(
		"id_groupe"	=> "bigint(21) NOT NULL",
		"titre"	=> "text DEFAULT '' NOT NULL",
		"descriptif"	=> "text DEFAULT '' NOT NULL");

	$spip_ciag_grpauteurs_key = array(
			"PRIMARY KEY"	=> "id_groupe");
	
	$tables_principales['spip_ciag_grpauteurs'] = array(
		'field' => &$spip_ciag_grpauteurs,
		'key' => &$spip_ciag_grpauteurs_key);

	return $tables_principales;
}


function ciag_declarer_tables_interfaces($interface){
	
	$interface['table_des_tables']['ciag_grpauteurs']='ciag_grpauteurs';
	$interface['table_des_tables']['ciag_grpauteurs_auteurs']='ciag_grpauteurs_auteurs';
	$interface['table_des_tables']['ciag_grpauteurs_rubriques']='ciag_grpauteurs_rubriques';
	$interface['table_des_tables']['ciag_grpauteurs_gestionnaires']='ciag_grpauteurs_gestionnaires';
	
	$interface['tables_jointures']['spip_auteurs'][] = 'ciag_grpauteurs_auteurs';
	$interface['tables_jointures']['spip_rubriques'][] = 'ciag_grpauteurs_rubriques';
	$interface['tables_jointures']['spip_auteurs'][] = 'ciag_grpauteurs_gestionnaires';
	$interface['tables_jointures']['spip_ciag_grpauteurs'][] = 'ciag_grpauteurs_auteurs';
	$interface['tables_jointures']['spip_ciag_grpauteurs'][] = 'ciag_grpauteurs_rubriques';
	$interface['tables_jointures']['spip_ciag_grpauteurs'][] = 'ciag_grpauteurs_gestionnaires';
	
	return $interface;
}


function ciag_declarer_tables_auxiliaires($tables_auxiliaires){

	// table de relation entre auteurs et groupes d’auteurs
	$spip_ciag_grpauteurs_auteurs = array(
		"id_groupe"	=> "bigint(21) NOT NULL",
		"id_auteur"	=> "bigint(21) NOT NULL",
		"cistatut_auteur_grp"	=> "VARCHAR(255) DEFAULT '' NOT NULL",
		"maj"	=> "TIMESTAMP");

	$spip_ciag_grpauteurs_auteurs_key = array(
		"PRIMARY KEY" 	=> "id_groupe,id_auteur",
		"KEY id_auteur"	=> "id_auteur",
		"KEY id_groupe" => "id_groupe");

	$tables_auxiliaires['spip_ciag_grpauteurs_auteurs'] = array(
		'field' => &$spip_ciag_grpauteurs_auteurs,
		'key' => &$spip_ciag_grpauteurs_auteurs_key);


	// table de relation entre rubriques et groupes d’auteurs
	$spip_ciag_grpauteurs_rubriques = array(
		"id_groupe"	=> "bigint(21) NOT NULL",
		"id_rubrique"	=> "bigint(21) NOT NULL",
		"maj"	=> "TIMESTAMP");
	
	$spip_ciag_grpauteurs_rubriques_key = array(
		"PRIMARY KEY" 	=> "id_groupe,id_rubrique",
		"KEY id_rubrique"	=> "id_rubrique",
		"KEY id_groupe" => "id_groupe");

	$tables_auxiliaires['spip_ciag_grpauteurs_rubriques'] = array(
		'field' => &$spip_ciag_grpauteurs_rubriques,
		'key' => &$spip_ciag_grpauteurs_rubriques_key);


	// table de relation entre groupes d’auteurs et gestionnaire
	$spip_ciag_grpauteurs_gestionnaires = array(
		"id_groupe"	=> "bigint(21) NOT NULL",
		"id_auteur"	=> "bigint(21) NOT NULL",
		"maj"	=> "TIMESTAMP");

	$spip_ciag_grpauteurs_gestionnaires_key = array(
		"PRIMARY KEY" 	=> "id_groupe,id_auteur",
		"KEY id_auteur"	=> "id_auteur",
		"KEY id_groupe" => "id_groupe");

	$tables_auxiliaires['spip_ciag_grpauteurs_gestionnaires'] = array(
		'field' => &$spip_ciag_grpauteurs_gestionnaires,
		'key' => &$spip_ciag_grpauteurs_gestionnaires_key);

		
	return $tables_auxiliaires;
}

/*
function ciag_declarer_tables_objets_sql($tables){

	$tables['spip_ciag_grpauteurs'] = array(
		'table_objet_surnoms' => array('groupeauteur','ciag_grpauteurs'),
		'type'=>'groupeauteur',
	  	'type_surnoms' => array('ciag_grpauteurs','groupeauteur'),
		'texte_retour' => 'icone_retour',
		'texte_objets' => 'ciag:titre_groupes_mots',
		'texte_objet' => 'ciag:titre_groupe_mots',
		'texte_modifier' => 'ciag:icone_modif_groupe_mots',
		'texte_creer' => 'ciag:icone_creation_groupe_mots',
		'texte_logo_objet' => 'ciag:logo_groupe',
		'info_aucun_objet'=> 'ciag:info_aucun_groupemots',
		'info_1_objet' => 'ciag:info_1_groupemots',
		'info_nb_objets' => 'ciag:info_nb_groupemots',
		'titre' => "titre, '' AS lang",
		'principale' => 'oui',
		'page' => '', // pas de page publique pour les groupes
		'field'=> array(
			"id_groupe"	=> "bigint(21) NOT NULL",
			"titre"	=> "text DEFAULT '' NOT NULL",
			"descriptif"	=> "text DEFAULT '' NOT NULL"
		),
		'key' => array(
			"PRIMARY KEY"	=> "id_groupe"
		),
		'rechercher_champs' => array(),
		'champs_versionnes' => array()		
	);

	return $tables;
}
*/
?>