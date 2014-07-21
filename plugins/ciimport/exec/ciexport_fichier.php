<?php
/**
 * Plugin ciimport
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');

function exec_ciexport_fichier() {

	if (!autoriser('configurer')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
	
		if ($forme=_request('forme') 
			AND in_array($forme,array('thunderbird','thunderbird_carnet_csv','outlook','sympa','auteurs_csv','auteurs_rubriques_csv','auteurs_ciimport_csv','motscles_csv'))) {

			$fichier_extension = 'txt';
			$content_type = 'text/plain';
			if (substr($forme,-4)=='_csv'){
				$fichier_extension = 'csv';
				$content_type = 'text/comma-separated-values';
			}
			$fichier_nom = $forme."_".date("Y-m-d").".".$fichier_extension;

			// par defaut, les auteurs (qui ne sont pas a la poubelle)
			$contexte = array('cistatuts'=>array('0minirezo','1comite'));
			// possibilite de demander un statut particulier
			if ($choixstatut=_request('statut')) {
				if (in_array($choixstatut, array('0minirezo','1comite','5poubelle','6forum')))
					$contexte = array('cistatuts'=>array($choixstatut));		
			}

			$fichier = trim(recuperer_fond("prive/objets/liste/ciexport_".$forme, $contexte));

			// changer de charset le cas echeant
			if ($cicharset=_request('charset') AND in_array($cicharset,array('iso-8859-1','utf-8'))){
				if ($GLOBALS['meta']['charset'] != $cicharset){
					include_spip('inc/charsets');
					$fichier = unicode2charset(charset2unicode($fichier), $cicharset);
				}
				$fichier_nom = $forme."_".$cicharset."_".date("Y-m-d").".".$fichier_extension;
			}

			// elever le dernier separateur le cas echeant
			if ($forme=='thunderbird' OR $forme=='outlook')
				$fichier = substr($fichier,0,-1);
			
			header("Content-type: ".$content_type);
			header("Content-Disposition: attachment; filename=\"$fichier_nom\";");			
			header("Expires: 0");
			header("Last-Modified: " .gmdate("D, d M Y H:i:s"). " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Pragma: no-cache");
			echo $fichier;
		} else {
			include_spip('inc/minipres');
			echo minipres();
		}
	}	
}

function ciimport_filtre_csv($letexte){
	// harmoniser les retours chariots
	$letexte = preg_replace(",\r\n?,S", "\n", $letexte);
	$letexte = preg_replace(",<p[>[:space:]],iS", "\n\n\\0", $letexte);
	$letexte = preg_replace(",</p[>[:space:]],iS", "\\0\n\n", $letexte);
	// supprimer les retours a la ligne
	$letexte = str_replace("\n", " ", $letexte);
	
	// remplacer les double quotes par des simples
	$letexte = str_replace('"', "'", $letexte);
	
	return $letexte;	
}

?>