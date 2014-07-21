<?php
/**
 * Plugin redacteur valideur
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');


function cirv_affiche_milieu($flux) {
  $exec = $flux["args"]["exec"];
 
  if ($exec == "auteur_infos") {
      $id_auteur = $flux["args"]["id_auteur"];
      $ret = "<div id='pave_selection'>";
      $ret .= recuperer_fond('prive/editer/cirv',array_merge($_GET,array('type'=>'auteur','id'=>$id_auteur)));
      $ret .= "</div>";
      $flux["data"] .= $ret;
  }
  return $flux;
}


function cirv_afficher_complement_objet($flux){
	if (spip_version()>=3) {
		if ($flux['args']['type']=='auteur'
			AND $id_auteur=intval($flux['args']['id'])
			AND (autoriser('modifier','auteur',$id_auteur))) {
		      $ret = "<div id='pave_selection'>";
		      $ret .= recuperer_fond('prive/editer/cirv',array_merge($_GET,array('type'=>'auteur','id'=>$id_auteur)));
		      $ret .= "</div>";
		      $flux["data"] .= $ret;
		}
	}
	return $flux;
}

?>