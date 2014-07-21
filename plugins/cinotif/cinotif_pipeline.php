<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

include_spip('inc/cinotif_commun');
 
 
/**
 * Ajoute un bloc dans la page auteurs
 * ajoute un bloc dans la page naviguer
 *
 * @param array
 * @return array
 */
function cinotif_affiche_gauche($flux) {
	$exec = $flux["args"]["exec"];

	if (spip_version()>=3) {
		include_spip('inc/presentation');
		include_spip('inc/filtres_boites');
				
		if ($exec == "auteurs") {
			$ret = boite_ouvrir('', 'raccourcis');
			$ret .= icone_horizontale(_T('cinotif:mesabonnements'), generer_url_ecrire("cinotif_mesabonnements", ''), "article-24.png", "", false);
			$ret .= boite_fermer();
			if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
				$flux['data'] = substr_replace($flux['data'],$ret,$p,0);
			else
				$flux["data"] .= $ret;
		}

		if ($en_cours = trouver_objet_exec($flux['args']['exec']) AND $en_cours['type']=='rubrique'){
	      	$id_rubrique = $flux["args"]["id_rubrique"];
	      	if (autoriser('modifier', 'rubrique', $id_rubrique)) {				
				$ret = boite_ouvrir('', 'raccourcis');
				$ret .= icone_horizontale(_T('cinotif:abonnements'), generer_url_ecrire("cinotif_suiviabonnements_rubrique", "id_rubrique=$id_rubrique"), "article-24.png", "", false);
				$ret .= boite_fermer();
				if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
					$flux['data'] = substr_replace($flux['data'],$ret,$p,0);
				else
					$flux["data"] .= $ret;
	      	}
		}
		
	} else {	
		if ($exec=="auteurs") {
			$flux["data"] .= bloc_des_raccourcis(icone_horizontale(_T('cinotif:mesabonnements'), generer_url_ecrire("cinotif_mesabonnements", ''), "breve-24.gif", "", false));
		}
		
		if ($exec=="naviguer") {
	      	$id_rubrique = $flux["args"]["id_rubrique"];
	      	if ($id_rubrique AND autoriser('modifier', 'rubrique', $id_rubrique)) {
				$ret = icone_horizontale(_T('cinotif:abonnements'), generer_url_ecrire("cinotif_suiviabonnements_rubrique","id_rubrique=$id_rubrique"), "breve-24.gif", "",false);
				$flux["data"] .= bloc_des_raccourcis($ret);
	      	}
		}		
	}

	return $flux;
}
 
 
/**
 * Si l'email d'un auteur est modifie dans SPIP
 * repercuter ce changement
 * 
 * @param <type> $flux
 * @return <type> 
 */
function cinotif_pre_edition($flux){
    if ($flux['args']['spip_table_objet']=='spip_auteurs' AND $flux['args']['action']=='modifier') {	    	
		$id_auteur = $flux['args']['id_objet'];
		if ($id_auteur AND isset($flux['data']['email'])) {
			$email = strtolower($flux['data']['email']);
			$row = sql_fetsel("email", "spip_auteurs", "id_auteur=".intval($id_auteur));
			$old_email = strtolower($row['email']);
			$champs = array('email' => $email, 'hash_email' => md5($email));

			if ($email AND $email!=$old_email)
				sql_updateq("spip_cinotif_abonnes", $champs, "id_auteur=".intval($id_auteur));
			if ($email AND $old_email AND $email!=$old_email)
				sql_updateq("spip_cinotif_abonnes", $champs, "hash_email='".md5($old_email)."'");
		}
    }
	
	return $flux;
}
 
 
/**
 * Cas sans notification par defaut : article modifie, document ajoute, etc.
 *
 * @param array $tableau
 * @return array
 */
function cinotif_post_edition($tableau) {

	// article modifie
	if (isset($tableau['args']['action']) AND $tableau['args']['action']=='modifier' 
		AND isset($tableau['args']['table_objet']) AND $tableau['args']['table_objet']=='articles') {
			$notifications = charger_fonction('notifications','inc');
			$notifications('articlemodifie',$tableau['args']['id_objet'],array());
	}

	// document ajoute
	if (isset($tableau['args']['operation']) AND $tableau['args']['operation']=='ajouter_document' 
		AND ((isset($tableau['args']['table']) AND $tableau['args']['table']=='spip_documents') 
			OR (isset($tableau['args']['table_objet']) AND $tableau['args']['table_objet']=='documents'))) {
			$notifications = charger_fonction('notifications','inc');
			$notifications('documentajoute',$tableau['args']['id_objet'],array());
	}

	return $tableau;
}



