<?php
/**
 * Plugin ciform
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');

/**
 * Antispam
 *
 * @param nom du formulaire, tableau des champs de type text_area (pour verifier le nombre d'URL)
 * @return $boolean
 */
function ciform_antispam($ciformulaire, $champs_texte) {
	$ci_nombre_url_max = 3;
	$ci_nombre_article_max = 5;
	$ci_duree_nombre_article_max = 1;	// en minutes
	$ci_duree = 30;						// en minutes
	$ciformulaire = $ciformulaire.' : ';
	
	// Verifier si un champ non visible a ete renseigne
	if (strlen(_request('nom_site')) OR strlen(_request('url_site')) OR strlen(_request('nobot'))){
		ciform_tracer_erreur($ciformulaire._T('ciform:spam_champ_interdit'));
		// retour silencieux
		return array('message_erreur'=>_T('ciform:envoi_effectue'));
	}

	// Si plus de 3 URL dans un text_area c'est peut etre un spam
	foreach ($champs_texte as $champ) {
		if (ciform_trop_url($champ,$ci_nombre_url_max)) {
			ciform_tracer_erreur($ciformulaire._T('ciform:spam_n_liens1', array('nombre' => strval($ci_nombre_url_max), 'champ' => $champ)));
			return array('message_erreur'=> _T('ciform:envoi_non_effectue')._T('ciform:spam_n_liens2', array('nombre' => strval($ci_nombre_url_max), 'champ' => $champ)));
		}	
	}
	
	// Verifier la cle
	if (!ciform_verifier_cle($ci_duree)) {
		ciform_tracer_erreur($ciformulaire._T('ciform:spam_trop_lent1', array('duree' => strval($ci_duree))));
		return array('message_erreur'=> _T('ciform:envoi_non_effectue')._T('ciform:spam_trop_lent2', array('duree' => strval($ci_duree))));
	}

	// Verifier si surcharge
	if (ciform_surcharge($ci_nombre_article_max,$ci_duree_nombre_article_max)) {	
		ciform_tracer_erreur($ciformulaire._T('ciform:spam_surcharge1', array('nombre' => strval($ci_nombre_article_max), 'duree' => strval($ci_duree_nombre_article_max))));
		return array('message_erreur'=> _T('ciform:envoi_non_effectue')._T('ciform:spam_surcharge2', array('nombre' => strval($ci_nombre_article_max), 'duree' => strval($ci_duree_nombre_article_max))));
	}
	
	return false;
}


// Si il y a trop d'URL dans le texte c'est peut etre un spam
function ciform_trop_url($champ,$nombre) {
	$return = false;
	if (intval($nombre)<1)
		$nombre = 3;
	$limite = $nombre + 1;

	$citexte = _request($champ);
	if ($citexte) {
		if (preg_match_all(',http,i',$citexte, $regshttp)) {
			if (count($regshttp[0]) > $nombre)
				$return = true;
		} elseif (preg_match_all(',www,i',$citexte, $regswww)) {
			if (count($regswww[0]) > $nombre)
				$return = true;			
		} elseif (preg_match_all(",<a\b[^>]*(/>|>.*</a\b[^>]*>|>),UimsS",$citexte, $regs, PREG_PATTERN_ORDER)) {
			if (count($regs[0]) > $nombre)
				$return = true;
		}
	}
	
	return $return;
}


// nombre d'articles proposes dans les n dernieres secondes
function ciform_surcharge($maxrecord=0,$minutes=0) {
	$return= false;
	
	if (!($maxrecord>0)) $maxrecord = 5; 	// par défaut 5 enregistrements
	$secondes = intval($minutes)*60;
	if (!($secondes>0)) $secondes = 60; // par défaut 60 secondes (soit 1 minutes)
	$mydate = date("YmdHis", time() - $secondes);
	
	$result = sql_select("COUNT(id_article) AS nbenreg", "spip_articles", "statut='prop' AND maj > $mydate","","");

	if ($row = sql_fetch($result)) {
		$nbenreg = $row['nbenreg'];

		// c'est peut etre un robot de spam
		if ($nbenreg > $maxrecord) {
			$return= true;
		}
//		if ($nbenreg==$maxrecord) ciform_tracer_erreur($type);
	}
		
	return $return;
}


// cle
function ciform_cle() {
	$citime = time();
	$cle = ciform_creer_cle(substr(strval($citime),0,-2),true);
	return $cle;
}


// calculer la cle
function ciform_creer_cle($ciorig,$debut=true) {
	$return = "";
	$deb="";
	if ($debut) {
		$fin = substr($ciorig,-2);
		if ($fin < 30) {
			$deb="s4";
		} elseif ($fin < 60) {
			$deb="rs";
		} elseif ($fin < 80) {
			$deb="bc";
		} else {
			$deb="u7";
		}
	}			
	$trans = substr(md5($ciorig),1,5);
	$res = str_replace(".","a",$trans);
	$return = $deb.str_replace("0","1",$res);

	return $return;
}


// verifier la cle
function ciform_verifier_cle($ci_duree) {
	$return = false;
	$citime = time();
	$compteur = 0;
	$tableau = array("s4","rs","bc","u7");
	if ($ci_duree)
		$compteur_max = round(($ci_duree*60)/100);
	else 
		$compteur_max = 20;
		
	
	if ($key = _request('ciformcle')) {
		if (in_array(substr($key,0,2),$tableau)) {
			$cle = substr($key,2);
			if ($cle) {
				// on remonte jusqu'à n fois 100 secondes en arrière soit près de 'ci_duree' en minutes
				while ($compteur < $compteur_max) {
					$testime = substr(strval($citime-(100*$compteur)),0,-2);
					if ($cle==ciform_creer_cle($testime,false)) {
						$return = true;
						break;
					}
					$compteur++;
				}
			}
		}
	}
	return $return;
}


// tracer les erreurs
function ciform_tracer_erreur($type='') {
	spip_log("erreur ciform ($type): ".print_r($_POST, true));
}

?>