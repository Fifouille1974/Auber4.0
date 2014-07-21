<?php
/**
 * Plugin ciimport
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');


function exec_ciexport_forum(){

	$objet = '';
	if ($req_objet = _request('objet')) {
		if (in_array($req_objet, array('article')))
			$objet = $req_objet;
	}
	$id_objet = intval(_request('id_objet'));
	
	$contexte = array('objet'=>$objet,'id_objet'=>$id_objet);
	
	if ($choixstatut=_request('statut')) {
		if (in_array($choixstatut, array('publie', 'off', 'prop', 'spam', 'tous', 'nonpublie')))
			$contexte['statut']=$choixstatut;
	}
	
	if (!autoriser('modererforum', $objet, $id_objet)) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
		
		if ($choixstatut=='tous')
			$grostitre = _T('ciimport:icone_export_forum_tous');
		elseif ($choixstatut=='nonpublie')
			$grostitre = _T('ciimport:icone_export_forum_nonpublie');
		else
			$grostitre = _T('ciimport:icone_export_forum_publie');

		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		echo "<br />\n";
		echo gros_titre($grostitre,'', false);
		
		echo debut_gauche('', true);
		
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
		
		if (spip_version()>=3){
			$url = generer_url_ecrire("article","id_article=$id_objet");
			$contexte['icone_retour'] = icone_verticale(_T('icone_retour'), $url, "secteur-24.gif", "rien.gif",$GLOBALS['spip_lang_left']);
		} else {
			$url = generer_url_ecrire("articles","id_article=$id_objet");
			$contexte['icone_retour'] = icone_inline(_T('icone_retour'), $url, "secteur-24.gif", "rien.gif",$GLOBALS['spip_lang_left']);
		}
		$contexte['redirect'] = $url;
		
		echo debut_cadre_relief('',true,'',$titre);
		
		echo recuperer_fond("prive/objets/liste/ciexport_forum", $contexte);

		echo fin_cadre_relief(true);
		
		echo  "<br />\n";
		echo fin_gauche(), fin_page();
	}
}

function ciimport_icone_verticale($lien, $texte, $fond, $fonction="", $class="", $javascript=""){
	if (spip_version()>=3)
		return icone_base($lien,$texte,$fond,$fonction,"verticale $class",$javascript);
	else
		return '';
}

?>