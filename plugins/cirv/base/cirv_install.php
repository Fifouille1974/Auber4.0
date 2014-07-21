<?php
/**
 * Plugin redacteur valideur
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/meta');

/**
 * Fonction d'installation, mise a jour de la base
 *
 * @param unknown_type $nom_meta_base_version
 * @param unknown_type $version_cible
 */
function cirv_upgrade($nom_meta_base_version,$version_cible){

	if ( (!isset($GLOBALS['meta'][$nom_meta_base_version]) )
			|| (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){
		
		if (version_compare($current_version,'1.0','<')){
			include_spip('base/cirv_tables');			
			include_spip('base/create');
			include_spip('base/abstract_sql');
			creer_base();
			maj_tables(array('spip_auteurs'));
			
			include_spip('base/cirv_migration');
			cirv_migration_info_auteurs();

		}

		effacer_meta($nom_meta_base_version);
		ecrire_meta($nom_meta_base_version,$current_version=$version_cible,'non');
		
		spip_log("cirv : installation version ".$version_cible);
			
	}
}

/**
 * Fonction de desinstallation
 *
 * @param unknown_type $nom_meta_base_version
 */
function cirv_vider_tables($nom_meta_base_version) {
			effacer_meta($nom_meta_base_version);
}

?>
