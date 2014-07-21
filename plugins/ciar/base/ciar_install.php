<?php
/**
 * Plugin Acces restreints Giseh 
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
function ciar_upgrade($nom_meta_base_version,$version_cible){

	if ( (!isset($GLOBALS['meta'][$nom_meta_base_version]) )
			|| (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){

		include_spip('base/ciar_install_fonctions');
		
		if (version_compare($current_version,'1.0','<')){
			ciar_remplir_meta();
			include_spip('base/ciar_tables');			
			include_spip('base/create');
			include_spip('base/abstract_sql');
			creer_base();
			maj_tables(array('spip_auteurs'));
			
			include_spip('base/ciar_migration');
			ciar_migration_mots_cles();
			ciar_migration_info_auteurs();

			// migration des affectations d'auteurs aux EC
			// Attention : le faire apres la migration des mots-cles et apres la migration des infos auteurs
			ciar_migration_affectation_ec();			
		}
		
		effacer_meta($nom_meta_base_version);
		ecrire_meta($nom_meta_base_version,$current_version=$version_cible,'non');
		
		ciar_purge();
		
		spip_log("ciar : installation version ".$version_cible);
			
	}
}

/**
 * Fonction de desinstallation
 *
 * @param unknown_type $nom_meta_base_version
 */
function ciar_vider_tables($nom_meta_base_version) {
			effacer_meta($nom_meta_base_version);
			include_spip('base/ciar_install_fonctions');
			ciar_purge();
}

?>
