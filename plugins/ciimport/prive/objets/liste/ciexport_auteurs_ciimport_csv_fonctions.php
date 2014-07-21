<?php
/**
 * Plugin ciimport
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

// Parametrage par fichier
include_spip('inc/ciimport_commun');
ciimport_lire_meta();		
 

/* 
[(#ENV{cistatuts}|ciimport_auteurs_ciimport_csv)] 
*/
function ciimport_auteurs_ciimport_csv($cistatuts=array()){
	$return = '';
	
	// structure du fichier annuaire.csv (parametrable par fichier)
	$rang_prenom = 0;
	$rang_nom = 1;
	$rang_email = 2;
	$n = 3;
	if ($config = $GLOBALS['ciconfig']['ciimportauteur']) {
		if (is_array($config)){
			$n = count($config);
			if (isset($config['prenom']))
				$rang_prenom = intval($config['prenom']);
			if (isset($config['nom']))
				$rang_nom = intval($config['nom']);
			if (isset($config['messagerie']))
				$rang_email = intval($config['messagerie']);
		}
	}

	$where = "";	
	if ($cistatuts)
		$where = sql_in('statut',$cistatuts);
		
	// Premiere ligne du CSV
	$premiere_ligne = '';
	if ($config = $GLOBALS['ciconfig']['ciimportauteur']) {
		if (is_array($config)){
			foreach ($config AS $cle=>$valeur)
				$premiere_ligne .= '"'.$cle.'",';
		}
	}
	if ($premiere_ligne)
		$premiere_ligne = substr($premiere_ligne,0,-1);

	$return = $premiere_ligne."\n";
		
	// Pour chaque auteur
	$result = sql_select("nom,email", "spip_auteurs", $where,"","nom");
	while($row = sql_fetch($result)){
		$ligne = '';
		for($i = 0;$i<$n;$i++) {
			if ($i==$rang_nom)
				$ligne .= '"'.str_replace('"','',$row['nom']).'",';
			elseif ($i==$rang_email)
				$ligne .= '"'.str_replace('"','',$row['email']).'",';
			else
				$ligne .= '"",';
		}
		if ($ligne)
			$ligne = substr($ligne,0,-1);

		$return .= $ligne."\n";
	}

	$return = interdire_scripts($return);

	return $return;
}


?>