<?php
/**
 * Plugin cilien
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;


function exec_cilienmesliens(){
	
	$res = '';
	$liste_rubriques = cilien_liste_rubriques_auteur();
		
	// si auteur non restreint on redirige vers la page cilien
	if (!$liste_rubriques OR $liste_rubriques=="0"){
		include_spip('inc/headers');
		redirige_par_entete(generer_url_public('cilien'));

	// si auteur restreint	
	} else {
		include_spip('inc/presentation');
		
		// lancer un traitement incremental uniquement si avancement < 100
		include_spip('inc/cilien');
		$traiter_auto = '';
		$avancements = cilien_avancement();
		foreach ($avancements AS $objet=>$avancement)
			if 	($avancement<100)
				$traiter_auto = 'pas'; // un pas normal
	
		if ($traiter_auto)
			cilien_traite_tranche($traiter_auto);
		
		
		// quel onglet ?
		$cionglet = "article";
		if (_request('onglet')=='rubrique')
			$cionglet = "rubrique";
		elseif (_request('onglet')=='site')
			$cionglet = "site";
		
		$pas = 400;
		$url = '';
		$requete = array();
			
			
		// les liens dans les articles		
		if ($cionglet == "article"){
			// mettre DISTINCT pour des raisons d'optimisation
			$requete['SELECT'] = 'DISTINCT L.url, L.id_objet';
			$requete['FROM'] = "spip_cilien AS L INNER JOIN spip_articles AS A ON L.id_objet=A.id_article AND L.objet='article'";
			$requete['WHERE'] = "L.obsolete='non' AND A.statut='publie' AND ".sql_in('A.id_rubrique',$liste_rubriques);
			$requete['GROUP BY'] = '';
			$requete['ORDER BY'] = 'L.id_objet DESC';
			$requete['LIMIT'] = '';
			
			$tranches = cilien_tranche_bandeau($requete, 'tranche', $url, NULL, $pas); // affecte au passage $requete['LIMIT']
	
			$lib = _T('info_article');
			$result = sql_select($requete['SELECT'], $requete['FROM'], $requete['WHERE'],$requete['GROUP BY'],$requete['ORDER BY'],$requete['LIMIT']);
			while ($row = sql_fetch($result)) {
					$res .= "<li><a href='".$row["url"]."'>".$row["url"]."</a> (".$lib." ".$row["id_objet"].")</li>";
			}
		}
		
		// les liens dans les rubriques		
		elseif ($cionglet == "rubrique"){
			// mettre DISTINCT pour des raisons d'optimisation
			$requete['SELECT'] = 'DISTINCT L.url, L.id_objet';
			$requete['FROM'] = "spip_cilien AS L INNER JOIN spip_rubriques AS R ON L.id_objet=R.id_rubrique AND L.objet='rubrique'";
			$requete['WHERE'] = "L.obsolete='non' AND R.statut='publie' AND ".sql_in('R.id_rubrique',$liste_rubriques);
			$requete['GROUP BY'] = '';
			$requete['ORDER BY'] = 'L.id_objet DESC';
			$requete['LIMIT'] = '';

			$tranches = cilien_tranche_bandeau($requete, 'tranche', $url, NULL, $pas); // affecte au passage $requete['LIMIT']
			
			$lib = _T('rubrique');
			$result = sql_select($requete['SELECT'], $requete['FROM'], $requete['WHERE'],$requete['GROUP BY'],$requete['ORDER BY'],$requete['LIMIT']);
			while ($row = sql_fetch($result)) {
					$res .= "<li><a href='".$row["url"]."'>".$row["url"]."</a> (".$lib." ".$row["id_objet"].")</li>";
			}
		}

		// les liens des sites references
		elseif ($cionglet == "site"){
			$requete['SELECT'] = 'url_site, id_syndic';
			$requete['FROM'] = 'spip_syndic';
			$requete['WHERE'] = "statut='publie' AND ".sql_in('id_rubrique',$liste_rubriques);
			$requete['GROUP BY'] = '';
			$requete['ORDER BY'] = 'id_syndic DESC';
			$requete['LIMIT'] = '';

			$tranches = cilien_tranche_bandeau($requete, 'tranche', $url, NULL, $pas); // affecte au passage $requete['LIMIT']
			
			$lib = _T('info_site_reference_2');
			$result = sql_select($requete['SELECT'], $requete['FROM'], $requete['WHERE'],$requete['GROUP BY'],$requete['ORDER BY'],$requete['LIMIT']);
			while ($row = sql_fetch($result)) {
					$res .= "<li><a href='".$row["url_site"]."'>".$row["url_site"]."</a> (".$lib." ".$row["id_syndic"].")</li>";
			}
		}
		
		// style pour l'onglet actif
		$class_article = '';
		$class_rubrique = '';
		$class_site = '';
		if ($cionglet == "article")
			$class_article = 'class="on"';
		elseif ($cionglet == "rubrique")
			$class_rubrique = 'class="on"';
		elseif ($cionglet == "site")
			$class_site = 'class="on"';

		$self = self();
		
		if (spip_version()>=3) {
			$icone_retour = icone_verticale(_T('cilien:retour'), generer_url_ecrire("accueil"), "article-24.png", "rien.gif",$GLOBALS['spip_lang_left']);
		} else {
			$icone_retour = icone_inline(_T('cilien:retour'), generer_url_ecrire("accueil"), "article-24.gif", "rien.gif",$GLOBALS['spip_lang_left']);
		}

		
		// debut de la page
		$debut_page = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
		<html>
		<head>
		<link href="'.find_in_path('_css/style_cilien.css').'" rel="stylesheet" type="text/css" />
		</head>
		<body>
		<div class="cadretitreep">'.$icone_retour.
		'<div class="titre">'._T('cilien:liens_auteur_reestreint').'</div>
		<div class="clearer"></div>
		</div>
		<div class="info">'._T('cilien:texte_verifier_liens').'</div>
		<div class="info">'._T('cilien:texte_verifier_liens2').'</div>
		<div class="onglets_lien">
		<ul>
		<li class="onglet_article"><a href="'.parametre_url($self,'onglet','article').'" '.$class_article.'>'._T('info_articles_2').'</a></li>
		<li class="onglet_rubrique"><a href="'.parametre_url($self,'onglet','rubrique').'" '.$class_rubrique.'>'._T('info_rubriques').'</a></li>
		<li class="onglet_site"><a href="'.parametre_url($self,'onglet','site').'" '.$class_site.'>'._T('cilien:sites_references').'</a></li>
		</ul>
		<div class="clearer"></div>
		</div>';
		
		// fin de la page
		$fin_page = '</body></html>';
		
		// affichage du debut de la page
		echo $debut_page;

		// affichage du titre
		if ($cionglet == "site")
			echo '<h1>'._T('titre_page_sites_tous').'</h1>';
		else
			echo '<h1>'._T('cilien:liens_dans_texte_'.$cionglet).'</h1>';
		
		// affichage de la mention	
		echo cilien_mention($cionglet);
		
		// affichage de la pagination
		if ($tranches)
			echo '<div class="folio"><div class="pagination">'.$tranches.'</div></div>';

		// affichage de la liste des resultats
		if ($res)
			echo '<ul>'.$res.'</ul>';
		else
			echo '<h3>'._T('avis_aucun_resultat').'</h3>';

		// affichage de la pagination
		if ($tranches)
			echo '<div class="folio"><div class="pagination">'.$tranches.'</div></div>';
			
		// affichage de la fin de la page
		echo $fin_page;
		
	}
	
}


/**
 * Renvoie, sous forme de liste (et pas sous forme de tableau),
 * les rubriques liees a cet auteur, independamment de son statut
 *
 * @param 	
 * @return 	$liste_rubriques
 */
