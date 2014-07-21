<?php
/**
 * Plugin CISPAM
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;
 
include_spip("inc/cispam_commun");

define('_cispam_RACCOURCI_MODELE', 
	 '(<([a-z_-]{3,})' # <modele
	.'\s*([0-9]+)\s*' # id
	.'([|](?:<[^<>]*>|[^>])*?)?' # |arguments (y compris des tags <...>)
	.'\s*/?'.'>)' # fin du modele >
       );

define('_cispam_RACCOURCI_CODE', '(<(code|quote|math)>(.*?)</(code|quote|math)>)');

define('_cispam_RACCOURCI_LIEN', "/\[([^][]*?([[]\w*[]][^][]*)*)->(>?)([^]]*)\]/msS");


function cispam_declarer_tables_interfaces($interface){

	// un oubli de SPIP pour les forums (NOM est l'alias de AUTEUR)
	if (cispam_config('cispam_forum_html_aval'))
		$interface['table_des_traitements']['NOM']['forums'] = 'safehtml(vider_url(%s))';
	
	return $interface;
}

function cispam_texte_sans_html($texte, $action=false){

	// pour ceux qui ne respectent pas les consignes dans le fichier de parametrage
	if ($action=='supprimer' OR $action=='echapper') {
		       
		if (strpos($texte,"<")!==false) {
			$z_matches = array();
			$c_matches = array();

			// proteger les raccourcis de CODE
			if (preg_match_all(_cispam_RACCOURCI_CODE, $texte, $c_matches, PREG_SET_ORDER)) {
				$c = 1;
				foreach ($c_matches as $code_match)
					$texte = str_replace($code_match[0],'cccc'.$c++.'_',$texte);
			}
			
			// proteger les modeles de SPIP
			if (preg_match_all(_cispam_RACCOURCI_MODELE, $texte, $z_matches, PREG_SET_ORDER)) {
				$z = 1;
				foreach ($z_matches as $match){
					if (!in_array(strtolower($match[0]),array('<table>')))
						$texte = str_replace($match[0],'zzzz'.$z++.'_',$texte);
				}
			}

			// proteger certains raccourcis de SPIP et certaines balises HTML (qui ne sont pas dangereuses) 
			$search = array('<br>','<br/>','<br />','<q>','</q>','<ins>','</ins>','<del>','</del>',
			'<html>','</html>','<cadre>','</cadre>','<poesie>','</poesie>','<poetry>','</poetry>',
			'< ',' >','<-','->','<|','|>');
			$replace = array('uuuu0','uuuu1','uuuu2','uuuu3','uuuu4','uuuu5','uuuu6','uuuu7','uuuu8',
			'tttt0','tttt1','tttt2','tttt3','yyyy0','yyyy1','yyyy2','yyyy3',
			'vvvv0','vvvv1','vvvv2','vvvv3','vvvv4','vvvv5');
			$texte = str_ireplace($search,$replace,$texte);
			
			include_spip("inc/texte");
			$texte = interdire_scripts($texte);
			
			// nettoyer le code HTML
			if ($action=='supprimer')
				$texte = supprimer_tags($texte);
			elseif ($action=='echapper')
				$texte = echapper_tags($texte);
	
			// deproteger certains raccourcis de SPIP et certaines balises HTML (qui ne sont pas dangereuses) 	
			$texte = str_replace($replace,$search,$texte);

			
			// remettre les raccourcis de CODE
			if ($c_matches) {
				reset($c_matches);
				$c = 1;
				foreach ($c_matches as $code_match)
					$texte = str_replace('cccc'.$c++.'_',$code_match[0],$texte);
			}
			
			// remettre les modeles de SPIP
			if ($z_matches) {
				reset($z_matches);
				$z = 1;
				foreach ($z_matches as $match)
					$texte = str_replace('zzzz'.$z++.'_',$match[0],$texte);
			}
			
		}
	}

	return $texte;	
}

