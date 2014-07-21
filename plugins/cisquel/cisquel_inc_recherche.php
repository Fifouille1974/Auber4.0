<?php
/**
 * Plugin squelettes de base
 * Auteur : Christophe IMBERTI
 * Licence GPL
 */


/**
 * Lecture du parametrage par fichier du plugin
 *
 * @param : aucun
 * @return : false si parametrage par fichier, sinon true
 */
function cisquel_lire_meta() {
	
	$return = true;

	if (!isset($GLOBALS['ciconfig']['cisqueloptionsrecherchecolcentrale'])) {

		// Afficher dans la colonne centrale le choix de restreindre la recherche avec des options de recherche (filtre par date et choix du tri)
		// valeurs possible : 'oui', 'non' (la valeur par defaut est 'oui')
		// Exemple :
		$GLOBALS['ciconfig']['cisqueloptionsrecherchecolcentrale'] = 'oui';
		
		// Afficher dans la colonne de gauche le choix de restreindre la recherche avec des options de recherche (filtre par date et choix du tri)
		// valeurs possible : 'oui', 'non' (la valeur par defaut est 'oui')
		// Exemple :
		$GLOBALS['ciconfig']['cisqueloptionsrecherchecolgauche'] = 'oui';
		
		// Afficher le choix d'elargir la recherche via un autre moteur de recherche
		// valeurs possible : 'oui', 'non' (la valeur par defaut est 'non')
		$GLOBALS['ciconfig']['cisquelchoixelargirrecherche'] = 'non';
		
		// tableau des choix d'elargir la recherche via un autre moteur de recherche selon le type de debut ou de fin d'adresse d'appel du site SPIP
		// valeurs possible : 'oui', 'non' (la valeur par defaut est 'non')
		// Exemple :
		$GLOBALS['ciconfig']['cisquelchoixelargirrecherche_selon_host'] = array();
		
		// Afficher la recherche sur le site et le contenu de ses documents
		// valeurs possible : 'oui', 'non' (la valeur par defaut est 'non')
		$GLOBALS['ciconfig']['cisquelchoixrecherchesiteetdocuments'] = 'non';
		
		// Afficher la recherche sur tous les sites
		// valeurs possible : 'oui', 'non' (la valeur par defaut est 'non')
		$GLOBALS['ciconfig']['cisquelchoixrecherchetouslessites'] = 'non';
		
		// moteur de recherche par defaut
		// valeurs possible : 'mnogosearch', 'google' (la valeur par defaut est 'google')
		$GLOBALS['ciconfig']['cisquelmoteur'] = 'google';
		
		// tableau des moteurs de recherche selon le type de terminaison de l'adresse d'appel du site SPIP
		// valeurs possible des moteurs : 'mnogosearch', 'google'
		// exemple : array('.i2' => 'mnogosearch', '.gouv.fr' => 'google')
		$GLOBALS['ciconfig']['cisquelmoteurs_selon_host'] = array();
		
		// URL du moteur de recherche par defaut (sans http://)
		// la valeur par defaut est 'www.google.fr/search'
		$GLOBALS['ciconfig']['cisquelmoteururl'] = 'www.google.fr/search';
		
		// tableau des URLS du moteur de recherche selon le type de terminaison de l'adresse d'appel du site SPIP
		// exemple : array('.i2' => 'recherche.....i2/index.mno', '.gouv.fr' => 'www.google.fr/search')
		$GLOBALS['ciconfig']['cisquelmoteursurls_selon_host'] = array();
		
		// domaine de recherche eventuel sur tous les sites (utile pour google)
		// exemple : '.developpement-durable.gouv.fr'
		$GLOBALS['ciconfig']['cisqueldomainetoussites'] = '';
		
		// valeurs de certains parametres de mnogosearch
		$GLOBALS['ciconfig']['cisquelmnogosearch'] = array(
			'avancee' => '',
			's' => 'R',
			'ps' => '15',
			'cat' => '',
			'wf' => '0A8A0000AAF7FACC1',
			'fmt' => 'long',
			'ftm' => 'long',
			'GroupBySite' => 'no',
			'tl' => 'yes',
			'm' => 'all',
			'categorie' => '',
			'type' => '',
			'dp' => '0',
			'dt' => 'back');

		// Affiner la recherche pour les types de documents (hors html) suivants (mettre l'extension en minuscules sans le point)
		// Attention : les libelles doivent etre independants de la langue (fr, es, de)
		$GLOBALS['ciconfig']['cisqueltypesdocuments'] = array('pdf'=>'Adobe PDF (*.pdf)','odt'=>'OpenOffice Text (*.odt)');
			
		// Types de documents (hors html) indexes par mnogosearch (mettre l'extension en minuscules sans le point)
		$GLOBALS['ciconfig']['cisquelmnotypesdocuments'] = array('pdf','odt','doc');
			
		// Option qui necessite le template de mnogosearch du MEDDTL
		// Afficher dans la page les resultats trouves par mnogosearch
		// valeurs possible : 'oui', 'non' (la valeur par defaut est 'non')
		$GLOBALS['ciconfig']['cisquelgetmnosite'] = 'non';
		
		// Option qui necessite le template de mnogosearch du MEDDTL
		// Afficher dans la page les resultats trouves par mnogosearch pour TOUS les sites
		// valeurs possible : 'oui', 'non' (la valeur par defaut est 'non')
		// Exemple :
		$GLOBALS['ciconfig']['cisquelgetmnotoussites'] = 'non';			
		
		// parametrage par le fichier _config_cisquel.php dans /config
		// ou parametrage par le fichier _config_commun_cisquel.php dans le dossier du plugin
		// Si les deux fichiers sont presents,
		// le contenu de celui present dans /config est prioritaire car il est propre au site
		// (le fichier _config_cisquel.php est charge apres le fichier _config_commun_cisquel.php).
		// Cela permet d'avoir les parametres communs a tous les sites dans _config_commun_cisquel.php
		// et les parametres propres a un site dans _config_cisquel.php		
		if (@file_exists($f2 = _DIR_RACINE . _DIR_PLUGIN_CISQUEL . '_config_commun_cisquel.php')) {
			include_once($f2);
			$return = false;
		}
		if (@file_exists($f = _DIR_RACINE . _NOM_PERMANENTS_INACCESSIBLES . '_config_cisquel.php')) {
			include_once($f);
			$return = false;
		}
	}
		
    return $return;
}

