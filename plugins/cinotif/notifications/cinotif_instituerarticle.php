<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/cinotif_commun');
	

// Fonction appelee par divers pipelines
// http://doc.spip.org/@notifications_instituerarticle_dist
function notifications_instituerarticle($quoi, $id_article, $options) {

	// ne devrait jamais se produire
	if ($options['statut'] == $options['statut_ancien']) {
		spip_log("statut inchange",'notifications');
		return;
	}
	
	$article_ec = false;
	$mails_membres_ec = array();
	if (defined('_DIR_PLUGIN_CIAR')){
		if ($id_article = intval($id_article)) {
			$row = sql_fetsel("id_rubrique", "spip_articles", "id_article=".$id_article);
			if ($row)
				$id_rubrique = $row['id_rubrique'];
			
			include_spip('ciar_fonctions');
			$article_ec = ciar_rub_ec($id_rubrique);
			if ($article_ec) {
				$mails_membres_ec = ciar_rubrique_mails_membres_ec($id_rubrique);
				$mails_membres_ec = array_map('strtolower', $mails_membres_ec);
			}
		}
	}

	include_spip('inc/texte');

	$modele = "";
	if ($options['statut'] == 'publie') {
		if ($GLOBALS['meta']["post_dates"]=='non'
			AND strtotime($options['date'])>time())
			$modele = "notifications/article_valide";
		else
			$modele = "notifications/article_publie";
	}

	if ($options['statut'] == 'prop' AND $options['statut_ancien'] != 'publie')
		$modele = "notifications/article_propose";

	if ($modele){
		$destinataires = array();
		if ($GLOBALS['meta']["suivi_edito"] == "oui")
			$destinataires = explode(',',$GLOBALS['meta']["adresse_suivi"]);


		$destinataires = pipeline('notifications_destinataires',
			array(
				'args'=>array('quoi'=>$quoi,'id'=>$id_article,'options'=>$options)
			,
				'data'=>$destinataires)
		);

		// en cas d'ec, ne prendre que les destinataires qui sont membres de l'EC
		if ($article_ec)
			$destinataires = array_intersect(array_map('strtolower', $destinataires),array_map('strtolower', $mails_membres_ec));

		$texte = ci_email_notification_article($id_article, $modele);			
		notifications_envoyer_mails($destinataires, $texte);
	}
	
	
	if (defined('_DIR_PLUGIN_CINOTIF')){
		$id_article = intval($id_article);

		$t = sql_fetsel("id_rubrique", "spip_articles", "id_article=".$id_article);
		$id_rubrique = $t['id_rubrique'];
		$ascendance = cinotif_ascendance($id_rubrique);
		
		// compatibilite avec le plugin CIMS
		$tableau_multisite = cinotif_tableau_multisite_rubrique($id_rubrique);
			

		// publication d'un article
		if ($quoi=='instituerarticle' AND $options['statut']=='publie' AND $options['statut_ancien']!='publie'){
			
			// lister les evenements
			$evenements = cinotif_tableau_evenements('articlepublie', 'site', '', $tableau_multisite);
			$evenements = array_merge($evenements,  cinotif_tableau_evenements('articlepublie', 'rubrique', $ascendance, $tableau_multisite));
			
			if (defined('_DIR_PLUGIN_CISQUEL')) {
				// Page d'accueil : actualites 
				$in = sql_in('raccourci',array('_portail_actu','_portail_permanent_bas','_portail_permanent_bas'));
				if (($nb=sql_countsel("spip_ci_raccourcis_articles", "id_article=".$id_article." AND ".$in))>0) {
					$evenements = array_merge($evenements,cinotif_tableau_evenements('actupublie', 'site', '', $tableau_multisite));

					// Derogation : cas d'un abonne a tous les articles du site, avec une config sans tous les articles du site, mais avec les actualites de la page d'accueil
					if (cinotif_config_quoi_objet('actupublie', 'site') AND !cinotif_config_quoi_objet('articlepublie','site')){
						$evenements = array_merge($evenements,cinotif_tableau_evenements('articlepublie', 'site', '', $tableau_multisite, false));
					}
				}

				// Sous page d'accueil : raccourci dans les actualites
				$in = sql_in('raccourci',array('_souspageaccueil_actu','_souspageaccueil_permanent_bas','_souspageaccueil_permanent_haut'));
				if (($nb=sql_countsel("spip_ci_raccourcis_articles", "id_article=".$id_article." AND ".$in))>0) {
					$ascendance_spaccueil = array();
					$result = sql_select('id_rubrique', 'spip_rubriques', sql_in('id_rubrique',$ascendance)." AND ciforme='_souspageaccueil'");
					while ($row = sql_fetch($result))
						$ascendance_spaccueil[] = $row['id_rubrique'];

					if ($ascendance_spaccueil)
						$evenements = array_merge($evenements,cinotif_tableau_evenements('actupublie', 'rubrique', $ascendance_spaccueil, $tableau_multisite));
						
					// Derogation : cas d'un abonne a tous les articles de la rubrique, avec une config sans tous les articles de la rubrique, mais avec les actualites de la rubrique
					if (cinotif_config_quoi_objet('actupublie', 'rubrique') AND !cinotif_config_quoi_objet('articlepublie','rubrique'))
						$evenements = array_merge($evenements,cinotif_tableau_evenements('articlepublie', 'rubrique', $ascendance_spaccueil, $tableau_multisite, false));						
				}
				
				// Rubrique : raccourci dans les actualites
				if (($nb=sql_countsel("spip_ci_raccourcis_articles", "id_article=".$id_article." AND raccourci='_rubrique_actu'"))>0) {
					$ascendance_avec_actu = array();
					$result = sql_select('id_rubrique', 'spip_rubriques', sql_in('id_rubrique',$ascendance)." AND ".sql_in('ciforme',array('_avecactualite','_3colonnes')));
					while ($row = sql_fetch($result))	
						$ascendance_avec_actu[] = $row['id_rubrique'];

					if ($ascendance_avec_actu)
						$evenements = array_merge($evenements,cinotif_tableau_evenements('actupublie', 'rubrique', $ascendance_avec_actu, $tableau_multisite));
					
					// Derogation : cas d'un abonne a tous les articles de la rubrique, avec une config sans tous les articles de la rubrique, mais avec les actualites de la rubrique
					if (cinotif_config_quoi_objet('actupublie', 'rubrique') AND !cinotif_config_quoi_objet('articlepublie','rubrique'))
						$evenements = array_merge($evenements,cinotif_tableau_evenements('articlepublie', 'rubrique', $ascendance_avec_actu, $tableau_multisite, false));						
				}
			}
				
			// Multirubricage
			$rub_multirubricage = cinotif_multirubricage($id_article);
			if ($rub_multirubricage)
				$evenements = array_merge($evenements,cinotif_tableau_evenements('articlepublie', 'rubrique', $rub_multirubricage, $tableau_multisite));
				
			
			// les abonnes de ces evenements
			$tableau_abonnes = cinotif_tableau_abonnes_evenement($evenements); 
			
			// enlever les destinataires de SPIP pour ne pas doublonner des envois
			// les emails des abonnes sont stockes en minuscules
			$tableau_id_destinataires = cinotif_id_abonnes('',$destinataires);
			$tableau_abonnes = array_diff($tableau_abonnes,$tableau_id_destinataires);
			
			// modele specifique
			$modele = "notifications/cinotif_article_publie";
			$texte = ci_email_notification_article($id_article, $modele);

			// envoyer
			if ($tableau_abonnes AND $texte) {
				$notification = array('quoi'=>'articlepublie',
									'objet'=> 'article',
									'id_objet'=> $id_article,
									'id_version'=> 0,
									'parent'=> 'rubrique',
									'id_parent'=> $id_rubrique,
									'texte'=>$texte,
									'sujet'=>'');
				cinotif_notifier($tableau_abonnes,$notification,$evenements);
			}


		// proposition d'un article
		} elseif ($quoi=='instituerarticle' AND $options['statut']=='prop' AND $options['statut_ancien']!='publie' 
					AND !cinotif_form_theme_ou_simple()) {

			// lister les abonnes qui sont des auteurs
			$evenements = cinotif_tableau_evenements('articlepropose', 'site', '', $tableau_multisite);
			$evenements = array_merge($evenements,  cinotif_tableau_evenements('articlepropose', 'rubrique', $ascendance, $tableau_multisite));

			// les id_auteur des abonnes de ces evenements
			$tableau_id_auteur_abonnes = cinotif_tableau_id_auteur_abonnes_evenement($evenements); 

			// enlever les visiteurs au sens SPIP car ils ne peuvent pas rentrer dans l'espace prive
			$tableau_id_auteur_abonnes_visiteur = array();
			$result = sql_select("id_auteur", "spip_auteurs", sql_in('id_auteur',$tableau_id_auteur_abonnes)." AND statut='6forum'");
			while ($row = sql_fetch($result))
				$tableau_id_auteur_abonnes_visiteur[] = $row['id_auteur'];
			
			if ($tableau_id_auteur_abonnes_visiteur)
				$tableau_id_auteur_abonnes = array_diff($tableau_id_auteur_abonnes,$tableau_id_auteur_abonnes_visiteur);

			// les admin restreints ne doivent voir que les notifications relatives a leurs rubriques
			if (!$article_ec) {
				
				// les abonnes qui peuvent etre restreints
				$tableau_id_auteur_statuts = array();
				$result = sql_select("id_auteur", "spip_auteurs", sql_in('id_auteur',$tableau_id_auteur_abonnes)." AND statut='0minirezo'");
				while ($row = sql_fetch($result))
					$tableau_id_auteur_statuts[] = $row['id_auteur'];
	
				// les abonnes qui sont restreints
				$tableau_id_auteur_restreints = array();
				if (spip_version()>=3)
					$result = sql_select("id_auteur", "spip_auteurs_liens", "objet='rubrique' AND ".sql_in('id_auteur',$tableau_id_auteur_statuts),"id_auteur","");
				else
					$result = sql_select("id_auteur", "spip_auteurs_rubriques", sql_in('id_auteur',$tableau_id_auteur_statuts),"id_auteur");

				while ($row = sql_fetch($result))
					$tableau_id_auteur_restreints[] = $row['id_auteur'];
					
				// les abonnes qui sont restreints aux rubriques concernees
				$tableau_id_auteur_restreints_ok = array();
				if (spip_version()>=3)
					$result = sql_select("id_auteur", "spip_auteurs_liens", "objet='rubrique' AND ".sql_in('id_auteur',$tableau_id_auteur_restreints)." AND ".sql_in('id_objet',$ascendance),"id_auteur","");
				else
					$result = sql_select("id_auteur", "spip_auteurs_rubriques", sql_in('id_auteur',$tableau_id_auteur_restreints)." AND ".sql_in('id_rubrique',$ascendance),"id_auteur");
				while ($row = sql_fetch($result))
					$tableau_id_auteur_restreints_ok[] = $row['id_auteur'];
				
				// les abonnes qui sont "restreints mais pas aux rubriques concernees"
				$tableau_id_auteur_restreints_pas_ok = array_diff($tableau_id_auteur_restreints,$tableau_id_auteur_restreints_ok);
				// les abonnes autres que ceux qui sont "restreints mais pas aux rubriques concernees"
				$tableau_id_auteur_abonnes = array_diff($tableau_id_auteur_abonnes,$tableau_id_auteur_restreints_pas_ok);
			}

			// les abonnes retenus
			$tableau_abonnes = array();
			$result = sql_select("id_abonne", "spip_cinotif_abonnes", sql_in('id_auteur',$tableau_id_auteur_abonnes), 'id_auteur');
			while ($row = sql_fetch($result))
				$tableau_abonnes[] = $row['id_abonne'];

			// enlever les destinataires de SPIP pour ne pas doublonner des envois
			// les emails des abonnes sont stockes en minuscules
			$tableau_id_destinataires = cinotif_id_abonnes('',$destinataires);
			$tableau_abonnes = array_diff($tableau_abonnes,$tableau_id_destinataires);
			
			// modele specifique
			$modele = "notifications/cinotif_article_propose";
			$texte = ci_email_notification_article($id_article, $modele);
			
			// envoyer
			if ($tableau_abonnes AND $texte) {
				$notification = array('quoi'=>'articlepropose',
									'objet'=> 'article',
									'id_objet'=> $id_article,
									'id_version'=> 0,
									'parent'=> 'rubrique',
									'id_parent'=> $id_rubrique,
									'texte'=>$texte,
									'sujet'=>'');
				cinotif_notifier($tableau_abonnes,$notification,$evenements);
			}
			
		}
	}
	
}


function ci_email_notification_article($id_article, $modele) {
	$envoyer_mail = charger_fonction('envoyer_mail','inc'); // pour nettoyer_titre_email

	return recuperer_fond($modele,array('id_article'=>$id_article));
}

?>