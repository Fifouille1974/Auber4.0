<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;


// Assurer la double compatibilite HTML 4.01 et XHTML 1.0
// - enlever le cas echeant les />
// - ou bien transformer le cas echeant les <br> saisis manuellement dans le texte d'un article
function ciparam_affichage_final($texte) {

	if (defined('_CIPARAM_DOCTYPE') AND _CIPARAM_DOCTYPE=='xhtml') {
		if (stripos($texte, '<br>')!==false)
			$texte = str_ireplace('<br>','<br />',$texte);
	} else {
		if (stripos($texte, '/>')!==false AND stripos($texte, '<?xml')===false)
			$texte = str_replace(array(' />','/>'),'>',$texte);
	}
		
	return $texte;
}

?>