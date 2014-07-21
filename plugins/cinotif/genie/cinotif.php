<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/cinotif_commun');

function genie_cinotif_dist($t) {
	cinotif_moteur_envoi();
	return true;
}

?>