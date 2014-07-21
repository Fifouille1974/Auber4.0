<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */


if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/filtres');

// http://doc.spip.org/@exec_rubriques_edit_dist
function exec_ciar_retirer_acces_auteurs_dist()
{
	exec_ciar_retirer_acces_auteurs_args(intval(_request('id_rubrique')));
}

// http://doc.spip.org/@exec_rubriques_edit_args
function exec_ciar_retirer_acces_auteurs_args($id_rubrique)
{
	global $connect_toutes_rubriques, $connect_statut, $spip_lang_right;

	$row = sql_fetsel("*", "spip_rubriques", "id_rubrique=$id_rubrique");
	if ($row) {
		$id_parent = $row['id_parent'];
		$titre = $row['titre'];
		$id_secteur = $row['id_secteur'];
	}
	
	$commencer_page = charger_fonction('commencer_page', 'inc');

	if (!autoriser('ecmodifier','rubrique',$id_rubrique))  {
		include_spip('inc/minipres');
		echo minipres();
	} else {

//	pipeline('exec_init',array('args'=>array('exec'=>'rubriques_edit','id_rubrique'=>$id_rubrique),'data'=>''));
	echo $commencer_page(_T('info_modifier_titre', array('titre' => $titre)), "naviguer", "rubriques", $id_rubrique);

//	if ($id_parent == 0) $ze_logo = "secteur-24.gif";
	$ze_logo = "rubrique-24.gif";

	echo debut_grand_cadre(true);
	echo afficher_hierarchie($id_rubrique,'',$id_rubrique,'rubrique');
	echo fin_grand_cadre(true);

	echo debut_gauche('', true);

//	echo pipeline('affiche_gauche',array('args'=>array('exec'=>'rubriques_edit','id_rubrique'=>$id_rubrique),'data'=>''));
//	echo creer_colonne_droite('', true);
//	echo pipeline('affiche_droite',array('args'=>array('exec'=>'rubriques_edit','id_rubrique'=>$id_rubrique),'data'=>''));	  
	echo debut_droite('', true);

	if (spip_version()>=3){
		$exec = "rubrique";
		$icone_retour = icone_verticale(_T('icone_retour'), generer_url_ecrire("ciar_rubrique_protection","id_rubrique=$id_rubrique"), $ze_logo, "rien.gif",$GLOBALS['spip_lang_left']);
	} else {
		$exec = "naviguer";
		$icone_retour = icone_inline(_T('icone_retour'), generer_url_ecrire("ciar_rubrique_protection","id_rubrique=$id_rubrique"), $ze_logo, "rien.gif",$GLOBALS['spip_lang_left']);
	}

	$contexte = array(
	'icone_retour'=>$icone_retour,
	'redirect'=>generer_url_ecrire($exec),
	'titre'=>$titre,
	'new'=>$id_rubrique,
	'id_rubrique'=>$id_parent, // pour permettre la specialisation par la rubrique appelante
	'ci_pagination'=> 1000,
	'ci_debut_auteur' => _request('debut_auteur_col1'),
	'config_fonc'=>'rubriques_edit_config',
	'lier_trad'=>$lier_trad
	);

	echo recuperer_fond("prive/editer/ciar_retirer_acces_auteurs", $contexte);

//	echo pipeline('affiche_milieu',array('args'=>array('exec'=>'rubriques_edit','id_rubrique'=>$id_rubrique),'data'=>''));	  

	echo fin_gauche(), fin_page();
	}
}

?>