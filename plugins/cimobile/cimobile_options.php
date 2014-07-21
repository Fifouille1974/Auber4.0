<?php
/**
 * Plugin cimobile
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

 
/**
 * Parametres generaux
 */
 
// Nom du dossier contenant les sous dossiers de squelettes mobiles 
define('CIMOBILE_RACINE_SQUELETTES', 'squel_mobiles');
// Valeur de vue pour l'affichage du site web classique
define('CIMOBILE_WEB', 'web');
// Valeur de vue pour retourner a la vue non classique
define('CIMOBILE_RETOUR', 'retour');


// parametrage par fichier
if ($f = find_in_path(CIMOBILE_RACINE_SQUELETTES . '/' . '_config_cimobile.php'))
	@include_once(_ROOT_CWD . $f);


// Par defaut, ne pas laisser CIPARAM chercher les formes d'articles et de rubriques
// dans les autres plugins que celui contenant les jeux de squelettes mobiles
if (!isset($GLOBALS['cimobile_impacter_ciparam']))
	$GLOBALS['cimobile_impacter_ciparam']='oui';


/**
 * Alimenter les variables globales dossier_squelettes et cimobile_dossier_squelettes
 */

$GLOBALS['cimobile_dossier_squelettes'] = "";

if ($squelette_mobile = cimobile_squelette()) {
	
	// path du dossier de squelettes mobiles
	$cimobile_squelettes_path = find_in_path(CIMOBILE_RACINE_SQUELETTES)."/".$squelette_mobile;

	// Definir dossier_squelettes	
	$GLOBALS['dossier_squelettes'] = $cimobile_squelettes_path;

	// Utile pour cimobile_fonctions et pour le plugin CIPARAM
	$GLOBALS['cimobile_dossier_squelettes'] = $cimobile_squelettes_path;	
}


/**
 * Tableau des mobiles individualises (smartphones et tablettes)
 * (les anciens mobiles sont deja pris en compte avec 'autre_mobile')
 * @param 
 * @return array (expression reguliere sur le user agent => nom du mobile) 
 */
function cimobile_mobiles_individualises() {
	// Priorite au parametrage par le fichier mes_options
	if (isset($GLOBALS['ciconfig']['cimobile_mobiles']))
		return $GLOBALS['ciconfig']['cimobile_mobiles'];
	else
		return array(
			',iphone,i'=>'iphone',
			',ipod,i'=>'ipod',
			',ipad,i'=>'ipad',
			',xoom,i'=>'androidtablette',
			',android,i'=>'android',
			',blackberry,i'=>'blackberry',
			',Windows Phone OS 7,i'=>'windowsphone7',
			'/(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)/i'=>'windowsmobile',
			',opera mini,i'=>'opera',
			'/(series60|series 60)/i'=>'S60',
			'/(symbian|series60|series70|series80|series90)/i'=>'symbianos',
			',webos,i'=>'palmwebos',
			'/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i'=>'palmos',
			'/nuvifone/i'=>'nuvifone',
			'/(qt embedded|com2)/i'=>'sonymylo',
			'/maemo/i'=>'nokiatablette',
			'/playbook/i'=>'blackberrytablette',
			'/archos/i'=>'archos'
			);
}	


/**
 * Tableau des groupes de mobiles
 * @param 
 * @return array (groupe de mobiles => tableau des mobiles correspondants) 
 */
function cimobile_groupes_mobiles() {
	// Priorite au parametrage par le fichier mes_options
	if (isset($GLOBALS['ciconfig']['cimobile_groupes_mobiles']))
		return $GLOBALS['ciconfig']['cimobile_groupes_mobiles'];
	else
		return array(
			'iphone' => array('iphone','ipod'),
			'smartphones' => array('android','blackberry','windowsphone7','windowsmobile','opera','S60','symbianos','palmwebos','palmos','nuvifone','sonymylo'),
			'tablettes' => array('ipad','androidtablette','nokiatablette','blackberrytablette','archos'),
			'autres_mobiles' => array('autre_mobile')
		);
}


/**
 * Correspondance entre les groupes de mobiles et les sous dossiers de squelettes mobiles
 * (ou le cas échéant, l'affichage du site web classique)
 * @param 
 * @return array (groupe de mobiles => sous dossier de squelettes mobiles) 
 */
function cimobile_correspondances() {
	// Priorite au parametrage par le fichier mes_options
	if (isset($GLOBALS['ciconfig']['cimobile_correspondances']))
		return $GLOBALS['ciconfig']['cimobile_correspondances'];
	else
		return array(
			'iphone' => CIMOBILE_WEB,
			'smartphones' => CIMOBILE_WEB,
			'tablettes' => CIMOBILE_WEB,
			'autres_mobiles' => CIMOBILE_WEB
		);
}
 

