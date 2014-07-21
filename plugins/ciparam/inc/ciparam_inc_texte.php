<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

function ciparam_pre_propre($letexte){

	// retour a la ligne
//	$letexte = preg_replace("/\n([\w\d{])/", "\n<br />\\1", $letexte);
	
	// Raccourci typographique "5 tirets"
	$letexte = preg_replace("/\n(-----+|_____+)/", "<div style='line-height:0em;clear:both;'>&nbsp;</div>", $letexte);
	
	return $letexte;
}

function ciparam_pre_typo($letexte){

	// ne pas imposer un blanc avant un point d'interrogation
	$letexte = str_replace('?', '_ci_pi_sblc_', $letexte);

	return $letexte;
}

function ciparam_post_typo($letexte){

	// ne pas imposer un blanc avant un point d'interrogation
	$letexte = str_replace('_ci_pi_sblc_', '?', $letexte);

	return $letexte;
}

?>