/**
 * Valeur d'un parametre du plugin
 *
 * @param : parametre du plugin
 *			cle dans un tableau de parametres
 * @return : valeur du parametre
 */
function cisquel_config($param, $param_cle='') {
	$return = false;

	// lire la configuration du plugin
	cisquel_lire_meta();
	
	if ($param) {
		if (isset($GLOBALS['ciconfig'][$param])) {
			$return = $GLOBALS['ciconfig'][$param];
			if (is_array($GLOBALS['ciconfig'][$param])) {
				if ($param_cle) {
					if (isset($GLOBALS['ciconfig'][$param][$param_cle]))
						$return = $GLOBALS['ciconfig'][$param][$param_cle];
				}
			}
		}
	}
	
    return $return;
}

function cisquel_choixelargirrecherche($valeur=false) {
	$return = 'non';
	
	// lire la configuration du plugin
	cisquel_lire_meta();
	
	// choix par defaut
	if (isset($GLOBALS['ciconfig']['cisquelchoixelargirrecherche']))
		$return = $GLOBALS['ciconfig']['cisquelchoixelargirrecherche'];

	// choix par url	
	if (isset($GLOBALS['ciconfig']['cisquelchoixelargirrecherche_selon_host'])) {
		$tableau_urls = $GLOBALS['ciconfig']['cisquelchoixelargirrecherche_selon_host'];
		if (is_array($tableau_urls)){
			$ci_host = cisquel_url_host();
			while (list($ciurl, $valurl) = each($tableau_urls)) {
				// HOST se terminant par ...
				if (substr($ciurl,0,1)=='.') {
					if ($ciurl==substr($ci_host,-strlen($ciurl))){
						$return = $valurl;
						break;
					}
				// HOST qui commence par ...
				} elseif (substr($ciurl,-1)=='.') {
					if ($ciurl==substr($ci_host,0,strlen($ciurl))){
						$return = $valurl;
						break;
					}
				}
			}
		}
	}

	return $return;
}

