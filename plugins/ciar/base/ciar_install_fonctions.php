<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;


/**
 * Purge complète par exemple en cas de changement de version
 *
 */
function ciar_purge() {

	include_spip('inc/invalideur');

	spip_log("Purger");

	supprime_invalideurs();
	purger_repertoire(_DIR_SKELS);

}

/**
 * Fonction d'initialisation des meta
 *
 */
function ciar_remplir_meta() {

	// parametrage par fichier	
	$f = _DIR_RACINE . _NOM_PERMANENTS_INACCESSIBLES . '_config_ciar.php';
	if (@file_exists($f))
		include_once($f);
	
	if (isset($GLOBALS['ciconfig']['ciar_protec_pj_par_filtre_lors_installation']))
		if ($GLOBALS['ciconfig']['ciar_protec_pj_par_filtre_lors_installation']=='oui')
			ecrire_meta('creer_htaccess', 'oui');
}

?>