<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');
include_spip('inc/texte');
include_spip('base/abstract_sql');
include_spip('inc/cinotif_commun');


function formulaires_cinotif_abonnement_charger_dist($objet='',$id_objet=0,$desabonner='',$quoi=array(),$a='',$e='',$i='',$j='',$lien_fermer='') {
	
	if (isset($GLOBALS['meta']['cinotif_formulaire']) AND $GLOBALS['meta']['cinotif_formulaire']!='defaut')
		return false;
	
	
	list($objet, $id_objet, $desabonner, $quoi, $action, $action_id_abonne, $id_courrier, $jeton, $lien_fermer) = cinotif_secure($objet, $id_objet, $desabonner, $quoi, $a, $e, $i, $j, $lien_fermer);

	$valeurs['editable'] = false;
	$valeurs['ci_objet'] = $objet; 
	$valeurs['ci_id_objet'] = $id_objet; 
	$valeurs['ci_lien_fermer'] = $lien_fermer; 
	$valeurs['ci_desabonner'] = $desabonner; 
	$valeurs['ci_action'] = $action; 
	$valeurs['ci_abo_autres_objets'] = '';

	
	//	pour confirmer un abonnement via mail (action, action_id_abonne, jeton)
	if ($action=='c') {
		if (!$action_id_abonne OR !$jeton) {
			$valeurs['message_erreur'] = _T('cinotif:acces_interdit');
		} else {
			if (cinotif_confirmer_email($action_id_abonne, $jeton))
				$valeurs['message_ok'] = _T('cinotif:email_confirme');
			else
				$valeurs['message_erreur'] = _T('cinotif:email_pas_confirme');
		}
	}

	//	pour confirmer un desabonnement via mail (action, action_id_abonne, jeton)
	elseif ($action=='d') {
		if (!$action_id_abonne OR !$jeton) {
			$valeurs['message_erreur'] = _T('cinotif:acces_interdit');
		} else {
			if (cinotif_desabonner($action_id_abonne, $jeton))
				$valeurs['message_ok'] = _T('cinotif:desabonnement_enregistre');
			else
				$valeurs['message_erreur'] = _T('cinotif:desabonnement_pas_enregistre');
		}
	}

	//	pour s'abonner : objet, id, quoi (facultatif)
	elseif (!$action AND !$desabonner) {
		if (!$objet)
			return false;	
			
		$valeurs['editable'] = true;

		$lesquoi = cinotif_tableau_des_quoi($objet,$id_objet);		
		
		// le parametre est prioritaire
		if ($quoi)
			$lesquoi = array_intersect($quoi,$lesquoi);


		$valeurs['_les_quoi'] = implode(',',$lesquoi);
		$valeurs['_liste_choix'] = cinotif_lesquoi_checkbox($objet, $id_objet, $lesquoi);
		$valeurs['_hidden'] .= "<input type='hidden' name='_cle' value='".cinotif_creer_cle()."' />";

		if (strpos($valeurs['_liste_choix'],'(1)')>1)
			$valeurs['legende_publie'] = 'oui';
		else
			$valeurs['legende_publie'] = '';


		// Les abonnements sont-ils autorises ?
		$cinotif_meta = cinotif_tableau_meta();
		$valeurs['abo_tous'] = $cinotif_meta['abo_tous'];

		// Si les personnes non authentifiees ne sont pas autorisees a s'abonner
		// alors, si on n'est pas connecte, on se redirige vers l'authentification
		if ($cinotif_meta['abo_tous']=='oui' AND $cinotif_meta['abo_non_auth']=='non' AND !isset($GLOBALS['visiteur_session']['id_auteur'])){
			// syntaxe avec '&' sinon cela ne fonctionne pas ici
			include_spip('inc/headers');
			redirige_par_entete(generer_url_public('login').'&url='.rawurlencode(self()));
		}
		
		// auteur authentifie deja abonne a d'autres objets
		if (isset($GLOBALS['visiteur_session']['id_auteur'])) {
			if (cinotif_abo_autres_objets_existe($GLOBALS['visiteur_session']['id_auteur'],$objet))
				$valeurs['ci_abo_autres_objets'] = 'oui';
		}
	} 

	//	pour se desabonner
	elseif (!$action AND $desabonner) {
			$valeurs['editable'] = true;
			$valeurs['_liste_choix'] = '';
			$valeurs['_hidden'] .= "<input type='hidden' name='_cle' value='".cinotif_creer_cle()."' />";

			$id_auteur = cinotif_id_auteur();
			$tableau_objet = array();

			if ($id_auteur) {
				// desabonnement avec l'identifiant du courrier
				if ($id_courrier AND $action_id_abonne) {
					 $tableau_objet = cinotif_trouver_objet_courrier_et_destinataire($id_courrier, $id_auteur, $action_id_abonne);
					 
					// auteur authentifie deja abonne a d'autres objets
					if (isset($tableau_objet['objet'])){
						if (cinotif_abo_autres_objets_existe($id_auteur,$tableau_objet['objet']))
							$valeurs['ci_abo_autres_objets'] = 'oui';
					}
				} else {
					if ($objet=='site')
						$existant = cinotif_abonnements_id_auteur_existe($id_auteur, $GLOBALS['visiteur_session']['email']);
					else
						$existant = cinotif_abonnements_auteur($id_auteur, $GLOBALS['visiteur_session']['email'], $objet, $id_objet);

					if (!$existant)
						$valeurs['message_ok'] = _T('cinotif:pas_abonne');
						
					// auteur authentifie deja abonne a d'autres objets
					if (cinotif_abo_autres_objets_existe($id_auteur,$objet))
						$valeurs['ci_abo_autres_objets'] = 'oui';
						
				}
			} else {
				if ($id_courrier AND $action_id_abonne)
					 $tableau_objet = cinotif_trouver_objet_courrier_et_destinataire($id_courrier, $id_auteur, $action_id_abonne);
			}
			
			if ($id_courrier) {
				if (defined('_DIR_PLUGIN_CISQUEL')){
					if (!$tableau_objet)
						$tableau_objet = cinotif_trouver_objet_courrier_et_destinataire($id_courrier, $id_auteur, $action_id_abonne);

					// syntaxe avec '&' sinon cela ne fonctionne pas ici
					$ciredirect = generer_url_public('abonnement-'.$tableau_objet['objet']).'&desabonner=oui';
					if ($tableau_objet['id_objet'])
						$ciredirect .= '&id_'.$tableau_objet['objet'].'='.$tableau_objet['id_objet'];
					include_spip('inc/headers');
				    redirige_par_entete($ciredirect);
				} else {
					$valeurs['ci_id_courrier'] = $id_courrier;
					$valeurs['ci_action_id_abonne'] = $action_id_abonne;
					$valeurs['ci_desabo_courrier_objet'] = $tableau_objet['objet'];
					$valeurs['ci_objet'] = $tableau_objet['objet'];
					$valeurs['ci_id_objet'] = $tableau_objet['id_objet'];
					if (!$valeurs['ci_objet'])
						$valeurs['message_ok'] = _T('cinotif:desabonnement_enregistre');
				}
			}
	}

	return $valeurs;
}


