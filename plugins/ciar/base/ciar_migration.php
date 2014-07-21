<?php
/**
 * Plugin Acces restreints Giseh
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');

/**
 * Fonction de migration des mots cles techniques d'acces restreints
 */
function ciar_migration_mots_cles() {
	$rub_ar = array();
	$rub_ar_ok = false;
	
	// acces restreints
	if (spip_version()>=3) {
		$result = sql_select("motsrub.id_objet,mots.titre", "spip_mots_liens as motsrub, spip_mots as mots", "objet='rubrique' AND motsrub.id_mot=mots.id_mot AND mots.type='_acces_restreint'","","");
		while ($row = sql_fetch($result))
			$rub_ar[$row['id_objet']] = $row['titre'];
	} else {
		$result = sql_select("motsrub.id_rubrique,mots.titre", "spip_mots_rubriques as motsrub, spip_mots as mots", "motsrub.id_mot=mots.id_mot AND mots.type='_acces_restreint'","","");
		while ($row = sql_fetch($result))
			$rub_ar[$row['id_rubrique']] = $row['titre'];
	}

	foreach ($rub_ar as $key=>$val) {
		$id = sql_insertq("spip_ciar_rubriques_protection", array(
		'id_rubrique' => $key,
		'acces_restreint' => $val));
	}

	if (sql_countsel('spip_ciar_rubriques_protection', "acces_restreint<>''") >= count($rub_ar)) $rub_ar_ok = true;
	
	// suppression complete des mots cles techniques d'acces restreints
	// si la migration correspondante a aboutie
	if ($rub_ar_ok)
		ciar_supprimer_groupe_mots('_acces_restreint');

	return true;
}


/**
 * Supprime un groupe de mots, ses mots cles et leurs relations
 */
function ciar_supprimer_groupe_mots($groupe) {
	
	$result = sql_select("id_mot", "spip_mots", "type='".$groupe."'","","");		
	while ($row = sql_fetch($result))	{
		$mots[] = $row['id_mot'];
	}
	
	if (isset($mots)) {
		$where = "id_mot IN (" . join(',', $mots) . ")";
		if (spip_version()>=3)
			sql_delete("spip_mots_liens", "objet='rubrique' AND ".$where);
		else
			sql_delete("spip_mots_rubriques", $where);
		
		sql_delete("spip_mots", $where);
		sql_delete("spip_groupes_mots", "titre='$groupe'");
	}

	return true;
}


/**
 * Fonction de migration des affectations d'auteurs aux EC
 * Attention : le faire apres la migration des mots-cles et apres la migration des infos auteurs
 */
