<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/cinotif_commun');
include_spip('formulaires/cinotif_abonnement');


function formulaires_cinotif_theme_charger_dist($id_evenement='new',$retour='', $config_fonc='groupes_mots_edit_config', $row=array(), $hidden=''){

	$valeurs = array();
	$valeurs['cieditable'] = true;
	$valeurs['cisupprimable'] = 'oui';
	$valeurs['titre'] = '';
	$valeurs['quoi'] = '';
	$valeurs['objet'] = '';
	$valeurs['id_objet'] = '';
	$valeurs['multisite'] = '';
	$valeurs['url_multisite'] = '';
	$valeurs['statut'] = 'prepa';
	$valeurs['_choix_site'] = '';

	if (!autoriser('configurer', 'configuration'))	
		$valeurs['cieditable'] = false;
	
	$id_evenement = intval($id_evenement);
	
	$id_table_objet = "id_evenement";
	$valeurs[$id_table_objet] = $id_evenement;

	if ($id_evenement) {
		$result = sql_select("*", "spip_cinotif_evenements", "id_evenement=".$id_evenement, "", "");
		while ($row = sql_fetch($result)) {
			$valeurs['titre'] = $row['titre'];
			$valeurs['quoi'] = $row['quoi'];
			$valeurs['objet'] = $row['objet'];
			$valeurs['id_objet'] = $row['id_objet'];
			$valeurs['multisite'] = $row['multisite'];
			$valeurs['url_multisite'] = $row['url_multisite'];
			$valeurs['statut'] = $row['statut'];
			$valeurs['adresse_liste_diffusion'] = $row['adresse_liste_diffusion'];
		}
		if (sql_countsel(spip_cinotif_abonnements,"id_evenement=".$id_evenement))
			$valeurs['cisupprimable'] = 'non';
	}
	
	if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF')){
		if (isset($GLOBALS['ciconfig']['cilistedessites'])) {
			include_spip('inc/cims_commun');
			$texte = '';
			$ci_site = ($valeurs['multisite'] ? $valeurs['multisite'] : $GLOBALS['ciconfig']['cisite']);
			
		    foreach ($GLOBALS['ciconfig']['cilistedessites'] as $key=>$id_site) {
		    	$checked = ($id_site==$ci_site ? 'checked="checked"' : '');
		    	if (function_exists('cims_htmlentities'))
			    	$description = cims_htmlentities($GLOBALS['ciconfig']['cinomdessites'][$key]);
			    else	
			    	$description = htmlentities($GLOBALS['ciconfig']['cinomdessites'][$key],ENT_QUOTES,'iso-8859-1');

	    		$texte .= '<div class="choix"><input type="radio" name="multisite" id="site_'.$id_site.'" value="'.$id_site.'" '.$checked.' /><label for="site_'.$id_site.'">'.$description.'</label></div>';
		    }
			$valeurs['_choix_site'] = $texte;
		}
	}
	
	if (cinotif_sympa_actif())
		$valeurs['sympa_actif'] = 'oui';
	else
		$valeurs['sympa_actif'] = 'non';	
	
	$valeurs['_hidden'] = "<input type='hidden' name='$id_table_objet' value='".intval($id_evenement)."' />";

	return $valeurs;
}

