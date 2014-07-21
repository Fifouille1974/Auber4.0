<?php

/*-----------------------------------------------------------------
// Fonctions pour etre compatible avec les acces restreint
------------------------------------------------------------------*/

// Signaletique acces restreints
function cisquel_logo_ari($id_rubrique) {
	$return = "";

	$f = cisquel_fonction_rubrique_restreinte();
	if ($f!="non") {
		if ($f($id_rubrique)) {
			$return = "<div class='ar'>"._T('cisquel:eq_ar')."</div>";
		}
	}

	$fec = cisquel_fonction_rubrique_ec();
	if ($fec!="non") {
		if ($fec($id_rubrique))
			$return = '<div class="ari">'._T('cisquel:eq_ari').'</div>';
	}

	return $return;
}

function cisquel_logoseul_ari($id_rubrique) {
	$return = "";

	$f = cisquel_fonction_rubrique_restreinte();
	if ($f!="non") {
		if ($f($id_rubrique)) {
			$return = '<img src="'.chemin('_images/ar.gif').'" title="'._T('cisquel:eq_ar').'" alt="'._T('cisquel:eq_ar').'" />';			
		}
	}

	$fec = cisquel_fonction_rubrique_ec();
	if ($fec!="non") {
		if ($fec($id_rubrique))
			$return = '<img src="'.chemin('_images/ari.gif').'" title="'._T('cisquel:eq_ari').'" alt="'._T('cisquel:eq_ari').'" />';			
	}

	return $return;
}


// Rubrique EC
function cisquel_rubrique_ec($id_rubrique) {
	$return = "non";

	$fec = cisquel_fonction_rubrique_ec();
	if ($fec!="non") {
		if ($fec($id_rubrique))
			$return = "oui";
	}

	return $return;
}



function cisquel_fonction_rubrique_restreinte() {
	static $return;
	
	if (!$return) {
		if (function_exists('ciar_rub_ar'))
			$return = "ciar_rub_ar";
		else
			$return = "non";
	}

	return $return;
}

function cisquel_fonction_rubrique_ec() {
	static $return;
	
	if (!$return) {
		if (function_exists('ciar_rub_ec'))
			$return = "ciar_rub_ec";
		else
			$return = "non";
	}

	return $return;
}


// cisquel_post_autobr : transforme les sauts de ligne en _
// en tenant compte de certains cas particuliers
function cisquel_post_autobr($texte, $delim="\n_ ") {
	return ciparam_post_autobr($texte, $delim);
}

/*-----------------------------------------------------------------
// Fonctions pour les recherches
------------------------------------------------------------------*/

function cisquel_filtrer_recherche($recherche, $verif_alpha=false) {
	$safe = '';
	$verif = true;

	// recherche avant traitement
	if ($t = _request('recherche'))	{
		$t = trim($t);
	// recherche apres traitement
	} else {	
		$t = trim($recherche);
		include_spip('inc/filtres');	
		$t = filtrer_entites($t);
	}

	if ($t) {
		// supprimer les accents
		include_spip('inc/charsets');
		$tsa = translitteration($t);
	
		// interdire les caracteres dangereux
		// limiter à a-zA-Z0-9 et aux espaces, underscores, apostrophe, guillemets, tirets, points, slash
		$tableau = array(" ","_","'",'"','-','.','/');
		$tscs = str_replace($tableau,'',$tsa);		
		if (!ctype_alnum($tscs)) {
			$verif = false;

			// passer le cas echeant en iso pour avoir la meme longueur avec ou sans accent en utf-8	
			if ($GLOBALS['meta']['charset'] != 'iso-8859-1')
				$tiso = iconv(strtoupper($GLOBALS['meta']['charset']), "ISO-8859-1", $t);
			else
				$tiso = $t;

			// enlever les caracteres speciaux
			$longueur = strlen($tsa);
		    for ($i = 0; $i < $longueur; $i++){
		    	if (ctype_alnum($tsa[$i]) OR in_array($tsa[$i], $tableau))
		    		$safe .= $tiso[$i];
		    	else
		        	$safe .= ' ';
		    }
			// repasser le cas echeant dans le charset du site
			if ($GLOBALS['meta']['charset'] != 'iso-8859-1')
				$safe = iconv("ISO-8859-1", strtoupper($GLOBALS['meta']['charset']), $safe);
		    
		} else {
			$safe = $t;
		}

	    // supprimer les espaces doubles
	    $safe = trim(preg_replace ( '~\s{2,}~' , ' ' , $safe )); 
	}

    if ($verif_alpha)
    	return $verif;
    
   	return $safe;
}