/**
 * Optimiser la base de donnee en supprimant les abonnements orphelins
 * et en supprimant les vieux abonnements non confirmes
 *
 * @param int $n
 * @return int
 */
function cinotif_optimiser_base_disparus($flux){

	$madate = date("YmdHis", time() - (24 * 3600));
	
	// les evenements d'articles effaces
	$result = sql_select("lien.id_evenement AS id",
		    "spip_cinotif_evenements AS lien LEFT JOIN spip_articles AS articles
		          ON lien.id_objet=articles.id_article",
			"lien.objet='article' AND articles.id_article IS NULL");

	// on les supprime
	while ($row = sql_fetch($result)) {
		sql_delete("spip_cinotif_abonnements", "id_evenement=".$row['id']);
		sql_delete("spip_cinotif_evenements", "id_evenement=".$row['id']);
	}

	// les evenements de rubriques effaces
	$result = sql_select("lien.id_evenement AS id",
		    "spip_cinotif_evenements AS lien LEFT JOIN spip_rubriques AS rubriques
		          ON lien.id_objet=rubriques.id_rubrique",
			"lien.objet='rubrique' AND rubriques.id_rubrique IS NULL");

	// on les supprime
	while ($row = sql_fetch($result)) {
		sql_delete("spip_cinotif_abonnements", "id_evenement=".$row['id']);
		sql_delete("spip_cinotif_evenements", "id_evenement=".$row['id']);
	}

	// les abonnes d'auteurs à la poubelle depuis plus de 24 heures
	$result = sql_select("lien.id_abonne AS id",
		    "spip_cinotif_abonnes AS lien LEFT JOIN spip_auteurs AS auteurs
		          ON lien.id_auteur=auteurs.id_auteur",	"auteurs.statut='5poubelle' AND auteurs.maj<".$madate);

	// on les supprime
	while ($row = sql_fetch($result)){
		sql_delete("spip_cinotif_abonnements", "id_abonne=".$row['id']);
		sql_delete("spip_cinotif_abonnes", "id_abonne=".$row['id']);
	}

	// supprimer les abonnements non confirmes, vieux de plus de 24 heures
	sql_delete("spip_cinotif_abonnements", "statut='prop' AND maj<".$madate);

	
	// les envois en instance dont l'abonne n'existe plus
	$result = sql_select("tmp.id_abonne AS id",
		    "spip_cinotif_tmp AS tmp LEFT JOIN spip_cinotif_abonnes AS lien
		          ON tmp.id_abonne=lien.id_abonne",	"lien.id_abonne IS NULL");

	// on les supprime
	while ($row = sql_fetch($result))
		sql_delete("spip_cinotif_tmp", "id_abonne=".$row['id']);
	

	// les courriers en instance dont les envois n'existent plus
	$result = sql_select("cour.id_courrier AS id",
		    "spip_cinotif_courriers AS cour LEFT JOIN spip_cinotif_tmp AS lien
		          ON cour.id_courrier=lien.id_courrier", "cour.statut<>'publie' AND lien.id_courrier IS NULL");

	// on change le statut
	$tableau_update['statut'] = 'publie';
	while ($row = sql_fetch($result))
		sql_updateq("spip_cinotif_courriers", $tableau_update, "id_courrier=".$row['id']);
		
		
	// les abonnements dont l'abonne n'existe plus
	$result = sql_select("abont.id_abonne AS id",
		    "spip_cinotif_abonnements AS abont LEFT JOIN spip_cinotif_abonnes AS lien
		          ON abont.id_abonne=lien.id_abonne", "lien.id_abonne IS NULL");

	// on les supprime
	while ($row = sql_fetch($result))
		sql_delete("spip_cinotif_abonnements", "id_abonne=".$row['id']);
		

	// les evenements sans abonnements
	cinotif_suppr_evenements_sans_abonnement();

	// les abonnes sans abonnements
	cinotif_suppr_abonnes_sans_abonnement();
		

    return $flux;
}	

?>