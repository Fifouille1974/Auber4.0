<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

// Ajouter un marqueur de cache pour le differencier selon les autorisations
if (!isset($GLOBALS['marqueur'])) $GLOBALS['marqueur'] = '';

if (isset($GLOBALS['visiteur_session']['id_auteur']))
	$GLOBALS['marqueur'] .= $GLOBALS['visiteur_session']['id_auteur'];


// Desactiver le menu deroulant de l'espace prive
if(!function_exists('exec_menu_rubriques')) {
function exec_menu_rubriques() {
	include_spip('inc/actions');
	header("Cache-Control: no-cache, must-revalidate");
//	ajax_retour("");
	$c = $GLOBALS['meta']["charset"];
	header('Content-Type: text/html; charset='. $c);
	echo "";		
}
}

?>