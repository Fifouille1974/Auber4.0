<?php
/**
 * Plugin ciimport
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
include_spip('inc/texte');

/* 
[(#ENV{cistatuts}|ciimport_auteurs_rubriques_csv)] 
*/
function ciimport_auteurs_rubriques_csv($cistatuts=array()){
	$return = '';
	$cirv = false;
	$ciar = false;

	$statutsetoptions = array(
	'statut_adminsite',
	'statut_0minirezo',
	'statut_1comite',
	'statut_6forum',
	'statut_ciredval',
	'statut_ciredvaltout',
	'cioption_ecadminsite',	
	'cioption_eccma',
	'cioption_ecadminrestreint');

	$libelles = array();
	foreach ($statutsetoptions AS $cle)
		$libelles[$cle] = _T('ciimport:'.$cle);
	
	
	$champs = array("id_auteur","nom","email","statut");
	if (defined('_DIR_PLUGIN_CIRV')) {
		$champs[] = "cistatut";
		$cirv = true;
	}
	if (defined('_DIR_PLUGIN_CIAR')){
		$champs[] = "cioption";
		$ciar = true;
		include_spip('ciar_fonctions');
	}

	$select = implode(',',$champs);

	$where = "";	
	if ($cistatuts)
		$where = sql_in('statut',$cistatuts);

	$whererub = "";	
	if ($ciar)
		$whererub = " AND ".sql_in('A.id_rubrique', ciar_tableau_rubriques_exclues(), 'NOT');
		
	// Premiere ligne du CSV
	$premiere_ligne = '"'._T('ciimport:csv_adresse_site').'","'._T('ciimport:csv_nom').'","'._T('ciimport:csv_email').'","'._T('ciimport:csv_statut_defaut').'",';
	if ($ciar)
		$premiere_ligne .= '"'._T('ciimport:csv_option').'",';

	$premiere_ligne .= '"'._T('ciimport:csv_id_rubrique').'","'._T('ciimport:csv_titre_rubrique').'",';
	if ($ciar)
		$premiere_ligne .= '"'._T('ciimport:csv_ec').'","'._T('ciimport:csv_statut_ec').'",';

	$return = substr($premiere_ligne,0,-1)."\n";
		
	// Pour chaque auteur
	$result = sql_select($select, "spip_auteurs", $where,"","nom");
	while($row = sql_fetch($result)){

		$ligne = '"'.$GLOBALS['meta']['adresse_site'].'","'.str_replace('"','',$row['nom']).'","'.$row['email'].'",';
		if ($cirv AND $row['cistatut'])
			$ligne .= '"'.$libelles['statut_'.$row['cistatut']].'",';
		else
			$ligne .= '"'.$libelles['statut_'.$row['statut']].'",';
			
		if ($ciar){
			if (trim($row['cioption']))
				$ligne .= '"'.$libelles['cioption_'.$row['cioption']].'",';
			else 
				$ligne .= '"",';
		}

		
		// Si CIAR noter les surcharges de statut
		$ec_statuts = array();
		$q = sql_select("id_rubrique,cistatut_auteur_rub", "spip_ciar_auteurs_acces_rubriques", "id_auteur=".$row['id_auteur']." AND id_rubrique!=0");
		while ($rowec = sql_fetch($q))
			$ec_statuts[$rowec['id_rubrique']] = $rowec['cistatut_auteur_rub'];

		
		// Pour chaque rubrique de cet auteur
		$lignerub = '';
		$resrub = sql_select("R.id_rubrique, R.titre", "spip_auteurs_rubriques AS A LEFT JOIN spip_rubriques AS R ON A.id_rubrique=R.id_rubrique", "A.id_auteur=".$row['id_auteur'].$whererub, "", "R.titre");
		while($rowrub = sql_fetch($resrub)){
			$lignerub = '"'.$rowrub['id_rubrique'].'","'.str_replace('"','',$rowrub['titre']).'",';
			if ($ciar) {
				if (ciar_rub_ec($rowrub['id_rubrique'])){
					$lignerub .= '"'._T('ciimport:ec').'",';
					if (isset($ec_statuts[$rowrub['id_rubrique']]))
						$lignerub .= '"'.$libelles['statut_'.$ec_statuts[$rowrub['id_rubrique']]].'",';
					else 
						$lignerub .= '"",';
				
				} else { 
					$lignerub .= '"",';
				}
			}
			$lignerub = substr($lignerub,0,-1)."\n";

			$return .= $ligne.$lignerub;
		}
		
		// Cas d'un auteur sans rubrique
		if (!$lignerub){

			// Cas d'un administrateur du site
			if ($row['statut']=='0minirezo'){		
				$ligne = '"'.$GLOBALS['meta']['adresse_site'].'","'.$row['nom'].'","'.$row['email'].'",';
				$ligne .= '"'.$libelles['statut_adminsite'].'",';
					
				if ($ciar){
					if (trim($row['cioption']))
						$ligne .= '"'.$libelles['cioption_'.$row['cioption']].'",';
					else 
						$ligne .= '"",';
				}
			}
						
			$lignerub = '"","",';
			if ($ciar)
				$lignerub .= '"","",';

			$lignerub = substr($lignerub,0,-1)."\n";
			
			$return .= $ligne.$lignerub;
		}
	}

	$return = interdire_scripts($return);

	return $return;
}

?>