function formulaires_cinotif_abonnement_verifier_dist($objet='',$id_objet=0,$desabonner='',$quoi=array(),$a='',$e='',$i='',$j='',$lien_fermer='') {

	list($objet, $id_objet, $desabonner, $quoi, $action, $action_id_abonne, $id_courrier, $jeton, $lien_fermer) = cinotif_secure($objet, $id_objet, $desabonner, $quoi, $a, $e, $i, $j, $lien_fermer);
	
	$erreurs = array();
	$lesquoi = array();
	$abo_auth_ok = false;
	$abo_ok = false;
	$id_auteur = cinotif_id_auteur();
	
	
	// securite antispam
	if (strlen(_request('nobot')))
		$erreurs['message_erreur'] =  _T('cinotif:deja_abonne');
			
	if (!cinotif_verifier_cle(_request('_cle')))
		$erreurs['message_erreur'] =  _T('cinotif:echec');
	
	// eviter attaques en saturation
	// maximum de 3 x 6 demandes d'abonnements (non authentifie) en 60 secondes
	$mydate = date("YmdHis", time() - 60);
	if (($nb=sql_countsel("spip_cinotif_abonnements","statut='prop' AND maj>".$mydate))>18)
		$erreurs['message_erreur'] =  _T('cinotif:plustard');
		
	// supprimer les abonnements non confirmes, vieux de plus de 24 heures
	$mydate2 = date("YmdHis", time() - (24 * 3600));
	sql_delete("spip_cinotif_abonnements", "statut='prop' AND maj<".$mydate2);
	cinotif_suppr_evenements_sans_abonnement();

	// Les abonnements sont-ils autorises ?
	$cinotif_meta = cinotif_tableau_meta();
	if (!$desabonner AND $cinotif_meta['abo_tous']=='non')	
		$erreurs['message_erreur'] =  _T('cinotif:msg_abo_tous');
		
	// traitement des erreurs
	if (!isset($erreurs['message_erreur'])) {	
		if ($quoi) {
			// le parametre est prioritaire sur le formulaire
			$lesquoi = $quoi;
		} else {
			$leschoix = _request('lesquoi');
			if (isset($leschoix) AND is_array($leschoix)) {
				foreach ($leschoix as $choix) {
					if (preg_match(',^[a-z]*$,',$choix))
						$lesquoi[] = $choix;
				}
			}
		}
		
		if (!$action AND !$desabonner AND !$lesquoi AND !$id_auteur) {
			$erreurs['message_erreur'] =  _T('cinotif:aucun_abonnement_selectionne');

		} else {
			$email = _request('session_email');	
			// l'email en base est est prioritaire sur le formulaire
			if ($auteur_email = cinotif_auteur_email())
				$email = $auteur_email;
			
			if (($email) == _T('info_mail_fournisseur'))
				$erreurs['adresse_email'] = _T('form_indiquer_email');
		
			// email invalide	
			elseif ($email AND !email_valide($email)) 
				$erreurs['adresse_email'] = _T('form_email_non_valide');
		
			elseif ($email) {
				if ($id_auteur) {
					$abo_ok = true;
				} else {
					if ($desabonner)
						$abo_ok = true;
					else {
						foreach ($lesquoi AS $quoi) {
							// le ou les abonnements existent-t-il pour cet email ?
							if (!cinotif_abonnement_existe($email, $quoi, $objet, $id_objet)){
								$abo_ok = true;
							}
						}
					}
				}
				if (!$abo_ok) {
					// Cas auteur authentifie avec un email dans la table auteurs
					if ($id_auteur AND cinotif_auteur_email())
						$erreurs['message_erreur'] =  _T('cinotif:deja_abonne');
					else 
						$erreurs['message_erreur'] =  _T('cinotif:deja_abonne_ou_attente_confirmation');
					
				}
			} else {
				$erreurs['session_email'] = _T('info_obligatoire');
			}
		}
	}

	return $erreurs;
}


