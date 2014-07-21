<?php
/**
 * Plugin ciimport : Importation d'auteurs et de mots-cles
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */


/**
 * Lecture des parametres de configuration du plugin
 * et alimentation de variables globales
 */
function ciimport_lire_meta() {

//	if (!isset($GLOBALS['ciconfig'])) {
		$GLOBALS['ciconfig']['ciimportannuaire'] = 'non';
		$GLOBALS['ciconfig']['ciimportauteur'] = array("prenom"=>"0", "nom"=>"1", "messagerie"=>"2");
		$GLOBALS['ciconfig']['ciimportmailcompatible'] = array();

		$f = _DIR_RACINE . _NOM_PERMANENTS_INACCESSIBLES . '_config_ciimport.php';
	
		// parametrage par fichier
		if (@file_exists($f))
			include_once($f);			
//	}
		
    return true;
}

?>