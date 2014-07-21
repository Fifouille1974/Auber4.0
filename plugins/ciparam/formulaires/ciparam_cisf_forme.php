<?php
/**
 * Plugin Saisie facile
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/cisf_commun');
include_spip('inc/ciparam_inc_commun');

function formulaires_ciparam_cisf_forme_charger_dist($id_article='new', $id_rubrique=0, $retour='', $lier_trad=0, $config_fonc='articles_edit_config', $row=array(), $hidden=''){	

	if (!autoriser('modifier', 'article', $id_article))
		return false;

	if (cisf_indesirable())
		return false;

	$valeurs = array();
	$valeurs["id_article"] = intval($id_article);
	$valeurs['_choix_forme_article'] = ciparam_choix_forme_radio("spip_articles","id_article",$id_article,"ciforme");
	
	if (!$valeurs['_choix_forme_article'])
		$valeurs['masquer'] = "oui";
	else	
		$valeurs['masquer'] = "non";
	
	$valeurs['_hidden'] = "<input type='hidden' name='id_article' value='$id_article' />";
	
	$valeurs['old_ciforme'] = ciparam_lire_valeur("spip_articles","id_article",$id_article,"ciforme");

	// Pour SPIP 2.1
	$valeurs['id_rubrique'] = $id_rubrique;
	
	// Impératif : preciser que le formulaire doit etre securise auteur/action sinon rejet
	$valeurs['_action'] = array("cisf_forme",$id_article);
	
	return $valeurs;
}

function formulaires_ciparam_cisf_forme_verifier_dist($id_article='new', $id_rubrique=0, $retour='', $lier_trad=0, $config_fonc='articles_edit_config', $row=array(), $hidden='')
{

//	$erreurs = formulaires_editer_objet_verifier('article',$id_article,array('titre'));
	return $erreurs;
}

function formulaires_ciparam_cisf_forme_traiter_dist($id_article='new', $id_rubrique=0, $retour='', $lier_trad=0, $config_fonc='articles_edit_config', $row=array(), $hidden='')
{
	// Préparation de l'aiguillage
	$fond = "cisf_article";
	$retour = generer_url_public("$fond", "id_article=$id_article&id_rubrique=$id_rubrique");
	
	// Traitement et aiguillage
	if ($id_article>0 AND !isset($_POST['annuler'])) {
		$ciforme = _request('ciforme');
		ciparam_maj_forme("article","spip_articles","id_article",$id_article,'ciforme',$ciforme);
	}
	
	$res['message_ok'] = "";
	if ($retour) $res['redirect'] = $retour;

	return $res;
}


function ciparam_choix_forme_radio($table,$id_table_objet,$id,$champ) {
	$texte = "";
	$valeur = "";
	
	if ($id) {	
		$valeur = ciparam_lire_valeur($table,$id_table_objet,$id,$champ);
		$formes = ciparam_charger_param_xml("formes-".substr($table,5));
    	$formes = ciparam_order_array($formes,'description');

		if ($formes) {
    		$nom = "__aucune_forme";
    		$description = _T('ciparam:eq_forme_normale');
			if (!$valeur) {
	    		$texte .= "\n<div class='textechk gras'>";
				$texte .= '<input id="mot'.$nom.'" type="radio" checked="checked" value="'.$nom.'" name="ciforme" />';
			} else {
	    		$texte .= "\n<div class='textechk'>";
				$texte .= '<input id="mot'.$nom.'" type="radio" value="'.$nom.'" name="ciforme" />';
			}
			$texte .= '<label for="mot'.$nom.'">'.$description.'</label></div>';
   		
		    foreach ($formes as $forme) {
		    	if ($valeur AND $valeur==$forme['nom']) 
		    		$selected = "selected";
		    	else
			    	$selected = "";

		    	$nom = $forme['nom'];
		    	$description = $forme['description'];
		    	$icon = $forme['icon'];
				if ($selected) {
		    		$texte .= "\n<div class='textechk gras'>";
					$texte .= '<input id="mot'.$nom.'" type="radio" checked="checked" value="'.$nom.'" name="ciforme" />';
				} else {
		    		$texte .= "\n<div class='textechk'>";
					$texte .= '<input id="mot'.$nom.'" type="radio" value="'.$nom.'" name="ciforme" />';
				}
				$texte .= '<label for="mot'.$nom.'">'.$description.'</label>';
				if ($icon)
					$texte .= '<img src="'.$icon.'" class="icone_de_forme" alt=" ">';
								
				$texte .= '</div>';
		    }
		}
	}
	
	return $texte;  
}	


?>