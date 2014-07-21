<?php
/**
 * Plugin ciarchive : Archivage d'articles
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

// declarer la fonction du pipeline
function ciarchive_autoriser(){}

function autoriser_ciarchive_menu_dist($faire,$quoi,$id,$qui,$options) {
	return $qui['statut'] == '0minirezo';
}

?>