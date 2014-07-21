<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');

/**
 * Envoyer un email de notification
 *
 * @param array $abonnes (par reference pour de meilleures performances)
 * @param array $notification
 * @param array $evenements
 */
function cinotif_notifier(&$abonnes,$notification,$evenements){

	// choix d'envoyer aux listes de diffusion de SYMPA (ou de desactiver les notifications)
	if (cinotif_type_notification_actif()=='sympa'){
		cinotif_sympa_notifier($notification,$evenements);
		return;
	} elseif (cinotif_type_notification_actif()=='aucun'){
		return;		
	}
	
	$quoi = $notification['quoi'];
	$objet = $notification['objet'];
	$id_objet = $notification['id_objet'];
	$id_version = $notification['id_version'];
	$parent = $notification['parent'];
	$id_parent = $notification['id_parent'];
	$texte = $notification['texte'];
	$sujet = $notification['sujet'];
	$id_moi = 0;

	// rien a faire si pas de texte ou pas d'abonnes
	if (!strlen($texte) OR !$abonnes)
		return;
	
	// le courrier existe-t-il deja (eviter la redondance d'envoi) ?
	$where_courrier = "quoi=".sql_quote($quoi)." AND objet=".sql_quote($objet)." AND id_objet=".$id_objet;
	if (intval($id_version)>0) {
		$where_complement = " AND id_version=".intval($id_version);
		if (defined('_CINOTIF_TOUTES_MODIF')) {
			if (_CINOTIF_TOUTES_MODIF=='oui')
				$where_complement = "";
		}
		$where_courrier .= $where_complement;
	}
	if (($nb=sql_countsel('spip_cinotif_courriers', $where_courrier))>0)
		return;

	// Suivant le parametrage, ecrire ou non a l'abonne (authentifie) qui declenche l'evenement
	$cinotif_meta = cinotif_tableau_meta();
	if ($id_moi = cinotif_id_abonne($GLOBALS['visiteur_session']['id_auteur'],'')){
		if (isset($cinotif_meta['auto_notif']) AND $cinotif_meta['auto_notif']=='oui'){
			if (!in_array($id_moi,$abonnes))
				$abonnes[] = $id_moi;
		} else {
			if (in_array($id_moi,$abonnes))
				$abonnes = array_diff($abonnes,array($id_moi));
		}
	}

	if (!$abonnes)
		return;

	// quelle est la rubrique parente ?
	$id_rubrique = 0;
	if ($parent=='rubrique')
		$id_rubrique = $id_parent;
	elseif ($parent=='article') {
		$row = sql_fetsel("id_rubrique", "spip_articles", "id_article=".$id_parent);
		if ($row)
			$id_rubrique = $row['id_rubrique'];
	}

	// si le plugin CIAR est actif
	if (defined('_DIR_PLUGIN_CIAR')){
		if ($id_rubrique){
			include_spip('ciar_fonctions');
			// en cas d'EC, ne prendre que les destinataires qui sont membres de l'EC
			if (ciar_rub_ec($id_rubrique)) {
				$membres_ec = ciar_tableau_membres($id_rubrique);
				if ($membres_ec) {
					$tableau_id_membres_ec = cinotif_id_abonnes($membres_ec,'');
					$abonnes = array_intersect($tableau_id_membres_ec,$abonnes);
					if (!$abonnes)
						return;
				} else {
					// securite
					return;
				}
			// en cas d'acces restreint simple, ne prendre que les destinataires qui ont un id_auteur
			} elseif (ciar_rub_ar($id_rubrique)) {
				$abonnes_auteurs = array();
				$result = sql_select('id_abonne', 'spip_cinotif_abonnes', sql_in('id_abonne',$abonnes)." AND id_auteur>0");
				while ($row = sql_fetch($result))
					$abonnes_auteurs[] = $row['id_abonne'];

				$abonnes = $abonnes_auteurs;
				if (!$abonnes)
					return;
			}
		} else {
			// securite
			return;
		}
	}


	// si le sujet est vide, extraire la premiere ligne du corps
	// Le texte d'un commentaire dans SPIP est au plus de 20 000 caracteres
	// on peut donc le stocker dans un champ TEXT
	if (!strlen($sujet)){
		// nettoyer un peu les retours chariots
		$texte = str_replace("\r\n", "\r", $texte);
		$texte = str_replace("\r", "\n", $texte);
		// decouper
		$texte = explode("\n",trim($texte));
		// extraire la premiere ligne
		$sujet = array_shift($texte);
		$texte = trim(implode("\n",$texte));
	}

	
	// stocker, le courrier a envoyer, dans la table spip_cinotif_courriers
	$nb_emails = count($abonnes);
	$ladate = date('Y-m-d H:i:s');
	$ci_url_site = strtolower($GLOBALS['meta']['adresse_site']);
	$champs = array(
			'quoi'=>$quoi,
			'objet'=> $objet,
			'id_objet'=> $id_objet,
			'id_version'=> $id_version,
			'parent'=> $parent,
			'id_parent'=> $id_parent,
			'sujet' => $sujet,
			'texte'	=> $texte,
			'url_site'	=> $ci_url_site,
			'date' => $ladate,
			'statut' => 'prop',
			'destinataires' => implode(',',$abonnes),
			'nb_emails' => $nb_emails,
			'date_debut_envoi' => $ladate);
	$id_courrier = sql_insertq('spip_cinotif_courriers', $champs);			

	// Ajouter un lien pour se desabonner (avec l'adresse du site public)
	$msg_desabo = filtrer_entites("\n\n" ._T('cinotif:tirets') ."\n" ._T('cinotif:desa_mail_courrier'));
	$msg_desabo .= "\n" .$ci_url_site."/spip.php?page=cinotif&desabonner=oui&e=&i=".$id_courrier;
	sql_updateq("spip_cinotif_courriers", array('texte'=>$texte.$msg_desabo), "id_courrier=".$id_courrier);


	// stocker les destinataires de ce courrier, dans la table spip_cinotif_tmp
	$nb_mails_par_paquet = cinotif_nb_mails_par_paquet();
	$delai_entre_paquet = cinotif_delai_entre_paquet();
	$clause_in_par_paquet = cinotif_clause_in_par_paquet();
	
	reset($abonnes);
	if (($clause_in_par_paquet>0) AND ($nb_emails > $clause_in_par_paquet)){
		// effectuer plusieurs iterations pour limiter les clauses IN 
		$offset = 0;
		$lenght = $clause_in_par_paquet;

        while ($offset < $nb_emails) {
        	$new_offset = $offset + $lenght;
        	if ($new_offset > $nb_emails)
        		$lenght = $nb_emails - $offset;

			cinotif_stocker_dans_tmp(array_slice($abonnes,$offset,$lenght),$id_courrier,$evenements,$nb_mails_par_paquet,$delai_entre_paquet);
			$offset = $new_offset;
        }
	} else {
		cinotif_stocker_dans_tmp($abonnes,$id_courrier,$evenements,$nb_mails_par_paquet,$delai_entre_paquet);
	}

	// Suivant le parametrage, ecrire ou non a l'auteur NON abonne (authentifie) qui declenche l'evenement
	if (!$id_moi AND isset($cinotif_meta['auto_notif']) AND $cinotif_meta['auto_notif']=='oui'){
		if (isset($GLOBALS['visiteur_session']['email']) AND $GLOBALS['visiteur_session']['email']){
			$envoyer_mail = charger_fonction('envoyer_mail','inc');
			$envoyer_mail($GLOBALS['visiteur_session']['email'], $sujet, $texte);
		}
	}
	
	
	// passer au moteur d'envoi
	cinotif_moteur_envoi($id_courrier);

	return true;
}