/**
 * Determiner le squlette a utiliser
 * Priorite au cookie sinon detection du mobile
 * @param 
 * @return string 
 */
function cimobile_squelette() {
	$return = '';
	
	// Correspondance entre les groupes de mobiles et les sous dossiers de squelettes mobiles
	$tableau_correspondances = cimobile_correspondances();


	// Le parametre d'URL cimobile est-il present ?	
	// On peut forcer l'affichage du site web classique et inversement retourner a la vue non classique
	$cimobile = isset($_GET['cimobile']) ? $_GET['cimobile'] : '';

	
	// Sinon un cookie de squelette est-il present ?
	if (!$cimobile)
		$cimobile = isset($_COOKIE['cimobile']) ? $_COOKIE['cimobile'] : '';

		
	// Priorite au cookie (ou au parametre d'URL) sinon detection du mobile
	if (in_array($cimobile,$tableau_correspondances) 
		OR $cimobile==CIMOBILE_WEB 
		OR $cimobile==CIMOBILE_RETOUR) {
		$return = $cimobile;
		if ($cimobile!=CIMOBILE_WEB)
			cimobile_host_redirection();

	} else {
		// detection du mobile		
		if ($mobile = cimobile_detecter()) {
			
			// groupes de mobile
			$groupes_mobiles = cimobile_groupes_mobiles();
			
			// correspondance entre le mobile, son groupe de mobile et le sous dossier de squelettes mobiles
			foreach($groupes_mobiles as $groupe => $mobiles) {
				if (in_array($mobile,$mobiles,true)) {
					if (isset($tableau_correspondances[$groupe]))
				    	$return = $tableau_correspondances[$groupe];
						break;
				}
			}
		}

		// Si c'est un mobile, poser un cookie de detection s'il n'existe pas ou si son contenu doit changer
		if ($return){
			if (!isset($_COOKIE['cimob_detecte']) OR (isset($_COOKIE['cimob_detecte']) AND $_COOKIE['cimob_detecte']!=$mobile)){
				include_spip('inc/cookie');
				spip_setcookie('cimob_detecte', $return);
			}
		}
		
		// Si ce n'est pas un mobile
		// et qu'il existe un parametre dans un fichier d'option
		// qui, en cas de debut ou de fin de HOST specifique mobile,
		// impose un sous dossier de squelette specifique
		if (!$return)
			$return = cimobile_host_correspondance();	
		else
			cimobile_host_redirection();
			
			
		// Si ce n'est pas un mobile
		// Eviter de les detections a repetition		
		if (!$return)
			$return = CIMOBILE_WEB;	
	}

	if ($return) {
		// Securite
		if (preg_match(',^[0-9a-z_]*$,i', $return)) {
		
			// Poser un cookie s'il n'existe pas ou si son contenu doit changer
			if (!isset($_COOKIE['cimobile']) OR (isset($_COOKIE['cimobile']) AND $_COOKIE['cimobile']!=$return)){
				include_spip('inc/cookie');
				spip_setcookie('cimobile', $return);
			}

		} else {
			$return = '';
		}
	}

	// cas du site web classique
	if ($return==CIMOBILE_WEB)
		$return = '';

	return $return;
}


/**
 * Detection du mobile
 * @param 
 * @return string 
 */
