<?php
/**
 * Plugin redacteur restreint
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/autoriser');

/**
 * Auteur non restreint
 */
function cirr_auteur_non_restreint() {
	$return = "2=2";
	
	if (isset($GLOBALS['visiteur_session']['id_auteur']) AND $GLOBALS['visiteur_session']['id_auteur'])
		if (liste_rubriques_auteur($GLOBALS['visiteur_session']['id_auteur']))
			$return = "2=0";
	
	return $return;
}


/**
 * Tableau des rubriques de l'auteur
 */
function cirr_tableau_rubriques_auteur() {
	if (isset($GLOBALS['visiteur_session']['id_auteur']) AND $GLOBALS['visiteur_session']['id_auteur'])
		return liste_rubriques_auteur($GLOBALS['visiteur_session']['id_auteur']);
	else
		return array(0);
}

?>