<?php
/**
 * Plugin Configurateur de squelettes
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
function ciparam_upgrade($nom_meta_base_version,$version_cible){

	if ( (!isset($GLOBALS['meta'][$nom_meta_base_version]) )
			|| (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){

		include_spip('base/ciparam_install_fonctions');
	
		if (version_compare($current_version,'1.0','<')){
			ciparam_remplir_meta();
			ciparam_remplir_type_document();
			
			include_spip('base/create');
			include_spip('base/abstract_sql');
			include_spip('base/ciparam_tables');

			creer_base();
			maj_tables(array('spip_rubriques','spip_articles'));
	
			include_spip('base/ciparam_migration');
			ciparam_migration_mots_cles();
		}

		if (version_compare($current_version,'1.1','<')){			
			include_spip('base/create');
			include_spip('base/abstract_sql');
			include_spip('base/ciparam_tables');
			maj_tables(array('spip_rubriques'));
		}

		effacer_meta($nom_meta_base_version);
		ecrire_meta($nom_meta_base_version,$current_version=$version_cible,'non');
		
//		ciparam_purge();
		ciparam_migrer_bandeau();

		spip_log("ciparam : installation version ".$version_cible);
			
	}

}

/**
 * Fonction de desinstallation
 *
 * @param unknown_type $nom_meta_base_version
 */
function ciparam_vider_tables($nom_meta_base_version) {
			effacer_meta($nom_meta_base_version);
//			include_spip('base/ciparam_install_fonctions');
//			ciparam_purge();
}

?>