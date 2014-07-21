<?php
/**
 * Plugin ciimport
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');

function exec_ciexport_fichier_forum() {

	$objet = '';
	if ($req_objet = _request('objet')) {
		if (in_array($req_objet, array('article')))
			$objet = $req_objet;
	}
	$id_objet = intval(_request('id_objet'));
	
	if (!autoriser('modererforum', $objet, $id_objet)) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
	
		if ($forme=_request('forme') AND in_array($forme,array('forum_csv','forum_texte','forum_html'))) {

			$fichier_extension = 'txt';
			$content_type = 'text/plain';
			if (substr($forme,-4)=='_csv'){
				$fichier_extension = 'csv';
				$content_type = 'text/comma-separated-values';
			}
			if (substr($forme,-5)=='_html'){
				$fichier_extension = 'html';
				$content_type = 'text/html';
			}
			$fichier_nom = $forme."_".$objet.$id_objet."_".date("Y-m-d").".".$fichier_extension;

			// par defaut, les commentaires publies
			$contexte = array('cistatuts'=>array('publie'));
			// possibilite de demander un statut particulier
			if ($choixstatut=_request('statut')) {
				if (in_array($choixstatut, array('publie', 'off', 'prop', 'spam')))
					$contexte = array('cistatuts'=>array($choixstatut));
				elseif ($choixstatut=='tous')
					$contexte = array('cistatuts'=>array('publie', 'off', 'prop', 'spam'));
				elseif ($choixstatut=='nonpublie')						
					$contexte = array('cistatuts'=>array('off', 'prop', 'spam'));
			}

			$contexte['id_objet'] = $id_objet;
			$contexte['id_article'] = $id_objet;
			
			$fichier = trim(recuperer_fond("prive/objets/liste/ciexport_".$forme, $contexte));

			// changer de charset le cas echeant
			if ($cicharset=_request('charset') AND in_array($cicharset,array('iso-8859-1','utf-8'))){
				if ($GLOBALS['meta']['charset'] != $cicharset){
					include_spip('inc/charsets');
					$fichier = unicode2charset(charset2unicode($fichier), $cicharset);
				}
				$fichier_nom = $forme."_".$objet.$id_objet."_".$cicharset."_".date("Y-m-d").".".$fichier_extension;
			}

			
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

function ciimport_filtre_forum_csv($letexte){
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