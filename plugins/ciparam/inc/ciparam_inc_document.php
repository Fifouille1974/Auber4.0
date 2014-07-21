<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

 
// Le titre du document doit être renseigné par défaut avec le nom du fichier sans extension  
function ciparam_post_edition($flux){
	if($flux['args']['operation']=='ajouter_document'){
		$id_document = intval($flux['args']['id_objet']);
		
		if ($id_document) {
			$row = sql_fetsel('*', 'spip_documents', 'id_document='.$id_document);
			$titre = $row['titre'];
			$mode = $row['mode'];
			if (!$titre AND $mode == 'document') {
				$titre = ciparam_titrefichier($row['fichier']);
				sql_updateq("spip_documents", array("titre" => $titre), "id_document=$id_document");
			}
		}
	}
	return $flux;
}

function ciparam_titrefichier($fichier) {
	// enlever l'extension et le chemin
	$titre=$fichier;
	$pos1 = strrpos($titre,".");
	if (!($pos1 === false)) $titre=substr($titre,0,$pos1);

	$pos3 = strrpos($titre,"/");
	if (!($pos3 === false)) $titre=substr($titre,$pos3+1);
	
	return $titre;
}

?>