/**
 * Détermination du HOST
 *
 * @param : aucun
 * @return : host
 */
function cisquel_url_host() {

	$ci_host = "";

	// lire la configuration du plugin
	cisquel_lire_meta();
	
	// ordre de recherche par défaut
	$cisquelhostordre = array('HTTP_X_FORWARDED_SERVER','SERVER_NAME','HTTP_HOST');
	
	// ordre de recherche personnalise dans le fichier de parametrage config/_config_cisquel.php
	if (isset($GLOBALS['ciconfig']['cisquelhostordre'])) {
		if (is_array($GLOBALS['ciconfig']['cisquelhostordre'])) {
			$cisquelhostordre = $GLOBALS['ciconfig']['cisquelhostordre'];
		}
	}
	
	foreach ($cisquelhostordre as $valeur) {
		if (isset($_SERVER[$valeur])) {
			if ($_SERVER[$valeur]) {
				$ci_host = $_SERVER[$valeur];
				break;
			}
		}
	}

	return $ci_host;
}

function cisquel_url_moteur() {

	$ciurlmoteur = '';
	$ci_host = cisquel_url_host();

	// lire la configuration du plugin
	cisquel_lire_meta();

	// adresse par defaut du moteur
	$ciurlmoteur = $GLOBALS['ciconfig']['cisquelmoteururl'];
	
	// autre adresse du moteur selon le type de terminaison de l'adresse d'appel du site SPIP
	if (isset($GLOBALS['ciconfig']['cisquelmoteursurls_selon_host'])) {
		if (is_array($GLOBALS['ciconfig']['cisquelmoteursurls_selon_host'])) {
			while (list($terminaison, $valurl) = each($GLOBALS['ciconfig']['cisquelmoteursurls_selon_host'])) {
				if (substr($ci_host,-strlen($terminaison))==$terminaison) {
					$ciurlmoteur = $valurl;
					break;
				}
			}
		}
	}

	return $ciurlmoteur;	
}

function cisquel_moteur() {

	$cimoteur = '';

	// lire la configuration du plugin
	cisquel_lire_meta();

	// moteur par defaut
	$cimoteur = $GLOBALS['ciconfig']['cisquelmoteur'];
	
	// autre moteur selon le type de terminaison de l'adresse d'appel du site SPIP
	if (isset($GLOBALS['ciconfig']['cisquelmoteurs_selon_host'])) {
		if (is_array($GLOBALS['ciconfig']['cisquelmoteurs_selon_host'])) {
			while (list($terminaison, $valurl) = each($GLOBALS['ciconfig']['cisquelmoteurs_selon_host'])) {
				if (substr($ci_host,-strlen($terminaison))==$terminaison) {
					$cimoteur = $valurl;
					break;
				}
			}
		}
	}

	return $cimoteur;	
}

