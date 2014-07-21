<?php

// Pour enlever le titre par défaut dans les forums
function cisquel_formulaire_charger($flux){
	$form = $flux['args']['form'];
	if ($form=='forum'){
		$flux['data']['titre'] = "";
		if ($flux['data']['formats_documents_forum']) {
			$flux['data']['formats_documents_forum'][] = '('._T('cisquel:poidsmax').' '.intval(cisquel_upload_max_filesize()/1048576).' Mo)';
			$flux['data']['_hidden'] .= "<input type='hidden' name='MAX_FILE_SIZE' value='".cisquel_upload_max_filesize()."' />";
		}
	}
	return $flux;
}

// Pour rendre le nom obligatoire dans les forums
// Pour ajouter le message de remerciement
function cisquel_formulaire_verifier($flux){
	$form = $flux['args']['form'];
	if ($form=='forum'){
		
		if (isset($flux['data']['previsu'])) {
			$flux['data']['previsu'] = '<div class="ligne-forumh"><div style="text-align: center;">'
				. _T('cisquel:forum_remerciement').'</div></div>'
				. $flux['data']['previsu'];
		}

		if (_request('session_nom')!==null) {
			if (strlen($titre=_request('session_nom')) < 3 ) {
				$flux['data']['session_nom'] = _T('cisquel:forum_attention_nom_trois_caracteres');
				unset($flux['data']['previsu']);
			}
		}
	}

	return $flux;
}

function cisquel_cisf_article_options($param){

	// Options supplementaires affectees a l'article
	if ($param['args']['type']=='options_affectees') {
		$id_article = intval($param['args']['id_article']);
		if($id_article){
			$row = sql_fetsel("date_redac", "spip_articles", "id_article=$id_article");
			if (isset($row['date_redac'])) {
				$param['data']['cisf:eq_menu_calendrier'] = affdate(normaliser_date($row['date_redac']),'d/m/Y H:i');
			}
	    } elseif ($a=_request('calendrier')) {
		    if (is_numeric($a)) {
		    	$dd = substr($a,6,2);
		    	$mm = substr($a,4,2);
		    	$yy = substr($a,0,4);
	            if (checkdate($mm,$dd,$yy))
					$param['data']['cisf:eq_menu_calendrier'] = $dd."/".$mm."/".$yy;
		    }
		}
		
	// Modification dans le menus des options avancees
	} elseif ($param['args']['type']=='options_avancees') {	
		if (isset($param['data']['bt_calendrier']))
			$param['data']['bt_calendrier']='cisf:eq_menu_calendrier';
	}
	
	return $param;
}

/**
 * Avant l’enregistrement d’un article ou d'une rubrique, verifier que des raccourcis <img…>, etc
 * ne figurent pas dans le titre.
 * Au passage on enleve tout script.
 *
 * @param array $flux
 * @return array
 */
function cisquel_pre_edition($flux){	
    if ($flux['args']['table']=='spip_articles' OR $flux['args']['table']=='spip_rubriques') {
		if ($flux['data']['titre']) {
			$flux['data']['titre'] = preg_replace(',<(img|doc|emb|image|audio|video)([^>]*)?'.'\s*/?'.'>,i', '', $flux['data']['titre']);
			$flux['data']['titre'] = preg_replace(',<script.*?($|</script.),isS', '', $flux['data']['titre']);
		}
    }

    return $flux;
}

// Poids maximum pour l'upload
function cisquel_upload_max_filesize() {
	$return = "";
	if (function_exists(ini_get)) {
		if (($post_max = ini_get('post_max_size')) AND ($upload_max = ini_get('upload_max_filesize'))) {
			$max_upload_size = min(cisquel_ini_to_num($post_max), cisquel_ini_to_num($upload_max));
			if ($max_upload_size > 0)
					$return = $max_upload_size;
		}
	}

	return $return;
}

// Transforme la notation de php.ini (comme '2M') en un entier (2*1048576)
function cisquel_ini_to_num($valeur){ 
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

function cisquel_header_prive($texte) {
	$texte.= '<link rel="stylesheet" type="text/css" href="' . _DIR_PLUGIN_CISQUEL . '_css/style_site_prive.css" />' . "\n";
	return $texte;
}

?>