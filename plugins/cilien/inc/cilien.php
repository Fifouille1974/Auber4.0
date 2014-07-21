<?php
/**
 * Plugin cilien
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
if (!defined('_ECRIRE_INC_VERSION')) return;

// Pour contourner une limite de SPIP 2.1 que j'ai signalee (http://core.spip.org/issues/3116)
// et qui a ete corrigee dans SPIP 3.0.14 mais pas dans SPIP 2.1
define('_CILIEN_EXTRAIRE_DOMAINE', '/^(?:[^\W_]((?:[^\W_]|-){0,61}[^\W_,])?\.)+[a-z0-9]{2,6}\b/Si');

include_spip('inc/lien');
include_spip('inc/filtres');
include_spip('inc/texte');

function cilien_traite_objet($objet,$id_objet,$champs){

	if ($objet AND $id_objet AND is_array($champs)){
	
		$letexte = '';
		// concatener les champs
		foreach ($champs AS $cle=>$valeur){
			if (is_string($valeur)){
				// article virtuel
				if ($cle=='chapo' AND $valeur AND substr($valeur,0,1)=='=')
					$valeur = substr($valeur,1);
				
				$letexte .= $valeur.' ';
			}
		}
	
		// marquer tous ses liens existants comme obsolete
		if (count($flux['data'])>=0.5*count($flux['args']['champs'])){
			sql_updateq("spip_cilien", array('obsolete'=>'oui'), "objet='".$objet."' AND id_objet=$id_objet");
			$objet_traite[$objet][$id_objet] = true;
		}
	
		// passer le contenu dans expanser_liens pour transformer les liens
		// Pour contourner une limite de SPIP 2.1 que j'ai signalee (http://core.spip.org/issues/3116)
		// et qui a ete corrigee dans SPIP 3.0.14 mais pas dans SPIP 2.1
		if (spip_version()>=3)
			cilien_extrait_liens($objet,$id_objet,expanser_liens($letexte));
		else
			cilien_extrait_liens($objet,$id_objet,cilien_expanser_liens($letexte));

		// supprimer les liens obsoletes de l'objet / id_objet
		sql_delete("spip_cilien", "objet='".$objet."' AND id_objet=".$id_objet." AND obsolete='oui'" );
	}
}


function cilien_extrait_liens($objet,$id_objet,$texte=''){
	
	if (strstr($texte, 'href=')){	// evite le preg_match_all si inutile
		if (preg_match_all(_EXTRAIRE_LIENS,$texte, $regs, PREG_SET_ORDER)){
			foreach ($regs as $r) {
				$tout = $r[0];
				
				$url ='';
				$l = '';
				if (strstr($tout, 'href=')){
					if (preg_match("/href='([^']*)'/",$tout, $result))
						$l = $result[1];
					elseif (preg_match('/href="([^"]*)"/',$tout, $result))
						$l = $result[1];
				}
	
				if ($l){
					// Enlever le slash de fin
					if (substr($l,-1)=='/')
						$l = substr($l,0,-1);

					// Ne pas memoriser les url de l'espace prive
					// Sur [lien->art8] SPIP 2.1 met une URL absolue dans l'espace prive
					// Sur <doc18> SPIP 2.1 met une URL relative avec ../IMG/ dans l'espace prive
					if (strpos($l,_DIR_RESTREINT_ABS)===FALSE AND substr($l,0,3)!=_DIR_RACINE) {
						
						// Reperer le protocole http
						// Si pas de protocole de lien -> lien relatif
						if (preg_match(',^(https?):/*,S', $l, $m)) {
							$l = substr($l, strlen($m[0]));
							$protocol = $m[1];
	
							// valider le nom de domaine
							if (preg_match(_CILIEN_EXTRAIRE_DOMAINE, $l)){
								// supprimer les ponctuations a la fin d'une URL
								preg_match('/^(.*?)([,.;?]?)$/', $l, $k);
								if (isset($k[1])  AND $k[1])
									$url = $protocol.'://'.$k[1];
								else
									$url = $protocol.'://'.$l;
							}
						} else {
							// precaution
							if (substr($l, 0, 3)=="../")
								$l = substr($l,3);
							
							// eviter les autres protocoles (mailto, etc.) et les ancres
							if (strpos(substr($l, 0, 12),':')===FALSE AND substr($l, 0, 1)!="#")
								$url = $l;
						}
					}
				}
				
				// Memoriser l'URL
				if ($url)
					cilien_enregistre_lien($objet,$id_objet,$url);
			
			}
		}
	}
}


function cilien_enregistre_lien($objet,$id_objet,$url){
	$id_objet = intval($id_objet);
	
	if ($objet AND $id_objet AND $url){		
		// regarder si le lien est deja reference pour cet objet et le creer eventuellement
		if ($row = sql_fetsel("*", "spip_cilien", "url=".sql_quote($url)." AND objet='".$objet."' AND id_objet=".$id_objet))
			sql_updateq("spip_cilien", array('maj'=>'NOW()', 'obsolete'=>'non'), "url=".sql_quote($row['url'])." AND objet='".$row['objet']."' AND id_objet=".$row['id_objet']);
		else
			$id_lien = sql_insertq("spip_cilien",array('objet' => $objet, 'id_objet' => $id_objet, 'url' => $url, 'obsolete' => 'non'));		
	}
}

// Recenser les liens par tranche
function cilien_traite_tranche($type_pas=''){

	$cibreak = false;
	$pas = 50;
	$dureemax = 3;	// duree maximum en secondes
	if ($type_pas=='grandpas'){
		$pas = 500;
		$dureemax = 10;	// duree maximum en secondes
	}	
		
	$time = time();

	if (!sql_count(sql_select("*", "spip_cilien_tranche"))){
		sql_insertq("spip_cilien_tranche",array('table_objet' => 'spip_articles', 'objet' => 'article', 'date_tranche' => ''));
		sql_insertq("spip_cilien_tranche",array('table_objet' => 'spip_rubriques', 'objet' => 'rubrique', 'date_tranche' => ''));
	}

	$tranches = sql_allfetsel("*","spip_cilien_tranche");

	foreach($tranches as $tranche) {
		if (!$cibreak){
			$table = $tranche['table_objet'];
			$objet = $tranche['objet'];
			$col_id = id_table_objet($table);		
			$tranche_inf = $tranche['date_tranche'];
			$tranche_sup = $tranche_inf;
			$where = '';
			
			if ($tranche_inf)
				$where = 'maj > '.date("YmdHis",strtotime($tranche['date_tranche']));
	
			// on prend au plus $pas enregistrement a partir de $tranche_inf
			$res = sql_select("*",$table,$where, '', maj, '0,'.$pas);
			while ($row = sql_fetch($res)){
				cilien_traite_objet($objet,$row[$col_id],$row);
				$tranche_sup = $row['maj'];
				// tranche auto ajustable
				// si plus de dureemax secondes on sort de la boucle
				$n = time() - $time;
				if ($n > $dureemax){
					$cibreak = true;
					break;
				}
			}
	
			if ($row = sql_fetsel("*", "spip_cilien_tranche", "table_objet='".$table."'"))
				sql_updateq("spip_cilien_tranche", array('date_tranche'=>$tranche_sup), "table_objet='".$table."'");
			else
				$return = sql_insertq("spip_cilien_tranche",array('table_objet' => $table, 'objet' => $objet, 'date_tranche' => $tranche_sup));
		}
	}	
}

// Pour contourner une limite de SPIP 2.1 que j'ai signalee (http://core.spip.org/issues/3116)
// et qui a ete corrigee dans SPIP 3.0.14 mais pas dans SPIP 2.1
function cilien_expanser_liens($texte, $connect='') {

	$texte = traiter_raccourci_ancre(traiter_raccourci_glossaire(cilien_traiter_raccourci_liens($texte)));	
	$sources = $inserts = $regs = array();
	if (preg_match_all(_RACCOURCI_LIEN, $texte, $regs, PREG_SET_ORDER)) {
		$lien = charger_fonction('lien', 'inc');
		foreach ($regs as $k => $reg) {

			$inserts[$k] = '@@SPIP_ECHAPPE_LIEN_' . $k . '@@';
			$sources[$k] = $reg[0];
			$texte = str_replace($sources[$k], $inserts[$k], $texte);

			list($titre, $bulle, $hlang) = traiter_raccourci_lien_atts($reg[1]);
			$r = $reg[count($reg)-1];
			// la mise en lien automatique est passee par la a tort !
			// corrigeons pour eviter d'avoir un <a...> dans un href...
			if (strncmp($r,'<a',2)==0){
				$href = extraire_attribut($r, 'href');
				// remplacons dans la source qui peut etre reinjectee dans les arguments
				// d'un modele
				$sources[$k] = str_replace($r,$href,$sources[$k]);
				// et prenons le href comme la vraie url a linker
				$r = $href;
			}
			$regs[$k] = $lien($r, $titre, '', $bulle, $hlang, '', $connect);
		}
	}

	// on passe a traiter_modeles la liste des liens reperes pour lui permettre
	// de remettre le texte d'origine dans les parametres du modele
	$texte = traiter_modeles($texte, false, false, $connect, array($inserts, $sources));
 	$texte = corriger_typo($texte);
	$texte = str_replace($inserts, $regs, $texte);
	
	return $texte;
}

function cilien_traiter_raccourci_liens($t) {
	return preg_replace_callback(_EXTRAIRE_LIENS, 'cilien_traiter_autoliens', $t);
}

function cilien_traiter_autoliens($r) {
	
	if (count($r)<2) return reset($r);
	list($tout, $l) = $r;
	if (!$l) return $tout;
	// reperer le protocole
	if (preg_match(',^(https?):/*,S', $l, $m)) {
		$l = substr($l, strlen($m[0]));
		$protocol = $m[1];
	} else 	$protocol = 'http';
	// valider le nom de domaine
	if (!preg_match(_CILIEN_EXTRAIRE_DOMAINE, $l)) return $tout;
	// supprimer les ponctuations a la fin d'une URL
	preg_match('/^(.*?)([,.;?]?)$/', $l, $k);
	$url = $protocol.'://'.$k[1];
	$lien = charger_fonction('lien', 'inc');
	$r = $lien($url,'','','','','nofollow') . $k[2];
	// si l'original ne contenait pas le 'http:' on le supprime du clic
	return $m ? $r : str_replace('>http://', '>', $r);
}

function cilien_avancement() {
	$return = array();

	$tranches = sql_allfetsel("*","spip_cilien_tranche");

	if (!$tranches)
		$tranches = array(
			array('table_objet'=>'spip_articles','objet'=>'article','date_tranche'=>''),
			array('table_objet'=>'spip_rubriques','objet'=>'rubrique','date_tranche'=>'')
			);
	
	foreach($tranches as $tranche){
		$table = $tranche['table_objet'];
		$objet = $tranche['objet'];
		$tranche_inf = $tranche['date_tranche'];
		
		if ($tranche_inf){
			$where = 'maj > '.date("YmdHis",strtotime($tranche_inf));
			$reste = sql_countsel($table,$where);
			$total = sql_countsel($table);
			$return[$objet] = 100*($total-$reste)/$total;
		} else {
			$return[$objet] = 0;
		}
	}
	
	return $return;
}

?>