function cisquel_getmno($recherche,$env) {
	$contenu = '';
	
	// lire la configuration du plugin
	cisquel_lire_meta();
	$cisquelgetmnosite = $GLOBALS['ciconfig']['cisquelgetmnosite'];
	$cisquelgetmnotoussites = $GLOBALS['ciconfig']['cisquelgetmnotoussites'];	
	
	// indispensable (necessite un #ENV sans filtre)
	$unenv = unserialize($env);
	
	// securite
	$p = cisquel_verifier_parametres($unenv);
	
	
	if (($p['elargir']=='doc' AND $cisquelgetmnosite=='oui') OR ($p['elargir']=='toussites' AND $cisquelgetmnotoussites=='oui')) {
		
		if ($p['elargir']=='toussites' AND $cisquelgetmnotoussites=='oui')
			$toussites = true;
		else
			$toussites = false;

		if ($p['elargir']=='doc' AND $cisquelgetmnosite=='oui')
			$getdoc = true;
		else
			$getdoc = false;
			
		$url = cisquel_url_recherche_mno($p, $toussites, $getdoc);

		// pagination
		if ($p['np'])
			$url .='&np='.$p['np'];
		
		include_spip('inc/distant');
		$contenu = recuperer_page($url);
		if ($contenu) {
			$pos1 = strpos($contenu, '<div id="results">');
			$pos2 = strpos($contenu, '<div class="folio">');
			if ($pos1!==false AND $pos2!==false) {
				
				// charset du moteur
				$charset_moteur='iso-8859-1';
				if (preg_match('#<meta (.*?) content=(.*?)>#s', $contenu, $reg)) {
					if (strpos($reg[0], 'charset=utf-8')!==false)
						$charset_moteur='utf-8';
				}

				// Remplacer les liens de pagination
				$pagination = substr($contenu,$pos2);
				$pos3 = strpos($pagination, '</div>');
				$pagination = substr($pagination,0,$pos3).'</div></div>';				
				$self = self();
				if (preg_match_all('#<a href=(.*?)>#', $pagination, $matches)) {
					for ($i=0; $i< count($matches[0]); $i++) {
						$lien = $matches[1][$i];
						if (preg_match('#&amp;np=(.*?)&amp;#',$lien,$r)) {
							list($x,$np)= $r;
							$pagination = str_replace($lien,parametre_url($self,'np',$np),$pagination);
						} else {
							// cas de la premiere page
							$pagination = str_replace($lien,parametre_url($self,'np',0),$pagination);
						}
					}
				}
				
				$contenu = substr($contenu,$pos1+18,($pos2-$pos1-18)) . $pagination;
				
				// elargir au contenu des documents
				// rendre les titres de document lisibles
				if ($getdoc) {
					// option s pour le retour a la ligne
					if (preg_match_all('#<strong><a href=(.*?)>(.*?)</a></strong>#s', $contenu, $matches)) {
						for ($i=0; $i< count($matches[0]); $i++) {
							$titre = $matches[2][$i];
							$titre_propre = $matches[1][$i];
							$titre_propre = str_replace('"',' ',$titre_propre);
							$titre_propre = cisquel_enlever_cle(cisquel_nomfichier($titre_propre));
							$titre_propre = str_replace('_',' ',$titre_propre);
							$contenu = str_replace($titre,$titre_propre,$contenu);
						}
					}
				}
				
				// modifier le chemin des vignettes
				$old = 'images/icones/';
				$new = _DIR_PLUGIN_CISQUEL . '_images/icones/';
				$contenu = str_replace($old,$new,$contenu);
				
				// enlever "vos criteres de recherche ..."
				if (preg_match('#<p class="enteteresult">(.*?)</p>#s', $contenu, $match))
					$contenu = str_replace($match[0],'',$contenu);
				
				// convertir le charset le cas echeant
				if ($charset_moteur!=$GLOBALS['meta']['charset']){
					include_spip('inc/charsets');
					$contenu = importer_charset($contenu, $charset_moteur);
				}
	
				
			} else {
				$contenu = '<p class="enteteresult">0 <strong>';
				if ($toussites)
					$contenu .= _T('cisquel:trouves');
				else
					$contenu .= _T('cisquel:doc_trouves');
					
				$contenu .= '</strong></p>';
			}
		}
	}
	return $contenu;
}

