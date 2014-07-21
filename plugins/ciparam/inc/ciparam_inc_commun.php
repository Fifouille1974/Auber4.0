<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

include_spip('inc/filtres');
 
function ciparam_lire_valeur($table,$id_table,$id_valeur,$champ) {
	$valeur = 'non';
	
	$result = sql_select("$champ", "$table", "$id_table=$id_valeur","","");	
	while ($row = sql_fetch($result)) {
		$valeur = $row[$champ];
	}

	return $valeur;
}

function ciparam_lire_valeurs($table,$id_table,$id_valeur,$champ) {
	$valeur = 'non';
	
	$result = sql_select("$champ", "$table", "$id_table=$id_valeur","","");	
	while ($row = sql_fetch($result)) {
		$valeurs[] = $row[$champ];
	}

	return $valeurs;
}

function ciparam_ajout_valeur($table,$id_table,$id_valeur,$champ,$valeur) {
	$id = sql_insertq("$table", array(
	$id_table => $id_valeur,
	$champ =>  $valeur));

	return true;
}

function ciparam_maj_valeur($table,$id_table,$id_valeur,$champ,$valeur) {
	$exist = false;
	$result = sql_select("$champ", "$table", "$id_table=$id_valeur","","");	
	while ($row = sql_fetch($result)) {
		$exist = true;
	}

	
	if ($exist) {
		sql_updateq("$table", array($champ=>$valeur), "$id_table=$id_valeur");
	} else {
		$id = sql_insertq("$table", array(
		$id_table => $id_valeur,
		$champ =>  $valeur));
	}

	return true;
}

// Anti hack
function ciparam_autoriser_forme($table,$ciforme) {
	$return = false;
	if ($ciforme) {
		if ($ciforme=='__aucune_forme') {
			$return = true;
		} else {
			$formes = ciparam_charger_param_xml("formes-".substr($table,5));
		    foreach ($formes as $forme) {
		    	if ($ciforme==$forme['nom']) {
		    		$return = true;
		    		break;
		    	}
		    }
		}
	}

	return $return;
}

// Anti hack
function ciparam_autoriser_raccourci($table,$ciraccourci) {
	$return = false;
	if ($ciraccourci) {
		$raccourcis = ciparam_charger_param_xml("raccourcis-".substr($table,19));
	    foreach ($raccourcis as $raccourci) {
	    	if ($ciraccourci==$raccourci['nom']) {
	    		$return = true;
	    		break;
	    	}
	    }
	}

	return $return;
}

// Anti hack
function ciparam_autoriser_tri($table,$nom,$ordre) {
	$return = false;
	if ($nom) {
		$tris = ciparam_charger_param_xml("tris-".substr($table,5));
	    foreach ($tris as $tri) {
	    	if ($nom==$tri['nom'] AND $ordre==$tri['ordre']) {
	    		$return = true;
	    		break;
	    	}
	    }
	}

	return $return;
}

// mise a jour : forme
function ciparam_maj_forme($objet,$table,$id_table,$id_valeur,$champ,$valeur) {
	
	// anti hack
	if (ciparam_autoriser_forme($table,$valeur)) {
		
		// cas de deselection d'une forme
		if ($champ=='ciforme' AND $valeur=='__aucune_forme')
			$valeur = '';

		ciparam_maj_valeur($table,$id_table,$id_valeur,$champ,$valeur);
		
		// Invalider les caches	
		include_spip('inc/invalideur');
		if (spip_version()>=3)
			suivre_invalideur("id='$objet/$id_valeur'");
		else
			suivre_invalideur("id='$id_table/$id_valeur'");
		
	}	
	return true;
}

// mise a jour : tri
function ciparam_maj_tri($table,$id_table,$id_valeur,$champ,$valeur) {
	
	// ordre de tri des articles d'une rubrique
	if ($champ=='citri') {		
		list($nom, $ordre) = explode("_", $valeur);
		// anti hack
		if (ciparam_autoriser_tri($table,$nom,$ordre)) {
			$valeur = ciparam_tri_to_row($nom,$ordre);
			sql_updateq("$table", array('citri'=>$valeur['tri'],'citrinum'=>$valeur['trinum'],'citriinverse'=>$valeur['triinverse']), "$id_table=$id_valeur");
		}
	}
}

