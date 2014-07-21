<?php
/**
 * Plugin citrace : tracer certaines actions
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
if (!defined("_ECRIRE_INC_VERSION")) return;


/**
 * Elaborer une trace avec :
 * la date
 * l'adresse IP
 * l'id du process PHP
 * l'id de l'auteur
 * l'identifiant CAS (compatibilite avec le plugin CICAS)
 * l'objet et son identifiant
 * l'action
 * le message
 * le cas echeant, le niveau de protection de la rubrique (compatibilite avec le plugin CIAR)
 * le cas echeant, les sites de publication de la rubrique (compatibilite avec le plugin CIMS)
 * le cas echeant, le site en cours (compatibilite avec le plugin CIMS)
 */
function citrace_contenu($objet, $id_objet, $action, $commentaire, $id_rubrique=0) {	

	// separateur de champs
	$sep = ' | ';

	// compatibilite avec le plugin CICAS
	// l'identifiant SSO est stocke dans le champ email ou bien dans le champ login
	$id_cas = 'email';
	if (defined('_DIR_PLUGIN_CICAS')){
		include_spip('inc/cicas_commun');
		// lire la configuration du plugin
		cicas_lire_meta();
		if ($GLOBALS['ciconfig']['cicasuid']=="login")
			$id_cas = 'login';		
	}	

	// la date
	$return = date("d/m/Y H:i:s");
	// l'adresse IP
	$return .= $sep.citrace_ip();
	// l'id de l'auteur
	$return .= $sep.(isset($GLOBALS['visiteur_session']['id_auteur']) ? 'auteur'.$GLOBALS['visiteur_session']['id_auteur'] : '');
	// l'identifiant CAS (compatibilite avec le plugin CICAS)
	$return .= $sep.(isset($GLOBALS['visiteur_session'][$id_cas]) ? $GLOBALS['visiteur_session'][$id_cas] : '');
	// l'objet et son identifiant
	$return .= $sep.$objet.$id_objet;
	// l'action
	$return .= $sep.$action;
	// le message
	$return .= $sep.$commentaire;
	// le cas echeant, le niveau de protection de la rubrique (compatibilite avec le plugin CIAR)
	$return .= $sep.citrace_protection_rubrique($id_rubrique);
	// le cas echeant, les sites de publication de la rubrique (compatibilite avec le plugin CIMS)
	$return .= $sep.citrace_sites_publication_rubrique($id_rubrique);
	// le cas echeant, le site en cours (compatibilite avec le plugin CIMS)
	$return .= $sep.citrace_site_en_cours();

	$return = preg_replace("/\n*$/", "\n", $return);
	
	return $return;
}


/**
 * Ecrire les traces dans un fichier
 * Les constantes suivantes peuvent etre placees dans un fichier d'options
 * _CITRACE_PERIODE_ROTATION : 'mois' ou 'semaine' ou 'jour' (valeur par defaut : 'mois')
 * _CITRACE_NOMBRE_ROTATIONS : un nombre entier (valeur par defaut : 12 si 'mois', 52 si 'semaine', 365 si 'jour')
 * _CITRACE_TAILLE_MAX : un nombre entier en Ko (valeur par defaut : 10000) taille maximale du fichier de trace en cours
 * _CITRACE_COMPRESSE : 'oui' ou 'non' (valeur par defaut : 'oui') compresse chaque fichier sauf celui en cours
 * _CITRACE_REPERTOIRE : nom du repertoire sans slash de fin (valeur par defaut : 'tmp')
 */
