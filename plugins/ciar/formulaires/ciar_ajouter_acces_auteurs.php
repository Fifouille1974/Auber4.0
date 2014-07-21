<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/ciar_commun');


function formulaires_ciar_ajouter_acces_auteurs_charger_dist($id_rubrique, $pagination, $debut_auteur, $retour='', $config_fonc='', $row=array(), $hidden='')
{		
	$id_rubrique = intval($id_rubrique);
	$valeurs['id_rubrique'] = $id_rubrique;
	$valeurs['tableau_auteurs'] = array();
	$valeurs['liste_auteurs'] = "";
	$valeurs['ci_pagination'] = $pagination;

	if (!autoriser('ecmodifier','rubrique',$id_rubrique))
		return false;


	// auteurs ayant actuellement acces a cette rubrique
	if ($id_rubrique>0) {
		$oldauteurs = ciar_liste_auteurs_acces_rubrique($id_rubrique,$pagination,$debut_auteur);
		if ($oldauteurs) {
			$valeurs['tableau_auteurs'] = $oldauteurs;
			$valeurs['liste_auteurs'] = implode(",", $oldauteurs);
		}
	}

	$valeurs['_hidden'] = "<input type='hidden' name='id_rubrique' value='".$id_rubrique."' />"
						. "<input type='hidden' name='cimodif_img_avant' value='".$valeurs['liste_auteurs']."' />";

	
	return $valeurs;
}

function formulaires_ciar_ajouter_acces_auteurs_verifier_dist($id_rubrique, $pagination, $debut_auteur, $retour='', $config_fonc='', $row=array(), $hidden='')
{
	return $erreurs;
}

function formulaires_ciar_ajouter_acces_auteurs_traiter_dist($id_rubrique, $pagination, $debut_auteur, $retour='', $config_fonc='', $row=array(), $hidden='')
{
	$ciauteurs_concurrent=false;
	
	// si id_rubrique est un nombre
	if ($id_rubrique = intval($id_rubrique)) {
	
		$eccma = false;
		$auteurs = _request('auteurs');
		$ci_auteurs_img_avant = _request('cimodif_img_avant');
		
		if (!$auteurs) $auteurs = array();
		$insertauteurs = array();
		$deleteauteurs = array();
		$oldauteurs = array();
		
		
		// auteurs ayant actuellement acces a cette rubrique
		$oldauteurs = ciar_liste_auteurs_acces_rubrique($id_rubrique,$pagination,$debut_auteur);
		
		$result = sql_select("id_auteur,cistatut_auteur_rub", "spip_ciar_auteurs_acces_rubriques", "id_rubrique=$id_rubrique AND id_auteur=".$GLOBALS['visiteur_session']['id_auteur'],"","id_auteur");
		while ($row = sql_fetch($result)) {
			if ($row['cistatut_auteur_rub']=='eccma')
				$eccma = true;
		}

		if ($eccma) {
			// Prévention des accès concurents
			$ci_auteurs_img_base = "";
			if ($oldauteurs) $ci_auteurs_img_base = implode(",", $oldauteurs);
			if ($ci_auteurs_img_avant) {
				if ($ci_auteurs_img_base) {
					if (!($ci_auteurs_img_avant==$ci_auteurs_img_base)) $ciauteurs_concurrent=true;
				} else {
					$ciauteurs_concurrent=true;
				}		
			} elseif ($ci_auteurs_img_base) $ciauteurs_concurrent=true;
		
			if (!$ciauteurs_concurrent) {
		
				if (isset($auteurs) AND is_array($auteurs)) {
					foreach ($auteurs as $auteur) {
						$auteur = intval($auteur);
						if (in_array($auteur,$oldauteurs)){
							// si le nouvel auteur est le même que celui en base, ne rien faire
						} elseif ($auteur>0) {
							$insertauteurs[] = $auteur;
						}		
					}
				}

				if ($insertauteurs)
					ciar_ajouter_auteurs_dans_ec($insertauteurs,$id_rubrique); 
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