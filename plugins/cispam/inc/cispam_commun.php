<?php
/**
 * Plugin CISPAM
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

// parametrage par fichier
$f = _DIR_RACINE . _NOM_PERMANENTS_INACCESSIBLES . '_config_cispam.php';
if (@file_exists($f))
	include_once($f);

/**
 * Lecture des parametres de configuration du plugin
 * et alimentation de variables globales
 */
function cispam_config($param) {
	$return = false;
	if ($param) {
		if (isset($GLOBALS['ciconfig'][$param]))
			$return = $GLOBALS['ciconfig'][$param];
	}
	
    return $return;
}

// contourner l'incompatibilite de SPIP avec les reverses proxy
function cispam_ip(){
	// par defaut
	$ci_ip = $GLOBALS['ip'];
	
	// ordre de recherche personnalise de l'IP dans le fichier de parametrage config/_config_cispam.php
	if (isset($GLOBALS['ciconfig']['cispam_ip_ordre'])) {
		if (is_array($GLOBALS['ciconfig']['cispam_ip_ordre'])){
			// determination de l'IP
			$cispam_ip_ordre = $GLOBALS['ciconfig']['cispam_ip_ordre'];	
			foreach ($cispam_ip_ordre as $valeur) {
				if (isset($_SERVER[$valeur])) {
					if ($_SERVER[$valeur]) {
						$ci_ip = $_SERVER[$valeur];
						break;
					}
				}
			}
		}
	}
	return $ci_ip;
}

?>