function formulaires_cinotif_theme_verifier_dist($id_evenement='new',$retour='', $config_fonc='groupes_mots_edit_config', $row=array(), $hidden=''){

	$titre = _request('titre');	
	if (!$titre)
		$erreurs['titre'] = _T('cinotif:label_nom_theme').' '._T('cinotif:obligatoire');

	$objet = _request('objet');	
	if (!$objet OR !in_array($objet,array('site','rubrique','article')))
		$erreurs['objet'] = _T('cinotif:label_objet').' '._T('cinotif:obligatoire');

	$id_objet = intval(_request('id_objet'));	
	if (!$id_objet AND $objet AND in_array($objet,array('rubrique','article')))
		$erreurs['id_objet'] = _T('cinotif:label_id_objet').' '._T('cinotif:obligatoire');

	if ($id_objet AND $objet){
		if ($objet=='rubrique'){
			if (!sql_countsel("spip_rubriques","id_rubrique=" .$id_objet))
				$erreurs['id_objet'] = _T('cinotif:id_objet_inexistant');
	      	elseif (!autoriser('modifier', $objet, $id_objet))	
				$erreurs['id_objet'] = _T('cinotif:pas_droit');
		}
		if ($objet=='article'){
			if (!sql_countsel("spip_articles","id_article=" .$id_objet))
				$erreurs['id_objet'] = _T('cinotif:id_objet_inexistant');			
	      	elseif (!autoriser('modifier', $objet, $id_objet))	
				$erreurs['id_objet'] = _T('cinotif:pas_droit');
		}		
	}
				
	if ($id_objet AND $objet AND in_array($objet,array('rubrique','article'))){		
      	if (!autoriser('modifier', $objet, $id_objet))
			$erreurs['id_objet'] = _T('cinotif:pas_droit');
		
		// compatibilite avec CIAR
		if (!cinotif_ciar_autorise($objet, $id_objet))
			$erreurs['id_objet'] = _T('cinotif:pas_droit');			
	}

	$quoi = _request('quoi');	
	if (!$quoi OR !in_array($quoi,array('actupublie','articlepublie','articlemodifie','documentajoute','forumvalide')))
		$erreurs['quoi'] = _T('cinotif:label_quoi').' '._T('cinotif:obligatoire');

	$statut = _request('statut');	
	if (!$statut OR !in_array($statut,array('prepa','publie','sansnotif','ferme')))
		$erreurs['statut'] = _T('cinotif:label_statut').' '._T('cinotif:obligatoire');

	if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF')){
		$multisite = _request('multisite');	
		if (!$multisite OR !in_array($multisite,$GLOBALS['ciconfig']['cilistedessites']))
			$erreurs['multisite'] = _T('cinotif:label_multisite').' '._T('cinotif:obligatoire');
			
		$url_multisite = _request('url_multisite');	
		if (!$url_multisite) 
			$erreurs['url_multisite'] = _T('cinotif:label_url_multisite').' '._T('cinotif:obligatoire');
			
	}		

	// adresse liste diffusion invalide	
	$adresse_liste_diffusion = _request('adresse_liste_diffusion');	
	if (cinotif_sympa_actif() AND !email_valide($adresse_liste_diffusion)) 
		$erreurs['adresse_liste_diffusion'] = _T('cinotif:email_non_valide');
		
		
	return $erreurs;
}

function formulaires_cinotif_theme_traiter_dist($id_evenement='new',$retour='', $config_fonc='groupes_mots_edit_config', $row=array(), $hidden=''){

	$c = array();
	// alignement sur l'approche de filtrage de SPIP
	$champs = array('titre','quoi','objet','id_objet','statut','adresse_liste_diffusion');
	if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF')){
		$champs[] = 'multisite';
		$champs[] = 'url_multisite';
	}

	foreach ($champs as $champ)
		$c[$champ] = _request($champ);

	if ($c['objet']=='site')
		$c['id_objet'] = 0;

	if (isset($c['url_multisite']))
		$c['url_multisite'] = str_replace(array('http://','https://'),'',$c['url_multisite']);
		
		
	$exist = false;
	if (intval($id_evenement)>0) {
		$row = sql_fetsel("*", "spip_cinotif_evenements", "id_evenement=$id_evenement","","");
		if ($row)
			$exist = true;
	}
	
	if ($exist)
		sql_updateq("spip_cinotif_evenements", $c, "id_evenement=$id_evenement");
	else
		$id_evenement = sql_insertq("spip_cinotif_evenements", $c);		


	$res['message_ok'] = "";
	
	$res['redirect'] = generer_url_ecrire("cinotif_config");
	
	return $res;	
}

?>