function cispam_forum_sans_html($texte, $action=false){
	
	// pour ceux qui ne respectent pas les consignes dans le fichier de parametrage
	if ($action=='supprimer' OR $action=='echapper') {
		       
		if (strpos($texte,"<")!==false) {
			$c_matches = array();
			
			// proteger les raccourcis de CODE
			if (preg_match_all(_cispam_RACCOURCI_CODE, $texte, $c_matches, PREG_SET_ORDER)) {
				$c = 1;
				foreach ($c_matches as $code_match)
					$texte = str_replace($code_match[0],'cccc'.$c++.'_',$texte);
			}
			
			// proteger certaines balises et certains raccourcis
			$search = array('<br>','<br/>','<br />','<q>','</q>','<ins>','</ins>','<del>','</del>',
			'<html>','</html>','<poesie>','</poesie>','<poetry>','</poetry>',
			'< ',' >','<-','->','<|','|>');
			$replace = array('uuuu0','uuuu1','uuuu2','uuuu3','uuuu4','uuuu5','uuuu6','uuuu7','uuuu8',
			'tttt0','tttt1','yyyy0','yyyy1','yyyy2','yyyy3',
			'vvvv0','vvvv1','vvvv2','vvvv3','vvvv4','vvvv5');
			$texte = str_ireplace($search,$replace,$texte);
			
			// enlever les raccourcis SPIP [xxx->url]
			if (preg_match_all(_cispam_RACCOURCI_LIEN, $texte, $regs, PREG_SET_ORDER)) {
				foreach ($regs as $k => $reg) {					
					$texte = str_replace($reg[0], $reg[1].' ('.$reg[count($reg)-1].')',$texte);
				}
			}

			// enlever les href proprement
			if (preg_match_all(",<a(.*?)href=\"(.*?)\"(.*?)>(.*?)</a>,i", $texte, $reg2s, PREG_SET_ORDER)) {
				foreach ($reg2s as $k => $reg) {					
					$texte = str_replace($reg[0], $reg[count($reg)-1].' ('.$reg[2].')',$texte);
				}
			}

			// nettoyer le code HTML
			if ($action=='supprimer')
				$texte = supprimer_tags($texte);
			elseif ($action=='echapper')
				$texte = echapper_tags($texte);
	
			// deproteger certaines balises et certains raccourcis	
			$texte = str_replace($replace,$search,$texte);
			
			// remettre les raccourcis de CODE
			if ($c_matches) {
				reset($c_matches);
				$c = 1;
				foreach ($c_matches as $code_match)
					$texte = str_replace('cccc'.$c++.'_',$code_match[0],$texte);
			}			
		}
	}

	return $texte;	
}


/**
 * Plugin No-SPAM
 * (c) 2008-2011 Cedric Morin Yterium.net + C. Imberti (adaptation)
 * Licence GPL
 *
 */

/**
 * Lister les formulaires a prendre en charge contre le SPAM
 * pour verifier le nobot et le jeton sur un formulaire, l'ajouter a cette liste
 * par le pipeline cispam_lister_formulaires
 * @return void
 */
function cispam_lister_formulaires(){
	if (!isset($GLOBALS['formulaires_no_spam']))
		$GLOBALS['formulaires_no_spam'] = array();
	$formulaires = array_merge($GLOBALS['formulaires_no_spam'],array('forum','ecrire_auteur','signature'));
	
	if (cispam_pipeline_est_utilise('cispam_lister_formulaires'))
		return pipeline('cispam_lister_formulaires',$formulaires);
	else
		return $formulaires;	
}



/**
 * Ajouter le champ de formulaire 'nobot' au besoin
 *
 * @param array $flux
 * @return array
 */
function cispam_recuperer_fond($flux){
	// determiner le nom du formulaire
	$fond = strval($flux['args']['fond']);
	if (false !== $pos = strpos($fond, 'formulaires/')) {
		$form = substr($fond, $pos + 12);
		if (in_array($form, cispam_lister_formulaires())){
			// on ajoute le champ 'nobot' si pas present dans le formulaire
			$texte = &$flux['data']['texte'];
			if ((false === strpos($texte, 'name="nobot"'))
			and (false !== $pos = strpos($texte, '</form>'))) {
				$nobot = recuperer_fond("inclure/nobot", array('nobot'=>''));
				$texte = substr_replace($texte, $nobot, $pos, 0);
			}
		}
	}
	return $flux;
}

