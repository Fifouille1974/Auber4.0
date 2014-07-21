<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2011                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('formulaires/joindre_document');



function formulaires_ciimport_joindre_document_charger_dist($id_document='new',$fichier='',$redirect='',$id_objet=0,$objet='',$mode = 'auto',$galerie = false, $proposer_media=true, $proposer_ftp=true){

	$valeurs = formulaires_joindre_document_charger_dist($id_document,$id_objet,$objet,$mode,$galerie,$proposer_media,$proposer_ftp);

	$valeurs['fichier'] = $fichier;
	
	return $valeurs;
}


function formulaires_ciimport_joindre_document_verifier_dist($id_document='new',$fichier='',$redirect='',$id_objet=0,$objet='',$mode = 'auto',$galerie = false, $proposer_media=true, $proposer_ftp=true){

//	return formulaires_joindre_document_verifier_dist($id_document,$id_objet,$objet,$mode,$galerie,$proposer_media,$proposer_ftp);
	return $erreurs;
}


function formulaires_ciimport_joindre_document_traiter_dist($id_document='new',$fichier='',$redirect='',$id_objet=0,$objet='',$mode = 'auto',$galerie = false, $proposer_media=true, $proposer_ftp=true){

	$res = array('editable'=>true);

	$ancre = '';
	// on joint un document deja dans le site
	if (_request('joindre_mediatheque')){
		if ($refdoc_joindre = intval(preg_replace(',^(doc|document|img),','',_request('refdoc_joindre')))){
			// lier le parent en plus
			$champs = array('ajout_parents' => array("$objet|$id_objet"));
			include_spip('action/editer_document');
			document_modifier($refdoc_joindre,$champs);
			set_request('refdoc_joindre',''); // vider la saisie
			$ancre = $refdoc_joindre;
			$sel[] = $refdoc_joindre;
			$res['message_ok'] = _T('medias:document_attache_succes');
		}
	}
	// sinon c'est un upload
	else {
		$ajouter_documents = charger_fonction('ajouter_documents', 'action');

		$mode = joindre_determiner_mode($mode,$id_document,$objet);
		include_spip('inc/joindre_document');
		$files = joindre_trouver_fichier_envoye();

		$nouveaux_doc = $ajouter_documents($id_document,$files,$objet,$id_objet,$mode);

		if (defined('_tmp_dir'))
			effacer_repertoire_temporaire(_tmp_dir);

		// checker les erreurs eventuelles
		$messages_erreur = array();
		$nb_docs = 0;
		$sel = array();
		foreach ($nouveaux_doc as $doc) {
			if (!is_numeric($doc))
				$messages_erreur[] = $doc;
			// cas qui devrait etre traite en amont
			elseif(!$doc){
				$messages_erreur[] = _T('medias:erreur_insertion_document_base',array('fichier'=>'<em>???</em>'));
			}
			else{
				if (!$ancre)
					$ancre = $doc;
				$sel[] = $doc;
				$nb_docs++;
			}
		}
		if (count($messages_erreur))
			$res['message_erreur'] = implode('<br />',$messages_erreur);
		if ($nb_docs){
			$res['message_ok'] = singulier_ou_pluriel($nb_docs,'medias:document_installe_succes','medias:nb_documents_installe_succes');
		}
		if ($ancre)
			$res['redirect'] = "#doc$ancre";
	}
	if ($nb_docs OR isset($res['message_ok'])){
		$callback = "";
		if ($ancre)
			$callback .= "jQuery('#doc$ancre a.editbox').eq(0).focus();";
		if (count($sel)){
			$sel = "#doc".implode(",#doc",$sel);
		  $callback .= "jQuery('$sel').animateAppend();";
		}
		$js = "if (window.jQuery) jQuery(function(){ajaxReload('documents',{callback:function(){ $callback }});});";
		$js = "<script type='text/javascript'>$js</script>";
		if (isset($res['message_erreur']))
			$res['message_erreur'].= $js;
		else
	    $res['message_ok'] .= $js;
	}

	if ($redirect=='ciimport_auteurs' OR $redirect=='ciimport_motscles')
		$res['redirect'] = generer_url_ecrire($redirect,"show_docs=$ancre");
	
	return $res;
}

?>