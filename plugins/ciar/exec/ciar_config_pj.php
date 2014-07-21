<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');


// parametrage par fichier	
$f = _DIR_RACINE . _NOM_PERMANENTS_INACCESSIBLES . '_config_ciar.php';
if (@file_exists($f))
	include_once($f);


function ciar_appliquer_modifs_config_pj() {
	include_spip('inc/meta');
	$disabled = false;

	// Parametrage par fichier ? 
	$f = _DIR_RACINE . _NOM_PERMANENTS_INACCESSIBLES . '_config_ciar.php';
	if (@file_exists($f))
		$param_fichier = true;
	else
		$param_fichier = false;
	
	
	$cle_filtre = _request('cle_filtre');
	if (in_array($cle_filtre,array('non_non','non_oui','oui_non','oui_oui'))) {
		$creer_htaccess = substr($cle_filtre,4,3);
		$tableau = array();		
		$tableau['cle_pj'] = substr($cle_filtre,0,3);
		ecrire_meta('ciar', @serialize($tableau));
	}

	if (isset($GLOBALS['ciconfig']['ciar_formulaire_protec_pj_inactif']))
		if ($GLOBALS['ciconfig']['ciar_formulaire_protec_pj_inactif']=='oui')
			$disabled = true;

	if (isset($GLOBALS['ciconfig']['ciar_protec_pj_par_filtre_non_desactivable']))
		if ($GLOBALS['ciconfig']['ciar_protec_pj_par_filtre_non_desactivable']=='oui')
			if ($GLOBALS['meta']['creer_htaccess'] === 'oui')
				$disabled = true;
	
	if (!$disabled) {		
		if ($creer_htaccess=='oui' OR $creer_htaccess='non') {
			if ($creer_htaccess != $GLOBALS['meta']['creer_htaccess']) {
				ecrire_meta('creer_htaccess', $creer_htaccess);
				ecrire_metas();
			}
		}		
	}
	
	return true;
}


function exec_ciar_config_pj(){

	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
		
		if (_request('changer_config') == 'oui')
			ciar_appliquer_modifs_config_pj();
	
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		echo "<br />\n";
		echo gros_titre(_T('ciar:titre_protection_pj'),'', false);

		echo debut_gauche('', true);
		
		$ciar_navigation = charger_fonction('ciar_navigation', 'configuration');
	  	echo $ciar_navigation();
			
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
	
		echo  ciar_choix_protection_pj(), "<br />\n";

	
		echo fin_gauche(), fin_page();
	}

}

