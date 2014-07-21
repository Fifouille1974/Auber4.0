<?php
/**
 * Plugin ciarchive : Archivage d'articles
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');


function exec_ciarchive_articles(){

	$grostitre = _T('ciarchive:archive_articles');
	$titre = _T('ciarchive:archive_articles');
	
	if ($id_rubrique=intval(_request('id_rubrique'))) {
		$titre = _T('ciarchive:archive_articles_rubrique');
		$row = sql_fetsel("id_rubrique,titre", "spip_rubriques", "id_rubrique=$id_rubrique");
		if ($row) {
			$type = $row['titre'];
			$grostitre = typo($type);
		}
	}

	
	$commencer_page = charger_fonction('commencer_page', 'inc');
	echo $commencer_page($titre, "accueil", "accueil");
	
	echo debut_gauche('', true);

	echo creer_colonne_droite('', true);
	echo debut_droite('', true);
	
	if ($id_rubrique){
		
		if (spip_version()>=3)
			$exec = "rubrique";
		else
			$exec = "naviguer";
	
		$url = generer_url_ecrire($exec,"id_rubrique=$id_rubrique");

		if (spip_version()>=3)
			$icone_retour = icone_verticale(_T('icone_retour'), $url, "secteur-24.gif", "rien.gif",$GLOBALS['spip_lang_right']);
		else
			$icone_retour = icone_inline(_T('icone_retour'), $url, "secteur-24.gif", "rien.gif",$GLOBALS['spip_lang_right']);
		
		echo '<div class="cadre cadre-r">';	
		echo '<div class="cadre_padding">';
		echo "<div class='bandeau_actions' style=''>$icone_retour</div>";
		echo '<h1>'.$grostitre.'</h1>';
		echo "<div class='nettoyeur'></div>";
	}
	
	if (spip_version()>=3){
		$contexte = array('titre'=>$titre,'statut'=>'archive','par'=>'date');
		if ($id_rubrique=intval(_request('id_rubrique')))
			$contexte['id_rubrique'] = $id_rubrique;

		echo recuperer_fond("prive/objets/liste/articles", $contexte);
	} else {
		$where = "statut='archive'";
		if ($id_rubrique)
			$where .= " AND id_rubrique=".$id_rubrique;

		echo  afficher_objets('article', $titre, array("WHERE" => $where, 'ORDER BY' => "date DESC"));
	}
	
	echo  "</div></div><br/>\n";

	echo fin_gauche(), fin_page();
}

?>