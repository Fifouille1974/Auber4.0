<?php
/**
 * Plugin cichat
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
function cichat_upgrade($nom_meta_base_version,$version_cible){
	include_spip('inc/invalideur');

	spip_unlink(_DIR_CACHE.'formes-articles-cache.txt');
	
	effacer_meta($nom_meta_base_version);
	ecrire_meta($nom_meta_base_version,$current_version=$version_cible,'non');
}


/**
 * Fonction de desinstallation
 *
 * @param unknown_type $nom_meta_base_version
 */
function cichat_vider_tables($nom_meta_base_version) {
	include_spip('inc/invalideur');
	
	spip_unlink(_DIR_CACHE.'formes-articles-cache.txt');
	
	effacer_meta($nom_meta_base_version);
}

?>