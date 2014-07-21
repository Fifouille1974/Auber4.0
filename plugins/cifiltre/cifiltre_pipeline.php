<?php
/**
 * Plugin cifiltre
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */



function cifiltre_affichage_final($texte) {
	global $fond;
	
	// si on n'est pas connecte et qu'on est filtre on se redirige vers l'authentification
	if (!$GLOBALS['visiteur_session']) {
		if (cifiltre_ip_site_public($fond) OR cifiltre_host_site_public($fond)) {
			include_spip('inc/headers');
			redirige_par_entete(generer_url_public('login', 'url='.rawurlencode(parametre_url(self(), 'var_mode', $_GET['var_mode'], '&')), true));
		}
	}
	
	return $texte;
}

/**
 * Pour toutes les adresses IP,
 * ou bien uniquement pour certaines plages d'adresses IP,
 * ou bien pour tous les adresses IP sauf certaines plages d'adresses IP,
 * il sera necessaire de s'authentifier pour consulter le site public.
 *	
 * @param : $fond
 * @return : boolean
 */
function cifiltre_ip_site_public($fond) {
	$ci_redirige = false;	// ne redirige pas par defaut

	cifiltre_lire_meta();
	$cifiltreipsp =	$GLOBALS['ciconfig']['cifiltreipsp'];
	$cifiltreipspnet = $GLOBALS['ciconfig']['cifiltreipspnetv4'];
	
	// ordre de recherche par défaut
	$cifiltreipspordre = array('HTTP_X_FORWARDED_FOR','REMOTE_ADDR');
	
	// ordre de recherche personnalise dans le fichier de parametrage config/_config_cifiltre.php
	if (isset($GLOBALS['ciconfig']['cifiltreipspordre'])) {
		if (is_array($GLOBALS['ciconfig']['cifiltreipspordre'])) {
			$cifiltreipspordre = $GLOBALS['ciconfig']['cifiltreipspordre'];	
		}
	}

	if ($cifiltreipsp AND in_array($cifiltreipsp,array('tous','uniquement','sauf'))){

		if (!in_array($fond, array("login","cicas_erreur1","cicas_erreur2","cicas_erreur3"))) {

			$ci_ip_binary = "";
			$ci_redirige = true;	// redirige
			if ($cifiltreipsp=='tous') {
				// rediriger
			} else {
				// determination de l'IP
				$ci_ip = "";
				foreach ($cifiltreipspordre as $valeur) {
					if (isset($_SERVER[$valeur])) {
						if ($_SERVER[$valeur]) {
							$ci_ip = $_SERVER[$valeur];
							break;
						}
					}
				}

				if ($ci_ip) {
					// IP v6 : ne pas le traiter pour l'instant
					if (substr_count($ip,":") > 0)
				        $ci_ip_binary_string = "";
					// IP v4
					else
				        $ci_ip_binary_string = sprintf("%032b",ip2long($ci_ip));
				}
				
				if ($ci_ip_binary_string) {
					if (isset($cifiltreipspnet)) {
						if (is_array($cifiltreipspnet)) {
							// l'IP est-elle dans la liste des réseaux ?
							// exemples : "172.16.0.0"=>"12", "10.0.0.0"=>"8"
							$ci_dans_liste = false;
							foreach ($cifiltreipspnet as $ci_netaddr=>$ci_netmask) {
							    	$ci_netmask = intval($ci_netmask);
							    	$ci_netaddr_binary_string = "";
									if ($ci_netaddr AND $ci_netmask >0)
								        $ci_netaddr_binary_string = sprintf("%032b",ip2long($ci_netaddr));

									if ($ci_netaddr_binary_string) {										
								        if (strncasecmp($ci_ip_binary_string,$ci_netaddr_binary_string,$ci_netmask) === 0){
											$ci_dans_liste = true;
								        	break;
								        }
									}
							}
							if ($cifiltreipsp=='uniquement') {
								// ne pas rediriger si pas dans la liste
								if (!$ci_dans_liste) $ci_redirige = false;
							} elseif ($cifiltreipsp=='sauf') {
								// ne pas rediriger si dans la liste
								if ($ci_dans_liste) $ci_redirige = false;								
							}
						}
					}					
				}
			}
		}
	}

	return $ci_redirige;
}


/**
 * Pour toutes les hosts,
 * ou bien uniquement pour certains hosts,
 * ou bien pour tous les hosts sauf hosts,
 * il sera necessaire de s'authentifier pour consulter le site public.
 *	
 * @param : $fond
 * @return : boolean
 */
