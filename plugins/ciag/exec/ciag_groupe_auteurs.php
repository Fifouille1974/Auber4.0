<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/ciag_commun');

function exec_ciag_groupe_auteurs_dist()
{
	$id_groupe= intval(_request('id_groupe'));

	if (!$id_groupe) {
	  $type = $titre = filtrer_entites(_T('ciag:titre_nouveau_groupe'));
	  $row = array();
	} else {
		$row = sql_fetsel("id_groupe,titre", "spip_ciag_grpauteurs", "id_groupe=$id_groupe");
		if ($row) {
			$id_groupe = $row['id_groupe'];
			$type = $row['titre'];
			$titre = typo($type);
		}
	}

	if (($id_groupe AND !$row) 
		OR (!$row AND !autoriser('creer', 'groupeauteur'))) {
		include_spip('inc/minipres');
		echo minipres();
	} else {

	$commencer_page = charger_fonction('commencer_page', 'inc');
	echo $commencer_page("&laquo; $titre &raquo;", "naviguer", "mots");
	
	echo debut_gauche('', true);

	echo creer_colonne_droite('', true);
	echo debut_droite('', true);
	
	if (spip_version()>=3)
		$icone_retour = icone_verticale(_T('icone_retour'), generer_url_ecrire("ciag_groupes_auteurs"), "redacteurs-48.png", "rien.gif",$GLOBALS['spip_lang_left']);
	else
		$icone_retour = icone_inline(_T('icone_retour'), generer_url_ecrire("ciag_groupes_auteurs"), "redacteurs-48.png", "rien.gif",$GLOBALS['spip_lang_left']);
	
	$contexte = array(
		'icone_retour'=>$icone_retour,
		'titre'=>$type,
		'redirect'=>generer_url_ecrire("ciag_groupes_auteurs",""),
		'new'=>_request('new') == "oui"?"oui":$id_groupe,
		'config_fonc'=>'ciag_groupe_auteurs_edit_config'
	);

	echo recuperer_fond("prive/editer/ciag_groupe_auteurs", $contexte);

	if (spip_version()>=3)
		$style = '<div style="width:50px;margin:-2em 0;">';
	else
		$style = '<div style="width:50px;">';
	
	if ($id_groupe) {
		if (spip_version()>=3)
			echo recuperer_fond('prive/objets/liste/ciag_3_auteurs',array('id_groupe'=>$id_groupe));
		else
			echo afficher_objets('auteur',_T('ciag:auteurs_de_ce_groupe'), array('FROM' => "spip_auteurs AS auteurs, spip_ciag_grpauteurs_auteurs AS lien ", "WHERE" => "auteurs.id_auteur=lien.id_auteur AND lien.id_groupe=$id_groupe", 'ORDER BY' => "auteurs.nom"),'',true);
		
		if (autoriser('modifier', 'groupeauteur',$id_groupe)) {
			if (spip_version()>=3)
				echo $style.icone_verticale(_T('ciag:gerer_liste_auteurs'), generer_url_ecrire("ciag_gerer_auteurs","id_groupe=$id_groupe"), "mot-cle-24.gif", "creer.gif", $spip_lang_right).'</div>';
			else
				echo $style.icone_inline(_T('ciag:gerer_liste_auteurs'), generer_url_ecrire("ciag_gerer_auteurs","id_groupe=$id_groupe"), "mot-cle-24.gif", "creer.gif", $spip_lang_right).'</div>';
		}

		if (spip_version()>=3)
			echo recuperer_fond('prive/objets/liste/ciag_3_rubriques',array('id_groupe'=>$id_groupe));
		else
			echo afficher_objets('rubrique',_T('ciag:rubriques_de_ce_groupe'), array('FROM' => "spip_rubriques AS rubriques, spip_ciag_grpauteurs_rubriques AS lien ", "WHERE" => "rubriques.id_rubrique=lien.id_rubrique AND lien.id_groupe=$id_groupe", 'ORDER BY' => "rubriques.titre"),'',true);

		if (autoriser('modifier', 'groupeauteur',$id_groupe)){
			if (spip_version()>=3)
				echo $style.icone_verticale(_T('ciag:gerer_liste_rubriques'), generer_url_ecrire("ciag_gerer_rubriques","id_groupe=$id_groupe"), "mot-cle-24.gif", "creer.gif", $spip_lang_right).'</div>';
			else
				echo $style.icone_inline(_T('ciag:gerer_liste_rubriques'), generer_url_ecrire("ciag_gerer_rubriques","id_groupe=$id_groupe"), "mot-cle-24.gif", "creer.gif", $spip_lang_right).'</div>';
		}

		if (spip_version()>=3)
			echo recuperer_fond('prive/objets/liste/ciag_3_gestionnaires',array('id_groupe'=>$id_groupe));
		else
			echo afficher_objets('auteur',_T('ciag:qui_gere_ce_groupe'), array('FROM' => "spip_auteurs AS auteurs, spip_ciag_grpauteurs_gestionnaires AS lien ", "WHERE" => "auteurs.id_auteur=lien.id_auteur AND lien.id_groupe=$id_groupe", 'ORDER BY' => "auteurs.nom"),'',true);
		
		if (autoriser('modifier', 'groupeauteur',$id_groupe)) {
			if (spip_version()>=3)
				echo $style.icone_verticale(_T('ciag:gerer_liste_gestionnaires'), generer_url_ecrire("ciag_gerer_gestionnaires","id_groupe=$id_groupe"), "warning-24.gif", "creer.gif", $spip_lang_right).'</div><br />';
			else
				echo $style.icone_inline(_T('ciag:gerer_liste_gestionnaires'), generer_url_ecrire("ciag_gerer_gestionnaires","id_groupe=$id_groupe"), "warning-24.gif", "creer.gif", $spip_lang_right).'</div><br />';
		}
	}
	
	echo fin_gauche(),
	fin_page();
	}
}

?>