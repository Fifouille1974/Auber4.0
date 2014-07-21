<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;


/**
 * Liste des meta de ciparam
 *
 */
function ciparam_tab_meta_ciparam() {
	$ci_dir_plugin_ciparam = _DIR_PLUGIN_CIPARAM;
	if (!is_dir(_DIR_RESTREINT_ABS))
		$ci_dir_plugin_ciparam = substr($ci_dir_plugin_ciparam,3);

	return array(
		'ci_abonnement_xiti' => $ci_dir_plugin_ciparam.'_images/void.gif?',
		'ci_xiti_email' => '',
		'ci_xiti_rss' => '',
		'ci_motcle_accueil' => '',
		'ci_nom_bandeau' => '',
		'ci_haut' => 'prop',
		'ci_masquer' => '',
		'ci_nbactu' => '5',
		'ci_menu_niveau' => '3',
		'ci_navigation_rubrique' => 'freres',
		'ci_navigation_article' => 'freres_et_articlesmememotcle',
		'ci_extra_rubrique' => 'sitesreferences',
		'ci_extra_article' => 'logo_seul',
		'ci_masquer' => array('ci_campagne_gouv'),
		'ci_sauts_lignes' => 'non'	
	);
}

/**
 * Liste des meta de SPIP que l'on veut alimenter 
 *
 */
function ciparam_tab_meta_spip() {
	return array(
			'config_precise_groupes' => 'oui',
			'articles_descriptif' => 'oui',
			'articles_redac' => 'oui',
			'articles_mots' => 'oui',
			'articles_redirection' => 'oui',
			'rubriques_descriptif' => 'oui',
			'post_dates' => 'oui',
			'activer_sites' => 'oui',
			'documents_article' => 'oui',
			'preview' => ',0minirezo,1comite,'
		);
}

?>