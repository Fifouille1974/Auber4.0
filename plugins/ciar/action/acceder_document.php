<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */


if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/headers');

/**
 * Protection par filtre d'acces
 * Filtre d'acces aux documents joints et aux images joints (hors logos)
 * 
 * @return boolean
 */
if(!function_exists('action_acceder_document')) {
function action_acceder_document() {
	include_spip('inc/documents');

	// $file exige pour eviter le scan id_document par id_document
	$f = rawurldecode(_request('file'));
	$file = get_spip_doc($f);
	$arg = rawurldecode(_request('arg'));

	$niveau_acces = "";
	$status = $dcc = false;
	if (strpos($f,'../') !== false
	OR preg_match(',^\w+://,', $f)) {
		$status = 403;
	}
	else if (!file_exists($file) OR !is_readable($file)) {
		$status = 404;
	} else {
		
			$where = "fichier=".sql_quote(set_spip_doc($file))
			. ($arg ? " AND id_document=".intval($arg): '');
	
			$doc = sql_fetsel("id_document, titre, fichier, extension", "spip_documents",$where);
			$ext = $doc['extension'];
			$type = sql_fetsel("mime_type, inclus", "spip_types_documents","extension='".$ext."'");
			
			if (!$doc) {
				$status = 404;
			} else {
	
				// ETag pour gerer le status 304
				$ETag = md5($file . ': '. filemtime($file));
				if (isset($_SERVER['HTTP_IF_NONE_MATCH'])
				AND $_SERVER['HTTP_IF_NONE_MATCH'] == $ETag) {
					http_status(304); // Not modified
					exit;
				}
	
				// Verifier les droits d'acces au document
				include_spip('ciar_fonctions');
				$niveau_acces = ciar_acces_document($doc['id_document']);
				if ($niveau_acces=='interdit')
					$status = 403;

			}
	
	}

	switch($status) {

	case 403:
		include_spip('inc/minipres');
		echo minipres();
		break;

	case 404:
		http_status(404);
		include_spip('inc/minipres');
		echo minipres(_T('erreur').' 404',
			_T('info_document_indisponible'));
		break;

	default:
		header("Content-Type: ". $type['mime_type']);

		// pour les images ne pas passer en attachment
		// sinon, lorsqu'on pointe directement sur leur adresse,
		// le navigateur les downloade au lieu de les afficher

//		if ($type['inclus']=='non') {
		if ($type['inclus']=='non' AND !(defined('_CIAR_DOC_PAS_ATTACHE') AND _CIAR_DOC_PAS_ATTACHE=='oui')) {

		  // Si le fichier a un titre avec extension,
		  // ou si c'est un nom bien connu d'Unix, le prendre
		  // sinon l'ignorer car certains navigateurs pataugent

			$f = basename($file);
			if (isset($doc['titre'])
				AND (preg_match('/^\w+[.]\w+$/', $doc['titre']) OR $doc['titre'] == 'Makefile'))
				$f = $doc['titre'];

			// ce content-type est necessaire pour eviter des corruptions de zip dans ie6
			header('Content-Type: application/octet-stream');

			header("Content-Disposition: attachment; filename=\"$f\";");
			header("Content-Transfer-Encoding: binary");

			if ($niveau_acces=='libre') {
				// fix for IE catching or PHP bug issue
				header("Pragma: public");
				header("Expires: 0"); // set expiration time
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			}
		}

		if ($niveau_acces=='libre') {
			// ETag pour gerer le status 304
			header('ETag: '.$ETag);
		}
		
		if ($niveau_acces=='autorise') {
			header("Pragma: no-cache");
			header("Expires: 0");
			header("Cache-Control: no-store, no-cache, must-revalidate");
		}	

		if ($cl = filesize($file))
			header("Content-Length: ". $cl);

		readfile($file);
		break;
	}

}
}

?>