// ajout : raccourci
function ciparam_ajout_raccourci($objet,$table,$id_table,$id_valeur,$champ,$valeur) {
	// cas de deselection d'un raccourci	
	if ($champ=='raccourci' AND $valeur=='__aucun_raccourci') {
		// ne rien faire
	} else {	
		// anti hack
		if (ciparam_autoriser_raccourci($table,$valeur)){
			ciparam_ajout_valeur($table,$id_table,$id_valeur,$champ,$valeur);
			
			// Invalider les caches	
			include_spip('inc/invalideur');
			if (spip_version()>=3)
				suivre_invalideur("id='$objet/$id_valeur'");
			else
				suivre_invalideur("id='$id_table/$id_valeur'");
			
		}
	}
}

function ciparam_choix_forme($table,$id_table_objet,$id,$champ) {
	$texte = "";
	$valeur = "";
	
	if ($id) {	
		$valeur = ciparam_lire_valeur($table,$id_table_objet,$id,$champ);
		$formes = ciparam_charger_param_xml("formes-".substr($table,5));
    	$formes = ciparam_order_array($formes,'description');

		if ($formes) {
			$texte = "\n<select name=$champ class=fondl style=width:410px;font-size:11px;>\n";
	    	if (!$valeur) 
	    		$selected = "selected";
    		$texte .= '<option value=__aucune_forme '.$selected.'></option>\n';
			
		    foreach ($formes as $forme) {
		    	if ($valeur AND $valeur==$forme['nom']) 
		    		$selected = "selected";
		    	else
			    	$selected = "";

		    	$nom = $forme['nom'];
		    	$description = $forme['description'];
	    		$texte .= "<option value=$nom $selected>".$description."</option>\n";
		    }
		    $texte .="</select>\n";
		}
	}
	
	return $texte;  
}	



function ciparam_choix_tri($table,$id_table_objet,$id,$champ) {
	$valeur = "";
	$ordre = "";	// num ou desc ou ''
	$texte = "";
	
	if ($id) {
		// valeur actuelle
		$result = sql_select("*", "$table", "$id_table_objet=$id","","");	
		$row = sql_fetch($result);
		$row_to_tri = ciparam_row_to_tri($row);
		$valeur = $row_to_tri['nom'];
		$ordre = $row_to_tri['ordre'];

		
    	// liste des tris
		$tris = ciparam_charger_param_xml("tris-".substr($table,5));
    	$tris = ciparam_order_array($tris,'description');
	    
		if ($tris) {
			$texte = "\n<select name=$champ class=fondl style=width:410px;font-size:11px;>\n";

		    foreach ($tris as $tri) {
		    	$selected = "";
		    	if ($valeur==$tri['nom'] AND $ordre==$tri['ordre'])
		    		$selected = "selected";

		    	$nom = $tri['nom']."_".$tri['ordre'];
		    	$description = $tri['description'];
	    		$texte .= "<option value=$nom $selected>".$description."</option>\n";
		    }
		    $texte .="</select>\n";
			
		}
	}
	
	return $texte;  
}	


function ciparam_choix_raccourci($table,$id_table_objet,$id,$champ) {
	$texte = "";
	$valeur = "";
	$tablejointure = "spip_ci_raccourcis_".substr($table,5);
	
	if ($id) {	
		$valeurs = ciparam_lire_valeurs($tablejointure,$id_table_objet,$id,$champ);
		$raccourcis = ciparam_charger_param_xml("raccourcis-".substr($table,5));
    	$raccourcis = ciparam_order_array($raccourcis,'description');
	    
		if ($raccourcis) {
			$texte = "\n<select name=$champ class=fondl style=width:410px;font-size:11px;>\n";
    		$texte .= '<option value=__aucun_raccourci selected></option>\n';
			
		    foreach ($raccourcis as $raccourci) {
		    	if (!$valeurs OR !in_array($raccourci['nom'],$valeurs)) { 
			    	$nom = $raccourci['nom'];
			    	$description = $raccourci['description'];
		    		$texte .= "<option value=$nom>".$description."</option>\n";
		    	}
		    }
		    $texte .="</select>\n";
		}
	}
	
	return $texte;  
}	

