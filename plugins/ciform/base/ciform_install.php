<?php
/**
 * Plugin ciform
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Fonction d'installation
 *
 * @param unknown_type $nom_meta_base_version
 * @param unknown_type $version_cible
 */
function ciform_upgrade($nom_meta_base_version,$version_cible){
	include_spip('inc/invalideur');

	spip_unlink(_DIR_CACHE.'formes-articles-cache.txt');
	spip_unlink(_DIR_CACHE.'formes-rubriques-cache.txt');
	spip_unlink(_DIR_CACHE.'raccourcis-rubriques-cache.txt');
	
	effacer_meta($nom_meta_base_version);
	ecrire_meta($nom_meta_base_version,$current_version=$version_cible,'non');
}


/**
 * Fonction de desinstallation
 *
 * @param unknown_type $nom_meta_base_version
 */
function ciform_vider_tables($nom_meta_base_version) {
	include_spip('inc/invalideur');
	
	spip_unlink(_DIR_CACHE.'formes-articles-cache.txt');
	spip_unlink(_DIR_CACHE.'formes-rubriques-cache.txt');
	spip_unlink(_DIR_CACHE.'raccourcis-rubriques-cache.txt');
	
	effacer_meta($nom_meta_base_version);
}

?>