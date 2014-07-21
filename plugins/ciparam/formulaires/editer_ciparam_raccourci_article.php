<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
include_spip('inc/ciparam_inc_commun');

function formulaires_editer_ciparam_raccourci_article_charger($type,$id){
	
	if (!autoriser('modifier','article',$id))
		return false;

	$valeurs = array();
	$table = table_objet_sql($type);
	$id_table_objet = id_table_objet($type);
	$champ = "raccourci";
	$valeurs[$id_table_objet] = intval($id);

	$valeurs['_liste_raccourci_article'] = ciparam_liste_raccourci($table,$id_table_objet,$id,$champ);
	$valeurs['_choix_raccourci_article'] = ciparam_choix_raccourci($table,$id_table_objet,$id,$champ);

	if (!$valeurs['_choix_raccourci_article'])
		$valeurs['masquer'] = "oui";
	else	
		$valeurs['masquer'] = "non";
	
	$valeurs['supprimer_lien'] = '';
	$valeurs['_hidden'] = "<input type='hidden' name='$id_table_objet' value='$id' />";

	return $valeurs;
}

function formulaires_editer_ciparam_raccourci_article_traiter($type,$id){
	$valeurs = array();
	$table_objet_sql = table_objet_sql($type);
	$id_table_objet = id_table_objet($type);

	if (spip_version()>=3) {
		$supprimer = _request('supprimer_lien');
		if ($supprimer){
			foreach($supprimer as $k=>$v) {
				if (preg_match(",^\w+-[\w*]+-[\w*]+-[\w*]+,",$k)){
					$lien = explode("-",$k);
					list($objet_source,$ids,$objet_lie,$idl) = $lien;
					if ($type AND $id AND $objet_lie=='raccourci' AND $raccourci=$idl) {
					  // desassocier un des raccourcis
						sql_delete("spip_ci_raccourcis_articles", "id_article=$id AND raccourci='$raccourci'");
					}
				}
			}
			// Invalider les caches	
			include_spip('inc/invalideur');
			suivre_invalideur("id='$type/$id'");			
		}
	}	
	
	ciparam_ajout_raccourci($type,"spip_ci_raccourcis_articles",$id_table_objet,$id,'raccourci',_request('raccourci'));
	
	return array('message_ok'=>'','editable'=>true);
}

?>