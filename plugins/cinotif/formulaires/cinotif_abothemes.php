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
include_spip('formulaires/cinotif_abonnement');


function formulaires_cinotif_abothemes_charger_dist($desabonner='',$a='',$e='',$i='',$j='',$lien_fermer='') {

	if (!isset($GLOBALS['meta']['cinotif_formulaire']))
		return false;
	elseif ($GLOBALS['meta']['cinotif_formulaire']=='defaut')
		return false;

	
	list($ob, $id_ob, $desabonner, $quoi, $action, $action_id_abonne, $id_courrier, $jeton, $lien_fermer) = cinotif_secure('', 0, $desabonner, array(), $a, $e, $i, $j, $lien_fermer);

	$valeurs['editable'] = false;
	$valeurs['ci_objet'] = $objet; 
	$valeurs['ci_id_objet'] = $id_objet; 
	$valeurs['ci_lien_fermer'] = $lien_fermer; 
	$valeurs['ci_desabonner'] = $desabonner; 
	$valeurs['ci_action'] = $action; 
	$valeurs['_checkboxthemes'] = '';

	
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
			if (cinotif_abothemes_desabonner($action_id_abonne, $jeton))
				$valeurs['message_ok'] = _T('cinotif:desabonnement_enregistre');
			else
				$valeurs['message_erreur'] = _T('cinotif:desabonnement_pas_enregistre');
		}
	}

	//	pour s'abonner
	elseif (!$action AND !$desabonner) {
			
		$valeurs['editable'] = true;

		$valeurs['_checkboxthemes'] = cinotif_checkboxthemes();
		
		$valeurs['_hidden'] .= "<input type='hidden' name='_cle' value='".cinotif_creer_cle()."' />";


		// Les abonnements sont-ils autorises ?
		$cinotif_meta = cinotif_tableau_meta();
		$valeurs['abo_tous'] = $cinotif_meta['abo_tous'];
		if ($valeurs['abo_tous']=='non')
			return;

		// texte d'information
		$valeurs['_abo_texte'] = cinotif_filtre_texte($cinotif_meta['abo_texte']);
			
		// Si les personnes non authentifiees ne sont pas autorisees a s'abonner
		// alors, si on n'est pas connecte, on se redirige vers l'authentification
		if ($cinotif_meta['abo_tous']=='oui' AND $cinotif_meta['abo_non_auth']=='non' AND !isset($GLOBALS['visiteur_session']['id_auteur'])){
			// syntaxe avec '&' sinon cela ne fonctionne pas ici
			include_spip('inc/headers');
			redirige_par_entete(generer_url_public('login').'&url='.rawurlencode(self()));
		}
		
	} 

	//	pour se desabonner
	elseif (!$action AND $desabonner) {
			$valeurs['editable'] = true;
			$valeurs['_liste_choix'] = '';
			$valeurs['_hidden'] .= "<input type='hidden' name='_cle' value='".cinotif_creer_cle()."' />";

			// texte d'information
			$cinotif_meta = cinotif_tableau_meta();
			$valeurs['_desabo_texte'] = cinotif_filtre_texte($cinotif_meta['desabo_texte']);
			
			$id_auteur = cinotif_id_auteur();
			$tableau_objet = array();

			if ($id_auteur) {
				// desabonnement avec l'identifiant du courrier
				if ($id_courrier AND $action_id_abonne) {
					 $tableau_objet = cinotif_trouver_objet_courrier_et_destinataire($id_courrier, $id_auteur, $action_id_abonne);
					 
				} else {
					$existant = cinotif_themes_auteur($id_auteur, $GLOBALS['visiteur_session']['email']);

					if (!$existant)
						$valeurs['message_ok'] = _T('cinotif:pas_abonne');
						
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
					$ciredirect = generer_url_public('abonnement-site').'&desabonner=oui';
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


function formulaires_cinotif_abothemes_verifier_dist($desabonner='',$a='',$e='',$i='',$j='',$lien_fermer='') {

	list($ob, $id_ob, $desabonner, $quoi, $action, $action_id_abonne, $id_courrier, $jeton, $lien_fermer) = cinotif_secure('', 0, $desabonner, array(), $a, $e, $i, $j, $lien_fermer);
	
	$erreurs = array();
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
		
		$lesthemes = cinotif_secure_themes(_request('lesthemes'));
				
		if (!$action AND !$desabonner AND !$lesthemes AND !$id_auteur) {
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
				$formsimple = cinotif_form_simple();
				
				if ($id_auteur AND !$formsimple) {
					$abo_ok = true;
				} else {
					if ($desabonner)
						$abo_ok = true;
					else {
						$id_abonne = cinotif_id_abonne($id_auteur, $email);

						// le ou les abonnements existent-t-il pour cet email ?
						$abos_existants = array();
						$result = sql_select('id_evenement', 'spip_cinotif_abonnements', "id_abonne=".intval($id_abonne));
						while ($row = sql_fetch($result))
							$abos_existants[] = $row['id_evenement'];

						if (count(array_diff($lesthemes,$abos_existants))>0)						
							$abo_ok = true;
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


function formulaires_cinotif_abothemes_traiter_dist($desabonner='',$a='',$e='',$i='',$j='',$lien_fermer='') {
	
	list($ob, $id_ob, $desabonner, $quoi, $action, $action_id_abonne, $id_courrier, $jeton, $lien_fermer) = cinotif_secure('', 0, $desabonner, array(), $a, $e, $i, $j, $lien_fermer);
	
	$abo_ok = false;
	$message = '';
	$id_auteur = cinotif_id_auteur();
	$suppr_only = false;
	$diff_existant = false;

	$lesthemes = cinotif_secure_themes(_request('lesthemes'));

	$req_email = _request('session_email');
	
	if ($req_email AND email_valide($req_email))
		$email = $req_email;
	else
		$email = '';

	// l'email en base est est prioritaire sur le formulaire
	if ($auteur_email = cinotif_auteur_email())
		$email = $auteur_email;

	// supprimer le cas echeant, si auteur authentifie
	if ($email AND !$desabonner) {
		if ($id_auteur) {	
			$existant = cinotif_themes_auteur($id_auteur, $email);
			if ($existant) {
				if ($lesthemes){
					$diffs = array_diff($existant,$lesthemes);
					$lesthemes = array_diff($lesthemes,$existant);
				} else {
					$diffs = $existant;
					$diff_existant = true;
				}

//				if (!$lesthemes)
//					$message .= _T('cinotif:desabonnement_enregistre');

				foreach ($diffs AS $diff) {
					cinotif_abothemes_supprimer($id_auteur, $diff);
					$suppr_only = true;
				}
				
				if ($suppr_only){
					if ($diff_existant)
						$message = _T('cinotif:desabonnement_enregistre');
					else
						$message = _T('cinotif:abonnement_enregistre');
				}
				
			}
		}
	}
		

	if ($desabonner AND $email) {

		if ($id_auteur>0) {
			// desabonnement d'un auteur authentifie
			if (cinotif_abothemes_desabonner_auteur())
				$message = _T('cinotif:desabonnement_enregistre');						
		} else {
			// desabonnement d'un auteur non authentifie
	
			// calcul du jeton 
			$jeton = cinotif_creer_jeton();
	
			// enregistrer avec un jeton
			$id_abonne = cinotif_id_abonne($id_auteur, $email);
			$abo_ok = sql_updateq("spip_cinotif_abonnements", array('jeton' =>  $jeton), "id_abonne=".$id_abonne);
			
			// envoi d'un mail avec un lien de confirmation
			if ($abo_ok)
				$message = cinotif_envoi_mail_desabonnement($jeton,'themes');	
			else
				$message = "echec";
		}
	} else {


		// maximum d'abonnes
		$cinotif_nb_max_abonnes = cinotif_nb_max_abonnes();
		if ($nb=sql_countsel("spip_cinotif_abonnes")>=$cinotif_nb_max_abonnes) {
			if (!($desabonner AND $email AND ($lesthemes OR $id_courrier)))
				$message .= _T('cinotif:max_abonnes');
		
		// enregistrer
		} elseif ($email AND $lesthemes) {
			$abos = array();
	
			// Cas auteur authentifie avec un email dans la table auteurs
			if ($id_auteur AND cinotif_auteur_email()) {	
				// enregistrer
				foreach ($lesthemes AS $theme) {
					$abos[] = cinotif_abotheme_enregistrer($email, $theme);
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
				foreach ($lesthemes AS $theme) {
					$abos[] = cinotif_abotheme_enregistrer($email, $theme, $jeton);
					$suppr_only = false;
				}	
				
				$abos = array_unique($abos);
				
				// envoi d'un mail avec un lien de confirmation
				if (in_array('ok',$abos))
					$message = cinotif_envoi_mail_confirmation($jeton,'themes');	
				else
					foreach ($abos AS $abo)
						$message .= _T('cinotif:'.$abo)." ";
			}
		}
	}

	$res['message_ok'] = $message;
	if (!$message AND $id_auteur AND (cinotif_auteur_email() OR $suppr_only) AND !$lien_fermer)
		$res['redirect'] = cinotif_retour('site',0);

	return $res;
}


function cinotif_themes_auteur($id_auteur, $email) {
	$return = array();
	$evenements = array();
	
	$in_statut = sql_in('statut',array('publie','sansnotif'));
	$where = $in_statut;

	// compatibilite avec le plugin CIMS
	// dans le cas d'un multisite, determiner le site en cours en fonction du host
	if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF'))
		$where .= "	AND multisite=".sql_quote(cims_site_en_cours());

	$result = sql_select('*', 'spip_cinotif_evenements', $where);
	while ($row = sql_fetch($result)) {
		$evenements[] = $row['id_evenement'];
	}
	
	$id_abonne = cinotif_id_abonne($id_auteur, $email);
	
	if ($evenements AND $id_abonne) {
		$in_evenements = sql_in('id_evenement',$evenements); 
		$result = sql_select('id_evenement', 'spip_cinotif_abonnements', "id_abonne=".$id_abonne." AND ".$in_evenements);
		while ($row = sql_fetch($result))
			$return[] = $row['id_evenement'];
	}

	return $return;
}

function cinotif_abotheme_enregistrer($email, $theme, $jeton='') {
	$return = '';
	$ok = true;

	if ($email AND $theme) {

		$id_evenement = intval($theme);
		
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
		$abonnement_existe = false;
		if ($id_evenement AND $id_abonne) {
			$row = sql_fetsel('id_abonne,id_evenement', 'spip_cinotif_abonnements', "id_abonne=".$id_abonne." AND id_evenement=".$id_evenement);
			if ($row)
				$abonnement_existe = true;		
		}
		
		if ($abonnement_existe) {
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
		
		if ($return=='ok' AND !$jeton)
			cinotif_sympa_abonner($email,$id_evenement);
		
	}
	
	return $return;
}

function cinotif_abothemes_supprimer($id_auteur, $id_evenement) {

	if ($id_auteur AND $id_evenement) {
		$id_evenement = intval($id_evenement);
		$id_abonne = cinotif_id_abonne($id_auteur, '');
		$email = '';
		$row = sql_fetsel('email', 'spip_cinotif_abonnes', "id_abonne=".intval($id_abonne));	
		if ($row)
			$email = $row['email'];
		
		sql_delete("spip_cinotif_abonnements", "id_abonne=".$id_abonne." AND id_evenement=".$id_evenement);
		cinotif_sympa_desabonner($email,$id_evenement);
		
//		cinotif_suppr_evenements_sans_abonnement();
		cinotif_suppr_abonnes_sans_abonnement();
	}
	
	return true;
}


// desabonnement d'un auteur authentifie
function cinotif_abothemes_desabonner_auteur() {
	$return = '';
	$id_auteur = cinotif_id_auteur();
	$id_abonne = cinotif_id_abonne($id_auteur, '');
	$email = '';
	$row = sql_fetsel('email', 'spip_cinotif_abonnes', "id_abonne=".intval($id_abonne));	
	if ($row)
		$email = $row['email'];
	
	if ($id_abonne){
		$where = "id_abonne=".$id_abonne;
		$evenements = array();
		$result = sql_select("id_evenement", "spip_cinotif_abonnements", $where);
		while ($row = sql_fetch($result)) {
			$evenements[] = $row['id_evenement'];	
		}
		
		$return = sql_delete("spip_cinotif_abonnements", $where);
		
		if ($return>0) {
			foreach ($evenements AS $id_evenement)
				cinotif_sympa_desabonner($email,$id_evenement);
			
//			cinotif_suppr_evenements_sans_abonnement();		
			cinotif_suppr_abonnes_sans_abonnement();
		}
	}

	return $return;
}

function cinotif_abothemes_desabonner($action_id_abonne, $jeton) {
	$ok = false;
	
	$id_abonne = $action_id_abonne;
	$email = '';
	$row = sql_fetsel('email', 'spip_cinotif_abonnes', "id_abonne=".intval($id_abonne));	
	if ($row)
		$email = $row['email'];

	$where = "id_abonne=".intval($id_abonne)." AND jeton=".sql_quote($jeton);
	$evenements = array();
	$result = sql_select("id_evenement", "spip_cinotif_abonnements", $where);
	while ($row = sql_fetch($result)) {
		$evenements[] = $row['id_evenement'];	
	}
		
	$e = sql_delete("spip_cinotif_abonnements", $where);
	if ($e>0) {
		foreach ($evenements AS $id_evenement)
			cinotif_sympa_desabonner($email,$id_evenement);
		
//		cinotif_suppr_evenements_sans_abonnement();
		cinotif_suppr_abonnes_sans_abonnement();
		$ok = true;
	}
	return $ok;
}

function cinotif_checkboxthemes(){

	if (cinotif_form_simple())
		return '';


	$return = '';
	$auteur_abonnements = array();
	
	// auteur authentifie
	if ($id_auteur = cinotif_id_auteur()) {		
		$id_abonne = cinotif_id_abonne($id_auteur, '');
		
		if ($id_abonne) {
			$result = sql_select('*', 'spip_cinotif_abonnements', "id_abonne=".$id_abonne." AND statut='publie'");
			while ($row = sql_fetch($result))
				$auteur_abonnements[] = $row['id_evenement'];
		}
	}			
	
	$in_statut = sql_in('statut',array('publie','sansnotif'));
	$where = $in_statut;

	// compatibilite avec le plugin CIMS
	// dans le cas d'un multisite, determiner le site en cours en fonction du host
	if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF'))
		$where .= "	AND multisite=".sql_quote(cims_site_en_cours());

	$result = sql_select('*', 'spip_cinotif_evenements', $where, '', '0+titre,titre');
	while ($row = sql_fetch($result)){
		$id_evenement = $row['id_evenement'];
		$titre = textebrut(supprimer_numero(typo($row['titre'])));		
    	$checked = (in_array($id_evenement,$auteur_abonnements) ? 'checked="checked"' : '');
		
		$return .= '<div class="textechk">';
		$return .= '<input id="abo'.$id_evenement.'" type="checkbox" value="'.$id_evenement.'" name="lesthemes[]" '.$checked.' />';
		$return .= '<label for="abo'.$id_evenement.'">'.$titre.'</label>';
		$return .= '</div>';
	}
	
	if ($return)
		$return = '<fieldset><legend>'._T('cinotif:abonnements').'</legend><div class="choix">'.$return.'</div></fieldset>';	
	else
		$return	= _T('cinotif:abosabsent');

	return $return;			
}

function cinotif_secure_themes($themes=''){
	$return = array();

	if (cinotif_form_simple())
		return cinotif_liste_themes();
	
	
	if ($themes) {
		if (!is_array($themes))
			$themes = array($themes);

		$themes = array_map('intval',$themes);
		
		$in_evenements = sql_in('id_evenement',$themes);
		$in_statut = sql_in('statut',array('publie','sansnotif'));

		$result = sql_select('id_evenement', 'spip_cinotif_evenements', $in_evenements." AND ".$in_statut);
		while ($row = sql_fetch($result))
			$return[] = $row['id_evenement'];
	
	}

	return $return;
}

function cinotif_liste_themes(){

	$return = array();
	
	$in_statut = sql_in('statut',array('publie','sansnotif'));
	$where = $in_statut;

	// compatibilite avec le plugin CIMS
	// dans le cas d'un multisite, determiner le site en cours en fonction du host
	if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF'))
		$where .= "	AND multisite=".sql_quote(cims_site_en_cours());

	$result = sql_select('*', 'spip_cinotif_evenements', $where, '', '0+titre,titre');
	while ($row = sql_fetch($result)){
		$return[] = $row['id_evenement'];
	}
	
	return $return;			
}

function cinotif_filtre_texte($texte){
	
	// Pour les retours a la ligne sous SPIP 2.1
	if (defined('_DIR_PLUGIN_CISQUEL'))
		$texte = cisquel_post_autobr($texte);

	return propre($texte);
}

?>