function cifiltre_host_site_public($fond) {
	$ci_redirige=false;	// ne redirige pas par defaut

	cifiltre_lire_meta();
	$cifiltrehostsp =	$GLOBALS['ciconfig']['cifiltrehostsp'];
	$cifiltrehostspurls = $GLOBALS['ciconfig']['cifiltrehostspurls'];

	// ordre de recherche par défaut (celui de phpCAS)
	$cifiltrehostspordre = array('HTTP_X_FORWARDED_SERVER','SERVER_NAME','HTTP_HOST');
	
	// ordre de recherche personnalise dans le fichier de parametrage config/_config_cas.php
	if (isset($GLOBALS['ciconfig']['cifiltrehostspordre'])) {
		if (is_array($GLOBALS['ciconfig']['cifiltrehostspordre'])) {
			$cifiltrehostspordre = $GLOBALS['ciconfig']['cifiltrehostspordre'];	
		}
	}
	
	if ($cifiltrehostsp AND in_array($cifiltrehostsp,array('tous','uniquement','sauf'))){
	
		if (!in_array($fond, array("login","cicas_erreur1","cicas_erreur2","cicas_erreur3"))) {
			
			$ci_host = "";
			$ci_redirige = true;	// redirige
			if ($cifiltresp=='tous') {
				// rediriger
			} else {
				// determination du HOST
				foreach ($cifiltrehostspordre as $valeur) {
					if (isset($_SERVER[$valeur])) {
						if ($_SERVER[$valeur]) {
							$ci_host = $_SERVER[$valeur];
							break;
						}
					}
				}
			
				if ($ci_host) {
					if (isset($cifiltrehostspurls)) {
						if (is_array($cifiltrehostspurls)) {
							// le HOST est-il dans la liste ?
							$ci_dans_liste = false;
							foreach ($cifiltrehostspurls as $ciurl) {
								// HOST se terminant par ...
								if (substr($ciurl,0,1)=='.') {
									if ($ciurl==substr($ci_host,-strlen($ciurl))){
										$ci_dans_liste = true;
										break;
									}
								} else {
									if ($ciurl==$ci_host){
										$ci_dans_liste = true;
										break;
									}
								}
							}
							if ($cifiltrehostsp=='uniquement') {
								// ne pas rediriger si pas dans la liste
								if (!$ci_dans_liste) $ci_redirige = false;
							} elseif ($cifiltrehostsp=='sauf') {
								// ne pas rediriger si dans la liste
								if ($ci_dans_liste) $ci_redirige = false;								
							}
						}
					}					
				}
			}
		}
	}
	
	return $ci_redirige;
}


/**
 * Lecture des parametres de configuration du plugin
 * et alimentation de variables globales
 * S'il existe, le parametrage par fichier est prioritaire
 *
 * @param : aucun
 * @return : false si parametrage par fichier, sinon true
 */
function cifiltre_lire_meta() {
	
	$return = true;
	
	if (!isset($GLOBALS['ciconfig']['cifiltreipsp'])) {

		$GLOBALS['ciconfig']['cifiltreipsp'] = '';
		$GLOBALS['ciconfig']['cifiltreipspnetv4'] = array();
		$GLOBALS['ciconfig']['cifiltreipspordre'] = array();
		$GLOBALS['ciconfig']['cifiltrehostsp'] = '';
		$GLOBALS['ciconfig']['cifiltrehostspurls'] = array();
		$GLOBALS['ciconfig']['cifiltrehostspordre'] = array();

		
		$f = _DIR_RACINE . _NOM_PERMANENTS_INACCESSIBLES . '_config_cifiltre.php';
	
		if (@file_exists($f)) {
			// parametrage par fichier
			include_once($f);
			$return = false;
				
		} else {
			// configuration du plugin
			if (isset($GLOBALS['meta']['cifiltre'])) {
				$tableau = array();
				$tableau = @unserialize($GLOBALS['meta']['cifiltre']);
		
				$GLOBALS['ciconfig']['cifiltreipsp'] = $tableau['cifiltreipsp'];
				$GLOBALS['ciconfig']['cifiltreipspnetv4'] = $tableau['cifiltreipspnetv4'];
				$GLOBALS['ciconfig']['cifiltreipspordre'] = $tableau['cifiltreipspordre'];
				$GLOBALS['ciconfig']['cifiltrehostsp'] = $tableau['cifiltrehostsp'];
				$GLOBALS['ciconfig']['cifiltrehostspurls'] = $tableau['cifiltrehostspurls'];
				$GLOBALS['ciconfig']['cifiltrehostspordre'] = $tableau['cifiltrehostspordre'];
			}
		}
	
		// valeur par défaut
		if (!isset($GLOBALS['ciconfig']['cifiltreipsp']))
			$GLOBALS['ciconfig']['cifiltreipsp'] = 'non';
		elseif ($GLOBALS['ciconfig']['cifiltreipsp']=='')
			$GLOBALS['ciconfig']['cifiltreipsp'] = 'non';
	
		if (!isset($GLOBALS['ciconfig']['cifiltrehostsp']))
			$GLOBALS['ciconfig']['cifiltrehostsp'] = 'non';
		elseif ($GLOBALS['ciconfig']['cifiltrehostsp']=='')
			$GLOBALS['ciconfig']['cifiltrehostsp'] = 'non';
	}
		
    return $return;
}

?>