function cisquel_url_recherche_mno($p=array(), $touslessites=false, $getdoc=false) {
	
	// lire la configuration du plugin
	cisquel_lire_meta();
	$qs = $GLOBALS['ciconfig']['cisquelmnogosearch'];

	if ($touslessites) {
		$ul = '';
		$site ='';
		$categ = '---+Choisissez+une+cat%E9gorie+---';
		$groupbysite = 'no';
	} else {
		$ul = urlencode($GLOBALS['meta']['adresse_site']);
		$site = 'u%3A'.urlencode($GLOBALS['meta']['adresse_site']);
		$categ = '0';
		$groupbysite = 'no';
	}
	
	// tenir compte des options de recherche
	// et les convertir au format attentdu par mnogosearch
	$s1 = $qs['s'];
	$s2 = $qs['s'];
	$db = '';
	$de = '';
	$type = '';
	
	if ($p) {
		// urlencode remplace au passage les espaces par le signe plus
		// urlencode n'est pas unicode
		if ($GLOBALS['meta']['charset']=='utf-8')
			$recherche = urlencode(utf8_decode($p['recherche']));
		else
			$recherche = urlencode($p['recherche']);
		
		if ($p['tri']=='date'){
			$s1 = 'dR';
			$s2 = 'dR';
			$qs['avancee'] = 'yes';
		} elseif ($p['tri']=='datedesc'){
			$s1 = 'DR';
			$s2 = 'DR';
			$qs['avancee'] = 'yes';
		}

		if ($p['age']) {
			$qs['dp'] = $p['age'].'d';
			$qs['avancee'] = 'yes';
		}

		if ($p['datedebut']){
			$db = '&db='.$p['datedebut'];
			$qs['dt'] = 'range';
			$qs['avancee'] = 'yes';
		}

		if ($p['datefin']){
			$de = '&de='.$p['datefin'];
			$qs['dt'] = 'range';
			$qs['avancee'] = 'yes';
		}

		if ($p['typedoc']){
			$qs['avancee'] = 'yes';
			$row = sql_fetsel("mime_type", "spip_types_documents", "extension=".sql_quote(strtolower($p['typedoc'])));
			if ($row)
				$type = '&type='.$row['mime_type'];
		} elseif ($getdoc) {
				if ($typesdoc = cisquel_config('cisquelmnotypesdocuments')) {
					if (is_array($typesdoc)) {
						$in = sql_in('extension',$typesdoc);		
						$result = sql_select("mime_type","spip_types_documents",$in);
						while ($row = sql_fetch($result))
							$type .= '&type='.$row['mime_type'];
					}
				}
				$qs['ps'] = 10;
		}		
	}

	// liaison
	$urlmoteur = cisquel_url_moteur();
	if (strpos($urlmoteur,'?')===false)
		$urlmoteur .= '?';
	else
		$urlmoteur .= '&';
			
	
	$url = $urlmoteur
	.'avancee='.$qs['avancee']
	.'&s='.$s1
	.'&ps='.$qs['ps']
	.'&cat='.$qs['cat']
	.'&ul='.$ul
	.'&wf='.$qs['wf']
	.'&ftm='.$qs['ftm']
	.'&GroupBySite='.$groupbysite
	.'&tl='.$qs['tl']
	.'&categorie='.$qs['categorie']
	.'&q='.$recherche
	.'&m='.$qs['m']
	.'&categ='.$categ
	.'&site='.$site
	.'&fmt='.$qs['fmt']
	.'&s='.$s2
	.'&GroupBySite='.$groupbysite
	.'&wf='.$qs['wf']
	.$type
	.'&dp='.$qs['dp']
	.'&dt='.$qs['dt']
	.'&ps='.$qs['ps']
	.$db
	.$de;
	
	return $url;
}

function cisquel_url_recherche_google($p=array(), $touslessites=false) {

	// liaison
	$urlmoteur = cisquel_url_moteur();
	if (strpos($urlmoteur,'?')===false)
		$urlmoteur .= '?';
	else
		$urlmoteur .= '&';
	

	// tenir compte des options de recherche
	// et les convertir au format attentdu par google
	$options = '';
	
	if ($p) {
		// urlencode remplace au passage les espaces par le signe plus
		// urlencode n'est pas unicode
		if ($GLOBALS['meta']['charset']=='utf-8')
			$recherche = urlencode(utf8_decode($p['recherche']));
		else
			$recherche = urlencode($p['recherche']);

		// a mettre en premier
		if ($p['typedoc'])
			$options .= '+filetype:'.$p['typedoc'];

		if ($p['age'])
			$options .= '&as_qdr=d'.$p['age'];

		if ($p['datedebut'] AND $p['datefin']) {
			$options .= '&tbs=cdr:1,cd_min:'.$p['datedebut'].',cd_max:'.$p['datefin'];
			if ($p['tri']=='datedesc' OR $p['tri']=='date')
				$options .= ',sbd:1';
		} else {
			if ($p['tri']=='datedesc' OR $p['tri']=='date')
				$options .= '&tbs=cdr:1,sbd:1';
			
		}				
	}

	if ($touslessites) {
		$url = $urlmoteur.'q='.$recherche.$options;
	
		// lire la configuration du plugin
		cisquel_lire_meta();
		if ($domainetoussites = urlencode($GLOBALS['ciconfig']['cisqueldomainetoussites']))
			$url .= '&as_sitesearch='.$domainetoussites;

	} else {
		$url = $urlmoteur.'q='.$recherche.$options.'&as_sitesearch='.urlencode($GLOBALS['meta']['adresse_site']);
	}

	return $url;
}