function formulaires_cinotif_abonnement_traiter_dist($objet='',$id_objet=0,$desabonner='',$quoi=array(),$a='',$e='',$i='',$j='',$lien_fermer='') {
	
	list($objet, $id_objet, $desabonner, $quoi, $action, $action_id_abonne, $id_courrier, $jeton, $lien_fermer) = cinotif_secure($objet, $id_objet, $desabonner, $quoi, $a, $e, $i, $j, $lien_fermer);
	
	$abo_ok = false;
	$message = '';
	$lesquoi = array();
	$id_auteur = cinotif_id_auteur();
	$suppr_only = false;
	$diff_existant = false;

	if ($quoi) {
		// le parametre est prioritaire sur le formulaire
		$lesquoi = $quoi;
	} else {
		$leschoix = _request('lesquoi');
		if (isset($leschoix) AND is_array($leschoix)) {
			foreach ($leschoix as $choix) {
				if (preg_match(',^[a-z]*$,',$choix))
					$lesquoi[] = $choix;
			}
		}
	}

	// securite
	if ($lesquoi) {
		$lesquoi_autorise =	cinotif_tableau_des_quoi($objet,$id_objet);
		if (is_array($lesquoi)) {
			$lesquoi = array_intersect($lesquoi, $lesquoi_autorise);
		} else {
			if (!in_array($lesquoi,$lesquoi_autorise))
				$lesquoi = '';
		}
	}

	$req_email = _request('session_email');
	
	if ($req_email AND email_valide($req_email))
		$email = $req_email;
	else
		$email = '';

	// l'email en base est est prioritaire sur le formulaire
	if ($auteur_email = cinotif_auteur_email())
		$email = $auteur_email;
		
	// supprimer le cas echeant, si auteur authentifie
	if ($email AND $objet AND $id_auteur AND !$desabonner) {
		$existant = cinotif_abonnements_auteur($id_auteur, $email, $objet, $id_objet);
		if ($existant) {
			if ($lesquoi){
				$diffs = array_diff($existant,$lesquoi);
				$lesquoi = array_diff($lesquoi,$existant);
			} else {
				$diffs = $existant;
				$diff_existant = true;
			}

			foreach ($diffs AS $diff) {
				if (cinotif_config_quoi_objet($diff, $objet)){
					cinotif_abonnement_supprimer($id_auteur, $diff, $objet, $id_objet);
					$suppr_only = true;
				}
			}

			if ($suppr_only){
				if ($diff_existant)
					$message = _T('cinotif:desabonnement_enregistre');
				else
					$message = _T('cinotif:abonnement_enregistre');
			}
		}
	}

	if ($desabonner AND $email AND ($objet OR $id_courrier)) {
		$desabo_objet = $objet;
		$desabo_id_objet = $id_objet;
		
		// desabonnement avec l'identifiant du courrier
		if ($id_courrier>0) {
			$desabo_id_abonne = cinotif_id_abonne('', $email);
			$tableau_objet = cinotif_trouver_objet_courrier_et_destinataire($id_courrier, $id_auteur, $desabo_id_abonne);
			$desabo_objet = $tableau_objet['objet'];
			$desabo_id_objet = $tableau_objet['id_objet'];			 
		}

		if ($id_auteur>0) {
			// desabonnement d'un auteur authentifie
			if (cinotif_desabonner_auteur($desabo_objet, $desabo_id_objet))
				$message = _T('cinotif:desabonnement_enregistre');
		} else {
			// desabonnement d'un auteur non authentifie
	
			// calcul du jeton 
			$jeton = cinotif_creer_jeton();
	
			// enregistrer avec un jeton
			$abo_ok = cinotif_desabonnement_enregistrer($email, $desabo_objet, $desabo_id_objet, $jeton);
			
			// envoi d'un mail avec un lien de confirmation
			if ($abo_ok)
				$message = cinotif_envoi_mail_desabonnement($jeton);	
			else
				$message = "echec";
		}
	}


	// maximum d'abonnes
	$cinotif_nb_max_abonnes = cinotif_nb_max_abonnes();
	if ($nb=sql_countsel("spip_cinotif_abonnes")>=$cinotif_nb_max_abonnes) {
		if (!($desabonner AND $email AND ($objet OR $id_courrier)))
			$message .= _T('cinotif:max_abonnes');
	
	// enregistrer
	} elseif ($email AND $objet AND $lesquoi) {
		$abos = array();

		// Cas auteur authentifie avec un email dans la table auteurs
		if ($id_auteur AND cinotif_auteur_email()) {	
			// enregistrer
			foreach ($lesquoi AS $quoi) {
				$abos[] = cinotif_abonnement_enregistrer($email, $quoi, $objet, $id_objet, '');
				$suppr_only = false;
			}
	
			$abos = array_unique($abos);

			if (in_array('ok',$abos))
				$message = _T('cinotif:abonnement_enregistre');
			
			if (!in_array('ok',$abos))
				foreach ($abos AS $abo)
					$message .= _T('cinotif:'.$abo)." ";

					
		// Cas non authentifie
		} else {
			// calcul du jeton 
			$jeton = cinotif_creer_jeton();
	
			// enregistrer avec le statut prop et un jeton
			foreach ($lesquoi AS $quoi) {
				$abos[] = cinotif_abonnement_enregistrer($email, $quoi, $objet, $id_objet, $jeton);
				$suppr_only = false;
			}	
			
			$abos = array_unique($abos);
			
			// envoi d'un mail avec un lien de confirmation
			if (in_array('ok',$abos))
				$message = cinotif_envoi_mail_confirmation($jeton);	
			else
				foreach ($abos AS $abo)
					$message .= _T('cinotif:'.$abo)." ";
		}
	}

	$res['message_ok'] = $message;
	if (!$message AND $id_auteur AND (cinotif_auteur_email() OR $suppr_only) AND !$lien_fermer)
		$res['redirect'] = cinotif_retour($objet,$id_objet);

	return $res;
}


function cinotif_retour($objet='',$id_objet=0) {
	
	switch($objet) {
		case 'article':
			$return = generer_url_public("article", "id_article=$id_objet");
			break;
		case 'rubrique':
			$return = generer_url_public("rubrique", "id_rubrique=$id_objet");
			break;
		case 'site':
			$return = $GLOBALS['meta']['adresse_site'];
			// compatibilite avec intraextra
			if (cinotif_intraextra())
				$return = str_replace(cinotif_site_host(),cinotif_url_host(),$return);

			break;
	}
	
	return $return;
}


function cinotif_id_auteur() {
	$id_auteur = 0;

	if (isset($GLOBALS['visiteur_session']['id_auteur']) && $GLOBALS['visiteur_session']['id_auteur'])
		$id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
		
	return $id_auteur;
}


function cinotif_auteur_email() {
	$email = '';

	if (isset($GLOBALS['visiteur_session']['email'])) {
		if ($GLOBALS['visiteur_session']['email']) {
			if (email_valide($GLOBALS['visiteur_session']['email']))
				$email = $GLOBALS['visiteur_session']['email'];
		}
	}
		
	return $email;
}


function cinotif_tableau_des_quoi($objet,$id_objet=0) {
	$return = array();
	$tout = array();
	$cinotif_meta = cinotif_tableau_meta(true);
	$id_auteur = cinotif_id_auteur();

	// tenir compte de la configuration
	if (isset($cinotif_meta['abonnements_'.$objet]) AND is_array($cinotif_meta['abonnements_'.$objet]))
		$tout = $cinotif_meta['abonnements_'.$objet];

	switch($objet) {
		case 'article':
			$return = $tout;
			break;
		case 'rubrique':
			$return = array_diff($tout, array('articlepropose'));

			// les admin restreints doivent pouvoir s'abonner aux proposition d'articles que pour leurs rubriques
			// si le plugin CIAR est actif et que la rubrique est un EC, le statut peut etre surcharge.
			if ($id_auteur) {
				if (defined('_DIR_PLUGIN_CIAR')) {
					include_spip('ciar_fonctions');
					if (ciar_rub_ec($id_objet)) {
						if ($GLOBALS['visiteur_session']['statut']!='6forum')
							$return = $tout;
					} elseif ($GLOBALS['visiteur_session']['statut']=='0minirezo') {
						include_spip('inc/autoriser');
						$rub_auteur = liste_rubriques_auteur($id_auteur);
						if (!$rub_auteur OR in_array($id_objet, $rub_auteur))		
							$return = $tout;
					}					
				} elseif ($GLOBALS['visiteur_session']['statut']=='0minirezo') {
					include_spip('inc/autoriser');
					$rub_auteur = liste_rubriques_auteur($id_auteur);
					if (!$rub_auteur OR in_array($id_objet, $rub_auteur))		
						$return = $tout;
				}
			}
			
			if (defined('_DIR_PLUGIN_CISQUEL')){
				// Sous page d'accueil : raccourci dans les actualites
				// Rubrique : raccourci dans les actualites
				if (!sql_countsel("spip_rubriques", "id_rubrique=".$id_objet." AND ".sql_in('ciforme',array('_souspageaccueil','_avecactualite','_3colonnes'))))
					$return = array_diff($return, array('actupublie'));
			}
			
			break;
		case 'site':
			$return = array_diff($tout, array('articlepropose'));
			if ($id_auteur) {
				if ((defined('_DIR_PLUGIN_CIAR') AND $GLOBALS['visiteur_session']['statut']!='6forum') 
					OR (!defined('_DIR_PLUGIN_CIAR') AND $GLOBALS['visiteur_session']['statut']=='0minirezo'))
					$return = $tout;
			}
			break;
	}

	return $return;
}