function ciparam_liste_raccourci($table,$id_table_objet,$id,$champ) {
	$texte = "";
	$valeurs = "";
	$tablejointure = "spip_ci_raccourcis_".substr($table,5);
	
	if ($id) {	
		$valeurs = ciparam_lire_valeurs($tablejointure,$id_table_objet,$id,$champ);
		$raccourcis = ciparam_charger_param_xml("raccourcis-".substr($table,5));
    	$raccourcis = ciparam_order_array($raccourcis,'description');
	    
		if ($valeurs AND $raccourcis) {
			$texte = '<table cellspacing="0" cellpadding="2" border="0" width="100%">';
			$texte .= '<tbody>';
		    foreach ($raccourcis as $raccourci) {
		    	if (in_array($raccourci['nom'],$valeurs)) {
		    		// subtilites de SPIP 
					if (spip_version()>=3) {
						$objet = objet_type($table);
						$fichier_exec = $objet;
					} else {
						if (substr($table,5)=='rubriques')
			    			$fichier_exec = 'naviguer';
			    		elseif (substr($table,5)=='syndic')
			    			$fichier_exec = 'sites';
			    		else
			    			$fichier_exec = substr($table,5);
	    		
			    		// enlever le "s" final le cas echeant
			    		if (substr($table,-1)=="s")
				    		$objet = substr($table,5,-1);
				    	else
				    		$objet = substr($table,5);
					}
		    		
					$own = array(true,$id, $objet, '', substr($table,5), $id_table_objet, $fichier_exec);
			    	$nom = $raccourci['nom'];
			    	$description = $raccourci['description'];
		    		$texte .= '<tr class="tr_liste"><td class="arial2">'.$description.'</td>';

	    			if (spip_version()>=3)
	    				//$objet_source,$ids,$objet_lie,$idl
			    		$texte .= '<td class="action"><button class="button link delete" name="supprimer_lien&#91;'
			    		.$objet.'-'.$id.'-raccourci-'.$nom.'&#93;" value="X">'
			    		._T('ciparam:retirer')."&nbsp;"
		  				. http_img_pack('croix-rouge.gif', "X", " class='puce' style='vertical-align: bottom;'")
		  				.'</button></td></tr>';
					else
			    		$texte .= '<td class="arial1">'.ciparam_editer_raccourcis_un($nom, $own).'</td></tr>';
		    	}
		    }
		    $texte .="</tbody></table>";
		}
	}

	return $texte;  
}	

function ciparam_editer_raccourcis_un($raccourci, $own) {
	$retire = '';

	list ($flag_editable, $id_objet, $objet, $ret, $table, $table_id, $url_base) = $own;
	
	$r =  _T('ciparam:retirer')
		  . "&nbsp;"
		  . http_img_pack('croix-rouge.gif', "X", " class='puce' style='vertical-align: bottom;'");

	$retire = ajax_action_auteur('editer_raccourcis', "$id_objet,$raccourci,$table,$table_id,$objet", $url_base, "$table_id=$id_objet", array($r,''),"&id_objet=$id_objet&objet=$objet");


	return $retire;
}


// trouver dans les plugins actifs les fichiers du nom demande (avec l'extension)
// et retourner le répertoire du plugin (précédé de _DIR_PLUGINS) 
function ciparam_multi_repertoire_plugin_fichier($cifichier){
	$return = '';
	
	include_spip('inc/plugin');
	$liste = liste_plugin_actifs();
	$lcpa = array();
	foreach ($liste as $prefix=>$infos) {
		// compat au moment d'une migration depuis version anterieure
		// si pas de dir_type, alors c'est _DIR_PLUGINS
		if (!isset($infos['dir_type']))
			$infos['dir_type'] = "_DIR_PLUGINS";

		$lcpa[$prefix] = constant($infos['dir_type'])."/".$infos['dir'];
	}
	
	foreach($lcpa as $cpa){
		if (@file_exists($cpa."/".$cifichier)) {
				$return[] = $cpa;
		}
	}
	
	return $return;
}

// Chemin du fichier XML
// parametre : le nom du fichier sans extension
function ciparam_chemin_param_xml($fichier) {
	$repertoire_param = "_ciparam/";
	$extension = ".xml";
	$chemin = "";
	$lang_defaut = "_fr";
	$lang = $GLOBALS['meta']['langue_site'];
	if (isset($GLOBALS['visiteur_session']['lang']))
		$lang = $GLOBALS['visiteur_session']['lang'];

	$chemin = find_in_path($repertoire_param.$fichier.$lang.$extension);
	
	if (!$chemin)
		$chemin = find_in_path($repertoire_param.$fichier.$lang_defaut.$extension);
		
	return $chemin;
}