function citrace_log($trace) {

	$trace_nom = 'citrace';
	$trace_suffix = '.log';

	// prise en compte des constantes
	$periode_rotation = ( (defined('_CITRACE_PERIODE_ROTATION') AND in_array(_CITRACE_PERIODE_ROTATION,array('mois','semaine','jour'))) ? _CITRACE_PERIODE_ROTATION : 'mois');
	
	switch($periode_rotation) {

	case 'semaine':	
		// le lundi de la semaine
		$jour_semaine = date('w');
		if ($jour_semaine==0)
			$delta = 6;
		else
			$delta = $jour_semaine-1;
			
		$periode_date = date("Ymd", time()-($delta*3600*24));
		break;

	case 'jour':
		$periode_date = date("Ymd");
		break;

	default:
		$periode_date = date("Ym")."01";
	}
	
	if (defined('_CITRACE_NOMBRE_ROTATIONS') AND intval(_CITRACE_NOMBRE_ROTATIONS)>0){
		$nombre_rotations = intval(_CITRACE_NOMBRE_ROTATIONS);
	} else {
		$tableau_rotations = array('mois'=>12,'semaine'>52,'jour'=>365);
		if (isset($tableau_rotations[$periode_rotation]))
			$nombre_rotations = $tableau_rotations[$periode_rotation];
		else
			$nombre_rotations = 12;
	}

	$taille_max = ( (defined('_CITRACE_TAILLE_MAX') AND intval(_CITRACE_TAILLE_MAX)>0) ? intval(_CITRACE_TAILLE_MAX) : 10000);	// en Ko	
	
	$compresse = ( (defined('_CITRACE_COMPRESSE') AND in_array(_CITRACE_COMPRESSE,array('oui','non'))) ? _CITRACE_COMPRESSE : 'oui');	
	
	$trace_dir = ((defined('_DIR_LOG') AND !defined('_DIR_PLUGIN_CIMS')) ? _DIR_LOG : _DIR_RACINE._NOM_TEMPORAIRES_INACCESSIBLES);
	
	if (defined('_CITRACE_REPERTOIRE') AND _CITRACE_REPERTOIRE){
		$repertoire = _CITRACE_REPERTOIRE;
		// securite
	    if ((strpos($repertoire,'../') === false)
			AND !(preg_match(',^\w+://,', $repertoire))) {
		
			if (substr($repertoire, 0, 1)=="/")
				$repertoire = substr($repertoire, 1);

			if (substr($repertoire, -1)=="/")
				$repertoire = substr($repertoire, 0, -1);
				
			if (is_dir(_DIR_RACINE.$repertoire))
				$trace_dir = _DIR_RACINE.$repertoire.'/';
		}
	}
	
	$trace_fichiersanssuffix = $trace_dir . $trace_nom . '_'. $periode_date;
	$trace_fichier = $trace_fichiersanssuffix . $trace_suffix;
	
	// verifier la taille
	$taille_trop_elevee = false;
	if (@is_readable($trace_fichier) AND (!$s = @filesize($trace_fichier) OR $s > $taille_max * 1024)) 
		$taille_trop_elevee = true;

	// ecrire la trace	
	$f = @fopen($trace_fichier, "ab");
	if ($f) {
		fputs($f, $trace);
		fclose($f);
	}

	// si taille trop elevee, repartir
	if ($taille_trop_elevee){
		// tourner jusqu'a trouver un numero correct en tenant compte des zip
		$n = 1;
		while (true){
			$newFile = $trace_fichiersanssuffix.'-'.$n.$trace_suffix;
			$n++;
			if (!@file_exists($newFile) AND !@file_exists($newFile.'.zip'))
				break;		 
		}	

		rename($trace_fichier, $newFile);
	}
	
	// suppression de fichiers si dépassement du nombre de rotation
	if ($nombre_rotations>0 AND function_exists('spip_unlink')) {

		// ne pas compter le fichier en cours
		$nombre_rotations++;

		switch($periode_rotation) {

		case 'semaine':
			$citrace_duree = $nombre_rotations * 7;
			break;
	
		case 'jour':
			$citrace_duree = $nombre_rotations;
			break;
	
		default:
			$citrace_duree = round($nombre_rotations * (365/12));
		}
		
	    $ci_expire = intval(date("Ymd", time() - (86400 * $citrace_duree)));
		$longueur_logname = strlen($trace_nom);

// spip_log("CI expire : ".$ci_expire);		
		
		$d = @opendir($trace_dir);
		if ($d) {
			while (($fichier = @readdir($d)) !== false) {
				if (substr($fichier,0,$longueur_logname)==$trace_nom) {
					if (intval(substr($fichier,$longueur_logname+1,8))<=$ci_expire){
						spip_unlink($trace_dir.$fichier);
					}
				}
			}
			closedir($d);
		}
	}
	
	// compresser le cas echeant
	if ($compresse=='oui' AND function_exists('spip_unlink')) {
		$d = @opendir($trace_dir);
		if ($d) {
			include_spip('inc/pclzip');
			while (($fichier = @readdir($d)) !== false) {
				// pas le fichier en cours
				if ($trace_dir.$fichier != $trace_fichier) {
					// que les autres fichiers de trace ...
					if (substr($fichier,0,$longueur_logname)==$trace_nom) {
						// ... s'il ne sont pas deja compresses
						if (substr($fichier,-4)!='.zip'){
							// et s'ils ne sont pas trop gros
							if($s=@filesize($trace_dir.$fichier) AND $s < 10100 * 1024){
								$archive = new PclZip($trace_dir.$fichier.'.zip');
								$v_list = $archive->create($trace_dir.$fichier);
								// supprimer la version non compressee
								if ($v_list)
									spip_unlink($trace_dir.$fichier);
							}
						}
					}
				}
			}
			closedir($d);
		}
	}

	return true;
}

function citrace_ip() {
	// par defaut
	$ci_ip = $GLOBALS['ip'];
	
	// ordre de recherche par defaut
	$ordre = array('HTTP_X_FORWARDED_FOR','REMOTE_ADDR');
	
	// ordre de recherche personnalise dans la constante _CI_ORDRE_IP
	if (defined('_CI_ORDRE_IP') AND _CI_ORDRE_IP AND is_array(_CI_ORDRE_IP))
		$ordre = _CI_ORDRE_IP;

	// determination de l'IP
	foreach ($ordre as $valeur) {
		if (isset($_SERVER[$valeur]) AND $_SERVER[$valeur]) {
			$ci_ip = $_SERVER[$valeur];
			break;
		}
	}
	
	return $ci_ip;
}

function citrace_protection_rubrique($id_rubrique) {
	$return = '';

	if ($id_rubrique = intval($id_rubrique)){
		if (defined('_DIR_PLUGIN_CIAR')){
			include_spip('ciar_fonctions');		
			$protection = ciar_protection($id_rubrique);
			if (!$protection)
				$protection = ciar_protection_par_heritage($id_rubrique);
			if (!$protection)
				$protection = '_acces_libre';
	
			$return = 'la rubrique '.$id_rubrique.' est en '.$protection;
		}
	}

	return $return;	
}

function citrace_sites_publication_rubrique($id_rubrique) {
	$return = '';

	if ($id_rubrique = intval($id_rubrique)){
		if (defined('_DIR_PLUGIN_CIMS')){
			include_spip('cims_fonctions');		
			$tableau_sites = cims_tableau_sites_de_la_rubrique($id_rubrique);
			$return = 'la rubrique '.$id_rubrique.' est publiee sur les sites:'.implode(',',$tableau_sites);
		}
	}

	return $return;
}

function citrace_site_en_cours() {
	$return = '';

	if (defined('_DIR_PLUGIN_CIMS'))
		$return = 'site en cours:'.cims_site_en_cours();

	return $return;
}

?>