// stocker les destinataires de ce courrier, dans la table spip_cinotif_tmp	
function cinotif_stocker_dans_tmp(&$abonnes,$id_courrier,&$evenements,$nb_mails_par_paquet,$delai_entre_paquet){

	// on regarde le pas_avant le plus recent, avec le statut 'prop'.
	// s'il existe, on decale pour eviter des envois trop rapproches
	$row = sql_fetsel('pas_avant', 'spip_cinotif_tmp',"statut='prop'",'','pas_avant DESC',1);
	if ($row)
		$pas_avant_time = strtotime($row['pas_avant']) + $delai_entre_paquet;
	else
		$pas_avant_time = time(); 	


	$pas_avant = date('Y-m-d H:i:s',$pas_avant_time);
	$tab_couples = array();
	$n = 1;

	// compatibilite avec le plugin CIMS
	// compatibilite avec intraextra
	if ( ( (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF')) OR cinotif_existe_abo_intraextra()) AND $evenements) {

		// on recupere les emails (il s'agit d'eviter une double jointure a forte volumetrie)
		$emails = array();
		$result = sql_select('id_abonne,email', 'spip_cinotif_abonnes', sql_in('id_abonne',$abonnes));	
		while ($row = sql_fetch($result))
			$emails[$row['id_abonne']] = $row['email'];

		// Dans le cas d'un multisite, determiner le site lors de l'abonnement a l'evenement.
		// Si plusieurs evenements correspondent au courrier, donner priorite a un abonnement au site, puis un abonnement a une rubrique, puis un abonnement a un article		
		$result = sql_select('abo.id_abonne as id_abonne,MAX(lien.objet) as monobjet,lien.multisite as codesite,lien.url_multisite as urlsite', 'spip_cinotif_abonnements AS abo LEFT JOIN spip_cinotif_evenements AS lien ON lien.id_evenement=abo.id_evenement', 'abo.id_evenement IN ('.implode(',',$evenements).') AND abo.id_abonne IN ('.implode(',',$abonnes).')',"abo.id_abonne");
		while ($row = sql_fetch($result)) {
			$id_abonne = $row['id_abonne'];
			$multisite = $row['codesite'];
			$url_multisite = $row['urlsite'];
			$n++;
			$tab_couples[] = array("id_courrier" => $id_courrier, "id_abonne" => $id_abonne, "email" => $emails[$id_abonne], "multisite" => $multisite, "url_multisite" => $url_multisite, "statut" => "prop", "pas_avant" => $pas_avant);
			if ($n>$nb_mails_par_paquet) {
				// Inserer par groupes de nb_mails_par_paquet
				sql_insertq_multi('spip_cinotif_tmp',$tab_couples);
				$tab_couples = array();
				$pas_avant_time = $pas_avant_time + $delai_entre_paquet; 
				$pas_avant = date('Y-m-d H:i:s',$pas_avant_time);
				$n = 1;
			}
		}
		if ($tab_couples)	// Inserer le reste
			sql_insertq_multi('spip_cinotif_tmp',$tab_couples);	
		
	} else {
		// site normal (pas un multisites)
		
		// on evite la fonction sql_in vu le risque de volumetrie eleve
		$result = sql_select('id_abonne,email', 'spip_cinotif_abonnes', 'id_abonne IN ('.implode(',',$abonnes).')');	
		while ($row = sql_fetch($result)) {
			$n++;
			$tab_couples[] = array("id_courrier" => $id_courrier, "id_abonne" => $row['id_abonne'], "email" => $row['email'], "statut" => "prop", "pas_avant" => $pas_avant);
			if ($n>$nb_mails_par_paquet) {
				// Inserer par groupes de nb_mails_par_paquet
				sql_insertq_multi('spip_cinotif_tmp',$tab_couples);
				$tab_couples = array();
				$pas_avant_time = $pas_avant_time + $delai_entre_paquet; 
				$pas_avant = date('Y-m-d H:i:s',$pas_avant_time);
				$n = 1;
			}
		}
		if ($tab_couples)	// Inserer le reste
			sql_insertq_multi('spip_cinotif_tmp',$tab_couples);
		
	}
}

function cinotif_moteur_envoi($id_courrier=0){
	$envoyer_mail = charger_fonction('envoyer_mail','inc');
	$nb_mails_par_paquet = cinotif_nb_mails_par_paquet();
	$id_courrier = intval($id_courrier);
	
	if ($id_courrier) {
		// sous reserve que la date minimum d'envoi soit depassee
		$dateok = false;
		$row = sql_fetsel('pas_avant', 'spip_cinotif_tmp',"id_courrier=".$id_courrier." AND statut='prop'",'','pas_avant',1);
		if ($row) {
			if (time() >= strtotime($row['pas_avant']))
				$dateok = true;
		}
		
		if ($dateok){
			$row = sql_fetsel('*', 'spip_cinotif_courriers', 'id_courrier='.$id_courrier);
			if ($row) {
				$sujet = $row['sujet'];
				$texte = $row['texte'];
				$ci_url_site = $row['url_site'];
				$nb_emails = $row['nb_emails'];
				$nb_emails_deja_envoyes = $row['nb_emails_envoyes'];
			}
			
			// envoyer un paquet de mails pour ce courrier 			
			$nb_envois = 0;
			$url_base = '';
			$select = 'id_abonne,email';

			// compatibilite avec le plugin CIMS
			// compatibilite avec intraextra
			if ( ( defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF') ) OR cinotif_existe_abo_intraextra()) {
				$select = 'id_abonne,email,url_multisite';
				$ci_url_site = str_replace(array('http://','https://'),'',$ci_url_site);
				$pos = strpos($ci_url_site,'/');
				if 	($pos!==false)
					$ci_url_site = substr($ci_url_site,0,$pos);
			}
	
			$ok = true;
			if (defined('_CINOTIF_DESACTIVER_ENVOIS')) {
				if (_CINOTIF_DESACTIVER_ENVOIS=='oui')
					$ok = false;
			}
			
			$result = sql_select($select, 'spip_cinotif_tmp', "id_courrier=".$id_courrier." AND statut='prop'",'','pas_avant',$nb_mails_par_paquet);
			while ($row = sql_fetch($result)) {
				$email = $row['email'];
				$id_abonne = $row['id_abonne'];
				$url_multisite = $row['url_multisite'];
				
				// ajout des liens pour se desabonner
				$texte_specif = str_replace('&e=&','&e='.$id_abonne.'&',$texte);

				// compatibilite avec le plugin CIMS : mettre l'URL du site en cours lors de l'abonnement a l'evenement.
				if ($ci_url_site AND $url_multisite){
					$url_multisite = str_replace(array('http://','https://'),'',$url_multisite);
					$pos = strpos($url_multisite,'/');
					if 	($pos!==false)
						$url_multisite = substr($url_multisite,0,$pos);
				
					$texte_specif = str_replace($ci_url_site,$url_multisite,$texte_specif);
				}

				// envoi
				if ($ok)
					$envoyer_mail($email, $sujet, $texte_specif);
	
				$nb_envois++;
				sql_updateq("spip_cinotif_tmp", array('statut'=>'publie'), "id_courrier=".$id_courrier." AND id_abonne='".$id_abonne."'");
			}
	
			// memoriser le cumul des envois pour ce courrier
			// s'il n'en reste plus a faire, changer le statut du courrier et memoriser la date de fin des envois 
			$nb_emails_envoyes = intval($nb_emails_deja_envoyes) + $nb_envois;
			$tableau_update = array('nb_emails_envoyes'=> $nb_emails_envoyes);
			$tableau_update['date_fin_envoi'] = date('Y-m-d H:i:s');
			if ($nb_emails_envoyes>=$nb_emails)
				$tableau_update['statut'] = 'publie';
	
			sql_updateq("spip_cinotif_courriers", $tableau_update, "id_courrier=".$id_courrier);
			
			// s'il n'en reste plus a faire, supprimer dans spip_cinotif_tmp les enregistrements correspondants
			// mais conserver l'enregistrement dans spip_cinotif_courriers
			if ($nb_emails_envoyes>=$nb_emails)
				sql_delete('spip_cinotif_tmp', "id_courrier=".$id_courrier);
		}
	} else {
		// on prend le courrier dont le pas_avant est le plus ancien (avec le statut 'prop')
		// sous reserve que la date minimum d'envoi soit depassee
		$row = sql_fetsel('id_courrier,pas_avant', 'spip_cinotif_tmp',"statut='prop'",'','pas_avant',1);
		if ($row) {
			if (time() > strtotime($row['pas_avant']))
				cinotif_moteur_envoi($row['id_courrier']);
		}
	}

	return true;
}


function cinotif_nettoyer_emails(&$emails, $exclure = array()){
	// filtrer et unifier
	$emails = array_unique(array_filter(array_map('email_valide',array_map('trim', $emails))));
	if ($exclure AND count($exclure)){
		// nettoyer les exclusions d'abord
		cinotif_nettoyer_emails($exclure);
		// faire un diff
		$emails = array_diff($emails,$exclure);
	}
}


// envoyer les mails par paquet de 50 mails (parametrable via la constante _CINOTIF_NB_MAILS_PAR_PAQUET)
function cinotif_nb_mails_par_paquet() {
	$nb_mails_par_paquet = 50;
	if (defined('_CINOTIF_NB_MAILS_PAR_PAQUET')) {
		if (intval(_CINOTIF_NB_MAILS_PAR_PAQUET)>1)
			$nb_mails_par_paquet = intval(_CINOTIF_NB_MAILS_PAR_PAQUET);
	}
	return $nb_mails_par_paquet;
}

// entre 2 paquets, attendre 50 secondes (parametrable via la constante _CINOTIF_DELAI_ENTRE_PAQUETS)
function cinotif_delai_entre_paquet() {
	$delai_entre_paquet = 50;
	if (defined('_CINOTIF_DELAI_ENTRE_PAQUETS')) {
		if (intval(_CINOTIF_DELAI_ENTRE_PAQUETS)>0)
			$delai_entre_paquet = intval(_CINOTIF_DELAI_ENTRE_PAQUETS);
	}
	return $delai_entre_paquet;
}

// limiter la clause IN des requetes SQL (parametrable via la constante _CINOTIF_CLAUSE_IN_PAR_PAQUET)
function cinotif_clause_in_par_paquet() {
	$clause_in_par_paquet = 2000;
	if (defined('_CINOTIF_CLAUSE_IN_PAR_PAQUET')) {
		if (intval(_CINOTIF_CLAUSE_IN_PAR_PAQUET)>100)
			$clause_in_par_paquet = intval(_CINOTIF_CLAUSE_IN_PAR_PAQUET);
	}
	return $clause_in_par_paquet;
}

function cinotif_nb_max_abonnes() {
	$cinotif_nb_max_abonnes = 10000;
	if (defined('_CINOTIF_NB_MAX_ABONNES')) {
		if (intval(_CINOTIF_NB_MAX_ABONNES)>1)
			$cinotif_nb_max_abonnes = intval(_CINOTIF_NB_MAX_ABONNES);
	}

	if (cinotif_type_notification_actif()=='sympa'){
		// limite theorique de SYMPA
		$cinotif_nb_max_abonnes = 700000;
	}

	return $cinotif_nb_max_abonnes;
}

function cinotif_tableau_evenements($quoi, $objet, $tableau_id_objet=array(), $tableau_multisite=array(), $ctrl_config=true){
	$tableau_id_evenement = array();
	
	if (!$ctrl_config OR cinotif_config_quoi_objet($quoi, $objet)) {
		$where = "quoi=".sql_quote($quoi)." AND objet=".sql_quote($objet);
	
		if ($tableau_id_objet) {
			if (is_array($tableau_id_objet))
				$where .= " AND ". sql_in('id_objet',$tableau_id_objet);
			else			
				$where .= " AND id_objet=".$tableau_id_objet;
		}
	
		// compatibilite avec le plugin CIMS
		if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF') AND $tableau_multisite) {
			if (is_array($tableau_multisite))
				$where .= " AND ". sql_in('multisite',$tableau_multisite);
			else			
				$where .= " AND multisite=".sql_quote($tableau_multisite);
		}
	
		// cas du formulaire par theme et du formulaire simple
		if (cinotif_form_theme_ou_simple())
			$where .= " AND statut='publie'";
		else
			$where .= " AND statut=''";
		

		$result = sql_select('id_evenement', 'spip_cinotif_evenements', $where);
		while ($row = sql_fetch($result))
			$tableau_id_evenement[] = $row['id_evenement'];		
	}

	return $tableau_id_evenement;	
}

function cinotif_config_quoi_objet($quoi, $objet){
	$return = false;
	$cinotif_meta = cinotif_tableau_meta(true);
	
	if (cinotif_form_theme_ou_simple()){
			$return = true;	
	} elseif (isset($cinotif_meta['abonnements_'.$objet]) AND is_array($cinotif_meta['abonnements_'.$objet])){
		// tenir compte de la configuration
		if (in_array($quoi, $cinotif_meta['abonnements_'.$objet]))
			$return = true;	
	}
		
	return $return;	
}

function cinotif_tableau_abonnes_evenement($tableau_id_evenement=array()){
	$return = array();

	if ($tableau_id_evenement) {
		$type_notification = cinotif_type_notification_actif();
		
		if ($type_notification=='sympa'){
			// renvoyer les adresses des listes de diffusion SYMPA (pour eviter une constitution inutile de la liste des abonnes)
			$result = sql_select("adresse_liste_diffusion", "spip_cinotif_evenements", sql_in('id_evenement',$tableau_id_evenement)." AND statut='publie'", "adresse_liste_diffusion");
			while ($row = sql_fetch($result))
				$return[] = $row['adresse_liste_diffusion'];

		} elseif ($type_notification=='aucun'){
			// ne rien faire

		} else {
			// constitution de la liste des abonnes
			$result = sql_select('id_abonne', 'spip_cinotif_abonnements', sql_in('id_evenement',$tableau_id_evenement)." AND statut='publie'",'id_abonne');
			while ($row = sql_fetch($result))
				$return[] = $row['id_abonne'];
		}
	}

	return $return;	
}


function cinotif_tableau_id_auteur_abonnes_evenement($tableau_id_evenement=array()){
	$return = array();

	if ($tableau_id_evenement) {
		$type_notification = cinotif_type_notification_actif();
		
		if ($type_notification=='sympa'){
			// ne rien faire
		} elseif ($type_notification=='aucun'){
			// ne rien faire
		} else {
			$tableau_abonnes = cinotif_tableau_abonnes_evenement($tableau_id_evenement);
			$result = sql_select('id_auteur', 'spip_cinotif_abonnes', sql_in('id_abonne',$tableau_abonnes)." AND id_auteur>0");
			while ($row = sql_fetch($result))
				$return[] = $row['id_auteur'];
		}
	}

	return $return;	
}


function cinotif_ascendance($id) {
	$return = array();
	
	if ($id) {
		// normaliser $id qui a pu arriver comme un array, comme un entier, ou comme une chaine NN,NN,NN
		if (!is_array($id)) $id = explode(',',$id);
		$id = join(',', array_map('intval', $id));
	
		// Notre branche commence par la rubrique de depart
		$branche = $id;
	
		// On ajoute une generation (les parents de la generation precedente)
		// jusqu'a epuisement
		while ($parents = sql_allfetsel('id_parent', 'spip_rubriques',
		sql_in('id_rubrique', $id)." AND id_parent>0")) {
			$id = join(',', array_map('array_shift', $parents));
			$branche .= ',' . $id;
		}
		
		$return = explode(',',$branche);
	}

	return $return;
}

function cinotif_url($objet, $id_objet){
	$return = '';

	switch($objet) {
		case 'article':
			$return = generer_url_ecrire("articles", "id_article=$id_objet");
			break;
		case 'rubrique':
			if (spip_version()>=3)
				$return = generer_url_ecrire("rubrique", "id_rubrique=$id_objet");
			else
				$return = generer_url_ecrire("naviguer", "id_rubrique=$id_objet");

			break;
		case 'site':
			$return = generer_url_ecrire("accueil");
			break;
	}
	
	return $return;	
}

function cinotif_url_public($objet, $id_objet){
	$return = '';

	switch($objet) {
		case 'article':
			$return = generer_url_public("article", "id_article=$id_objet");
			break;
		case 'rubrique':
			$return = generer_url_public("rubrique", "id_rubrique=$id_objet");
			break;
		case 'site':
			$return = generer_url_public("sommaire");
			break;
	}
	
	return $return;	
}

function cinotif_titrefichier($fichier) {
	// enlever l'extension et le chemin
	$titre=$fichier;
	$pos1 = strrpos($titre,".");
	if (!($pos1 === false)) $titre=substr($titre,0,$pos1);

	$pos3 = strrpos($titre,"/");
	if (!($pos3 === false)) $titre=substr($titre,$pos3+1);
	
	return $titre;
}

function cinotif_tableau_meta($evenements_only=false,$autres_only=false) {
	
	$return = array();
		
	if (isset($GLOBALS['meta']['cinotif'])) {
		$meta_cinotif = @unserialize($GLOBALS['meta']['cinotif']);
		
		if (is_array($meta_cinotif)) {
			$return = $meta_cinotif;

			// La modification d'article necessite que le suivi des revisions soit active
			if (!cinotif_revision_active()) {
				$cinotif_objets = cinotif_objets();
				foreach ($cinotif_objets AS $objet) {
					if (is_array($return['abonnements_'.$objet]) AND (in_array('articlemodifie',$return['abonnements_'.$objet])))
						$return['abonnements_'.$objet] = array_diff($return['abonnements_'.$objet],array('articlemodifie'));
				}
			}
			
			// parametrage par constante dans un fichier d'options
			if (defined("_CINOTIF_ADRESSE_SYMPA") AND _CINOTIF_ADRESSE_SYMPA)
				$return['adresse_sympa'] = _CINOTIF_ADRESSE_SYMPA;
			if (defined("_CINOTIF_ADRESSE_PROPRIO_LISTE_SYMPA") AND _CINOTIF_ADRESSE_PROPRIO_LISTE_SYMPA)
				$return['adresse_proprio_liste_sympa'] = _CINOTIF_ADRESSE_PROPRIO_LISTE_SYMPA;
			if (defined("_CINOTIF_ABONNEMENT_SYMPA") AND in_array(_CINOTIF_ABONNEMENT_SYMPA, array('oui','non')))
				$return['abo_sympa'] = _CINOTIF_ABONNEMENT_SYMPA;
			if (defined("_CINOTIF_TYPE_NOTIFICATION") AND in_array(_CINOTIF_TYPE_NOTIFICATION, array('abonnes','sympa','aucun')))
				$return['typenotif'] = _CINOTIF_TYPE_NOTIFICATION;
	
		}
	}

	if (!$return){
		
		// valeurs par defaut
		$return['abo_tous'] = 'oui';
		$return['abo_non_auth'] = 'oui';
		$return['abonnements_site'] = array();
		$return['abonnements_rubrique'] = array();
		$return['abonnements_article'] = array();
		$return['abo_texte'] = '';
		$return['desabo_texte'] = '';
		$return['typeabo'] = '';
		$return['adresse_sympa'] = '';
		$return['adresse_proprio_liste_sympa'] = '';
		$return['abo_sympa'] = 'non';
		$return['typenotif'] = 'abonnes';
		$return['auto_notif'] = 'non';
		$return['lien_abo_uniquement_en_page_accueil'] = 'non';

		// parametrage par constante dans un fichier d'options
		if (defined("_CINOTIF_ADRESSE_SYMPA") AND _CINOTIF_ADRESSE_SYMPA)
			$return['adresse_sympa'] = _CINOTIF_ADRESSE_SYMPA;
		if (defined("_CINOTIF_ADRESSE_PROPRIO_LISTE_SYMPA") AND _CINOTIF_ADRESSE_PROPRIO_LISTE_SYMPA)
			$return['adresse_proprio_liste_sympa'] = _CINOTIF_ADRESSE_PROPRIO_LISTE_SYMPA;
		
		// Les articles en actualite necessite le plugin CISQUEL
		if (defined('_DIR_PLUGIN_CISQUEL')){
			$return['abonnements_site'][] = 'actupublie';
			$return['abonnements_rubrique'][] = 'actupublie';
		} else {
			$return['abonnements_site'][] = 'articlepublie';
		}
		
		// Par defaut
		$return['abonnements_rubrique'][] = 'articlepublie';
		$return['abonnements_rubrique'][] = 'articlepropose';

		// La modification d'article necessite que le suivi des revisions soit active
		if (cinotif_revision_active()){
			$return['abonnements_rubrique'][] = 'articlemodifie';
			$return['abonnements_article'][] = 'articlemodifie';
		}

		// Si plugin CIAR proposer par defaut 'documentajoute'
		if (defined('_DIR_PLUGIN_CIAR')){
			$return['abonnements_rubrique'][] = 'documentajoute';
			$return['abonnements_article'][] = 'documentajoute';			
		}
		
		// Proposer le suivi des commentaires que si les forums publics sont autorises et utilises
		if (cinotif_forum_public_utilise()){
			$return['abonnements_site'][] = 'forumvalide';
			$return['abonnements_rubrique'][] = 'forumvalide';
			$return['abonnements_article'][] = 'forumvalide';
		}		
	}

	if ($evenements_only){
		unset($return['abo_tous']);
		unset($return['abo_non_auth']);
	}
	if ($autres_only){
		unset($return['abonnements_site']);
		unset($return['abonnements_rubrique']);
		unset($return['abonnements_article']);
	}
	
    return $return;
}

// Le suivi des revisions est-il active ?
function cinotif_revision_active() {
	$return = false;
	if (spip_version()>=3) {
		$liste_objets_versionnees = is_array(unserialize($GLOBALS['meta']['objets_versions'])) ? unserialize($GLOBALS['meta']['objets_versions']) : array();
		if  (in_array('spip_articles',$liste_objets_versionnees))
			$return = true;
	} else {
		if  ($GLOBALS['meta']["articles_versions"]=='oui')
			$return = true;
	}

	return $return;
}

// les forums publics sont-ils actives et utilises ?
function cinotif_forum_public_utilise() {
	$return = false;
	if ($GLOBALS['meta']["forums_publics"]!='non'){
		if (sql_countsel('spip_forum', "statut='publie'")>0)
			$return = true;	
	}
	return $return;
}

function cinotif_abonnements_configurables($objet='') {

	if ($objet!='article'){
		if (defined('_DIR_PLUGIN_CISQUEL'))
			$abonnements = array('actupublie','articlepublie','articlepropose');
		else
			$abonnements = array('articlepublie','articlepropose');
	}

	// La modification d'article necessite que le suivi des revisions soit active
	if (cinotif_revision_active())
		$abonnements[] = 'articlemodifie';

	$abonnements[] = 'documentajoute';
		
	// les forums publics sont-ils actives ?
	if ($GLOBALS['meta']["forums_publics"]!='non')
		$abonnements[] = 'forumvalide';

	return $abonnements;
}

function cinotif_objets() {
	return array('site','rubrique','article');
}


function cinotif_suppr_evenements_sans_abonnement() {

	// les evenements sans abonnements (s'ils n'ont pas de statut)
	$result = sql_select("lien.id_evenement AS id",
		    "spip_cinotif_evenements AS lien LEFT JOIN spip_cinotif_abonnements AS abo
		          ON lien.id_evenement=abo.id_evenement",
			"abo.id_evenement IS NULL AND lien.statut=''");

	// on les supprime
	while ($row = sql_fetch($result))
		sql_delete("spip_cinotif_evenements", "id_evenement=".$row['id']);
}

function cinotif_suppr_abonnes_sans_abonnement() {

	// les abonnes sans abonnements
	$result = sql_select("lien.id_abonne AS id",
		    "spip_cinotif_abonnes AS lien LEFT JOIN spip_cinotif_abonnements AS abo
		          ON lien.id_abonne=abo.id_abonne",
			"abo.id_abonne IS NULL");

	// on les supprime
	while ($row = sql_fetch($result))
		sql_delete("spip_cinotif_abonnes", "id_abonne=".$row['id']);
}

function cinotif_tableau_multisite_rubrique($id_rubrique) {
	$tableau_multisite = array();

	if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF')){
		if ($id_rubrique = intval($id_rubrique)) {
			$result = sql_select('multisite', 'spip_cims_multisites', 'id_rubrique='.$id_rubrique);
			while ($row = sql_fetch($result))
				$tableau_multisite[] = $row['multisite'];		
		}
	}

	return $tableau_multisite;
}

function cinotif_id_abonne($id_auteur, $email, $mailcompatible=true) {
	$id_abonne = 0;

	if ($id_auteur = intval($id_auteur)) {
		$result = sql_select('id_abonne', 'spip_cinotif_abonnes', "id_auteur=".$id_auteur);
		while ($row = sql_fetch($result))
			$id_abonne = $row['id_abonne'];
	}
	
	if (!$id_abonne) {
		if ($email AND email_valide($email)){
			$email = strtolower($email);
			$hash_email = md5($email);
			$result = sql_select('id_abonne', 'spip_cinotif_abonnes', "hash_email=".sql_quote($hash_email));
			while ($row = sql_fetch($result))
				$id_abonne = $row['id_abonne'];
				
			if (!$id_abonne AND $mailcompatible) {
				$emails = cinotif_mailcompatible($email);
				$emails = array_diff($emails,array($email));
				$hash_emails = array_map('md5',$emails);
				$in = sql_in('hash_email',$hash_emails);
				$result = sql_select('*', 'spip_cinotif_abonnes', $in);
				while ($row = sql_fetch($result))
					$id_abonne = $row['id_abonne'];
						
			}
		}
	}	

	return $id_abonne;
}

function cinotif_id_abonnes($tableau_id_auteur, $tableau_email, $mailcompatible=true) {
	$tableau_id_abonne = array();

	if ($tableau_id_auteur AND is_array($tableau_id_auteur)) {
		$in = sql_in('id_auteur',$tableau_id_auteur);
		$result = sql_select('id_abonne', 'spip_cinotif_abonnes', $in);
		while ($row = sql_fetch($result))
			$tableau_id_abonne[] = $row['id_abonne'];
	}
	
	if ($tableau_email AND is_array($tableau_email)) {
		foreach ($tableau_email AS $email){
			if ($email AND email_valide($email)){
				$email = strtolower($email);
				$hash_email = md5($email);
				$result = sql_select('id_abonne', 'spip_cinotif_abonnes', "hash_email=".sql_quote($hash_email));
				while ($row = sql_fetch($result))
					$tableau_id_abonne[] = $row['id_abonne'];
					
				if (!$tableau_id_abonne AND $mailcompatible) {
					$emails = cinotif_mailcompatible($email);
					$emails = array_diff($emails,array($email));
					$hash_emails = array_map('md5',$emails);
					$in = sql_in('hash_email',$hash_emails);
					$result = sql_select('*', 'spip_cinotif_abonnes', $in);
					while ($row = sql_fetch($result))
						$tableau_id_abonne[] = $row['id_abonne'];
							
				}
			}
		}
	}	

	return $tableau_id_abonne;	
}

function cinotif_mailcompatible($email) {
	$tableau = array();
	$tableau[] = $email;
	
	// compatibilite avec le plugin CICAS
	// compatibilité avec les anciennes adresses email	
	if (defined('_DIR_PLUGIN_CICAS')){
		$f = _DIR_RACINE . _NOM_PERMANENTS_INACCESSIBLES . '_config_cas.php';

		if (@file_exists($f)) {
			// parametrage par fichier
			include_once($f);

			$ci_pos = strpos($email, '@');
			if ($ci_pos AND $ci_pos > 0) {
				$ci_tableau_email = explode('@',$email);
				$ci_nom_mail = strtolower($ci_tableau_email[0]);
				$ci_domaine_mail = strtolower($ci_tableau_email[1]);	
				
				// compatibilite par defaut
				$cicasmailcompatible = array('equipement.gouv.fr' => 'developpement-durable.gouv.fr');
				
				// compatibilite figurant dans le fichier de parametrage config/_config_cas.php
				if (isset($GLOBALS['ciconfig']['cicasmailcompatible'])) {
					if (is_array($GLOBALS['ciconfig']['cicasmailcompatible'])) {
						$cicasmailcompatible = $GLOBALS['ciconfig']['cicasmailcompatible'];
					}
				}
				
				foreach ($cicasmailcompatible as $cle=>$valeur) {
					if ($ci_domaine_mail==$valeur)
						$tableau[] = $ci_nom_mail.'@'.$cle;
					elseif ($ci_domaine_mail==$cle)
						$tableau[] = $ci_nom_mail.'@'.$valeur;
				}
			}
		}
	}
	
	return $tableau;
}

// rubriques multirubricage pour cet article
function cinotif_multirubricage($id_article) {

	$id_article = intval($id_article);
	$rubriques = array();

	if (defined('_DIR_PLUGIN_CISQUEL')) {
		if ($id_article) {
			// mots du multirubricage
			$id_mots = array();
			$result = sql_select("id_mot", "spip_mots", "type='_multirubricage'");
			while ($row = sql_fetch($result))
				$id_mots[] = $row['id_mot'];
	
			if ($id_mots) {
				// mots multirubricage de cet article
				$id_mots_article = array();
				$in_mots = sql_in('id_mot',$id_mots); 
				if (spip_version()>=3)
					$result = sql_select("id_mot", "spip_mots_liens", "objet='article' AND id_objet=".$id_article." AND ".$in_mots,"","id_mot");
				else
					$result = sql_select("id_mot", "spip_mots_articles", "id_article=".$id_article." AND ".$in_mots,"","id_mot");
				
				while ($row = sql_fetch($result))
					$id_mots_article[] = $row['id_mot'];
						
				// rubriques avec un des mots multirubricage de cet article
				if ($id_mots_article){
					$rubriques_multi = array();
					$in_mot = sql_in('id_mot',$id_mots_article); 
					if (spip_version()>=3)
						$result = sql_select("id_objet as id_rubrique", "spip_mots_liens", "objet='rubrique' AND ".$in_mot,"","id_mot");
					else
						$result = sql_select("id_rubrique", "spip_mots_rubriques", $in_mot,"","id_mot");
					
					while ($row = sql_fetch($result))
						$rubriques_multi[] = $row['id_rubrique'];
		
					// la rubrique doit avoir l'une des formes multirubricage
					if ($rubriques_multi) {	
						$in_rub = sql_in('id_rubrique',$rubriques_multi); 
						$in_forme = sql_in('ciforme',array('_multirubricage',
										'_multirubricagetrirubrique',
										'_2colonnes',
										'_3colonnes',
										'_avecactualite',
										'_calendrier',
										'_espacededie',
										'_etablissements',
										'_etablissements2',
										'_tableau'));
			
						$result = sql_select("id_rubrique", "spip_rubriques", $in_rub." AND ".$in_forme,"","id_rubrique");
						while ($row = sql_fetch($result))
							$rubriques[] = $row['id_rubrique'];
							
					}
				}
			}
		}
	}
	
	return $rubriques;
}

function cinotif_icone_verticale($lien, $texte, $fond, $fonction="", $class="", $javascript=""){
	if (spip_version()>=3)
		return icone_base($lien,$texte,$fond,$fonction,"verticale $class",$javascript);
	else
		return '';
}

function cinotif_xtor($var=''){
	$return = '';

	// si abonnement xiti
	if (defined('_DIR_PLUGIN_CIPARAM')) {
		include_spip('ciparam_fonctions');
		if (function_exists('ciparam_xtor_email'))
			$return = ciparam_xtor_email();
	}

	return $return;
}

function cinotif_intraextra(){
	$return = false;
	$intra = false;
	$ci_dom_intra = array('.i2','.agri');
	$ci_dom_inter = array('.fr','.net');

	// domaines definis dans la constante _CI_DOMAINES_INTRA
	if (defined('_CI_DOMAINES_INTRA'))
		$ci_dom_intra = explode(',',_CI_DOMAINES_INTRA);

	// domaines definis dans la constante _CI_DOMAINES_INTER
	if (defined('_CI_DOMAINES_INTER'))
		$ci_dom_inter = explode(',',_CI_DOMAINES_INTER);

	if ($ci_host_site = cinotif_site_host()){
				
		// le site est-il un intranet ?
		foreach ($ci_dom_intra AS $terminaison){
			$terminaison = trim($terminaison);
			if (substr($ci_host_site,-strlen($terminaison))==$terminaison){
				$intra = true;
				break;
			}
		}

		if ($intra){
			if ($ci_host = cinotif_url_host()){
				// si l'URL de la page n'est pas intranet
				foreach ($ci_dom_inter AS $terminaison){
					$terminaison = trim($terminaison);
					if (substr($ci_host,-strlen($terminaison))==$terminaison){
						$return = true;
						break;
					}
				}		
			}
		}
	}

	return $return;
}

/**
 * Determiner le HOST de l'url en cours
 */
function cinotif_url_host() {
	
	$ci_host = "";

	// ordre de recherche par défaut (celui de phpCAS)
	$cihostordre = array('HTTP_X_FORWARDED_SERVER','SERVER_NAME','HTTP_HOST');
		
	// ordre de recherche defini dans la constante _CI_HOST_ORDRE
	if (defined('_CI_HOST_ORDRE') AND is_array(_CI_HOST_ORDRE))
		$cihostordre = _CI_HOST_ORDRE;

	foreach ($cihostordre as $valeur) {
		if (isset($_SERVER[$valeur])) {
			if ($_SERVER[$valeur]) {
				$ci_host = strtolower($_SERVER[$valeur]);
				break;
			}
		}
	}

	return $ci_host;
}

/**
 * Determiner le HOST du site
 */
function cinotif_site_host() {
	$ci_url_site = $GLOBALS['meta']['adresse_site'];

	if ($ci_url_site){
		$ci_url_site = str_replace(array('http://','https://'),'',$ci_url_site);
		$pos = strpos($ci_url_site,'/');
		if 	($pos!==false)
			$ci_url_site = substr($ci_url_site,0,$pos);
	}
	
	return $ci_url_site; 
}

/**
 * Existe-t-il des abonnements pris sur une adresse
 * autre que celle de la configuration de SPIP ?
 */
function cinotif_existe_abo_intraextra(){
	return sql_countsel('spip_cinotif_evenements', "url_multisite<>''");
}


/**
 * Formulaire par theme ou simple
 */
function cinotif_form_theme_ou_simple(){
	$return = false;
	
	$cinotif_meta = cinotif_tableau_meta();
	if (isset($cinotif_meta['typeabo']) AND in_array($cinotif_meta['typeabo'],array('theme','simple'))){
		$return = true;		
	}

	return $return;
}

/**
 * Formulaire simple
 */
function cinotif_form_simple(){
	$return = false;
	
	$cinotif_meta = cinotif_tableau_meta();
	if (isset($cinotif_meta['typeabo']) AND $cinotif_meta['typeabo']=='simple'){
		$return = true;	
	}

	return $return;
}


/**
 * L'interface avec SYMPA est-elle active ?
 */
function cinotif_sympa_actif(){
	$return = false;

	$cinotif_meta = cinotif_tableau_meta();
	if (isset($cinotif_meta['sympa_actif'])){
		if ($cinotif_meta['sympa_actif']=='oui')
			$return = true;		
	}

	// priorite de la constante
	if (defined('_CINOTIF_SYMPA')){
		if (_CINOTIF_SYMPA=='oui'){
			$return = true;
		} elseif (_CINOTIF_SYMPA=='non'){
			$return = false;
		}
	}

	return $return;
}

/**
 * Les notifications via SYMPA sont-elles actives ?
 */
function cinotif_type_notification_actif(){
	$return = 'abonnes';

	if (cinotif_sympa_actif()){
		$cinotif_meta = cinotif_tableau_meta();
		if (cinotif_form_theme_ou_simple()) {
			if (isset($cinotif_meta['typenotif'])){
				$return = $cinotif_meta['typenotif'];
			}
		}
	}
	return $return;
}


/**
 * Abonner a la liste de diffusion SYMPA
 */
function cinotif_sympa_abonner($email,$id_evenement=''){
	static $deja_fait = array();
	
	if (!cinotif_sympa_actif())	
		return true;
	
	$cinotif_meta = cinotif_tableau_meta();
	if (isset($cinotif_meta['abo_sympa']) AND $cinotif_meta['abo_sympa']=='oui') {
		list($adresse_liste_sympa,$adresse_sympa,$adresse_proprio_liste_sympa) = cinotif_sympa_adresses($id_evenement);

		if ($email AND $adresse_liste_sympa AND $adresse_sympa AND $adresse_proprio_liste_sympa) {
			// eviter les envois en double
			if (isset($deja_fait[$email]) AND $deja_fait[$email]==$adresse_liste_sympa){
				return true;
			}
			
			$cinotif_sympa_gerer_abonne = charger_fonction('cinotif_sympa_gerer_abonne', 'inc');
			$cinotif_sympa_gerer_abonne('abonner',$email,$adresse_liste_sympa,$adresse_sympa,$adresse_proprio_liste_sympa);
			$deja_fait[$email] = $adresse_liste_sympa;
		}
	}
	
	return true;
}

/**
 * Supprimer de la liste de diffusion SYMPA
 */
function cinotif_sympa_desabonner($email,$id_evenement=''){
	static $deja_fait = array();

	if (!cinotif_sympa_actif())	
		return true;
	
	$cinotif_meta = cinotif_tableau_meta();
	if (isset($cinotif_meta['abo_sympa']) AND $cinotif_meta['abo_sympa']=='oui') {
		list($adresse_liste_sympa,$adresse_sympa,$adresse_proprio_liste_sympa) = cinotif_sympa_adresses($id_evenement);

		if ($email AND $adresse_liste_sympa AND $adresse_sympa AND $adresse_proprio_liste_sympa) {
			// eviter les envois en double
			if (isset($deja_fait[$email]) AND $deja_fait[$email]==$adresse_liste_sympa){
				return true;
			}

			$cinotif_sympa_gerer_abonne = charger_fonction('cinotif_sympa_gerer_abonne', 'inc');
			$cinotif_sympa_gerer_abonne('desabonner',$email,$adresse_liste_sympa,$adresse_sympa,$adresse_proprio_liste_sympa);
			$deja_fait[$email] = $adresse_liste_sympa;
		}
	}

	return true;
}

/**
 * Les différentes adresses qui concernent SYMPA
 */
function cinotif_sympa_adresses($id_evenement=''){
	$adresse_liste_sympa = '';
	$adresse_sympa = '';
	$adresse_proprio_liste_sympa = '';
	
	// si abonnement par theme ou simple
	if (cinotif_form_theme_ou_simple()) {
		if ($id_evenement = intval($id_evenement)){
			$row = sql_fetsel("*", "spip_cinotif_evenements", "id_evenement=$id_evenement");
			if ($row) {
				$adresse_liste_sympa = $row['adresse_liste_diffusion'];
			}
		}
	}

	$cinotif_meta = cinotif_tableau_meta();

	if (isset($cinotif_meta['adresse_sympa']))
		$adresse_sympa = $cinotif_meta['adresse_sympa'];

	if (isset($cinotif_meta['adresse_proprio_liste_sympa']))
		$adresse_proprio_liste_sympa = $cinotif_meta['adresse_proprio_liste_sympa'];

	return array($adresse_liste_sympa,$adresse_sympa,$adresse_proprio_liste_sympa);	
}

/**
 * Notification automatique via SYMPA
 */
function cinotif_sympa_notifier($notification,$evenements) {

	if (!cinotif_sympa_actif())	
		return true;	
	
	$quoi = $notification['quoi'];
	$objet = $notification['objet'];
	$id_objet = $notification['id_objet'];
	$id_version = $notification['id_version'];
	$parent = $notification['parent'];
	$id_parent = $notification['id_parent'];
	$texte = $notification['texte'];
	$sujet = $notification['sujet'];
	
	$destinataires = array();
	$cinotif_meta = cinotif_tableau_meta();
	
	// rien a faire si pas de texte
	if (!strlen($texte))
		return;
		
	// le courrier existe-t-il deja (eviter la redondance d'envoi) ?
	$where_courrier = "quoi=".sql_quote($quoi)." AND objet=".sql_quote($objet)." AND id_objet=".$id_objet;
	if (intval($id_version)>0) {
		$where_complement = " AND id_version=".intval($id_version);
		if (defined('_CINOTIF_TOUTES_MODIF')) {
			if (_CINOTIF_TOUTES_MODIF=='oui')
				$where_complement = "";
		}
		$where_courrier .= $where_complement;
	}
	if (($nb=sql_countsel('spip_cinotif_courriers', $where_courrier))>0)
		return;


	// quelle est la rubrique parente ?
	$id_rubrique = 0;
	if ($parent=='rubrique')
		$id_rubrique = $id_parent;
	elseif ($parent=='article') {
		$row = sql_fetsel("id_rubrique", "spip_articles", "id_article=".$id_parent);
		if ($row)
			$id_rubrique = $row['id_rubrique'];
	}

	// si le plugin CIAR est actif
	if (defined('_DIR_PLUGIN_CIAR')){
		if ($id_rubrique){
			include_spip('ciar_fonctions');
			// en cas d'EC, ne pas envoyer
			if (ciar_rub_ec($id_rubrique))
					return;
			// en cas d'acces restreint simple, ne pas envoyer
			elseif (ciar_rub_ar($id_rubrique))
					return;
		} else {
			// securite
			return;
		}
	}

	// si c'est un article non publie
	if ($objet=='article' AND $id_objet){
		$row = sql_fetsel("statut", "spip_articles", "id_article=".intval($id_objet));
		if ($row){
			if ($row['statut']!='publie')
				return;
		}
	}
	
	// si c'est un commentaire d'un article non publie
	if ($parent=='article' AND $id_parent) {
		$row = sql_fetsel("statut", "spip_articles", "id_article=".intval($id_parent));
		if ($row){
			if ($row['statut']!='publie')
				return;
		}
	}

	// si le sujet est vide, extraire la premiere ligne du corps
	// Le texte d'un commentaire dans SPIP est au plus de 20 000 caracteres
	// on peut donc le stocker dans un champ TEXT
	if (!strlen($sujet)){
		// nettoyer un peu les retours chariots
		$texte = str_replace("\r\n", "\r", $texte);
		$texte = str_replace("\r", "\n", $texte);
		// decouper
		$texte = explode("\n",trim($texte));
		// extraire la premiere ligne
		$sujet = array_shift($texte);
		$texte = trim(implode("\n",$texte));
	}
	
	
	// stocker, le courrier a envoyer, dans la table spip_cinotif_courriers
	$nb_emails = 0;
	$ladate = date('Y-m-d H:i:s');
	$ci_url_site = strtolower($GLOBALS['meta']['adresse_site']);
	$champs = array(
			'quoi'=>$quoi,
			'objet'=> $objet,
			'id_objet'=> $id_objet,
			'id_version'=> $id_version,
			'parent'=> $parent,
			'id_parent'=> $id_parent,
			'sujet' => $sujet,
			'texte'	=> $texte,
			'url_site'	=> $ci_url_site,
			'date' => $ladate,
			'statut' => 'prop',
			'destinataires' => '',
			'nb_emails' => $nb_emails,
			'date_debut_envoi' => $ladate);
	$id_courrier = sql_insertq('spip_cinotif_courriers', $champs);			

	// Ajouter un lien pour se desabonner (avec l'adresse du site public)
	$msg_desabo = filtrer_entites("\n\n" ._T('cinotif:tirets') ."\n" ._T('cinotif:desa_mail_courrier'));
	if (defined('_DIR_PLUGIN_CISQUEL'))
		$msg_desabo .= "\n" .$ci_url_site."/spip.php?page=abonnement-site&desabonner=oui";
	else
		$msg_desabo .= "\n" .$ci_url_site."/spip.php?page=cinotif&desabonner=oui";

	sql_updateq("spip_cinotif_courriers", array('texte'=>$texte.$msg_desabo), "id_courrier=".$id_courrier);
	$texte = $texte.$msg_desabo;
	
	if ($id_courrier) {

		// envoyer un paquet de mails pour ce courrier 			
		$nb_envois = 0;

		// compatibilite avec le plugin CIMS
		// compatibilite avec intraextra
		if ( ( defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF') ) OR cinotif_existe_abo_intraextra()) {
			$ci_url_site = str_replace(array('http://','https://'),'',$ci_url_site);
			$pos = strpos($ci_url_site,'/');
			if 	($pos!==false)
				$ci_url_site = substr($ci_url_site,0,$pos);
		}

		$ok = true;
		if (defined('_CINOTIF_DESACTIVER_ENVOIS')) {
			if (_CINOTIF_DESACTIVER_ENVOIS=='oui')
				$ok = false;
		}
		
		
		foreach ($evenements AS $id_evenement){

			$email = '';
			$url_multisite = '';

			// si abonnement par theme ou simple
			if (cinotif_form_theme_ou_simple()){
				if ($id_evenement = intval($id_evenement)){
					$row = sql_fetsel("*", "spip_cinotif_evenements", "id_evenement=".$id_evenement." AND statut='publie'");
					if ($row) {
						$email = $row['adresse_liste_diffusion'];
						$url_multisite = $row['url_multisite'];
					}
				}
			}

			// compatibilite avec le plugin CIMS : mettre l'URL du site en cours lors de l'abonnement a l'evenement.
			if ($ci_url_site AND $url_multisite){
				$url_multisite = str_replace(array('http://','https://'),'',$url_multisite);
				$pos = strpos($url_multisite,'/');
				if 	($pos!==false)
					$url_multisite = substr($url_multisite,0,$pos);
	
				$texte = str_replace($ci_url_site,$url_multisite,$texte);
			}

			// envoi
			if ($email){
				if ($ok){
					if (isset($cinotif_meta['adresse_sympa']))
						$adresse_sympa = $cinotif_meta['adresse_sympa'];
				
					if (isset($cinotif_meta['adresse_proprio_liste_sympa']))
						$adresse_proprio_liste_sympa = $cinotif_meta['adresse_proprio_liste_sympa'];
	
					$destinataires[] = $email;
						
					$cinotif_sympa_notifier = charger_fonction('cinotif_sympa_notifier', 'inc');
					$cinotif_sympa_notifier($email,$sujet,$texte,$adresse_proprio_liste_sympa);
				}

				$nb_envois++;
			}
		}

		
		// Suivant le parametrage, ecrire a celui qui declenche l'evenement
		$id_moi = cinotif_id_abonne($GLOBALS['visiteur_session']['id_auteur'],'');
		if ($destinataires AND isset($cinotif_meta['auto_notif']) AND $cinotif_meta['auto_notif']=='oui'){
			$in_evenements = sql_in('id_evenement',$evenements); 
			if (!$id_moi OR !sql_countsel('spip_cinotif_abonnements', "id_abonne=".$id_moi." AND ".$in_evenements)){
				if (isset($GLOBALS['visiteur_session']['email']) AND $GLOBALS['visiteur_session']['email']){
					$envoyer_mail = charger_fonction('envoyer_mail','inc');
					$envoyer_mail($GLOBALS['visiteur_session']['email'], $sujet, $texte);
				}
			}
		}

		// memoriser le cumul des envois pour ce courrier
		// s'il n'en reste plus a faire, changer le statut du courrier et memoriser la date de fin des envois 
		$nb_emails_envoyes = intval($nb_emails_deja_envoyes) + $nb_envois;
		$tableau_update = array();
		$tableau_update['nb_emails_envoyes'] = $nb_emails_envoyes;
		$tableau_update['nb_emails'] = $nb_emails_envoyes;
		$tableau_update['date_fin_envoi'] = date('Y-m-d H:i:s');
		if ($nb_emails_envoyes>=$nb_emails)
			$tableau_update['statut'] = 'publie';

		$tableau_update['destinataires'] = implode(',',$destinataires);
			
		sql_updateq("spip_cinotif_courriers", $tableau_update, "id_courrier=".$id_courrier);		
	}
	
	return true;
}

?>