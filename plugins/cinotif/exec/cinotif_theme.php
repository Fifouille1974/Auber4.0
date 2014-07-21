<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/cinotif_commun');

function exec_cinotif_theme_dist()
{
	$id_evenement= intval(_request('id_evenement'));

	if (!$id_evenement) {
	  $type = $titre = filtrer_entites(_T('cinotif:titre_nouveau_theme'));
	  $row = array();
	} else {
		$row = sql_fetsel("id_evenement,titre", "spip_cinotif_evenements", "id_evenement=$id_evenement");
		if ($row) {
			$id_evenement = $row['id_evenement'];
			$type = $row['titre'];
			$titre = typo($type);
		}
	}

	if (($id_evenement AND !$row) 
		OR (!$row AND !autoriser('configurer', 'configuration'))) {
		include_spip('inc/minipres');
		echo minipres();
	} else {

	$commencer_page = charger_fonction('commencer_page', 'inc');
	echo $commencer_page("&laquo; $titre &raquo;", "naviguer", "mots");
	
	echo debut_gauche('', true);

	echo creer_colonne_droite('', true);
	echo debut_droite('', true);
	
	if (spip_version()>=3)
		$icone_retour = icone_verticale(_T('icone_retour'), generer_url_ecrire("cinotif_config"), "redacteurs-48.png", "rien.gif",$GLOBALS['spip_lang_left']);
	else
		$icone_retour = icone_inline(_T('icone_retour'), generer_url_ecrire("cinotif_config"), "redacteurs-48.png", "rien.gif",$GLOBALS['spip_lang_left']);
	
	$contexte = array(
		'icone_retour'=>$icone_retour,
		'titre'=>$type,
		'redirect'=>generer_url_ecrire("cinotif_config",""),
		'new'=>_request('new') == "oui"?"oui":$id_evenement,
		'config_fonc'=>'cinotif_theme_edit_config'
	);

	echo recuperer_fond("prive/editer/cinotif_theme", $contexte);
	
	echo fin_gauche(),
	fin_page();
	}
}

?>