function cimobile_detecter() {
	$return = '';
	$user_agent = isset($_SERVER['HTTP_USER_AGENT'])?strtolower($_SERVER['HTTP_USER_AGENT']):'';
	
	// Cas d'un desktop (pour eviter des tests inutiles)
	if (strpos($user_agent,'firefox')!==false AND strpos($user_agent,'mobile')===false AND strpos($user_agent,'tablet')===false) {
		// firefox (sauf version mobile)
		return $return;
	} elseif (strpos($user_agent,'msie')!==false AND strpos($user_agent,'windows ce')===false AND strpos($user_agent,'iemobile')===false) {
		// internet explorer (sauf version mobile)
		return $return;
	}
	
	// Tableau des mobiles individualises (smartphones et tablettes)
	// expression reguliere sur le user agent => nom du mobile
	$mobiles = cimobile_mobiles_individualises();
			
	foreach($mobiles as $regexp=>$val){
		if (preg_match($regexp,$user_agent)) {
	    	$return = $val;
	    	break;
		}
	}

	// Les autres cas
	if (!$return) {
		$httpaccept = isset($_SERVER['HTTP_ACCEPT'])?strtolower($_SERVER['HTTP_ACCEPT']):'';
		$user_agent_4car = substr($user_agent,0,4);
		
		if (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i',$user_agent))
		    $return = 'autre_mobile';
				
	    elseif ((strpos($httpaccept,'text/vnd.wap.wml')>0)||(strpos($httpaccept,'application/vnd.wap.xhtml+xml')>0))
		    $return = 'autre_mobile';
		    
	    elseif (isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE']))
		    $return = 'autre_mobile';
	
	    elseif (in_array($user_agent_4car, array('1207','3gso','4thp','501i','502i','503i','504i','505i','506i','6310','6590','770s','802s','a wa','acer','acs-','airn','alav','asus','attw','au-m','aur ','aus ','abac','acoo','aiko','alco','alca','amoi','anex','anny','anyw','aptu','arch','argo','bell','bird','bw-n','bw-u','beck','benq','bilb','blac','c55/','cdm-','chtm','capi','cond','craw','dall','dbte','dc-s','dica','ds-d','ds12','dait','devi','dmob','doco','dopo','el49','erk0','esl8','ez40','ez60','ez70','ezos','ezze','elai','emul','eric','ezwa','fake','fly-','fly_','g-mo','g1 u','g560','gf-5','grun','gene','go.w','good','grad','hcit','hd-m','hd-p','hd-t','hei-','hp i','hpip','hs-c','htc ','htc-','htca','htcg','htcp','htcs','htct','htc_','haie','hita','huaw','hutc','i-20','i-go','i-ma','i230','iac','iac-','iac/','ig01','im1k','inno','iris','jata','java','kddi','kgt','kgt/','kpt ','kwc-','klon','lexi','lg g','lg-a','lg-b','lg-c','lg-d','lg-f','lg-g','lg-k','lg-l','lg-m','lg-o','lg-p','lg-s','lg-t','lg-u','lg-w','lg/k','lg/l','lg/u','lg50','lg54','lge-','lge/','lynx','leno','m1-w','m3ga','m50/','maui','mc01','mc21','mcca','medi','meri','mio8','mioa','mo01','mo02','mode','modo','mot ','mot-','mt50','mtp1','mtv ','mate','maxo','merc','mits','mobi','motv','mozz','n100','n101','n102','n202','n203','n300','n302','n500','n502','n505','n700','n701','n710','nec-','nem-','newg','neon','netf','noki','nzph','o2 x','o2-x','opwv','owg1','opti','oran','p800','pand','pg-1','pg-2','pg-3','pg-6','pg-8','pg-c','pg13','phil','pn-2','pt-g','palm','pana','pire','pock','pose','psio','qa-a','qc-2','qc-3','qc-5','qc-7','qc07','qc12','qc21','qc32','qc60','qci-','qwap','qtek','r380','r600','raks','rim9','rove','s55/','sage','sams','sc01','sch-','scp-','sdk/','se47','sec-','sec0','sec1','semc','sgh-','shar','sie-','sk-0','sl45','slid','smb3','smt5','sp01','sph-','spv ','spv-','sy01','samm','sany','sava','scoo','send','siem','smar','smit','soft','sony','t-mo','t218','t250','t600','t610','t618','tcl-','tdg-','telm','tim-','ts70','tsm-','tsm3','tsm5','tx-9','tagt','talk','teli','topl','hiba','up.b','upg1','utst','v400','v750','veri','vk-v','vk40','vk50','vk52','vk53','vm40','vx98','virg','vite','voda','vulc','w3c ','w3c-','wapj','wapp','wapu','wapm','wig ','wapi','wapr','wapv','wapy','wapa','waps','wapt','winc','winw','wonu','x700','xda2','xdag','yas-','your','zte-','zeto','acs-','alav','alca','amoi','aste','audi','avan','benq','bird','blac','blaz','brew','brvw','bumb','ccwa','cell','cldc','cmd-','dang','doco','eml2','eric','fetc','hipt','http','ibro','idea','ikom','inno','ipaq','jbro','jemu','java','jigs','kddi','keji','kyoc','kyok','leno','lg-c','lg-d','lg-g','lge-','libw','m-cr','maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','mywa','nec-','newt','nok6','noki','o2im','opwv','palm','pana','pant','pdxg','phil','play','pluc','port','prox','qtek','qwap','rozo','sage','sama','sams','sany','sch-','sec-','send','seri','sgh-','shar','sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-','tosh','treo','tsm-','upg1','upsi','vk-v','voda','vx52','vx53','vx60','vx61','vx70','vx80','vx81','vx83','vx85','wap-','wapa','wapi','wapp','wapr','webc','whit','winw','wmlb','xda-'),true))	    
		    $return = 'autre_mobile';
	}

	return $return;
}




/**
 * Correspondance entre un HOST specifique mobile
 * et un sous dossier de squelette specifique
 * @param : aucun
 * @return : sous dossier de squelettes mobiles
 */
function cimobile_host_correspondance() {

	$return = "";
	$ci_host = "";
	$cimobile_host_correspondances = "";
	
	// Existe-t-il un parametre dans un fichier d'option
	// qui, dans le cas ou ce n'est pas un mobile qui consulte le site
	// et que l'adresse est specifique pour les mobiles,
	// impose un sous dossier de squelette specifique ?
 	// Exemple : array('mobile.' => 'itwx', '.mobi' => 'itwx');
	
	if (isset($GLOBALS['ciconfig']['cimobile_host_correspondances']))
		if (is_array($GLOBALS['ciconfig']['cimobile_host_correspondances']))
			$cimobile_host_correspondances = $GLOBALS['ciconfig']['cimobile_host_correspondances'];


	if ($cimobile_host_correspondances) {			
		if ($ci_host = cimobile_host()) {
			foreach($cimobile_host_correspondances as $debutoufin => $squelette) {
				if (substr($ci_host,-strlen($debutoufin))==$debutoufin OR substr($ci_host,0,strlen($debutoufin))==$debutoufin) {
					$return = $squelette;
					break;
				}
			}
		}
	}
	
	return $return;
}

/**
 * Redirection le cas echeant
 * vers un HOST specifique mobile
 * @param : aucun
 * @return : aucun
 */
function cimobile_host_redirection() {

	$redirect = "";
	$ci_host = "";
	$cimobile_host_redirection = "";
	
	// Existe-t-il un parametre dans un fichier d'option
	// qui, dans le cas ou c'est un mobile qui consulte le site
	// et que l'adresse n'est pas specifique pour les mobiles
	// (debut ou fin de HOST specifique mobile)
	// impose une redirection en remplacant le debut (ou la fin)
	// de l'adresse actuelle avec le debut (ou la fin) du HOST specifique mobile
 	// Exemple : 'mobile.'

	if (isset($GLOBALS['ciconfig']['cimobile_host_redirection']))
		if (is_array($GLOBALS['ciconfig']['cimobile_host_redirection']))
			$cimobile_host_redirection = $GLOBALS['ciconfig']['cimobile_host_redirection'];

	if ($cimobile_host_redirection) {
		if ($ci_host = cimobile_host()) {
			foreach($cimobile_host_redirection as $debutoufin => $debutoufinmobile) {
			
				if (substr($ci_host,-strlen($debutoufinmobile))==$debutoufinmobile OR substr($ci_host,0,strlen($debutoufinmobile))==$debutoufinmobile) {
					// URL mobile : ne rien faire
				
				} else {
					if (substr($ci_host,-strlen($debutoufin))==$debutoufin) {
						$redirect = substr($ci_host,0,-strlen($debutoufin)).$debutoufinmobile;
					} elseif (substr($ci_host,0,strlen($debutoufin))==$debutoufin) {
						$redirect = $debutoufinmobile.substr($ci_host,strlen($debutoufin));
					}
				}
				
			}
		}
	}
	
	if ($redirect) {
		include_spip('inc/headers');
		redirige_par_entete('http://'.$redirect.$_SERVER['REQUEST_URI']);
	}

	return true;
}


/**
 * Redirection le HOST
 * @param : aucun
 * @return : HOST
 */
function cimobile_host() {

	$ci_host = "";
			
	// ordre de recherche par défaut (celui de phpCAS)
	$cimobilehostordre = array('HTTP_X_FORWARDED_SERVER','SERVER_NAME','HTTP_HOST');
	
	// ordre de recherche personnalise dans un fichier d'option
	if (isset($GLOBALS['ciconfig']['cimobile_host_ordre'])) {
		if (is_array($GLOBALS['ciconfig']['cimobile_host_ordre']))
			$cimobilehostordre = $GLOBALS['ciconfig']['cimobile_host_ordre'];
	}
	
	foreach ($cimobilehostordre as $valeur) {
		if (isset($_SERVER[$valeur])) {
			if ($_SERVER[$valeur]) {
				$ci_host = $_SERVER[$valeur];
				break;
			}
		}
	}
	
	return $ci_host;
}


?>