function ciar_migration_affectation_ec() {

	// pour ceux qui ne respectent pas le guide
	// Migrer sous SPIP 2.0 un MULTI sites GISEH
	if (sql_countsel('spip_ciar_auteurs_acces_rubriques')>0)
		return true;


	$rubriques_ec = array();
	$result = sql_select("id_rubrique", "spip_ciar_rubriques_protection", "acces_restreint='_acces_indiv'","id_rubrique","");
	while ($row = sql_fetch($result)) {
		$rubriques_ec[] = $row['id_rubrique'];
	}

	$table_ci_droit_exist = false;
	$res = sql_showbase("spip_ci_droit_rubriques");
	if ($res)
		if (sql_fetch($res))
			$table_ci_droit_exist = true;

	if ($rubriques_ec) {
		$liste_ec = "(".implode(",",$rubriques_ec).")";
		$liens = array();
		$auteurs = array();

		// Dupliquer les liens de spip_auteurs_rubriques, concernant les EC, dans spip_ciar_auteurs_acces_rubriques ...
		if (spip_version()>=3)
			$result = sql_select("*", "spip_auteurs_liens", "objet='rubrique' AND id_objet IN ".$liste_ec,"","");
		else
			$result = sql_select("*", "spip_auteurs_rubriques", "id_rubrique IN ".$liste_ec,"","");
		while ($row = sql_fetch($result)) {
			$liens[] = array('id_rubrique'=>$row['id_rubrique'],'id_auteur'=>$row['id_auteur']);
			$auteurs[] = $row['id_auteur'];
		}

		// statut particulier (administrateur eccma ou ecadminsite)
		$statut_particulier = array();
		if ($auteurs) {
			$liste_auteurs = "(".implode(",",$auteurs).")";
			$result = sql_select("*", "spip_auteurs", "statut='0minirezo' AND cioption IN ('eccma','ecadminsite') AND id_auteur IN ".$liste_auteurs,"","");
			while ($row = sql_fetch($result)) {
				$statut_particulier[$row['id_auteur']] = $row['id_auteur'];
			}
		}		
		
		// ... avec les surcharges de statut dans un EC		
		$surcharges = array();
		if ($table_ci_droit_exist) {
			$result = sql_select("*", "spip_ci_droit_rubriques", "","","");
			while ($row = sql_fetch($result)) {
				$surcharges[$row['id_rubrique']."_".$row['id_auteur']] = $row['statut'];
			}
		}
	
		
		foreach ($liens as $val) {
			if (isset($surcharges[$val['id_rubrique']."_".$val['id_auteur']])) {
				$surcharge = $surcharges[$val['id_rubrique']."_".$val['id_auteur']];
			} else {
				$surcharge = "";
				if (isset($statut_particulier[$val['id_auteur']]))
					$surcharge = "eccma";
			}

			sql_insertq("spip_ciar_auteurs_acces_rubriques", array(
			'id_rubrique' => $val['id_rubrique'],
			'id_auteur' => $val['id_auteur'],
			'cistatut_auteur_rub' => $surcharge));		
		}
			
	}

	// Cas des « ecadminsite » : enlever les liens dans spip_auteurs_rubriques
	$ecadminsite = array();
	$result = sql_select("id_auteur", "spip_auteurs","cioption='ecadminsite'","","");	
	while ($row = sql_fetch($result))
		$ecadminsite[] = $row['id_auteur'];
			
	if ($ecadminsite) {
		$liste_ecadminsite = "(".implode(",",$ecadminsite).")";
		if (spip_version()>=3)
			sql_delete("spip_auteurs_liens", "objet='rubrique' AND id_auteur IN ".$liste_ecadminsite);
		else
			sql_delete("spip_auteurs_rubriques", "id_auteur IN ".$liste_ecadminsite);
	}

	// supprimer la table spip_ci_droit_rubriques
	if ($table_ci_droit_exist)
		sql_drop_table("spip_ci_droit_rubriques");		
	
	
	return true;	
}
	

/**
 * Fonction de migration des informations additionnelles des auteurs
 */
function ciar_migration_info_auteurs() {
	
	$result = sql_select("id_auteur,statut,pgp", "spip_auteurs", "","","");		
	while ($row = sql_fetch($result))	{
		$cioption = "";
		$pgp = $row['pgp'];
		$id_auteur = $row['id_auteur'];
		
		if ($pgp) {
			if (ciar_lirepgp($pgp, 'ecadminsite')=='ecadminsite')
				$cioption = "ecadminsite";
			elseif (ciar_lirepgp($pgp, 'eccma')=='eccma')
				$cioption = "eccma";
			// subtilite : 	ecadminrestreint est dans la balise ecadminsite
			elseif (ciar_lirepgp($pgp, 'ecadminsite')=='ecadminrestreint')
				$cioption = "ecadminrestreint";
			elseif (ciar_lirepgp($pgp, 'ecredacnr')=='ecredacnr')
				$cioption = "ecredacnr";
		}

		// migration du statut de redacteur valideur
		if ($row['statut']=='ciredval')
			sql_updateq("spip_auteurs", array("statut" => "1comite", "cistatut" => "ciredval"), "id_auteur=$id_auteur");

		// migration des options
		if ($cioption)
			sql_updateq("spip_auteurs", array("cioption" => $cioption, "pgp" => ""), "id_auteur=$id_auteur");
		// vider les tags d'un auteur sans option	
		elseif (ciar_lirepgp($pgp, 'ecadminsite')=='non')
			sql_updateq("spip_auteurs", array("pgp" => ""), "id_auteur=$id_auteur");

	}
	return true;	
}


/**
 * Lecture des options stockees dans le champ PGP
 */
function ciar_lirepgp($texte,$cle){
	$value=false;
	if($texte AND $cle){	
		$tagdebut="<".$cle.">";
		$posdebut = strpos($texte, $tagdebut);
		if ($posdebut === false){
			$value=false;
		} else {	 		
			$tagfin="</".$cle.">";
			$posdebut = $posdebut + strlen($cle) +2;
			$longval = strpos($texte, $tagfin)-$posdebut;
			$value=substr($texte,$posdebut,$longval);
		}
	}	
	return $value;
}

?>