/* Renvoi sous forme d'un tableau, apres verification des droits,
*  le contenu d'un fichier de paramétrage XML du type
* <item>
*	<nom>nom1</nom>
*	<description>desc1</description>
* </item>
* <item>
*	<nom>nom2</nom>
*	<description>desc2</description>
* </item>
*/
function ciparam_charger_param_xml($fichier) {
	// tableau brut
	$tableau = ciparam_charger_param_xml_brut($fichier);

	// verification des droits le cas echeant
	$return = array();
	$auteur_statut = $GLOBALS['visiteur_session']['statut'];

	if (isset($GLOBALS['visiteur_session']['cistatut']))
		$auteur_cistatut = $GLOBALS['visiteur_session']['cistatut'];
	else
		$auteur_cistatut = '';

	if (isset($GLOBALS['visiteur_session']['cioption']))
		$auteur_cioption = $GLOBALS['visiteur_session']['cioption'];
	else
		$auteur_cioption = '';

	if (spip_version()>=3)
		$auteur_restreint = sql_countsel("spip_auteurs_liens", "objet='rubrique' AND id_auteur=".$GLOBALS['visiteur_session']['id_auteur']);
	else							
		$auteur_restreint = sql_countsel("spip_auteurs_rubriques", "id_auteur=".$GLOBALS['visiteur_session']['id_auteur']);

	if ($tableau) {
	    foreach ($tableau as $row) {
	    	if (isset($row['statuts'])) { 
	    		$statuts = explode(",",$row['statuts']);
	    		if (in_array($auteur_statut,$statuts))
		    		$return[] = $row;
	    		elseif ($auteur_statut=='0minirezo' AND $auteur_restreint<1 AND in_array('webmestre',$statuts))
		    		$return[] = $row;
	    		elseif ($auteur_cistatut AND in_array($auteur_cistatut,$statuts))
		    		$return[] = $row;
	    		elseif ($auteur_cioption AND in_array($auteur_cioption,$statuts))
		    		$return[] = $row;
	    	} else {
	    		$return[] = $row;
	    	}
	    }
	}

	return $return;
}


// Renvoi sous forme d'un tableau, du contenu d'un fichier de paramétrage XML
function ciparam_charger_param_xml_brut($fichier) {
	static $tableau = array();
	if (!$fichier) return array();
	elseif (isset($tableau[$fichier])) return $tableau[$fichier];
	
		$return = array();
		$chemin = _DIR_CACHE.$fichier.'-cache.txt';
		
		// lire le cache
		if (jeune_fichier($chemin, 3600) AND lire_fichier($chemin, $cache)) {
			$return = unserialize($cache);
		} else {
		
			$contenu = "";
			$chemin_complet = "";
			$fichier_ajout = "";
			$ajout = "ajout-";
			$repertoire_param = "_ciparam/";
			
			// si il y une variante avec le fichier demandé
			// alors ne pas prendre en compte le plugin contenant les squelettes de base
			$chemin_complet = ciparam_chemin_param_xml($fichier);
			if ($chemin_complet) {
				$handle = fopen($chemin_complet, "r");
				if ($handle) {
					while (!feof($handle)) {
						$contenu .= fgets($handle, 4096);
					}
					fclose($handle);
				}
				
				$pos = strpos($chemin_complet,$repertoire_param);
				if ($pos === false) {
					spip_log("CI chemin ".$chemin_complet." incorrect");
				} else {
					$fichier_ajout = $repertoire_param.$ajout.substr($chemin_complet,$pos + strlen($repertoire_param));
				}
				
				// si des plugins ajoutent des parametres (sans ecraser)
				if ($repertoires = ciparam_multi_repertoire_plugin_fichier($fichier_ajout)) {
					foreach ($repertoires as $repertoire) {		
						$handle = fopen($repertoire."/".$fichier_ajout, "r");
						if ($handle) {
							while (!feof($handle)) {
								$contenu .= fgets($handle, 4096);
							}
							fclose($handle);
						}
					}
				}
			}
			
			if ($contenu) {
				$return = ciparam_analyser_xml($contenu);
				// mettre en cache
				ecrire_fichier($chemin, serialize($return));
			}
		}

	return $tableau[$fichier] = $return;
}


