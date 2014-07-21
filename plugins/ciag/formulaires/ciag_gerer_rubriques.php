<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/ciag_commun');


function formulaires_ciag_gerer_rubriques_charger_dist($id_groupe,$retour='', $config_fonc='', $row=array(), $hidden='')
{		
	$id_groupe = intval($id_groupe);
	$valeurs['id_groupe'] = $id_groupe;
	$valeurs['tableau_rubriques'] = array();
	$valeurs['liste_rubriques'] = "";

	if (!autoriser('modifier','groupeauteur',$id_groupe))
		return false;


	// rubriques actuelles de cette rubrique
	if ($id_groupe>0) {
		$oldrubriques = array();	
		$result = sql_select("id_rubrique", "spip_ciag_grpauteurs_rubriques", "id_groupe=$id_groupe","","id_rubrique");
		while ($row = sql_fetch($result)) { 
			$oldrubriques[] = $row['id_rubrique'];
		}
		
		if ($oldrubriques) {
			$valeurs['tableau_rubriques'] = $oldrubriques;
			$valeurs['liste_rubriques'] = implode(",", $oldrubriques);
		}
	}

	$valeurs['_hidden'] = "<input type='hidden' name='id_groupe' value='".$id_groupe."' />"
						. "<input type='hidden' name='ciag_grpauteurs_img_avant' value='".$valeurs['liste_rubriques']."' />";

	
	return $valeurs;
}

function formulaires_ciag_gerer_rubriques_verifier_dist($id_groupe,$retour='', $config_fonc='', $row=array(), $hidden='')
{
	return $erreurs;
}

function formulaires_ciag_gerer_rubriques_traiter_dist($id_groupe,$retour='', $config_fonc='', $row=array(), $hidden='')
{
	$rubriques = _request('rubriques');
	$ctrl_concurrent = true;
	$image_avant = _request('ciag_grpauteurs_img_avant');
	$ciauteurs_concurrent = !ciag_modifier_rubriques_dans_grpauteurs($rubriques,$id_groupe,$ctrl_concurrent,$image_avant);

	if ($ciauteurs_concurrent) {
		$res['message_erreur'] = _T("alerte_modif_info_concourante");
	} else {
		$res['message_ok'] = "";
		$res['redirect'] = generer_url_ecrire("ciag_groupe_auteurs","id_groupe=$id_groupe");
	}

	return $res;	
}


function ciag_lister_rubriques($msg, $id_groupe, $idem=0) {
	global $oldrubriques;
	$data = array();
	$type = 'auteur';
	$rubriques = array();	
	$result = sql_select("id_rubrique", "spip_ciag_grpauteurs_rubriques", "id_groupe=$id_groupe","","id_rubrique");
	while ($row = sql_fetch($result)) { 
		$rubriques[] = $row['id_rubrique'];
	}
	$oldrubriques = $rubriques;
	
	// si plugin ciar, tenir compte des EC
	$rub_interdites = array();
	$ec_non_gere = array();
	$rub_admin_restreint = array();
	
	if (defined('_DIR_PLUGIN_CIAR')){
		include_spip('ciar_fonctions');
		
		// tableau des rubriques EC non geres
		$ec_non_gere = ciar_tableau_ec_non_gere();
		$rub_interdites = ciar_descendance($ec_non_gere);
	}
	
	// rubriques si on est admin restreint, redacteur restreint
	$rubriques_auteur_restreint = liste_rubriques_auteur($GLOBALS['visiteur_session']['id_auteur']);
	
	// creer une structure contenant toute l'arborescence
	include_spip('base/abstract_sql');
	$where = '';
	if ($rubriques_auteur_restreint)
		$where = sql_in('id_rubrique',$rubriques_auteur_restreint);

	if ($rub_interdites)
		$where = $where.(($where) ? ' AND ' : '').sql_in('id_rubrique',$rub_interdites, 'NOT');

	$q = sql_select("id_rubrique, id_parent, titre, statut, lang, langue_choisie", "spip_rubriques", $where, '', "0+titre,titre");
	while ($r = sql_fetch($q)) {
		// titre largeur maxi a 50
		$titre = supprimer_tags(typo(extraire_multi($r['titre'])));
		$data[$r['id_rubrique']] = $titre;
		$enfants[$r['id_parent']][] = $r['id_rubrique'];
		if (in_array($r['id_rubrique'],$rubriques)) $id_parent = $r['id_parent'];
	}

	// cas des administrateurs restreints
	if (spip_version()>=3)
		$result = sql_select("id_objet AS id_rubrique", "spip_auteurs_liens", "objet='rubrique' AND id_auteur=".intval($GLOBALS['visiteur_session']['id_auteur']),"","");
	else
		$result = sql_select("id_rubrique","spip_auteurs_rubriques", "id_auteur=".intval($GLOBALS['visiteur_session']['id_auteur']));
	
	while ($row = sql_fetch($result)) {
		if (!in_array($row['id_rubrique'],$rub_interdites))
			$rub_admin_restreint[] = $row['id_rubrique'];
	}

	if ($rub_admin_restreint) {		
		foreach ($rub_admin_restreint AS $id_rub_admin_restreint) {
			// cas ou un admin restreint est affecte a deux rubriques dont l'une descend de l'autre
			$ascendants = ciag_ascendance_strict($id_rub_admin_restreint);
			if ($ascendants)
				$cas = array_intersect($ascendants,$rub_admin_restreint);
			else
				$cas = false;

			if (!$cas)	
				$opt .= ciag_sous_menu_rubriques($rubriques,$id_rub_admin_restreint, 0,$data,$enfants,$idem);
		}
	} else {	
		$opt = ciag_sous_menu_rubriques($rubriques,0, 0,$data,$enfants,$idem);
	}


	// mettre en hidden les rubriques cochees non affichees	
	foreach ($oldrubriques AS $id_hidden)
		$hide = '<input type="hidden" name="rubriques[]" value="'.$id_hidden.'" checked="checked" >';	

	$r = $opt.$hide;
	
	return $r;
}