function cinotif_lesquoi_checkbox($objet, $id_objet='', $lesquoi) {
    
	if ($lesquoi) {
		if (cinotif_id_auteur())
			$valeurs_actuelles = cinotif_abonnements_auteur($GLOBALS['visiteur_session']['id_auteur'], $GLOBALS['visiteur_session']['email'],$objet, $id_objet);
		else 
			$valeurs_actuelles = array();

	    foreach ($lesquoi as $quoi) {
	    	$nom = $quoi;
	    	$description = _T('cinotif:'.$quoi);
	    	if (!$valeurs_actuelles OR !in_array($nom, $valeurs_actuelles)) { 
	    		$texte .= "\n<div class='textechk'>";
				$texte .= '<input id="mot'.$nom.'" type="checkbox" value="'.$nom.'" name="lesquoi[]" />';
	    	} else {
	    		$texte .= "\n<div class='textechk gras'>";
				$texte .= '<input id="mot'.$nom.'" type="checkbox" checked="checked" value="'.$nom.'" name="lesquoi[]" />';
	    	}
			$texte .= '<label for="mot'.$nom.'">'.$description.'</label></div>';
	    }
	}
	
	return $texte;  
}	


function cinotif_abonnements_auteur($id_auteur, $email, $objet, $id_objet='') {
	$return = array();
	$quois = array();
	$evenements = array();

	$where = "objet=".sql_quote($objet);
	if ($id_objet)
		$where .= " AND id_objet=".$id_objet;
	
	// compatibilite avec le plugin CIMS
	// dans le cas d'un multisite, determiner le site en cours en fonction du host
	if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF'))
		$where .= "	AND multisite=".sql_quote(cims_site_en_cours());

	$result = sql_select('id_evenement,quoi', 'spip_cinotif_evenements', $where);
	while ($row = sql_fetch($result)) {
		$evenements[] = $row['id_evenement'];
		$quois[$row['id_evenement']] = $row['quoi'];
	}

	$id_abonne = cinotif_id_abonne($id_auteur, $email);
	
	if ($evenements AND $id_abonne) {
		$in_evenements = sql_in('id_evenement',$evenements); 
		$result = sql_select('*', 'spip_cinotif_abonnements', "id_abonne=".$id_abonne." AND ".$in_evenements);
		while ($row = sql_fetch($result))
			$return[] = $quois[$row['id_evenement']];
	}

	return $return;
}


function cinotif_abonnements_id_auteur_existe($id_auteur, $email) {
	$return = false;
	
	$id_abonne = cinotif_id_abonne($id_auteur, $email);
	$result = sql_select('*', 'spip_cinotif_abonnements', "id_abonne=".$id_abonne);
	while ($row = sql_fetch($result))
		$return = true;

	return $return;
}


function cinotif_evenement_enregistrer($quoi, $objet, $id_objet='') {
	$return = '';

	if ($quoi AND $objet) {
		
		$champs = array(
			'quoi' =>  $quoi,
			'objet' =>  $objet);

		if ($id_objet)
			$champs['id_objet'] = $id_objet;

		// compatibilite avec le plugin CIMS
		// dans le cas d'un multisite, determiner le site en cours en fonction du host
		if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF')) {
			$champs['multisite'] = cims_site_en_cours();
			$champs['url_multisite'] = cims_url_host();
		}

		// compatibilite avec intraextra
		if (cinotif_intraextra())		
			$champs['url_multisite'] = cinotif_url_host();

		// l'enregistrement existe-il deja ?
		if ($id_evenement = cinotif_id_evenement($quoi, $objet, $id_objet))
			$return = $id_evenement;
		else
			$return = sql_insertq('spip_cinotif_evenements', $champs);			
	}

	return $return;
}



function cinotif_abonnement_enregistrer($email, $quoi, $objet, $id_objet='', $jeton='') {
	$return = '';
	$ok = true;

	if ($email AND $quoi AND $objet) {

		$id_evenement = cinotif_evenement_enregistrer($quoi, $objet, $id_objet);
		
		$id_auteur = cinotif_id_auteur();
		
		if ($id_auteur AND $auteur_email = cinotif_auteur_email())
			// l'email de la table auteur est prioritaire
			$email = $auteur_email;
		
		$email = strtolower($email);	
		$hash_email = md5($email);	
		
		// l'abonne existe-il ?		
		$id_abonne = cinotif_id_abonne($id_auteur, $email);

		// creer l'abonne le cas echeant	
		if (!$id_abonne)
			$id_abonne = sql_insertq('spip_cinotif_abonnes', array('id_auteur'=>$id_auteur,'email'=>$email,'hash_email'=>$hash_email));			
		
		

		// l'abonnement existe-il deja ?
		if (cinotif_abonnement_existe($email, $quoi, $objet, $id_objet)) {
			$ok = false;
			$return = 'deja_abonne';
			// cas auteur authentifie et enregistrement existant sans id_auteur
			if ($id_abonne AND $id_auteur AND cinotif_auteur_email())
				sql_updateq("spip_cinotif_abonnes", array('id_auteur'=>$id_auteur,'email'=>$email,'hash_email'=>$hash_email), "id_abonne=".$id_abonne);
			else
				$return = 'deja_abonne_ou_attente_confirmation';		
		}
		
		// compatibilite avec le plugin CIAR
		elseif (!cinotif_ciar_autorise($objet, $id_objet)) {
			$ok = false;
			$return = 'acces_interdit';			
		}

		// un abonnement parent existe-il deja ?
		elseif ($message = cinotif_abonnement_parent_existe($email, $quoi, $objet, $id_objet)) {
			$ok = false;
			$return = $message;
		}

		// enregistrer le cas echeant	
		if ($ok) {
			$champs = array(
				'id_abonne' => $id_abonne,
				'id_evenement' => $id_evenement);
	
			// Cas authentifie avec un email dans la table auteurs	
			if ($id_auteur AND cinotif_auteur_email()) {
				$champs['statut'] = 'publie';
			} else {
				// enregistrer avec le statut prop
				$champs['statut'] = 'prop';
				$champs['jeton'] = $jeton;
			}
			
			sql_insertq('spip_cinotif_abonnements', $champs);			
			$return = 'ok';
		}
	}

	return $return;
}


