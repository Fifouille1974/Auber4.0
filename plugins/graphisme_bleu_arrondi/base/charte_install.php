<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Vider le cache de SPIP
 * lors de l'installation du plugin
 */
function charte_upgrade($nom_meta_base_version,$version_cible){
	include_spip('inc/invalideur');
	supprime_invalideurs();
	spip_unlink(_CACHE_RUBRIQUES);
	purger_repertoire(_DIR_CACHE);
	purger_repertoire(_DIR_SKELS);			
	
	effacer_meta($nom_meta_base_version);
	ecrire_meta($nom_meta_base_version,$current_version=$version_cible,'non');
}


/**
 * Fonction de desinstallation
 *
 * @param unknown_type $nom_meta_base_version
 */
function charte_vider_tables($nom_meta_base_version) {
	include_spip('inc/invalideur');
	supprime_invalideurs();
	spip_unlink(_CACHE_RUBRIQUES);
	purger_repertoire(_DIR_CACHE);
	purger_repertoire(_DIR_SKELS);
	
	effacer_meta($nom_meta_base_version);	
}

?>