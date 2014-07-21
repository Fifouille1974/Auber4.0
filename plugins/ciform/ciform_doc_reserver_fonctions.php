<?php
/**
 * Plugin ciform
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
function ciform_doc_reserver($id_article,$id_document){
	
	$id_document = intval($id_document);
	$id_article = intval($id_article);

	if(!$id_document OR !$id_article)
		return 'non';

	// verifier que l'utilisateur est authentifie
	if (!isset($GLOBALS['visiteur_session']['id_auteur']))
		return 'non';

	// verifier que le document appartient a l'article
	$n = sql_countsel("spip_documents_liens", "id_document=".intval($id_document)." AND objet='article' AND id_objet=".intval($id_article));
	if (!$n)
		return 'non';

	// si plugin ciparam est actif, verifier que l'article a la forme d'article requise
	if (defined('_DIR_PLUGIN_CIPARAM')) {
		$row = sql_fetsel("ciforme", "spip_articles", "id_article=$id_article", "", "");
		if ($row['ciforme']!='_wiki')
			return 'non';
	}
		
	// vérifier si le document est déjà réservé
	if ($nom = ciform_nom_resapj($id_article, $id_document))
		return 'non';


	// Réserver le document
	ciform_maj_resapj($id_article, array($id_document => $GLOBALS['visiteur_session']['id_auteur'], "time_".$id_document=> time()));
	
	return 'oui';
}


function ciform_maj_resapj($id_article,$arg=array()){

	$texte = ciform_ps($id_article);
	if (!$texte)
		$texte = '';

	foreach ($arg as $cle => $valeur) {
		if($cle){	
			$cle = strval($cle);			
			$pattern = ',<'.$cle.'>.*</'.$cle.'>,Uims';
			$remplacement = '<'.$cle.'>'.$valeur.'</'.$cle.'>';

			if (preg_match($pattern, $texte, $reg)) {
				$texte = preg_replace($pattern, $remplacement, $texte);				
			} else {
				$texte .= $remplacement;
			}
		}
	}
			
	sql_updateq("spip_articles", array("ps" => $texte), "id_article=$id_article");
	
	return true;
}

?>