function ciag_sous_menu_rubriques($rubriques, $root, $niv, &$data, &$enfants, $exclus) {
	global $browser_name, $browser_version, $oldrubriques;
	static $decalage_secteur;
	
	// Si on a demande l'exclusion ne pas descendre dans la rubrique courante
//	if ($exclus > 0
//	AND $root == $exclus) return '';

	// selected ?
	$ok = in_array($root,$rubriques);
	$selected = ($ok) ? 'class="selected"' : '';
	$checked = ($ok) ? 'checked="checked"' : '';
	$gras = ($ok) ? ' on' : '';

	$disabled = ($exclus) ? 'disabled="disabled"' : '';
	
	if (defined('_DIR_PLUGIN_CIAR')){
		if (ciar_rub_ec_direct($root))
			$disabled = '';
	}
	
	// a placer apres
	if ($ok) {
		$exclus = $ok;
		$oldrubriques = array_diff($oldrubriques,array($root));
	}
		
	if (spip_version()>=3)
		$marge = "margin-left:20px;";
	else
		$marge = "";
		
	switch ($niv) {
	case 0:
		$ulclass = "style='list-style-type:none;$marge'";
		$divclass = "";
		break;
	case 1:
		$ulclass = "style='list-style-type:none;$marge'";
		$divclass = "plansecteur";
		break;
	case 2:
		$ulclass = "style='list-style-type:none;$marge'";
		$divclass = "planrubniv1";
		break;
	default:
		$ulclass = "style='list-style-type:none;$marge'";
		$divclass = "planrub";
		break;
	}
	


	if (isset($data[$root])) # pas de racine sauf pour les rubriques
	{
		$r = '<li style="list-style-type:none;"><div '.$selected.'><input type="checkbox" id="rub'.$root.'" name="rubriques[]" value="'.$root.'" '.$checked.' '.$disabled.' ><label for="rub'.$root.'" class="texte'.$gras.'"> '.$data[$root].'</label></div>';
	} else 	$r = '';
	
	
	// et le sous-menu pour ses enfants
	$sous = '';
	if (isset($enfants[$root]))
		foreach ($enfants[$root] as $sousrub)
			$sous .= ciag_sous_menu_rubriques($rubriques, $sousrub,
				$niv+1, $data, $enfants, $exclus);

	if ($sous) {
		$sous = "<ul ".$ulclass.">".$sous."</ul>";
		if ($niv>0)
			$sous .= "</li>";
	} else {
		$sous = "</li>";
	}
				

	return $r.$sous;
}


function ciag_ascendance_strict($id) {
	$return = array(0);
	
	if ($id) {
		// normaliser $id qui a pu arriver comme un array, comme un entier, ou comme une chaine NN,NN,NN
		if (!is_array($id)) $id = explode(',',$id);
		$id = join(',', array_map('intval', $id));
	
		// Notre branche NE commence PAS par la rubrique de depart
		$branche = '';
	
		// On ajoute une generation (les parents de la generation precedente)
		// jusqu'a epuisement
		while ($parents = sql_allfetsel('id_parent', 'spip_rubriques',
		sql_in('id_rubrique', $id))) {
			$id = join(',', array_map('array_shift', $parents));
			$branche .= ($branche ? ',' : '') . $id;
		}
		
		$return = explode(',',$branche);
	}

	return $return;
}

?>