function cinotif_desabonnement_enregistrer($email, $objet, $id_objet='', $jeton='') {
	$return = '';

	if ($email AND $objet) {
		// auteur non authentifie	
		$id_auteur = cinotif_id_auteur();
		if (!$id_auteur) {
			// compatibilite avec le plugin CIAR
			if (cinotif_ciar_autorise($objet, $id_objet)) {
				$id_abonne = cinotif_id_abonne($id_auteur, $email);
				if ($objet=='site') {
					$where = "id_abonne=".$id_abonne;
				} else {
					$tableau_id_evenement = cinotif_ids_evenement($objet, $id_objet='');				
					$in = sql_in('id_evenement',$tableau_id_evenement);
					$where = "id_abonne=".$id_abonne." AND ".$in;
				}
				$return = sql_updateq("spip_cinotif_abonnements", array('jeton' =>  $jeton), $where);
			}
		}
	}

	return $return;
}


function cinotif_ciar_autorise($objet, $id_objet='') {
	$return = true;
	
	// compatibilite avec le plugin CIAR
	if (defined('_DIR_PLUGIN_CIAR')){
		include_spip('ciar_fonctions');
		$rubriques_exclues = ciar_accessrubec();
		$id_objet = intval($id_objet);
		
		if ($rubriques_exclues) {			
			switch($objet) {
				case 'article':
					$t = sql_fetsel("id_rubrique", "spip_articles", "id_article=".intval($id_objet));
					if ($t) {
						$id_rubrique = $t['id_rubrique'];
						if (in_array($id_rubrique,$rubriques_exclues))
							$return = false;
					}
					break;
				case 'rubrique':
					if (in_array($id_objet,$rubriques_exclues))
						$return = false;
					break;
			}
		}		
	}
	
	return $return;
}


function cinotif_abonnement_supprimer($id_auteur, $quoi, $objet, $id_objet='') {

	if ($id_auteur AND $quoi AND $objet) {
		$id_evenement = cinotif_id_evenement($quoi, $objet, $id_objet);
		$id_abonne = cinotif_id_abonne($id_auteur, '');		
		$where = "id_abonne=".$id_abonne." AND id_evenement=".$id_evenement;	
		sql_delete("spip_cinotif_abonnements", $where);
		cinotif_suppr_evenements_sans_abonnement();
		cinotif_suppr_abonnes_sans_abonnement();
	}
	
	return true;
}


function cinotif_id_evenement($quoi, $objet, $id_objet='') {
	$return = false;
	
	$where = "quoi=".sql_quote($quoi)." AND objet=".sql_quote($objet);

	if ($id_objet)
		$where .= "	AND id_objet=".$id_objet;

	// compatibilite avec le plugin CIMS
	// dans le cas d'un multisite, determiner le site en cours en fonction du host
	if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF'))
		$where .= "	AND multisite=".sql_quote(cims_site_en_cours());

	// compatibilite avec intraextra
	if (cinotif_intraextra())
		$where .= "	AND url_multisite=".sql_quote(cinotif_url_host());
	
	// cas du formulaire par theme et du formulaire simple
	if (cinotif_form_theme_ou_simple())
		$where .= " AND statut<>''";
	else
		$where .= " AND statut=''";

		
	$row = sql_fetsel('id_evenement', 'spip_cinotif_evenements', $where);
	if ($row)
		$return = $row['id_evenement'];		

	return $return;
}


function cinotif_ids_evenement($objet, $id_objet='') {
	$return = array();
	
	$where = "objet=".sql_quote($objet);

	if ($id_objet)
		$where .= "	AND id_objet=".$id_objet;

	// compatibilite avec le plugin CIMS
	// dans le cas d'un multisite, determiner le site en cours en fonction du host
	if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF'))
		$where .= "	AND multisite=".sql_quote(cims_site_en_cours());

	// compatibilite avec intraextra
	if (cinotif_intraextra())
		$where .= "	AND url_multisite=".sql_quote(cinotif_url_host());
		
	$result = sql_select('id_evenement', 'spip_cinotif_evenements', $where);
	while ($row = sql_fetch($result))
		$return[] = $row['id_evenement'];

	return $return;
}


function cinotif_evenement($id_evenement) {
	$return = false;
	
	$where = "id_evenement=".intval($id_evenement);

	// compatibilite avec le plugin CIMS
	// dans le cas d'un multisite, determiner le site en cours en fonction du host
	if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF'))
		$where .= "	AND multisite=".sql_quote(cims_site_en_cours());

	$row = sql_fetsel('*', 'spip_cinotif_evenements', $where);
	if ($row)
		$return = $row;		

	return $return;
}


function cinotif_abonnement_existe($email, $quoi, $objet, $id_objet='') {
	$return = false;
	$id_evenement = cinotif_id_evenement($quoi, $objet, $id_objet);
	$id_auteur = cinotif_id_auteur();
	
	if ($id_evenement) {
		$id_abonne = cinotif_id_abonne($id_auteur, $email);		
		$row = sql_fetsel('id_abonne,id_evenement', 'spip_cinotif_abonnements', "id_abonne=".$id_abonne." AND id_evenement=".$id_evenement);
		if ($row)
			$return = $row;		
	}

	return $return;
}


function cinotif_abonnement_parent_existe($email, $quoi, $objet, $id_objet='') {
	$return = false;
	$id_auteur = cinotif_id_auteur();
	
	if ($id_auteur) {
		// l'email de la table auteur est prioritaire
		if ($auteur_email = cinotif_auteur_email())
			$email = $auteur_email;
	}
	
	$emails = cinotif_mailcompatible($email);
	$in = sql_in('email',$emails);
	
	if ($id_auteur)
		$debut_where = "(".$in." OR id_auteur=".$id_auteur.")";
	else
		$debut_where = $in;

	// Cas auteur authentifie avec un email dans la table auteurs
	if ($id_auteur AND cinotif_auteur_email())
		$fin_message = '';
	else 
		$fin_message = '_ou_attente_confirmation';
			
		
	if ($objet=='rubrique' OR $objet=='article') {
		if ($id_objet) {
			// existe-t-il un abonnement pour le site ?
			if (cinotif_abonnement_existe($email, $quoi, 'site'))
				$return = 'abonnement_site_existe'.$fin_message;

			// existe-t-il un abonnement d'une rubrique parente ?
			if (!$return) {
				if ($objet=='rubrique')
					$id_rubrique = $id_objet;
				if ($objet=='article') {
					$t = sql_fetsel("id_rubrique", "spip_articles", "id_article=".intval($id_objet));
					if ($t)
						$id_rubrique = $t['id_rubrique'];
					else
						$id_rubrique = 0;
				}			
				$ascendants = cinotif_ascendance($id_rubrique);
				if ($ascendants) {
					foreach ($ascendants as $id_ascendant) {
						if (cinotif_abonnement_existe($email, $quoi, 'rubrique', $id_ascendant)) {
							$return = 'abonnement_rubrique_parente_existe'.$fin_message;
							break;
						}
					}
				}
			}
		}
	}

	
	return $return;
}




