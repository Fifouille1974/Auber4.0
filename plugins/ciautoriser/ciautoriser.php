<?php
/**
 * Plugin ciautoriser
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */


// declarer la fonction du pipeline
function ciautoriser_autoriser(){}

// Autoriser a publier dans la rubrique
if(!function_exists('autoriser_rubrique_publierdans')) {
function autoriser_rubrique_publierdans($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);
}
}

// Autoriser a creer un article dans la rubrique
if(!function_exists('autoriser_rubrique_creerarticledans')) {
function autoriser_rubrique_creerarticledans($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);
}
}

// Voir une rubrique
if(!function_exists('autoriser_rubrique_voir')) {
function autoriser_rubrique_voir($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);	
}
}

// Voir un article
if(!function_exists('autoriser_article_voir')) {
function autoriser_article_voir($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);	
}
}

// Voir une breve
if(!function_exists('autoriser_breve_voir')) {
function autoriser_breve_voir($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);	
}
}

// Voir un site reference
if(!function_exists('autoriser_site_voir')) {
function autoriser_site_voir($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);	
}
}

// Voir un document
if(!function_exists('autoriser_document_voir')) {
function autoriser_document_voir($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);	
}
}

// Autoriser a modifier la rubrique
if(!function_exists('autoriser_rubrique_modifier')) {
function autoriser_rubrique_modifier($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);
}
}

// Autoriser a modifier l'article
// Surcharge de la dist avec ajout d'un pipeline
if(!function_exists('autoriser_article_modifier')) {
function autoriser_article_modifier($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);
}
}

// Autoriser a modifier la breve
if(!function_exists('autoriser_breve_modifier')) {
function autoriser_breve_modifier($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);
}
}

// Autoriser a modifier site reference
if(!function_exists('autoriser_site_modifier')) {
function autoriser_site_modifier($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);
}
}

// Autoriser a modifier un auteur
if(!function_exists('autoriser_auteur_modifier')) {
function autoriser_auteur_modifier($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);
}
}

// Autoriser a modifier un groupe de mots $id
// y compris en ajoutant/modifiant les mots lui appartenant
if(!function_exists('autoriser_groupemots_modifier')) {
function autoriser_groupemots_modifier($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);
}
}

// Autoriser a modifier un mot $id
if(!function_exists('autoriser_mot_modifier')) {
function autoriser_mot_modifier($faire, $type, $id, $qui, $opt) {
	return ciautoriser_pipeline($faire, $type, $id, $qui, $opt);
}
}


include_spip('inc/filtres');

// Ajout d'un pipeline
function ciautoriser_pipeline($faire, $type, $id, $qui, $opt) {
	$param = array('faire'=>$faire,'type'=>$type,'id'=>$id,'qui'=>$qui,'opt'=>$opt);
	
	// Chercher une fonction d'autorisation "dist"
	// Dans l'ordre on va chercher autoriser_type_faire_dist, autoriser_type_dist,
	// autoriser_faire_dist, autoriser_defaut_dist
	$fonctions = $type
		? array (
			'autoriser_'.$type.'_'.$faire.'_dist',
			'autoriser_'.$type.'_dist',
			'autoriser_'.$faire.'_dist',
			'autoriser_defaut_dist'
		)
		: array (
			'autoriser_'.$faire.'_dist',
			'autoriser_defaut_dist'
		);
		
	foreach ($fonctions as $f) {
		if (function_exists($f)) {
			include_spip('inc/filtres');			
			if (spip_version()>=3) {
				if ($f=='autoriser_voir_dist')
					$f='ciautoriser_voir_spip3';
			}
			
			if ($f=='autoriser_rubrique_modifier_dist')
				$f='ciautoriser_rubrique_modifier';
			
			$autoriser = $f($faire,$type,$id,$qui,$opt);
			$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'dist');
			break;
		}
	}

	// Ajout d'un pipeline
	$param = pipeline('ciautoriser', $param);
	
	return ciautoriser_ciresultat($param);
}

function ciautoriser_voir_spip3($faire, $type, $id, $qui, $opt) {
	# securite, mais on aurait pas du arriver ici !
/*	
	if (function_exists($f='autoriser_'.$type.'_voir') OR function_exists($f='autoriser_'.$type.'_voir_dist')){
		return $f($faire, $type, $id, $qui, $opt);
	}
*/
	if ($qui['statut'] == '0minirezo') return true;
	// seuls les admin peuvent voir un auteur
	if ($type == 'auteur')
		return false;
	// sinon par defaut tout est visible
	// sauf cas particuliers traites separemment (ie article)
	return true;
}


// Il faut contourner un bug de SPIP. En effet, la fonction autoriser_rubrique_modifier_dist
// indique que l'on a le droit de modifier la rubrique zéro !!!
function ciautoriser_rubrique_modifier($faire, $type, $id, $qui, $opt) {
	return
		$id AND autoriser('publierdans', 'rubrique', $id, $qui, $opt);
}
	

// Résultat d'un ensemble d'autorisations
// (dist OR cumul_des_OR) AND cumul_des_AND
function ciautoriser_ciresultat($param) {
	$dist = true;
	$cumul_des_AND = true;
	$cumul_des_OR = false;
	
	if (isset($param['autorisations'])) {
		if (is_array($param['autorisations'])) {
			foreach($param['autorisations'] as $key=>$val){
				if ($val['operateur']=='dist') {
					if ($dist)
						$dist = $val['autoriser'];
				} elseif ($val['operateur']=='AND') {
					if ($cumul_des_AND)
						$cumul_des_AND = $val['autoriser'];
				} elseif ($val['operateur']=='OR') {
					if (!$cumul_des_OR)
						$cumul_des_OR = $val['autoriser'];
				}
			}
		}
	}

	// subtilite de SPIP dans generer_url_document_dist
	if ($param['type']=='document' AND !is_bool($dist) AND $dist=='htaccess') {
		if (intval($param['id'])>0)
			$dist = true;
		else
			return $dist;
	}

	return ($dist OR $cumul_des_OR) AND $cumul_des_AND;
}

?>