function ciparam_analyser_xml($contenu) {

	// tranlitteration
	include_spip('inc/charsets'); 
	$contenu = transcoder_page($contenu);
	
	// Echapper les CDATA
	$echappe_cdata = array();
	if (preg_match_all(',<!\[CDATA\[(.*)]]>,Uims', $contenu,
	$regs, PREG_SET_ORDER)) {
		foreach ($regs as $n => $reg) {
			$echappe_cdata[$n] = $reg[1];
			$contenu = str_replace($reg[0], "@@@SPIP_CDATA$n@@@", $contenu);
		}
	}

	// supprimer les commentaires
	$contenu = preg_replace(',<!--.*-->,Ums', '', $contenu);

	// langue
	if (preg_match(',<([^>]*xml:)?lang(uage)?'.'>([^<>]+)<,i',
	$header, $match))
		$langue_du_site = $match[3];

	// splitter les items
	$items = preg_split(',<(item)\b.*>,Uims', $contenu);
	array_shift($items);
	foreach ($items as $k=>$item)
		$items[$k] = preg_replace(',</(item)\b.*>.*$,UimsS', '', $item);


	// Analyser chaque <item>...</item> du fichier XML et le transformer en tableau
	if (!count($items)) return false;

	foreach ($items as $item) {
		$data = array();

		// nom
		if (preg_match(",<nom[^>]*>(.*?)</nom>,ims",$item,$match))
			$data['nom'] = $match[1];
		else if (preg_match(',<link[[:space:]][^>]*>,Uims',$item,$mat)
		AND $nom = extraire_attribut($mat[0], 'nom'))
			$data['nom'] = $nom; 
		if (!strlen($data['nom'] = trim($data['nom'])))
			$data['nom'] = _T('ecrire:info_sans_nom');

		// Description
		if (preg_match(',<(description)\b.*'.'>(.*)</\1\b,Uims',$item,$match)) {
			$data['description'] = trim($match[2]);
		}

		// Ordre
		if (preg_match(',<(ordre)\b.*'.'>(.*)</\1\b,Uims',$item,$match)) {
			$data['ordre'] = trim($match[2]);
		}
		
		// Statuts
		if (preg_match(',<(statuts)\b.*'.'>(.*)</\1\b,Uims',$item,$match)) {
			$data['statuts'] = trim($match[2]);
		}

		// Icon
		if (preg_match(',<(icon)\b.*'.'>(.*)</\1\b,Uims',$item,$match)) {
			if ($icon = find_in_path(trim($match[2])))
				$data['icon'] = $icon;
		}		

		$articles[] = $data;
	}
	
	return $articles;
}


// tri d'un tableau de tableaux
function ciparam_order_array($array, $champ) {
    $tmp = array();
    $tmp2 = array();
    
    if (isset($array) AND isset($champ)) {
    	if (is_array($array)) {
		    foreach($array as $akey => $array2) {
		        $tmp[$akey] = $array2[$champ];
		   	}
		    asort($tmp);
		    foreach($tmp as $key => $value) {
		        $tmp2[$key] = $array[$key];
		    }
    	}
    }

    return $tmp2;
}


// retourne l'ordre de tri des articles
// d'une row de rubrique
function ciparam_row_to_tri($row) {
	$nom = "";
	$ordre = "";

	// valeur actuelle
	if (isset($row)) {
		if ($row['citri'])
			$nom = $row['citri'];
			$ordre = "asc";
		if ($row['citrinum']) {
			$nom = $row['citrinum'];	
			$ordre = "num";
		}
		if ($row['citriinverse']) {
			$nom = $row['citriinverse'];
			$ordre = "desc";
		}
	}

	// sinon valeur pas defaut
	if (!$nom) {
		$nom = "titre";
		$ordre = "num";
	}

   	return array('nom' => $nom, 'ordre' => $ordre);
}

// retourne un tableau avec les 3 champs
// pour l'ordre de tri des articles dans une rubrique
function ciparam_tri_to_row($nom,$ordre) {
	$tri = '';
	$trinum = '';
	$triinverse = '';

	if ($ordre=="num")
		$trinum = $nom;     // tri par numéro de ...
	elseif ($ordre=="desc") 
		$triinverse = $nom; // tri par ordre décroissant
	else
		$tri = $nom;

   	return array('tri' => $tri, 'trinum' => $trinum, 'triinverse' => $triinverse);
}

?>