function ciar_choix_protection_pj() {
	
	$action = generer_url_ecrire('ciar_config_pj');

	// Parametrage par fichier ? 
	$f = _DIR_RACINE . _NOM_PERMANENTS_INACCESSIBLES . '_config_ciar.php';
	if (@file_exists($f)){
		include_once($f);
		$param_fichier = true;
	} else {
		$param_fichier = false;
	}
	
	include_spip('inc/acces');
	$creer_htaccess = gerer_htaccess();
	
	if (!$creer_htaccess)
		$creer_htaccess = 'non';

	if (!$param_fichier) {
		// Choix avec ou sans cle
		// Par defaut, proposer une protection sans cle	
		if (isset($GLOBALS['meta']['ciar'])) {
			$ciar_config = @unserialize($GLOBALS['meta']['ciar']);
			if (isset($ciar_config['cle_pj']))
				$cle_filtre = $ciar_config['cle_pj']."_".$creer_htaccess;
		} else {
			$cle_filtre = "non_".$creer_htaccess;
		}
	} else {
		$cle_filtre = "oui_".$creer_htaccess;
	}

	
	$disabled ='';
	if (isset($GLOBALS['ciconfig']['ciar_formulaire_protec_pj_inactif']))
		if ($GLOBALS['ciconfig']['ciar_formulaire_protec_pj_inactif']=='oui')
			$disabled =' disabled="disabled" ';

	if (isset($GLOBALS['ciconfig']['ciar_protec_pj_par_filtre_non_desactivable']))
		if ($GLOBALS['ciconfig']['ciar_protec_pj_par_filtre_non_desactivable']=='oui')
			if ($creer_htaccess=='oui')
				$disabled =' disabled="disabled" ';
			
			
	$res = '<form action="'.$action.'" method="post"><div>'.form_hidden($action)
	. '<input type="hidden" name="changer_config" value="oui" /></div>';

	$res .= '<table width="100%" cellspacing="1" cellpadding="3" border="0"><tbody>';
	$res .= '<tr><td class="verdana2">';
	if ($disabled)
		$res .= _T('ciar:protection_pj_info_param_fichier');
	else
		$res .= '<span style="color:red; font-weight:bold;">'._T('ciar:attention').'</span>'._T('ciar:protection_pj_info');
	$res .= '<br><br>';
	$res .= '</td></tr><tr><td align="left" class="verdana2">';

	
	if (!$param_fichier) {
		// Choix aucune protection
		$res .= '<input type="radio" name="cle_filtre" value="non_non" id="aucune_protection"';
		$titre = _T('ciar:protection_pj_aucune');
		if ($cle_filtre=='non_non') {
			$res .= ' checked="checked"';
			$titre = '<b>'.$titre.'</b>';
		}
		$res .= ' /> <label for="aucune_protection">'.$titre.'</label>';
		$res .= '<ul><li><p class="explication">'._T('ciar:protection_pj_aucune_impact').'<br />'._T('ciar:protection_pj_aucune_info').'</p></li></ul>';
	}
		
	
	$res .= '<input type="radio" name="cle_filtre" value="oui_non" id="label_creer_htaccess_non"'.$disabled;
	$titre = _T('ciar:protection_pj_simple');
	if ($cle_filtre=='oui_non') {
		$res .= ' checked="checked"';
		$titre = '<b>'.$titre.'</b>';
	}
	$res .= ' /> <label for="label_creer_htaccess_non">'.$titre.'</label>';
	$res .= '<ul><li><p class="explication">'._T('ciar:protection_pj_simple_impact').'<br />'._T('ciar:protection_pj_simple_info').'</p></li></ul>';
	

	if (!$param_fichier) {
		// Filtre sans cle
		$res .= '<input type="radio" name="cle_filtre" value="non_oui" id="filtre_sans_cle"'.$disabled;
		$titre = _T('ciar:protection_pj_filtre_sans_cle');
		if ($cle_filtre=='non_oui') {
			$res .= ' checked="checked"';
			$titre = '<b>'.$titre.'</b>';
		}
		$res .= ' /> <label for="filtre_sans_cle">'.$titre.'</label>';	
		$res .= '<ul><li><p class="explication">'._T('ciar:protection_pj_filtre_sans_cle_impact').'<br />'._T('ciar:protection_pj_filtre_sans_cle_info').'</p></li></ul>';
	}	
	

	$res .= '<input type="radio" name="cle_filtre" value="oui_oui" id="label_creer_htaccess_oui"'.$disabled;
	$titre = _T('ciar:protection_pj_filtre');
	if ($cle_filtre=='oui_oui') {
		$res .= ' checked="checked"';
		$titre = '<b>'.$titre.'</b>';
	}
	$res .= ' /> <label for="label_creer_htaccess_oui">'.$titre.'</label>';	
	$res .= '<ul><li><p class="explication">'._T('ciar:protection_pj_filtre_impact').'<br />'._T('ciar:protection_pj_filtre_info').'</p></li></ul>';
	
	$res .= '</td></tr>';
	$res .= '</tbody></table>';

	
	if (!$disabled)
		$res .= '<div><input type="submit" class="fondo" style="float: right;" value="Valider"/></div>';
		
	$res .= "</form>";

	
	$res = debut_cadre_trait_couleur("", true, "", _T('ciar:titre_protection_pj'))
	. $res
	. fin_cadre_trait_couleur(true);
	
	return $res;
}

?>