/**
 * Ajouter un jeton temporaire lie a l'heure et a l'IP pour limiter la reutilisation possible du formulaire
 *
 * @param array $flux
 * @return array
 */
function cispam_formulaire_charger($flux){
	$form = $flux['args']['form'];
	if (in_array($form, cispam_lister_formulaires())){
		include_spip("inc/cispam");
		$jeton = creer_jeton($form);
		$flux['data']['_hidden'] .= "<input type='hidden' name='_jeton' value='$jeton' />";
	}
	return $flux;
}

/**
 * Verifier le jeton temporaire lie a l'heure et a l'IP pour limiter la reutilisation possible du formulaire
 *
 * @param array $flux
 * @return array
 */
function cispam_formulaire_verifier($flux){
	$form = $flux['args']['form'];
	if (in_array($form, cispam_lister_formulaires())){
		include_spip("inc/cispam");
		$jeton = _request('_jeton');
		// le jeton prend en compte l'heure et l'ip de l'internaute
		if (_request('nobot') // trop facile !
		OR (!verifier_jeton($jeton, $form))){
			#spip_log('pas de jeton pour '.var_export($flux,true),'cispam');
			$flux['data']['message_erreur'] .= _T('cispam:erreur_jeton');
			if ($form=='forum')
				unset($flux['data']['previsu']);
		}
	}
	if ($form=='forum'){
		if (!isset($flux['data']['texte'])
			AND $GLOBALS['meta']['forums_texte'] == 'oui'){
			include_spip("inc/cispam");
			
			// regarder si il y a du contenu en dehors des liens !
			$caracteres = compter_caracteres_utiles(_request('texte'));
			if ($caracteres < 10){
				$flux['data']['texte'] = _T('forum_attention_dix_caracteres');
				unset($flux['data']['previsu']);
			}
		
//----- Debut ajout CI -----
			// comme on n'enregistre pas certains messages suspects
			// il faut l'indiquer a l'auteur du message 

			// si c'est un message bourre de liens, on ne l'accepte pas
			$seuils = array(
					'titre'=>1, // seuils par defaut
					'session_nom'=>1,
					'session_email'=>1,
					'nom_site'=>1,
					'url_site' =>2, // 2 liens dans le champ url, c'est vraiment louche
					'texte'=>4 // pour le champ texte
			);

			foreach($seuils as $champ=>$seuil) {
				$infos = analyser_spams(_request($champ));
				if ($infos['nombre_liens'] > 0) {
					// si un lien a un titre de moins de 3 caracteres, c'est louche...
					if ($infos['caracteres_texte_lien_min'] < 3) {
						$flux['data']['texte'] = _T('cispam:erreur_spam');
						unset($flux['data']['previsu']);
					}

					if ($infos['nombre_liens'] >= $seuil) {
						$flux['data']['texte'] = _T('cispam:erreur_spam');
						unset($flux['data']['previsu']);
					}
				}
			}
			
			// Anti scan de document via <doc...>, etc
	   		if (defined('_DIR_PLUGIN_CIAR')){
	   			$texteforum = _request('texte');
				if (preg_match(',<([a-z]+)([0-9]+)(\|([^>]*))?'.'\s*/?'.'>,i', $texteforum, $matchforum)) {
					$flux['data']['texte'] = _T('cispam:erreur_spam_modele');
					unset($flux['data']['previsu']);
				}	
	   		}
	   				
			// verifier qu'un message identique n'a pas ete publie il y a peu
			if (sql_countsel('spip_forum','maj>DATE_SUB(NOW(),INTERVAL 1440 minute) AND texte='.sql_quote(_request('texte'))." AND statut IN ('publie','off','spam')")>0) {
				$flux['data']['texte'] = _T('cispam:erreur_spam_doublon');
				unset($flux['data']['previsu']);
			}
			
//----- Fin ajout CI -----
		
		}
	
	}
	if ($form=='ecrire_auteur'){
		if (!isset($flux['data']['texte_message_auteur'])){
			include_spip("inc/cispam");
			include_spip("inc/texte");
			// regarder si il y a du contenu en dehors des liens !
			$texte_message_auteur = _request('texte_message_auteur');
			$caracteres = compter_caracteres_utiles($texte_message_auteur);
			if ($caracteres < 10){
				$flux['data']['texte_message_auteur'] = _T('forum_attention_dix_caracteres');
				unset($flux['data']['previsu']);
			}
			// on analyse le sujet
			$infos_sujet = analyser_spams(_request('sujet_message_auteur'));
			// si un lien dans le sujet = spam !
			if ($infos_sujet['nombre_liens'] > 0){
				$flux['data']['sujet_message_auteur'] = _T('cispam:erreur_spam');
				unset($flux['data']['previsu']);
			}

			// on analyse le texte
			$infos_texte = analyser_spams($texte_message_auteur);
			if ($infos_texte['nombre_liens'] > 0) {
				// si un lien a un titre de moins de 3 caracteres = spam !
				if ($infos_texte['caracteres_texte_lien_min'] < 3) {
					$flux['data']['texte_message_auteur'] = _T('cispam:erreur_spam');
				}
				// si le texte contient plus de trois liens = spam !
				if ($infos_texte['nombre_liens'] >= 3)
					$flux['data']['texte_message_auteur'] = _T('cispam:erreur_spam');
			}
		}
	}
	if ($form=='signature'){
		$id_article = $flux['args']['args'][0];
		$row = sql_fetsel('*', 'spip_petitions', "id_article=".intval($id_article));
		if ((!isset($flux['data']['message'])) && ($row['message']  == "oui")){
			include_spip("inc/cispam");
			include_spip("inc/texte");
			// regarder si il y a du contenu en dehors des liens !
			$message = _request('message');
			// on analyse le texte
			$infos_texte = analyser_spams($message);
			if ($infos_texte['nombre_liens'] > 0) {
				// si un lien a un titre de moins de 3 caracteres = spam !
				if ($infos_texte['caracteres_texte_lien_min'] < 3) {
					$flux['data']['message_erreur'] = _T('cispam:erreur_spam');
				}
				// si le texte contient plus de trois liens = spam !
				if ($infos_texte['nombre_liens'] >= 2)
					$flux['data']['message_erreur'] = _T('cispam:erreur_spam');
			}
		}
		// S'il y a un lien dans le champ session_nom => spam
		if (!isset($flux['data']['session_nom'])){
			include_spip("inc/cispam");
			$infos_texte = analyser_spams(_request('session_nom'));
			if ($infos_texte['nombre_liens'] > 0) {
				$flux['data']['message_erreur'] = _T('cispam:erreur_spam');
//				spip_log("Lien dans le champ session_nom ".$flux['data']['message_erreur'],'nospam');
			}
		}
	}
	return $flux;
}