function cisquel_recherche_autorise($recherche) {
	static $resultat = array();

	if (isset($GLOBALS['cisquel_recherche_sans_filtre']) AND $GLOBALS['cisquel_recherche_sans_filtre']=='oui') {
		$return = 'oui';
	} else {
		if (isset($resultat[$recherche])) {
			$return = $resultat[$recherche];
		} else {
			if (cisquel_filtrer_recherche($recherche, true))
				$return = 'oui';
			else
				$return = 'non';
				
			$resultat[$recherche] = $return;
		}
	}
				
	return $return;	
}

function cisquel_accesinterdit($autorise='oui') {
	if ($autorise=='non') {
		include_spip('inc/headers');
		redirige_par_entete(generer_url_public('recherche_interdit'));
	}
	return '';
}

function cisquel_date_vers_age($date) {
	$date_en = '';
	$return = '';

	// verifier
	$date_en = cisquel_verifier_et_convertir_date($date);
	
	// calcul de l'age en jours
	if ($date_en) {
		$ref_time = time();
		$decal = date("U",$ref_time) - date("U", strtotime($date_en));
		$return = intval($decal/(3600*24));		
	}
	
	return $return;
}

function cisquel_age_vers_date($age) {
	$return = '';

	// calcul de l'age en jours
	if ($age = intval($age)) {
		$ref_time = time();
		$timestamp = date("U",$ref_time) - intval($age*3600*24);
		$return = date("Y-m-d",$timestamp);		
	}
	
	return $return;
}

function cisquel_verifier_date($a) {
	$return = '';

   	if (isset($a) AND $a!="") {
	    list($dd,$mm,$yy) = explode("/",$a);
	    if ($dd!="" && $mm!="" && $yy!="") {
		    if (is_numeric($yy) && is_numeric($mm) && is_numeric($dd)) {
		            if (checkdate($mm,$dd,$yy))
						$return = $dd."/".$mm."/".$yy;
		    }
	    } 
   	}

   	return $return;
}

function cisquel_verifier_et_convertir_date($a) {
	$return = false;

   	if (isset($a) AND $a!="") {
	    list($dd,$mm,$yy) = explode("/",$a);
	    if ($dd!="" && $mm!="" && $yy!="") {
		    if (is_numeric($yy) && is_numeric($mm) && is_numeric($dd)) {
		            if (checkdate($mm,$dd,$yy))
						$return = $yy."-".$mm."-".$dd;
		    }
	    } 
   	}

   	return $return;
}

function ci_rub_multirubricage($id_rubrique,$env_id_rubrique){
	$return = $id_rubrique;
	
	if ($env_id_rubrique) {
		$env_id_rubrique = intval($env_id_rubrique);
		if ($env_id_rubrique>0){
			$in = sql_in('ciforme',array('_multirubricage',
										'_multirubricagetrirubrique',
										'_2colonnes',
										'_3colonnes',
										'_avecactualite',
										'_calendrier',
										'_espacededie',
										'_etablissements',
										'_etablissements2',
										'_tableau'));
			
			if (sql_countsel("spip_rubriques", "id_rubrique=".$env_id_rubrique." AND ".$in)>0)
				$return = $env_id_rubrique;
		}
	}

	return $return;
}

function cisquel_temps_telechargement($taille) {
	$return = "";
	
	$taille = intval($taille);
	if ($taille>5242880) {
		// passer en Kb
		$taille = ($taille * 8) / 1000;
		$return = _T('cisquel:temps_telechargement').' : ';
		$return .=  cisquel_convertir_secondes($taille/(512)).' (512 K), ';
		$return .=  cisquel_convertir_secondes($taille/(1024)).' (1024 K), ';
		$return .=  cisquel_convertir_secondes($taille/(2048)).' (2 M), ';
		$return .=  cisquel_convertir_secondes($taille/(5120)).' (5 M).';
	}
	
	return $return;		
}

function cisquel_convertir_secondes($total) {
	$return = "";
	$heure = intval($total / 3600);
	$total = $total - ($heure * 3600);
	$minute = intval($total / 60);
	$total = $total - ($minute * 60);
	$seconde = intval($total);

	if ($heure!=0)
		$return = $heure." h";
		
	if ($minute!=0 OR ($heure!=0 AND $seconde!=0)) {
		if ($return) $return .= " ";
		$return .= $minute." min";
	}
	
	if ($seconde!=0) {
		if ($return) $return .= " ";
		$return .= $seconde." s";
	}

	return $return;	
}

function cisquel_convertir_minutes($total) {
	$return = "";
	$heure = intval($total / 3600);
	$total = $total - ($heure * 3600);
	$minute = round($total / 60);
	
	if ($heure!=0)
		$return = $heure." h";
		
	if ($minute!=0 OR ($heure!=0 AND $seconde!=0)) {
		if ($return) $return .= " ";
		$return .= $minute." min";
	}
	
	return $return;	
}

?>