function cilien_liste_rubriques_auteur() {
	static $liste_rubriques;

	if (!$liste_rubriques) {
		$id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
		$where = "id_auteur=$id_auteur AND id_rubrique!=0";
		$table =  "spip_auteurs_rubriques";
		// Recurrence sur les sous-rubriques
		$rubriques = array();
		while (true) {
			$q = sql_select("id_rubrique", $table, $where);
			$r = array();
			while ($row = sql_fetch($q)) {
				$id_rubrique = $row['id_rubrique'];
				$r[]= $rubriques[$id_rubrique] = $id_rubrique;
			}
	
			// Fin de la recurrence : $rubriques est complet
			if (!$r) break;
			$table = 'spip_rubriques';
			$where = sql_in('id_parent', $r) . ' AND ' . 
			  sql_in('id_rubrique', $r, 'NOT');
		}

		$liste_rubriques = implode(",",$rubriques);

		if (!$liste_rubriques)
			$liste_rubriques = "0";
	}
    return $liste_rubriques;
}

// Mention
function cilien_mention($objet){
	$return = '';
	
	if ($objet=='site'){
		$return = _T('cilien:listeajour');
	} else {		
		$t = sql_fetsel("*", "spip_cilien_tranche", "objet='$objet'");
		if ($t) {
			$cidatetranche = $t['date_tranche'];
			if (sql_countsel("spip_".$objet."s","maj>'".$cidatetranche."'")>0){
				$return = _T('cilien:maj').' '.affdate($cidatetranche, 'd/m/Y H:i:s').' <a href="'.self().'">'._T('cilien:actualiser').'</a>';
			} else {
				$return = _T('cilien:listeajour');
			}
		} else {
			$return = _T('cilien:aucunrecensement')."<a href='".self()."'>"._T('cilien:actualiser')."</a>";
		}
	}
	
	return $return;
}


