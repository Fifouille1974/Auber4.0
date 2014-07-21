<?php
/**
 * Plugin cilien
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

// pour le pipeline d'autorisation
function cilien_autoriser(){}


// bouton du bandeau
function autoriser_cilienmesliens_menu_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	return 	true;
}

function autoriser_ciliengerer_menu_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	return 	true;
}

?>