function cinotif_secure($objet='',$id_objet=0, $desabonner='', $quoi=array(),$a='',$e='',$i='',$j='', $lien_fermer='') {
	
	if ($objet AND preg_match(',^[a-z]*$,',$objet)) $objet = $objet;
	else $objet = '';	

	$id_objet = intval($id_objet);
	
	if ($quoi) {
		if (!is_array($quoi))
			$quoi = array($quoi);
	}
	
	// c pour confimer et d pour desabonner
	if (!$a) $a = _request('a');
	if ($a AND ($a=='c' OR $a=='d')) $action = $a;
	else $action = '';
		
	if (!$e) $e = _request('e');
	$action_id_abonne = intval($e);
		
	if (!$i) $i = _request('i');
	$id_courrier = intval($i);

	// le jeton doit etre un md5 valide
	if (!$j) $j = _request('j');
	if (!empty($j) && preg_match('/^[a-f0-9]{32}$/', $j)) $jeton = $j;
	else $jeton = '';	

	if ($lien_fermer AND ($lien_fermer=='oui')) $lien_fermer = $lien_fermer;
	else $lien_fermer = '';

	if ($desabonner AND ($desabonner=='oui')) $desabonner = $desabonner;
	else $desabonner = '';

	return array($objet, $id_objet, $desabonner, $quoi, $action, $action_id_abonne, $id_courrier, $jeton, $lien_fermer);
}


function cinotif_creer_jeton() {
	include_spip('inc/acces');	
	return md5(creer_uniqid());
}


function cinotif_envoi_mail_confirmation($jeton,$formulaire='') {
	$return = _T('cinotif:abonnement_pas_enregistre');
	$publie = false;
	$envoyer = false;
	$email = '';
	$objet = '';
	$id_objet = '';
	$id_abonne = 0;

	$result = sql_select('*', 'spip_cinotif_abonnements', "jeton=".sql_quote($jeton));	
	while ($row = sql_fetch($result)) {
		$id_abonne = $row['id_abonne'];
		$id_evenement = $row['id_evenement'];
		$jeton = $row['jeton'];
		if ($row['statut']=='publie') {
			$publie = true;
		} else {
			$envoyer = true;
		}
	}

	$result = sql_select('email', 'spip_cinotif_abonnes', "id_abonne=".$id_abonne);	
	while ($row = sql_fetch($result))
		$email = $row['email'];

	
	if ($evenement = cinotif_evenement($id_evenement)) {
		$objet = $evenement['objet'];
		$id_objet = $evenement['id_objet'];
	}

	if (in_array($formulaire,array('simple','themes')))
		$objet='site';

	if ($envoyer) {
		$ci_url_site = strtolower($GLOBALS['meta']['adresse_site']);
		
		// compatibilite avec intraextra
		if (cinotif_intraextra())			
			$ci_url_site = str_replace(cinotif_site_host(),cinotif_url_host(),$ci_url_site);

		$page = 'cinotif';
		if (defined('_DIR_PLUGIN_CISQUEL')){
			if ($objet=='site')
				$page = "abonnement-site";
			else
				$page = "abonnement-".$objet."&id_".$objet."=".$id_objet;
		}
		
		$url = $ci_url_site."/spip.php?page=".$page."&a=c&e=".$id_abonne."&j=".$jeton;

		if ($objet=='site') {
			$titre_objet = textebrut(typo($GLOBALS['meta']['nom_site']));	
			$texte = _T('cinotif:texte_mail_confirmation_site',
				 array('titre' => $titre_objet,
				       'nom_email' => $email,
				       'url_site' => $ci_url_site, 
				       'url' => $url));
		} else {
			$titre_objet = textebrut(typo(cinotif_titreobjet($objet, $id_objet))).' (' .textebrut(typo($objet))." ".$id_objet.')';	
			$texte = _T('cinotif:texte_mail_confirmation',
				 array('titre' => $titre_objet,
				       'nom_email' => $email,
				       'url_site' => $ci_url_site, 
				       'url' => $url));
		}
	
		$titre = textebrut(typo($GLOBALS['meta']['nom_site']." : "._T('cinotif:titre_mail_confirmation')));
		$envoyer_mail = charger_fonction('envoyer_mail','inc');
		if ($envoyer_mail($email,$titre, $texte))
			$return = _T('cinotif:envoi_mail_confirmation',array('email'=>$email));

	} elseif ($publie) {
		$return = _T('cinotif:abonnement_enregistre');
	}

	return $return;
}