/**
 * Au moment de decider du statut d'un forum,
 * quelques verifications et une moderation si necessaire !
 *
 * @param array $flux
 * @return array
 */
function cispam_pre_edition($flux){
	if ($flux['args']['table']=='spip_forum'
	  AND $flux['args']['action']=='instituer'){
	  
	  // ne pas publier automatiquement certains messages suspects ...
		// sauf si le posteur a de toute facon le pouvoir de moderer et de se publier
		include_spip('inc/autoriser');
//----- Debut ajout CI -----
   		// contourner l'incompatibilite de SPIP avec les reverses proxy
		$cispam_ip = cispam_ip();

//	  if ($flux['data']['statut'] == 'publie'
//	  AND (!isset($GLOBALS['visiteur_session']['id_auteur']) OR !autoriser('modererforum'))){

	  // Eviter	de se retrouver avec un grand nombre de messages dans la base de donnees
	  // y compris pour les forums moderes a priori 
	  if ( ($flux['data']['statut'] == 'publie' OR $flux['data']['statut'] == 'prop')
	  AND (!isset($GLOBALS['visiteur_session']['id_auteur']) OR !autoriser('modererforum'))) {
//----- Fin ajout CI -----

			$email = strlen($flux['data']['email_auteur']) ? " OR email_auteur=".sql_quote($flux['data']['email_auteur']):"";
			$spammeur_connu = (!isset($GLOBALS['visiteur_session']['statut']) AND (sql_countsel('spip_forum','(ip='.sql_quote(cispam_ip)."$email) AND statut='spam'")>0));

			// si c'est un spammeur connu,
			// verifier que cette ip n'en est pas a son N-ieme spam en peu de temps
			// a partir d'un moment on refuse carrement le spam massif
			if ($spammeur_connu){
				// plus de 10 spams dans les dernieres 2h, faut se calmer ...
				// ou plus de 30 spams dans la dernieres 1h, faut se calmer ...
//----- Debut ajout CI -----
/*
				if (
					($nb=sql_countsel('spip_forum','statut=\'spam\' AND (ip='.sql_quote(cispam_ip).$email.') AND maj>DATE_SUB(NOW(),INTERVAL 120 minute)'))>10
					OR
					($nb=sql_countsel('spip_forum','statut=\'spam\' AND (ip='.sql_quote(cispam_ip).$email.') AND maj>DATE_SUB(NOW(),INTERVAL 60 minute)'))>30
					){
					$flux['data']['statut']=''; // on n'en veut pas !
					spip_log("[Refuse] $nb spam pour (ip=".cispam_ip."$email) dans les 2 dernieres heures",'cispam');
					
					return $flux;
				}
*/				
				// si IP d'un spammeur connu, ne pas enregistrer 
				if ((sql_countsel('spip_forum','statut=\'spam\' AND (ip='.sql_quote(cispam_ip).')'))>0){
					$flux['data']['statut']=''; // on n'en veut pas !
					spip_log("[Refuse] spam pour (ip=".cispam_ip."$email)",'cispam');
					
					return $flux;
				}
//----- Fin ajout CI -----
			}

			// si c'est un message bourre de liens, on le modere
			// le seuil varie selon le champ et le fait que le spammeur est deja connu ou non
			$seuils = array(
				// seuils par defaut
				0=>array(
					0=>array(1=>'prop',3=>'spam'), // seuils par defaut
					'url_site' => array(2=>'spam'), // 2 liens dans le champ url, c'est vraiment louche
					'texte'=>array(4=>'prop',20=>'spam') // pour le champ texte
				),
				// seuils severises pour les spammeurs connus
				'spammeur'=>array(
					0=>array(1=>'spam'),
					'url_site' => array(2=>'spam'), // 2 liens dans le champ url, c'est vraiment louche
					'texte'=>array(1=>'prop',5=>'spam')
				)
			);

			$seuils = $spammeur_connu?$seuils['spammeur']:$seuils[0];
			include_spip("inc/cispam"); // pour analyser_spams()
			foreach($flux['data'] as $champ=>$valeur) {
				$infos = analyser_spams($valeur);
				if ($infos['contenu_cache']) {
					// s'il y a du contenu cache avec des styles => spam direct
					$flux['data']['statut'] = 'spam';
				} elseif ($infos['nombre_liens'] > 0) {
					// si un lien a un titre de moins de 3 caracteres, c'est louche...
					if ($infos['caracteres_texte_lien_min'] < 3) {
						$flux['data']['statut'] = 'prop'; // en dur en attendant une idee plus generique
					}
					
					if (isset($seuils[$champ]))
						$seuil = $seuils[$champ];
					else
						$seuil = $seuils[0];

					foreach($seuil as $s=>$stat)
						if ($infos['nombre_liens'] >= $s) { 
							$flux['data']['statut'] = $stat;
						}
				}
			}


			// verifier qu'un message identique n'a pas ete publie il y a peu
			if ($flux['data']['statut'] != 'spam'){
//----- Debut ajout CI -----
//				if (sql_countsel('spip_forum','texte='.sql_quote($flux['data']['texte'])." AND statut IN ('publie','off','spam')")>0)
				// il n'y avait pas de limite de temps ce qui rendait lourd le traitement SQL
				if (sql_countsel('spip_forum','maj>DATE_SUB(NOW(),INTERVAL 1440 minute) AND texte='.sql_quote($flux['data']['texte'])." AND statut IN ('publie','off','spam')")>0)
//----- Fin ajout CI -----
					$flux['data']['statut']='spam';
			}

			// verifier que cette ip n'en est pas a son N-ieme post en peu de temps
			// plus de 5 messages en 5 minutes c'est suspect ...
			if ($flux['data']['statut'] != 'spam'){
//----- Debut ajout CI -----
//				if (($nb=sql_countsel('spip_forum','ip='.sql_quote(cispam_ip).' AND maj>DATE_SUB(NOW(),INTERVAL 5 minute)'))>5)
				// alignement sur Giseh : 5 messages en 60 secondes quelque soit l'IP 
					$mydate = date("YmdHis", time() - 60);
				if (($nb=sql_countsel('spip_forum','maj>'.$mydate))>5)
//----- Fin ajout CI -----
					$flux['data']['statut']='spam';
				#spip_log("$nb post pour l'ip ".cispam_ip." dans les 5 dernieres minutes",'cispam');
			}
			
//----- Debut ajout CI -----
			// Ne pas les enregistrer les spams dans la base de donnees
//			if ($flux['data']['statut']=='spam' OR $flux['data']['statut']=='prop')
			if ($flux['data']['statut']=='spam') {
				$flux['data']['statut']=''; 
				return $flux;				
			}
//----- Fin ajout CI -----
			
	  }
	}


//----- Debut ajout CI -----
	$champs = array();
	$champs_forum = array();
	
    if ($flux['args']['table']=='spip_forum') {
		// Pour enlever le code HTML dans les forums publics
		// lors de l'enregistrement d'un commentaire
   		if ($action = cispam_config('cispam_forum_html_amont')){
			$champs_forum = array('titre', 'texte', 'nom_site', 'url_site', 'auteur', 'email_auteur');
			foreach ($champs_forum as $champ) {
				if ($flux['data'][$champ])
		            $flux['data'][$champ] = cispam_forum_sans_html($flux['data'][$champ],$action);
			}
   		}

   		// contourner l'incompatibilite de SPIP avec les reverses proxy
		$cispam_ip = cispam_ip();
   		
    } else {
		// Pour enlever le code HTML dans les autres objets (articles, rubriques, etc.)
		// lors de leur enregistrement (creation ou modification)
   		if ($action = cispam_config('cispam_autresobjets_html_amont')){
			$champs = array('surtitre', 'titre', 'soustitre', 'descriptif', 'nom_site', 'url_site', 'chapo', 'texte', 'ps',
			'lien_titre', 'lien_url', 'nom', 'bio', 'email', 'nom_email', 'ad_email', 'message');
			foreach ($champs as $champ) {
				if (isset($flux['data'][$champ]))
		            $flux['data'][$champ] = cispam_texte_sans_html($flux['data'][$champ],$action);
			}
   		}
    }
//----- Fin ajout CI -----
	
	return $flux;
}


function cispam_pipeline_est_utilise($pipeline) {
	static $return = array();

	if (!isset($return[$pipeline])){	
		$cipipeline = true;
		if (@is_readable($charger = _CACHE_PIPELINES)){
			include_once($charger);
			if (!function_exists('execute_pipeline_'.$pipeline))
				$cipipeline = false;
		}
		$return[$pipeline] = $cipipeline;
	}
	
	return $return[$pipeline];
}

?>