/**
 * Filtre de redirection
 *
 * @param : $recherche (via la securite de SPIP)
 *			$env (qui necessite un #ENV sans filtre, sachant que la securite est assuree apres par cisquel_verifier_parametres)
 * @return : rien
 */
function cisquel_redirige($recherche, $env) {
	
	// indispensable (necessite un #ENV sans filtre)
	$unenv = unserialize($env);
	
	// securite
	$p = cisquel_verifier_parametres($unenv);


	if ($p['recherche'] AND $p['elargir']) {

		if (cisquel_choixelargirrecherche()=='oui') {
		
			// lire la configuration du plugin
			cisquel_lire_meta();
			$cisquelgetmnosite = $GLOBALS['ciconfig']['cisquelgetmnosite'];
			$cisquelgetmnotoussites = $GLOBALS['ciconfig']['cisquelgetmnotoussites'];	
			$cisquelchoixrecherchetouslessites = $GLOBALS['ciconfig']['cisquelchoixrecherchetouslessites'];
		
			if ($p['elargir']=='toussites' AND $cisquelchoixrecherchetouslessites=='oui')
				$toussites = true;
			else
				$toussites = false;
		
			$url = '';	
			$moteur = cisquel_moteur();	
			if ($moteur=='mnogosearch') {
				if (($p['elargir']=='doc' AND $cisquelgetmnosite!='oui') OR ($p['elargir']=='toussites' AND $cisquelgetmnotoussites!='oui')) {
					$url = "http://".cisquel_url_recherche_mno($p, $toussites);
				}
			} elseif ($moteur=='google') {
				$url = "http://".cisquel_url_recherche_google($p, $toussites);
			}
	
			if ($url) {
				include_spip('inc/headers');
				redirige_par_entete($url);
			}
		}
	}

	return '';
}

function cisquel_verifier_parametres($p) {
	$p_safe = array();

	$p_safe['recherche'] = '';
	$p_safe['tri'] = false;
	$p_safe['age'] = false;
	$p_safe['datedebut'] = false;
	$p_safe['datefin'] = false;
	$p_safe['typedoc'] = false;
	$p_safe['elargir'] = false;
	$p_safe['np'] = false;
	$p_safe['onglet'] = 'article';

	if (isset($p['recherche']))
		$p_safe['recherche'] = cisquel_filtrer_recherche($p['recherche']);
	
	if (isset($p['tri']) AND in_array($p['tri'],array('date','datedesc')))
		$p_safe['tri'] = $p['tri'];
	
	if (isset($p['age']) AND intval($p['age'])>0)
		$p_safe['age'] = intval($p['age']);
	
	if (isset($p['datedebut']) AND cisquel_verifier_et_convertir_date($p['datedebut'])>0)
		$p_safe['datedebut'] = $p['datedebut'];

	if (isset($p['datefin']) AND cisquel_verifier_et_convertir_date($p['datefin'])>0)
		$p_safe['datefin'] = $p['datefin'];

	if (isset($p['typedoc'])) {
		$typesdoc = cisquel_typesdoc();
		if ($typesdoc AND is_array($typesdoc)) {
			if (in_array($p['typedoc'],$typesdoc))
				$p_safe['typedoc'] = $p['typedoc'];
		}
	}

	if (isset($p['elargir']) AND in_array($p['elargir'],array('doc','toussites')))
		$p_safe['elargir'] = $p['elargir'];

	if (isset($p['np']) AND intval($p['np'])>0)
		$p_safe['np'] = intval($p['np']);
		
	if (isset($p['onglet']) AND in_array($p['onglet'],array('article','rubrique','document','forum')))
		$p_safe['onglet'] = $p['onglet'];
	
	return $p_safe;
}

function cisquel_nomfichier($fichier) {
	// enlever le chemin
	$titre=$fichier;
	$pos3 = strrpos($titre,"/");
	if (!($pos3 === false)) $titre=substr($titre,$pos3+1);
	
	return $titre;
}