function cinotif_envoi_mail_desabonnement($jeton,$formulaire='') {
	$return = _T('cinotif:desabonnement_pas_enregistre');
	$publie = false;
	$envoyer = false;
	$email = '';
	$objet = '';
	$id_objet = '';
	$id_abonne = 0;

	$result = sql_select('*', 'spip_cinotif_abonnements', "jeton=".sql_quote($jeton));
	while ($row = sql_fetch($result)) {
		$id_abonne = $row['id_abonne'];
		$id_evenement = $row['id_evenement'];
		$jeton = $row['jeton'];
	}
	
	$result = sql_select('email', 'spip_cinotif_abonnes', "id_abonne=".$id_abonne);	
	while ($row = sql_fetch($result))
		$email = $row['email'];
	
	if ($evenement = cinotif_evenement($id_evenement)) {
		$objet = $evenement['objet'];
		$id_objet = $evenement['id_objet'];
	}
	
	if (in_array($formulaire,array('simple','themes')))
		$objet='site';
	
	if ($email) {
		$ci_url_site = strtolower($GLOBALS['meta']['adresse_site']);
		
		// compatibilite avec intraextra
		if (cinotif_intraextra())			
			$ci_url_site = str_replace(cinotif_site_host(),cinotif_url_host(),$ci_url_site);
		
		$page = 'cinotif';
		if (defined('_DIR_PLUGIN_CISQUEL')){
			if ($objet=='site')
				$page = "abonnement-site&desabonner=oui";
			else
				$page = "abonnement-".$objet."&id_".$objet."=".$id_objet."&desabonner=oui";
		}
		
		$url = $ci_url_site."/spip.php?page=".$page."&a=d&e=".$id_abonne."&j=".$jeton;
		
		if ($objet=='site') {
			$titre_objet = textebrut(typo($GLOBALS['meta']['nom_site']));	
			$texte = _T('cinotif:texte_mail_desabonnement_site',
				 array('titre' => $titre_objet,
				       'nom_email' => $email,
				       'url_site' => $ci_url_site, 
				       'url' => $url));
		} else {		
			$titre_objet = textebrut(typo(cinotif_titreobjet($objet, $id_objet))). ' (' .textebrut(typo($objet))." ".$id_objet.')';	
			$texte = _T('cinotif:texte_mail_desabonnement',
				 array('titre' => $titre_objet,
				       'nom_email' => $email,
				       'url_site' => $ci_url_site, 
				       'url' => $url));
		}

		$titre = textebrut(typo($GLOBALS['meta']['nom_site']." : "._T('cinotif:titre_mail_desabonnement')));
		$envoyer_mail = charger_fonction('envoyer_mail','inc');
		if ($envoyer_mail($email,$titre, $texte))
			$return = _T('cinotif:envoi_mail_desabonnement',array('email'=>$email));

	} else {
		$return = _T('cinotif:desabonnement_enregistre');
	}

	return $return;
}


function cinotif_confirmer_email($action_id_abonne, $jeton) {
	$ok = false;

	$abonne = sql_fetsel('email', 'spip_cinotif_abonnes', 'id_abonne='.intval($action_id_abonne));	
	if ($abonne)
		$email = $abonne['email'];
	else
		$email = '';
	
	// les abonnements existent-t-il ?
	// verifier email et jeton
	$result = sql_select('*', 'spip_cinotif_abonnements', 'id_abonne='.intval($action_id_abonne).' AND jeton='.sql_quote($jeton));
	while ($row = sql_fetch($result)) {
		$id_abonne = $row['id_abonne'];
		$id_evenement = $row['id_evenement'];
		
		if ($id_abonne) {
			// si l'email existe dans la table des auteurs de SPIP, noter l'id_auteur
			// a noter que dans SPIP, deux auteurs peuvent avoir le meme email
			if ($email){
				$nb = 0;
				$result = sql_select("id_auteur", "spip_auteurs", "LOWER(email)=".sql_quote($email));
				while ($row = sql_fetch($result)) {
					$id_auteur = $row['id_auteur'];
					$nb++;
				}
				if ($nb>0 AND $nb<2)
					sql_updateq("spip_cinotif_abonnes", array('id_auteur'=>$id_auteur), "id_abonne=".intval($id_abonne));
			}

			// si ok, changer le statut de l'abonnement
			$champs = array("statut" => 'publie', "jeton" => '');
			$e = sql_updateq("spip_cinotif_abonnements", $champs, "jeton=".sql_quote($jeton)." AND id_abonne=".intval($id_abonne));
			if ($e!==false){
				$ok = true;
				cinotif_sympa_abonner($email,$id_evenement);
			}
		}
	}
	
	if (!$ok) {
		// si on clique plusieurs fois sur le lien
		// ou si l'admin du site a confirme l'abonnement
		if (sql_countsel("spip_cinotif_abonnements", "id_abonne=".intval($action_id_abonne)." AND statut='publie'")>0){
			if (sql_countsel("spip_cinotif_abonnements", "id_abonne=".intval($action_id_abonne)." AND statut<>'publie'")<1)
				$ok = true;
		}
	}
	
	return $ok;
}


function cinotif_desabonner($action_id_abonne, $jeton) {
	$ok = false;
	
	$id_abonne = $action_id_abonne;

	$e = sql_delete("spip_cinotif_abonnements", "id_abonne=".intval($id_abonne)." AND jeton=".sql_quote($jeton));
	if ($e>0) {
		cinotif_suppr_evenements_sans_abonnement();
		cinotif_suppr_abonnes_sans_abonnement();
		$ok = true;
	}
	return $ok;
}


// desabonnement d'un auteur authentifie
function cinotif_desabonner_auteur($objet, $id_objet='') {
	$return = '';
	$id_auteur = cinotif_id_auteur();
	$id_abonne = cinotif_id_abonne($id_auteur, '');
	
	if ($objet AND $id_abonne){
		if ($objet=='site') {
			$where = "id_abonne=".$id_abonne;
		} else {
			$tableau_id_evenement = cinotif_ids_evenement($objet, $id_objet);				
			$in = sql_in('id_evenement',$tableau_id_evenement);
			$where = "id_abonne=".$id_abonne." AND ".$in;
		}
		
		$return = sql_delete("spip_cinotif_abonnements", $where);
		cinotif_suppr_evenements_sans_abonnement();		
		cinotif_suppr_abonnes_sans_abonnement();
	}

	return $return;
}


function cinotif_hash_env() {
	static $res ='';
	if ($res) return $res;
	$ip = explode('.',$GLOBALS['ip']);
	array_pop($ip);
	$ip = implode('.',$ip).".xxx";
	$res = md5($ip. $_SERVER['HTTP_USER_AGENT']);
	return $res;
}

// Calcule une cle pour le formulaire
function cinotif_creer_cle() {
	$form = 'cinotif';
	$time = date('Y-m-d-H');
	if (isset($GLOBALS['visiteur_session']['id_auteur']) AND intval($GLOBALS['visiteur_session']['id_auteur']))
		$qui = ":".$GLOBALS['visiteur_session']['id_auteur'].":".$GLOBALS['visiteur_session']['nom'];
	else {
		include_spip('inc/session');
		$qui = cinotif_hash_env();
	}
		
	include_spip('inc/securiser_action');
	// la cle prend en compte l'heure et l'identite de l'internaute
	return calculer_cle_action("cle$form$time$qui");
}

// Verifie une cle pour le formulaire
function cinotif_verifier_cle($cle) {
	$form = 'cinotif';
	$time = time();
	$time_old = date('Y-m-d-H',$time-3600);
	$time = date('Y-m-d-H',$time);

	if (isset($GLOBALS['visiteur_session']['id_auteur']) AND intval($GLOBALS['visiteur_session']['id_auteur']))
		$qui = ":".$GLOBALS['visiteur_session']['id_auteur'].":".$GLOBALS['visiteur_session']['nom'];
	else {
		include_spip('inc/session');
		$qui = cinotif_hash_env();
	}
	
	$ok = (verifier_cle_action("cle$form$time$qui",$cle)
			or verifier_cle_action("cle$form$time_old$qui",$cle));

	return $ok;
}