// Pagination
function cilien_tranche_bandeau(&$requete, $idom='', $url='', $cpt=NULL, $pas=10) {

	if (!isset($requete['GROUP BY'])) $requete['GROUP BY'] = '';

	if ($cpt === NULL)
		$cpt = sql_countsel($requete['FROM'], $requete['WHERE'], $requete['GROUP BY']);

	$deb_aff = intval(_request($idom));
	$nb_aff = $pas + ($pas>>1);

	if (isset($requete['LIMIT']) AND $requete['LIMIT']) $cpt = min($requete['LIMIT'], $cpt);

	if ($cpt > $nb_aff) {
		$nb_aff = $pas;
		$res = cilien_tranches_requete($cpt, $idom, $url, $nb_aff);
	} else $res = '';

	if (!isset($requete['LIMIT']) OR !$requete['LIMIT'])
		$requete['LIMIT'] = "$deb_aff, $nb_aff";

	return $res;
}

function cilien_tranches_requete($num_rows, $idom, $url='', $nb_aff = 10, $old_arg=NULL) {
	static $ancre = 0;
	$ancre++;
	$self = self();
	$nav= cilien_navigation_pagination($num_rows, $nb_aff, $url, _request($idom), $idom, true);
	return "<div id='a$ancre'>" . $nav . "</div>\n";
}

function cilien_navigation_pagination($num_rows, $nb_aff=10, $href=null, $debut, $tmp_var=null, $on='') {

	$texte = '';
	$self = parametre_url(self(), 'date', '');
	$deb_aff = intval($debut);
	$seuil = ($nb_aff*5)+1;

	for ($i = 0; $i < $num_rows; $i += $nb_aff){
		$deb = $i + 1;
		$page = round($i / $nb_aff) +  1;
		$page_fin = round($num_rows / $nb_aff) + 1;
		
		// Pagination : si on est trop loin, on met des '...'
		if (abs($deb-$deb_aff)>$seuil) {
			if ($deb<$deb_aff) {
				if (!isset($premiere)) {
					$premiere = '<a href="'.parametre_url($self, $tmp_var, 0).'">1</a> ... ';
					$texte .= $premiere;
				}
			} else {
				$derniere = ' | ... '.'<a href="'.parametre_url($self, $tmp_var, ($page_fin-1)*$nb_aff).'">'.$page_fin.'</a>';
				$texte .= $derniere;
				break;
			}
		} else {

			$fin = $i + $nb_aff;
			if ($fin > $num_rows)
				$fin = $num_rows;

			if ($deb > 1)
				$texte .= " |\n";
			if ($deb_aff + 1 >= $deb AND $deb_aff + 1 <= $fin) {
				$texte .= "<b>$page</b>";
			}
			else {
				$script = parametre_url($self, $tmp_var, $deb-1);
				$texte .= "<a href=\"$script\"$on>$page</a>";
			}
		}
	}

	return $texte;
}

?>