function cisquel_titrefichier($fichier) {
	// enlever l'extension et le chemin
	$titre=$fichier;
	$pos1 = strrpos($titre,".");
	if (!($pos1 === false)) $titre=substr($titre,0,$pos1);

	$pos3 = strrpos($titre,"/");
	if (!($pos3 === false)) $titre=substr($titre,$pos3+1);
	
	return $titre;
}

function cisquel_enlever_cle($fichier) {
	if (substr($fichier, -10, 4)=='_cle')
		$fichier = substr($fichier,0,-10);

	return $fichier;
}

function cisquel_taille_en_octets ($taille) {
	if ($taille)
		$taille = taille_en_octets($taille);
	else
		$taille = '';

	return $taille;
}

function cisquel_getmnochoix($recherche, $env) {

	// lire la configuration du plugin
	cisquel_lire_meta();
	$cisquelgetmnosite = $GLOBALS['ciconfig']['cisquelgetmnosite'];
	$cisquelgetmnotoussites = $GLOBALS['ciconfig']['cisquelgetmnotoussites'];	
	
	// indispensable (necessite un #ENV sans filtre)
	$unenv = unserialize($env);
	
	// securite
	$p = cisquel_verifier_parametres($unenv);
	
	if ($p['elargir']=='toussites' AND $cisquelgetmnotoussites=='oui')
		$return = 'toussites';
	elseif ($p['elargir']=='doc' AND $cisquelgetmnosite=='oui')
		$return = 'doc';
	else
		$return = '';

	return $return;
}

function cisquel_getonglet($recherche, $env) {

	// indispensable (necessite un #ENV sans filtre)
	$unenv = unserialize($env);
	
	// securite
	$p = cisquel_verifier_parametres($unenv);	

	return $p['onglet'];
}


function cisquel_typesdoc($a='') {
	$typesdoc = array();
	
	// lire la configuration du plugin
	$t = cisquel_config('cisqueltypesdocuments');
	if ($t AND is_array($t)) {
		foreach ($t as $cle=>$valeur)
			$typesdoc[] = $cle;
	}

	return $typesdoc;
}

function cisquel_typesdoclib($extension) {
	static $typesdoclib = array();
	$return	= $extension;
	
	if (!$typesdoclib) {
		// lire la configuration du plugin
		$t = cisquel_config('cisqueltypesdocuments');
		if ($t AND is_array($t))
			$typesdoclib = $t;	
	}
	
	if (isset($typesdoclib[$extension]))
		$return = $typesdoclib[$extension];
	
	return $return;
}

function cisquel_extensions_utilisees(){
	$return = array();
	$result = sql_select('extension','spip_documents','','extension');
	while ($row = sql_fetch($result))
		$return[] = $row['extension'];
	
	return $return;
}

function cisquel_extension_est_utilisee($extension=''){
	$extensions = cisquel_extensions_utilisees();
	return in_array($extension,$extensions);
}

function cisquel_extensions_media($media=''){
	$return = array();
	if ($media){
		$typesdoc = cisquel_extensions_utilisees();
	
		if (is_array($typesdoc)) {
			$in = sql_in('extension',$typesdoc);		
			$result = sql_select("mime_type,extension","spip_types_documents",$in);
			while ($row = sql_fetch($result)) {
				$type_mime = $row['mime_type'];
				// type de media
				$media_row = "file";
				if (preg_match(",^image/,",$type_mime) OR in_array($type_mime,array('application/illustrator')))
					$media_row = "image";
				elseif (preg_match(",^audio/,",$type_mime))
					$media_row = "audio";
				elseif (preg_match(",^video/,",$type_mime) OR in_array($type_mime,array('application/ogg','application/x-shockwave-flash','application/mp4')))
					$media_row = "video";
					
				if ($media_row == $media)
					$return[] = $row['extension'];
			}
		}
	}	
	
	return $return;
}

function cisquel_choix_extensions($media='',$extension=''){
	$return = array();	

	if ($extension AND cisquel_extension_est_utilisee($extension))
		$return = array($extension);
	elseif ($media AND in_array($media,array('file','image','audio','video')))
		$return = cisquel_extensions_media($media);
	else
		$return = cisquel_extensions_utilisees();
	
	return $return;
}

?>