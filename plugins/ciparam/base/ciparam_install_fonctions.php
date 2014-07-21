<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;


/**
 * Fonction d'initialisation des meta de ciparam
 *
 */
function ciparam_remplir_meta() {

	$tableau = array();

	if (!$GLOBALS['meta']['ciparam']) {	
		include_spip('base/ciparam_meta');
		
		$liste_meta_ciparam = ciparam_tab_meta_ciparam();	
		$liste_meta_spip = ciparam_tab_meta_spip();
				
	
		while (list($nom, $valeur) = each($liste_meta_spip)) {
			ecrire_meta($nom, $valeur);
		}
		
		while (list($nom, $valeur) = each($liste_meta_ciparam)) {
			$tableau[$nom] = $valeur;
			
			// compatiblite ascendante
			$nom_old = 'equip_'.substr($nom,3);
			if ($GLOBALS['meta'][$nom_old])
				$tableau[$nom] = $GLOBALS['meta'][$nom_old];

			if (substr($nom,0,14)=='ci_navigation_') {
				$nom_old = 'ci_gauche_'.substr($nom,14);
				if ($GLOBALS['meta'][$nom_old])
					$tableau[$nom] = $GLOBALS['meta'][$nom_old];
			}
			if (substr($nom,0,9)=='ci_extra_') {
				$nom_old = 'ci_droite_'.substr($nom,9);
				if ($GLOBALS['meta'][$nom_old])
					$tableau[$nom] = $GLOBALS['meta'][$nom_old];
			}
			if (substr($nom,0,10)=='ci_masquer') {
				$nom_old = 'ci_campagne_gouv';
				if ($GLOBALS['meta'][$nom_old]) {
					if ($GLOBALS['meta'][$nom_old]=='oui')
						$tableau[$nom] = array();
					if ($GLOBALS['meta'][$nom_old]=='non')
						$tableau[$nom] = array('ci_campagne_gouv');
				}
			}
		}
		
		ecrire_meta('ciparam', @serialize($tableau));
	}
}


/**
 * Purge complète par exemple en cas de changement de version de ciparam
 *
 */
function ciparam_purge() {

	include_spip('inc/invalideur');

	spip_log("ciparam : purger");
/*
	supprime_invalideurs();
	spip_unlink(_CACHE_RUBRIQUES);
	purger_repertoire(_DIR_CACHE);
*/
	purger_repertoire(_DIR_SKELS);

}


/**
 * Remplir les formats de pièce jointe
 *
 */
function ciparam_remplir_type_document() {
	
	include_spip('base/ciparam_type_document');
	$citab = ciparam_type_document();
	while (list(,$val) = each($citab)) {
		ciparam_ajout_type_document($val[0],$val[1],$val[2],$val[3]);
	}
	
	return true;
}


/**
 * Ajouter un format de pièce jointe
 *
 */
function ciparam_ajout_type_document($extension,$titre,$inclus,$mime_type='') {
	$id_format = 0;
	
	if ($extension){
		if (!sql_countsel("spip_types_documents", "extension=".sql_quote($extension))){
			$id_format = sql_insertq("spip_types_documents", array(
				'extension' => $extension,
				'titre' => $titre,
				'inclus' => $inclus,
				'mime_type' => $mime_type
				));
//			if ($id_format()>0) spip_log("ciparam : spip_types_documents - insert - ".$extension);
		}
	}	
	
	return $id_format;
}


/**
 * Migrer perso_bandeau.jpg
 *
 */
function ciparam_migrer_bandeau() {
	if (file_exists(_DIR_IMG . 'perso_bandeau.jpg') AND !file_exists(_DIR_IMG . 'siteon0.jpg')) {
		rename(_DIR_IMG . 'perso_bandeau.jpg', _DIR_IMG . 'siteon0.jpg');
	}
}

?>