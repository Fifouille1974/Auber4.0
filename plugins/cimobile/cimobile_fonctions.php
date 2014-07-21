<?php
/**
 * Plugin cimobile
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

/*-----------------------------------------------------------------
// Balise propre au plugin
------------------------------------------------------------------*/

function balise_CIMOBILE($p) {
	$p->code = "cimobile_dossier_squelettes(\$Pile)";
	$p->statut = 'html';
	return $p;
}

function balise_CIMOBILE_LISTE($p) {
	$p->code = "cimobile_liste_correspondances(\$Pile)";
	$p->statut = 'html';
	return $p;
}

/*-----------------------------------------------------------------
// Fonction relative a la balise propre au plugin
------------------------------------------------------------------*/

// Sous dossier de squelette actif
function cimobile_dossier_squelettes($Pile) {
	return $GLOBALS['cimobile_dossier_squelettes'];
} 

function cimobile_liste_correspondances($Pile) {
	return cimobile_correspondances();
} 

?>