<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/ciar_commun');


function formulaires_ciar_retirer_acces_auteurs_charger_dist($id_rubrique, $pagination, $debut_auteur,$retour='', $config_fonc='', $row=array(), $hidden='')
{	
	$id_rubrique = intval($id_rubrique);
	$valeurs['id_rubrique'] = $id_rubrique;	
	$valeurs['tableau_auteurs'] = array();
	$valeurs['liste_auteurs'] = "";
	$valeurs['heritage_auteurs'] = array();
	$valeurs['ci_pagination'] = $pagination;

	if (!autoriser('ecmodifier','rubrique',$id_rubrique))
		return false;
	
	
	// auteurs ayant actuellement acces a cette rubrique
	if ($id_rubrique>0) {
		$oldauteurs = ciar_liste_retirer_auteurs_acces_rubrique($id_rubrique,$pagination,$debut_auteur);	
		if ($oldauteurs) {
			$valeurs['tableau_auteurs'] = $oldauteurs;
			$valeurs['liste_auteurs'] = implode(",", $oldauteurs);
		}
	}

	if (defined('_DIR_PLUGIN_CIAG')){
		include_spip('inc/ciag_commun');
		$heritage_auteurs = ciag_liste_auteurs_des_grpauteurs(ciag_liste_grpauteurs_de_rubrique($id_rubrique));
		if ($heritage_auteurs)
			$valeurs['heritage_auteurs'] = $heritage_auteurs;
	}

	$valeurs['_hidden'] = "<input type='hidden' name='id_rubrique' value='".$id_rubrique."' />"
						. "<input type='hidden' name='cimodif_img_avant' value='".$valeurs['liste_auteurs']."' />";
	
	return $valeurs;
}

function formulaires_ciar_retirer_acces_auteurs_verifier_dist($id_rubrique, $pagination, $debut_auteur,$retour='', $config_fonc='', $row=array(), $hidden='')
{
	return $erreurs;
}

function formulaires_ciar_retirer_acces_auteurs_traiter_dist($id_rubrique, $pagination, $debut_auteur,$retour='', $config_fonc='', $row=array(), $hidden='')
{
	// si id_rubrique est un nombre
	if ($id_rubrique = intval($id_rubrique)) {
	
		$auteurs = _request('auteurs');
		$ci_auteurs_img_avant = _request('cimodif_img_avant');
		
		if (!$auteurs) $auteurs = array();
		$oldauteurs = array();
		
		// auteurs ayant actuellement acces a cette rubrique
		$oldauteurs = ciar_liste_retirer_auteurs_acces_rubrique($id_rubrique,$pagination,$debut_auteur);	

		if (autoriser('ecmodifier','rubrique',$id_rubrique)) {
			// Prévention des accès concurents
			$ci_auteurs_img_base = "";
			if ($oldauteurs) $ci_auteurs_img_base = implode(",", $oldauteurs);
			$ciauteurs_concurrent=false;
			if ($ci_auteurs_img_avant) {
				if ($ci_auteurs_img_base) {
					if (!($ci_auteurs_img_avant==$ci_auteurs_img_base)) $ciauteurs_concurrent=true;
				} else {
					$ciauteurs_concurrent=true;
				}		
			} elseif ($ci_auteurs_img_base) $ciauteurs_concurrent=true;
		
			if (!$ciauteurs_concurrent) {		
				if (isset($auteurs) AND is_array($auteurs)) {
					ciar_retirer_auteurs_dans_ec($auteurs,$id_rubrique);
				}
	
			}
		}
			 
	}

	if ($ciauteurs_concurrent) {
		$res['message_erreur'] = _T("alerte_modif_info_concourante");
	} else {
		$res['message_ok'] = "";
		$res['redirect'] = generer_url_ecrire("ciar_rubrique_protection","id_rubrique=$id_rubrique");
	}

	return $res;	
}

?>