<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/ciag_commun');

function exec_ciag_gerer_grpauteurs_rubrique_dist()
{
	$id_rubrique= intval(_request('id_rubrique'));

	if (!$id_rubrique) {
	  $type = $titre = filtrer_entites(_T('titre_nouveau_groupe'));
	  $row = array();
	} else {
		$row = sql_fetsel("id_rubrique,titre", "spip_rubriques", "id_rubrique=$id_rubrique");
		if ($row) {
			$id_rubrique = $row['id_rubrique'];
			$type = $row['titre'];
			$titre = typo($type);
		}
	}

	if (!$id_rubrique OR ($id_rubrique AND !$row) OR
		!autoriser('modifier','rubrique',$id_rubrique)) {
		include_spip('inc/minipres');
		echo minipres();
	} else {

	$commencer_page = charger_fonction('commencer_page', 'inc');
	echo $commencer_page("&laquo; $titre &raquo;", "naviguer", "mots");
	
	echo debut_gauche('', true);

	$javascript = 'onclick="window.open(this.href, \'\', \'width=550px, height=700px, top=20, left=20, toolbar=no, menubar=no, location=no, resizable=yes, scrollbars=yes, status=no\'); return false;" title="'._T('ciag:nouvelle_fenetre').'" ';
	$rac = icone_horizontale (_T('ciag:liste_grpauteurs_auteurs'), generer_url_ecrire("ciag_miniliste_grpauteurs_auteurs"), "breve-24.gif", "", false, $javascript);
	$rac .= icone_horizontale (_T('ciag:liste_grpauteurs_rubriques'), generer_url_ecrire("ciag_miniliste_grpauteurs_rubriques"), "breve-24.gif", "", false, $javascript);
	echo bloc_des_raccourcis($rac);
	

	echo creer_colonne_droite('', true);
	echo debut_droite('', true);
	
	if (spip_version()>=3){
		$exec = "rubrique";
		$icone_retour = icone_verticale(_T('icone_retour'), $url, "secteur-24.gif", "rien.gif",$GLOBALS['spip_lang_left']);
	} else {
		$exec = "naviguer";
		$icone_retour = icone_inline(_T('icone_retour'), $url, "secteur-24.gif", "rien.gif",$GLOBALS['spip_lang_left']);
	}

	$url = generer_url_ecrire($exec,"id_rubrique=$id_rubrique");
	if (_request('retour')) {
		if (_request('retour')=='ciar_rubrique_protection')
			$url = generer_url_ecrire("ciar_rubrique_protection","id_rubrique=$id_rubrique");
	}
	
	$contexte = array(
		'icone_retour'=>$icone_retour,
		'titre'=>$type,
		'redirect'=>$url,
		'new'=> $id_rubrique,
		'config_fonc'=>''
	);

	echo recuperer_fond("prive/editer/ciag_gerer_grpauteurs_rubrique", $contexte);

	
	echo fin_gauche(),
	fin_page();
	}
}
?>