<?php
/**
 * Plugin ciform
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
function balise_CIFORM_JOINDRE($p) {
	$p->code = "ciform_joindre(\$Pile)";
	$p->statut = 'html';
	return $p;
}


// Joindre un document 
// Adapté de inc_documenter_objet_dist
function ciform_joindre($Pile) {
	include_spip('inc/presentation');
	include_spip('inc/actions');
	include_spip('inc/autoriser');

	$id_article = intval($Pile[0]['id_article']);
	$id_rubrique = intval($Pile[0]['id_rubrique']);
	$type = "article";
//	$script = generer_url_public('ciform_doc_modifier', "id_article=".$id_article."&id_rubrique=".$id_rubrique);
	// compatibilite avec SPIP 2.1.8
	$script = "spip.php?page=ciform_doc_modifier&amp;id_article=".$id_article."&amp;id_rubrique=".$id_rubrique;
	$mode = 'choix';
	$titre = _T('bouton_ajouter_document');
	$icone = 'doc-24.gif';



	// Joindre ?
	if (isset($GLOBALS['visiteur_session']['id_auteur']) && $GLOBALS['visiteur_session']['id_auteur']) {

		$joindre = charger_fonction('joindre', 'inc');
		$res = $joindre(array(
			'cadre' => 'relief',
			'icone' => $icone,
			'fonction' => 'creer.gif',
			'titre' => $titre,
			'script' => $script,
			'args' => "id_$type=$id_article",
			'id' => $id_article,
			'intitule' => _T('info_telecharger_ordinateur'),
			'mode' => $mode,
			'type' => $type,
			'ancre' => '',
			'id_document' => $id_document,
			'iframe_script' => ''
//			'iframe_script' => generer_url_ecrire("documenter","id_$type=$id_article&type=$type",true)
		));
	}
	
	$res = str_replace("size='15'","size='55'",$res);
	$res = str_replace("name='url'","name='url' size='55'",$res);
	
	return $res;
}


// Poids maximum pour l'upload
function ciform_upload_max_filesize($id_article) {
	$return = "";
	if (function_exists(ini_get)) {
		if (($post_max = ini_get('post_max_size')) AND ($upload_max = ini_get('upload_max_filesize'))) {
			$max_upload_size = min(ciform_ini_to_num($post_max), ciform_ini_to_num($upload_max));
			if ($max_upload_size > 0)
				$return = ($max_upload_size/(1048576))." Mo.";
		}
	}

	return $return;
}


// Transforme la notation de php.ini (comme '2M') en un entier (2*1048576)
function ciform_ini_to_num($valeur){ 
	$valeur = trim($valeur);
    $unite = substr($valeur, -1);
    $return = substr($valeur, 0, -1);
    $return = floatval($return);
    
    switch(strtoupper($unite)){
    case 'G':
        $return *= 1073741824;
        break;
    case 'M':
        $return *= 1048576;
        break;
    case 'K':
        $return *= 1024;
        break;
    }

    return $return;
}

?>