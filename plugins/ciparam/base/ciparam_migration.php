<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');


/**
 * Fonction de migration des mots cles techniques de forme, de raccourci et de tri
 *
 */
function ciparam_migration_mots_cles() {
	$rub_forme = array();
	$art_forme = array();
	$rub_raccourci = array();
	$art_raccourci = array();
	$site_raccourci = array();	
	$rub_tri = array();
	
	$rub_forme_ok = false;
	$art_forme_ok = false;
	$rub_raccourci_ok = false;
	$art_raccourci_ok = false;
	$site_raccourci_ok = false;	
	$rub_tri_ok = false;

	$nb_rejet_forme_article = 0;
	
	// formes de rubrique
	if (spip_version()>=3)
		$result = sql_select("motsrub.id_rubrique,mots.titre", "spip_mots_liens as motsrub, spip_mots as mots", "motsrub.objet='rubrique' AND motsrub.id_mot=mots.id_mot AND mots.type='_forme_rubrique'","","");
	else
		$result = sql_select("motsrub.id_rubrique,mots.titre", "spip_mots_rubriques as motsrub, spip_mots as mots", "motsrub.id_mot=mots.id_mot AND mots.type='_forme_rubrique'","","");

	while ($row = sql_fetch($result))	{
		$rub_forme[$row['id_rubrique']] = $row['titre'];
	}

	foreach ($rub_forme as $key=>$val) {
		sql_updateq("spip_rubriques", array("ciforme" => $val), "id_rubrique=$key");
	}

	if (sql_countsel('spip_rubriques', "ciforme<>''") >= count($rub_forme)) $rub_forme_ok = true;

	
	// formes d'article
	if (spip_version()>=3)
		$result = sql_select("motsart.id_objet as id_article,mots.titre", "spip_mots_liens as motsart, spip_mots as mots", "motsart.objet='article' AND motsart.id_mot=mots.id_mot AND mots.type='_forme_article'","","");
	else
		$result = sql_select("motsart.id_article,mots.titre", "spip_mots_articles as motsart, spip_mots as mots", "motsart.id_mot=mots.id_mot AND mots.type='_forme_article'","","");
	
	while ($row = sql_fetch($result))	{
		$art_forme[$row['id_article']] = $row['titre'];
	}

	foreach ($art_forme as $key=>$val) {
		// la forme d'article "url" a été remplacé par un traitement
		if ($val=='_url')
			$nb_rejet_forme_article = 1;
		else
			sql_updateq("spip_articles", array("ciforme" => $val), "id_article=$key");
	}

	if ((sql_countsel('spip_articles', "ciforme<>''") + $nb_rejet_forme_article) >= count($art_forme))
		$art_forme_ok = true;
	
	
	// raccourcis de rubrique
	if (spip_version()>=3)
		$result = sql_select("motsrub.id_objet as id_rubrique,mots.titre", "spip_mots_liens as motsrub, spip_mots as mots", "motsrub.objet='rubrique' AND motsrub.id_mot=mots.id_mot AND mots.type='_affichage_rubrique'","","");
	else
		$result = sql_select("motsrub.id_rubrique,mots.titre", "spip_mots_rubriques as motsrub, spip_mots as mots", "motsrub.id_mot=mots.id_mot AND mots.type='_affichage_rubrique'","","");

	while ($row = sql_fetch($result))	{
		$rub_raccourci[] = array('id_rubrique' => $row['id_rubrique'], 'raccourci' => $row['titre']);
	}

	foreach ($rub_raccourci as $valeur) {
		$id = sql_insertq("spip_ci_raccourcis_rubriques", array(
		'id_rubrique' => $valeur['id_rubrique'],
		'raccourci' =>  $valeur['raccourci']));
	}

	if (sql_countsel('spip_ci_raccourcis_rubriques', "") >= count($rub_raccourci)) $rub_raccourci_ok = true;

	
	// raccourcis d'article
	if (spip_version()>=3)
		$result = sql_select("motsart.id_article,mots.titre", "spip_mots_liens as motsart, spip_mots as mots", "motsart.objet='article' AND motsart.id_mot=mots.id_mot AND mots.type='_affichage_article'","","");
	else
		$result = sql_select("motsart.id_article,mots.titre", "spip_mots_articles as motsart, spip_mots as mots", "motsart.id_mot=mots.id_mot AND mots.type='_affichage_article'","","");

	while ($row = sql_fetch($result))	{
		$art_raccourci[] = array('id_article' => $row['id_article'], 'raccourci' => $row['titre']);
	}

	foreach ($art_raccourci as $valeur) {
		$id = sql_insertq("spip_ci_raccourcis_articles", array(
		'id_article' => $valeur['id_article'],
		'raccourci' =>  $valeur['raccourci']));
	}

	if (sql_countsel('spip_ci_raccourcis_articles', "") >= count($art_raccourci)) $art_raccourci_ok = true;


	// raccourcis de site référencé
	if (spip_version()>=3)
		$result = sql_select("motssite.id_objet as id_syndic,mots.titre", "spip_mots_liens as motssite, spip_mots as mots", "motssite.objet='site' AND motssite.id_mot=mots.id_mot AND mots.type='_affichage_site_ou_syndication'","","");
	else
		$result = sql_select("motssite.id_syndic,mots.titre", "spip_mots_syndic as motssite, spip_mots as mots", "motssite.id_mot=mots.id_mot AND mots.type='_affichage_site_ou_syndication'","","");

	while ($row = sql_fetch($result))	{
		$site_raccourci[] = array('id_syndic' => $row['id_syndic'], 'raccourci' => $row['titre']);
	}

	foreach ($site_raccourci as $valeur) {
		$id = sql_insertq("spip_ci_raccourcis_syndic", array(
		'id_syndic' => $valeur['id_syndic'],
		'raccourci' =>  $valeur['raccourci']));
	}

	if (sql_countsel('spip_ci_raccourcis_syndic', "") >= count($site_raccourci)) $site_raccourci_ok = true;


	// Ordre de tri des rubriques
	// formes de rubrique
	if (spip_version()>=3)
		$result = sql_select("motsrub.id_objet as id_rubrique,mots.titre", "spip_mots_liens as motsrub, spip_mots as mots", "motsrub.objet='rubrique' AND motsrub.id_mot=mots.id_mot AND mots.type='_articles_ordre_de_tri'","","");
	else
		$result = sql_select("motsrub.id_rubrique,mots.titre", "spip_mots_rubriques as motsrub, spip_mots as mots", "motsrub.id_mot=mots.id_mot AND mots.type='_articles_ordre_de_tri'","","");

	while ($row = sql_fetch($result))	{
		$rub_tri[$row['id_rubrique']] = $row['titre'];
	}

	foreach ($rub_tri as $key=>$val) {
		$valeurtri = '';
		$valeurtriinverse = '';
		$valeurtrinum = '';
		
		switch($val) {
	
		case '_par_date_croissante':
			$valeurtri = 'date';
			break;
	
		case '_par_date_decroissante':
			$valeurtriinverse = 'date';
			break;

		case '_par_numero_de_titre':
			$valeurtrinum = 'titre';
			break;

		case '_par_ordre_alphabetique':
			$valeurtri = 'titre';
			break;
		}

		sql_updateq("spip_rubriques", array('citri'=>$valeurtri,'citrinum'=>$valeurtrinum,'citriinverse'=>$valeurtriinverse), "id_rubrique=$key");
		
	}
	
	if (sql_countsel('spip_rubriques', "citri<>'' OR citrinum<>'' OR citriinverse<>''") >= count($rub_tri)) $rub_tri_ok = true;


	// suppression complete des mots cles techniques de forme, de raccourci et de tri
	// si la migration correspondante a aboutie
	if ($rub_forme_ok)
		ciparam_supprimer_groupe_mots('_forme_rubrique');

	if ($art_forme_ok)
		ciparam_supprimer_groupe_mots('_forme_article');

	if ($rub_raccourci_ok)
		ciparam_supprimer_groupe_mots('_affichage_rubrique');

	if ($art_raccourci_ok)
		ciparam_supprimer_groupe_mots('_affichage_article');
	
	if ($site_raccourci_ok)	
		ciparam_supprimer_groupe_mots('_affichage_site_ou_syndication');
	
	if ($rub_tri_ok)
		ciparam_supprimer_groupe_mots('_articles_ordre_de_tri');

	return true;
}


// suppression un groupe de mots, ses mots cles et leurs relations
function ciparam_supprimer_groupe_mots($groupe) {
	
	$result = sql_select("id_mot", "spip_mots", "type='".$groupe."'","","");		
	while ($row = sql_fetch($result))	{
		$mots[] = $row['id_mot'];
	}
	
	if (isset($mots)) {
		$where = "id_mot IN (" . join(',', $mots) . ")";

		if (spip_version()>=3) {
			sql_delete("spip_mots_liens", "objet='article' AND ".$where);
			sql_delete("spip_mots_liens", "objet='rubrique' AND ".$where);
			sql_delete("spip_mots_liens", "objet='site' AND ".$where);
		} else {
			sql_delete("spip_mots_articles", $where);
			sql_delete("spip_mots_rubriques", $where);
			sql_delete("spip_mots_syndic", $where);
		}
		
		sql_delete("spip_mots", $where);
		sql_delete("spip_groupes_mots", "titre='$groupe'");
	}

	return true;
}

?>