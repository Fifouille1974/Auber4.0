<?php

function cinotif_duree_envoi($valeur){
	$return = '';

	$i = 0;
	$cumul = 0;
	$result = sql_select("date_debut_envoi,date_fin_envoi", "spip_cinotif_courriers", "statut='publie'");
	while ($row = sql_fetch($result)){
		$cumul = $cumul + cinotif_diff_date($row['date_debut_envoi'],$row['date_fin_envoi']);
		$i++;
	}

	if ($i>0){
		$time = round($cumul/$i);
		$heures = floor($time/3600);
		$time = $time - ($heures*3600);
		$min = floor($time/60);
		$sec = $time - ($min*60);
		$return = $heures.'h'.$min.'min'.$sec.'s';
	}

	return $return;
}

function cinotif_diff_date($datedebut,$datefin){
		
	$debut = mktime(
	          substr($datedebut,11,2),
	          substr($datedebut,14,2),
	          substr($datedebut,17,2),
	          substr($datedebut,5,2),
	          substr($datedebut,8,2),
	          substr($datedebut,0,4));
	
	$fin = mktime(
	          substr($datefin,11,2),
	          substr($datefin,14,2),
	          substr($datefin,17,2),
	          substr($datefin,5,2),
	          substr($datefin,8,2),
	          substr($datefin,0,4));
	          
  return $fin - $debut; 
}

?>