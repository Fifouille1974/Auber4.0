<?php

if (defined('_DIR_PLUGIN_CIAG'))
	include_spip('inc/ciag_commun');


function ciar_statut_auteur_rubrique($statut, $ci_statut_auteur_rub, $cistatut='') {

	static $titre_des_statuts ='';

	// eviter de retraduire a chaque appel
	if (!$titre_des_statuts) {
		
		$titre_des_statuts = array(
		"1comite" => _T('ciar:titre_1comite'),
		"ciredval" => _T('ciar:titre_ciredval'),
		"ciredvaltout" => _T('ciar:titre_ciredvaltout'),
		"0minirezo" => _T('ciar:titre_0minirezo'),
		"eccma" => _T('ciar:titre_eccma'),
		"6forum" => _T('ciar:titre_6forum')
		);
		
	}

	if ($cistatut) $statut = $cistatut;
	if ($ci_statut_auteur_rub) $statut = $ci_statut_auteur_rub;
	  
	return $titre_des_statuts[$statut];
}

?>