function cinotif_titreobjet($objet, $id_objet='') {
	$return = '';

	switch($objet) {
		case 'article':
			$row = sql_fetsel("titre", "spip_articles", "id_article=".intval($id_objet));
			if ($row)
				$return = $row['titre'];
			break;
		case 'rubrique':
			$row = sql_fetsel("titre", "spip_rubriques", "id_rubrique=".intval($id_objet));
			if ($row)
				$return = $row['titre'];
			break;
		case 'site':
			$return = $GLOBALS['meta']['nom_site'];
			break;
	}

	return $return;
}

// Trouver quel est l'objet et l'id_objet de l'evenement
// qui a declenche l'envoi de ce courrier a ce destinataire.
// Si plusieurs evenements, prendre le plus proche du contenu notifie 
function cinotif_trouver_objet_courrier_et_destinataire($id_courrier, $id_auteur=0, $id_abonne=0) {
	$return = array('objet'=>'','id_objet'=>0);
	$id_courrier = intval($id_courrier);
	$id_auteur = intval($id_auteur);
	$id_abonne = intval($id_abonne);
	$evenement_recherche = 0;

	if ($id_courrier>0 AND ($id_auteur OR $id_abonne)) {
		
		// contenu notifie
		$quoi = '';
		$objet = '';
		$id_objet = 0;
		$row = sql_fetsel('*', 'spip_cinotif_courriers', 'id_courrier='.$id_courrier);
		if ($row) {
			$quoi = $row['quoi'];
			$objet = $row['objet'];
			$id_objet = intval($row['id_objet']);
			$parent = $row['parent'];
			$id_parent = intval($row['id_parent']);
		}

		// article
		if ($parent=='article'){
			$evenement_recherche = cinotif_evenement_recherche($id_auteur,$id_abonne,$quoi,$parent,array($id_parent));
			
			if (!$evenement_recherche) {
				$t = sql_fetsel("id_rubrique", "spip_articles", "id_article=".$id_parent);
				$id_parent = $t['id_rubrique'];
				$parent = 'rubrique';
			}
		}			

		// rubrique
		if ($parent=='rubrique'){
			$ascendance = cinotif_ascendance($id_parent);
			$evenement_recherche = cinotif_evenement_recherche($id_auteur,$id_abonne,$quoi,$parent,$ascendance);
			
			// cas particulier ou on s'abonne a un article a l'evenement modification d'article
			if (!$evenement_recherche AND $quoi=='articlemodifie')
				$evenement_recherche = cinotif_evenement_recherche($id_auteur,$id_abonne,$quoi,$objet,array($id_objet));
		}
		
		// site
		if (!$evenement_recherche) {
			$evenement_recherche = cinotif_evenement_recherche($id_auteur,$id_abonne,$quoi,'site',array());
		}
		if ($evenement_recherche) {
			$row = sql_fetsel('*', 'spip_cinotif_evenements', "id_evenement=".$evenement_recherche);
			$return = array('objet'=>$row['objet'],'id_objet'=>$row['id_objet']);
		}
	}
	
	return $return;
}

function cinotif_evenement_recherche($id_auteur=0,$id_abonne=0,$quoi,$objet,$tableau_id_objet=array()) {
	$evenement_recherche = 0;

	if ($id_abonne){
		$where_auteur = "id_abonne=".intval($id_abonne);
		$where = "quoi=".sql_quote($quoi)." AND objet=".sql_quote($objet);

		// compatibilite avec le plugin CISQUEL
		if (defined('_DIR_PLUGIN_CISQUEL') AND $quoi=='articlepublie')
			$where = sql_in('quoi',array('articlepublie','actupublie'))." AND objet=".sql_quote($objet);

		// compatibilite avec le plugin CIMS
		// dans le cas d'un multisite, determiner le site en cours en fonction du host
		if ($multisite AND defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF'))
			$where .= "	AND multisite=".sql_quote(cims_site_en_cours());
		
		// les evenements possibles
		if ($tableau_id_objet) {
			foreach ($tableau_id_objet as $id_objet) {
				$tableau_id_evenement = array();	
				$result = sql_select('id_evenement', 'spip_cinotif_evenements', $where." AND id_objet=".$id_objet);
				while ($row = sql_fetch($result))
					$tableau_id_evenement[] = $row['id_evenement'];		
			
				if ($tableau_id_evenement) {
					foreach ($tableau_id_evenement as $id_evenement){
						if (sql_countsel('spip_cinotif_abonnements', "id_evenement=".$id_evenement." AND ".$where_auteur)>0) {
							$evenement_recherche = $id_evenement;
							break;
						}
					}
				}
				if ($evenement_recherche)
					break;
			}			
		} else {
			$tableau_id_evenement = array();	
			$result = sql_select('id_evenement', 'spip_cinotif_evenements', $where);
			while ($row = sql_fetch($result))
				$tableau_id_evenement[] = $row['id_evenement'];		
		
			if ($tableau_id_evenement) {
				foreach ($tableau_id_evenement as $id_evenement){
					if (sql_countsel('spip_cinotif_abonnements', "id_evenement=".$id_evenement." AND ".$where_auteur)>0) {
						$evenement_recherche = $id_evenement;
						break;
					}
				}
			}
		}
	}	
	return $evenement_recherche;
}

function cinotif_abo_autres_objets_existe($id_auteur,$objet){
	$return = false;
	
	$id_auteur = intval($id_auteur);
	if ($id_auteur AND $objet) {
		$id_abonne = cinotif_id_abonne($id_auteur, '');
		if ($nb=sql_countsel("spip_cinotif_abonnements AS abo LEFT JOIN spip_cinotif_evenements AS lien ON abo.id_evenement=lien.id_evenement",
			"abo.id_abonne=".intval($id_abonne)." AND lien.objet<>".sql_quote($objet))>0)
			$return = true;
	}

	return $return;
}

function cinotif_info($objet) {
	$return = '';
	$cinotif_meta = cinotif_tableau_meta(true);
	
	if ($objet=='site') {
		if (isset($cinotif_meta['abonnements_rubrique']) AND count($cinotif_meta['abonnements_rubrique'])>=1)
			$return = 'oui';
		elseif (isset($cinotif_meta['abonnements_article']) AND count($cinotif_meta['abonnements_article'])>=1)
			$return = 'oui';
		
	} elseif ($objet=='rubrique' OR $objet=='article') {
		if (isset($cinotif_meta['abonnements_site']) AND count($cinotif_meta['abonnements_site'])>=1)
			$return = 'oui';		
	}

	return $return;
}

?>