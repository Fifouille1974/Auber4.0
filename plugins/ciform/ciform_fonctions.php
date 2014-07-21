<?php
/**
 * Plugin Formulaire
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

/*-----------------------------------------------------------------
// Fonctions pour le WIKI
------------------------------------------------------------------*/

// Nom de celui qui a reserve le document
function ciform_nom_resapj($id_article, $id_document){
	$ps = ciform_ps($id_article);
	$cle = strval($id_document);
	$id_auteur = 0;
	$nom_auteur_modif = "";
	
	if($ps AND $cle){	
		if (preg_match(',<'.$cle.'>(.*)</'.$cle.'>,Uims', $ps, $reg)) {
			$id_auteur = intval($reg[1]);
		}	
		if ($id_auteur>0) {
				$result = sql_select("nom", "spip_auteurs", "id_auteur=$id_auteur","","");		
				while ($row = sql_fetch($result))	{
					$nom_auteur_modif = htmlentities($row["nom"]);
				}				
		}
	}

	return $nom_auteur_modif;
}

// Date a laquelle le document a ete reserve
function ciform_time_resapj($id_article, $id_document){
	$ps = ciform_ps($id_article);
	$cle = 'time_'.strval($id_document);
	$cledoc = strval($id_document);
	$citime=0;
	$textetime="";

	if($ps AND strval($id_document)){	
		if (preg_match(',<'.$cledoc.'>(.*)</'.$cledoc.'>,Uims', $ps, $regdoc)) {
			if (intval($regdoc[1])) {
				if (preg_match(',<'.$cle.'>(.*)</'.$cle.'>,Uims', $ps, $reg))
					$citime = intval($reg[1]);
		
				if ($citime>0) {
					$textetime = date("d/m/y",$citime)." (".date("H",$citime)."h".date("i",$citime).")";
				}
			}
		}
	}	

	return $textetime;
}

// Le champ PS de l’article est utilise pour stocker
// qui a reserve quel document et quand
function ciform_ps($id_article){
	static $ps = " ";

	if ($ps==" ") {
		$id_article = intval($id_article);
		if ($id_article > 0) {
			$row = sql_fetsel("ps", "spip_articles", "id_article=".$id_article);
			$ps = $row['ps'];
		}
	}
	
	return $ps;	
}

?>