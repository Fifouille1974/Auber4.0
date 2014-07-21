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

function formulaires_ciparam_cisf_raccourci_charger_dist($id_article='new', $id_rubrique=0, $retour='', $lier_trad=0, $config_fonc='articles_edit_config', $row=array(), $hidden='')
{	
	if (!autoriser('modifier', 'article', $id_article))
		return false;

	if (cisf_indesirable())
		return false;

	$valeurs = array();
	$valeurs["id_article"] = intval($id_article);
	$valeurs['_choix_raccourci_article'] = ciparam_choix_raccourci_checkbox("spip_articles","id_article",$id_article,"raccourci");
	
	if (!$valeurs['_choix_raccourci_article'])
		$valeurs['masquer'] = "oui";
	else	
		$valeurs['masquer'] = "non";
	
	$valeurs['_hidden'] = "<input type='hidden' name='id_article' value='$id_article' />";
		
	$old_raccourcis = ciparam_lire_valeurs("spip_ci_raccourcis_articles","id_article",$id_article,"raccourci");
	if ($old_raccourcis)
		$valeurs['old_raccourcis'] = implode(",", $old_raccourcis);
	else
		$valeurs['old_raccourcis'] = "";
	
	// Pour SPIP 2.1
	$valeurs['id_rubrique'] = $id_rubrique;
	
	// Impératif : preciser que le formulaire doit etre securise auteur/action sinon rejet
	$valeurs['_action'] = array("ciparam_cisf_raccourci",$id_article);

	return $valeurs;
}

function formulaires_ciparam_cisf_raccourci_verifier_dist($id_article='new', $id_rubrique=0, $retour='', $lier_trad=0, $config_fonc='articles_edit_config', $row=array(), $hidden='')
{

//	$erreurs = formulaires_editer_objet_verifier('article',$id_article,array('titre'));
	return $erreurs;
}

function formulaires_ciparam_cisf_raccourci_traiter_dist($id_article='new', $id_rubrique=0, $retour='', $lier_trad=0, $config_fonc='articles_edit_config', $row=array(), $hidden='')
{
	// Préparation de l'aiguillage
	$fond = "cisf_article";
	$retour = generer_url_public("$fond", "id_article=$id_article&id_rubrique=$id_rubrique");
	

	// Traitement et aiguillage
	if (!isset($_POST['annuler'])) {
		return cisf_formulaires_editer_objet_traiter('ciparam_cisf_raccourci',$id_article,$id_rubrique,$lier_trad,$retour,$config_fonc,$row,$hidden);
	} else {
		$res['message_ok'] = "";
		$res['redirect'] = $retour;
		return $res;
	}
}


function ciparam_choix_raccourci_checkbox($table,$id_table_objet,$id,$champ) {
	$texte = "";
	$valeur = "";
	$tablejointure = "spip_ci_raccourcis_".substr($table,5);
	
	if ($id) {	
		$valeurs = ciparam_lire_valeurs($tablejointure,$id_table_objet,$id,$champ);
		$raccourcis = ciparam_charger_param_xml("raccourcis-".substr($table,5));
    	$raccourcis = ciparam_order_array($raccourcis,'description');
	    
		if ($raccourcis) {			
		    foreach ($raccourcis as $raccourci) {
		    	$nom = $raccourci['nom'];
		    	$description = $raccourci['description'];
		    	if (!$valeurs OR !in_array($raccourci['nom'],$valeurs)) { 
		    		$texte .= "\n<div class='textechk'>";
					$texte .= '<input id="mot'.$nom.'" type="checkbox" value="'.$nom.'" name="motscle[]" />';
		    	} else {
		    		$texte .= "\n<div class='textechk gras'>";
					$texte .= '<input id="mot'.$nom.'" type="checkbox" checked="checked" value="'.$nom.'" name="motscle[]" />';
		    	}
				$texte .= '<label for="mot'.$nom.'">'.$description.'</label></div>';
		    }
		}
	}
	
	return $texte;  
}	

?>