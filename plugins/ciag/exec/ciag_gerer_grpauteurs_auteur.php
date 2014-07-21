<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/ciag_commun');

function exec_ciag_gerer_grpauteurs_auteur_dist()
{
	$id_auteur= intval(_request('id_auteur'));

	if (!$id_auteur) {
	  $type = $titre = filtrer_entites(_T('titre_nouveau_groupe'));
	  $row = array();
	} else {
		$row = sql_fetsel("id_auteur,nom", "spip_auteurs", "id_auteur=$id_auteur");
		if ($row) {
			$id_auteur = $row['id_auteur'];
			$type = $row['nom'];
			$titre = typo($type);
		}
	}

	if (!$id_auteur OR ($id_auteur AND !$row) OR
		!autoriser('modifier','auteur',$id_auteur)) {
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
		$retour = "auteur";
		$icone_retour = icone_verticale(_T('icone_retour'), generer_url_ecrire($retour,"id_auteur=$id_auteur"), "redacteurs-48.png", "rien.gif",$GLOBALS['spip_lang_left']);
	} else {
		$retour = "auteur_infos";
		$icone_retour = icone_inline(_T('icone_retour'), generer_url_ecrire($retour,"id_auteur=$id_auteur"), "redacteurs-48.png", "rien.gif",$GLOBALS['spip_lang_left']);
	}

	$contexte = array(
		'icone_retour'=>$icone_retour,
		'titre'=>$type,
		'redirect'=>generer_url_ecrire($retour,"id_auteur=$id_auteur"),
		'new'=> $id_auteur,
		'config_fonc'=>''
	);

	echo recuperer_fond("prive/editer/ciag_gerer_grpauteurs_auteur", $contexte);

	
	echo fin_gauche(),
	fin_page();
	}
}
?>