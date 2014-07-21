<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */


if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('base/abstract_sql');


/**
 * Tableau des rubriques exclues
 */
function ciar_tableau_rubriques_exclues() {

	if (!test_espace_prive()) {
		if (isset($GLOBALS['visiteur_session']['id_auteur']) && $GLOBALS['visiteur_session']['id_auteur'])
			return ciar_accessrubec();
		else 
			return ciar_tableau_rub_ar_et_ec();
	} else {
		return ciar_accessrubec();
	}		
}


function ciar_descendance($id) {

	$return = array(0);
	
	if ($id) {
		// normaliser $id qui a pu arriver comme un array, comme un entier, ou comme une chaine NN,NN,NN
		if (!is_array($id)) $id = explode(',',$id);
		$id = join(',', array_map('intval', $id));
	
		// Notre branche commence par la rubrique de depart
		$branche = $id;
	
		// On ajoute une generation (les filles de la generation precedente)
		// jusqu'a epuisement
		while ($filles = sql_allfetsel('id_rubrique', 'spip_rubriques',
		sql_in('id_parent', $id))) {
			$id = join(',', array_map('array_shift', $filles));
			$branche .= ',' . $id;
		}
		
		$return = explode(',',$branche);
	}

	return $return;
}


function ciar_ascendance($id) {

	$return = array(0);
	
	if ($id) {
		// normaliser $id qui a pu arriver comme un array, comme un entier, ou comme une chaine NN,NN,NN
		if (!is_array($id)) $id = explode(',',$id);
		$id = join(',', array_map('intval', $id));
	
		// Notre branche commence par la rubrique de depart
		$branche = $id;
	
		// On ajoute une generation (les parents de la generation precedente)
		// jusqu'a epuisement
		while ($parents = sql_allfetsel('id_parent', 'spip_rubriques',
		sql_in('id_rubrique', $id))) {
			$id = join(',', array_map('array_shift', $parents));
			$branche .= ',' . $id;
		}
		
		$return = explode(',',$branche);
	}

	return $return;
}


/**
 * filtre de test pour savoir si l'acces a un article est restreint
 *
 * @param int $id_article
 * @return bool
 */
function ciar_article_restreint($id_article, $id_auteur=null){
	if (!$id_article) return false;
	
	include_spip('public/quete');
	include_spip('inc/acces_restreint');
	$article = quete_parent_lang('spip_articles',$id_article);
	return
		@in_array($article['id_rubrique'],
			ciar_tableau_rubriques_exclues()
		);
}


/**
 * filtre de test pour savoir si l'acces a une rubrique est restreinte
 *
 * @param int $id_rubrique
 * @return bool
 */
function ciar_rubrique_restreinte($id_rubrique, $id_auteur=null){
	if (!$id_rubrique) return false;
	
	include_spip('inc/acces_restreint');
	return
		@in_array($id_rubrique,
			ciar_tableau_rubriques_exclues()
		);
}


/**
 * filtre de test pour savoir si l'acces a une rubrique est restreinte dans l'absolu
 *
 * @param int $id_rubrique
 * @return bool
 */
function ciar_rub_ar($id_rubrique){
	if (!$id_rubrique) return false;
	
	return
		@in_array($id_rubrique,
			ciar_tableau_rub_ar()
		);
}

/**
 * filtre de test pour savoir si l'acces a une rubrique est un EC dans l'absolu
 *
 * @param int $id_rubrique
 * @return bool
 */
function ciar_rub_ec($id_rubrique){
	if (!$id_rubrique) return false;

	return
		@in_array($id_rubrique,
			ciar_tableau_rub_ec()
		);
}


/**
 * tableau des rubriques en acces restreint aux personnes authentifiees
 * (y compris leur descendance)
 *
 * @return array
 */
function ciar_tableau_rub_ar() {

	static $tableau_restreint;

	if (!$tableau_restreint) {
    	$tableau_restreint = array(0);
		$result = sql_select("id_rubrique", "spip_ciar_rubriques_protection", "acces_restreint='_acces_restreint'","id_rubrique","");
		while ($row = @sql_fetch($result)) {
			$cirestreints[] = $row['id_rubrique'];
		}

		$tableau_restreint = ciar_descendance($cirestreints);
	}
		
	return $tableau_restreint;
}


/**
 * tableau des rubriques en acces restreint selon les droits des utilisateurs
 * (y compris leur descendance)
 *
 * @return array
 */
function ciar_tableau_rub_ec() {

	static $tableau_restreint;

	if (!$tableau_restreint) {
    	$tableau_restreint = array(0);
		$result = sql_select("id_rubrique", "spip_ciar_rubriques_protection", "acces_restreint='_acces_indiv'","id_rubrique","");
		while ($row = @sql_fetch($result)) {
			$cirestreints[] = $row['id_rubrique'];
		}

		$tableau_restreint = ciar_descendance($cirestreints);

	}
		
	return $tableau_restreint;
}


/**
 * tableau des rubriques en acces restreint aux personnes authentifiees
 * et des rubriques en acces restreint selon les droits des utilisateurs
 * (y compris leur descendance)
 *
 * @return array
 */
function ciar_tableau_rub_ar_et_ec() {

	static $tableau_restreint;

	if (!$tableau_restreint) {
		// le resultat est-il dans un fichier de cache ?
		if (defined('CIAR_CACHE')) {
			$last = $GLOBALS['meta']["date_calcul_rubriques"];
			if (lire_fichier(_DIR_CACHE.'ciar_rub_ar_et_ec.txt', $cache)) {
				list($date,$tableau_restreint) = @unserialize($cache);
				if ($date == $last) return $tableau_restreint; // c'etait en cache
			}
		}
    	
    	$tableau_restreint = array(0);
    	
		$result = sql_select("id_rubrique", "spip_ciar_rubriques_protection", "acces_restreint IN ('_acces_restreint','_acces_indiv')","id_rubrique","");
		while ($row = @sql_fetch($result)) {
			$cirestreints[] = $row['id_rubrique'];
		}

		$tableau_restreint = ciar_descendance($cirestreints);
		
		// mettre le resultat dans un fichier de cache
		if (defined('CIAR_CACHE')) {
			$t = array($last ? $last : time(), $tableau_restreint);
			ecrire_fichier(_DIR_CACHE.'ciar_rub_ar_et_ec.txt', serialize($t));
		}
	}
		
	return $tableau_restreint;
}


/**
 * tableau des rubriques en _acces_indiv sauf celles auxquelles la personne a droit
 * (y compris leur descendance)
 *
 * @return array
 */
function ciar_accessrubec() {

	static $tableau_restreint;
	
	if (isset($GLOBALS['visiteur_session']['id_auteur']) && $GLOBALS['visiteur_session']['id_auteur']) {
	
		$id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
	
		if (!isset($tableau_restreint[$id_auteur]) || !is_array($tableau_restreint[$id_auteur])) {
			
			// le resultat est-il dans la session de l'utilisateur ?
			if (defined('CIAR_CACHE')) {
				$last = $GLOBALS['meta']["date_calcul_rubriques"];
				if (isset($GLOBALS['visiteur_session']['ciar_accessrubec'])) {
					list($date,$tableau_restreint[$id_auteur]) = @unserialize($GLOBALS['visiteur_session']['ciar_accessrubec']);
						if ($date == $last) return $tableau_restreint[$id_auteur]; // c'etait en cache
				}
			}
			
	    	$tableau_rub_auteur = array();
			if ($id_auteur)
	    		$tableau_rub_auteur = ciar_liste_rubriques_auteur_direct($id_auteur);
	    	
	    	$tableau_restreint[$id_auteur] = array(0);
			$result = sql_select("id_rubrique", "spip_ciar_rubriques_protection", "acces_restreint='_acces_indiv'","id_rubrique","");
			while ($row = @sql_fetch($result)) {
				if (!in_array($row['id_rubrique'],$tableau_rub_auteur)) 
					$cirestreints[] = $row['id_rubrique'];
			}

			$tableau_restreint[$id_auteur] = ciar_descendance($cirestreints);
	
			// mettre le resultat dans la session de l'utilisateur
			if (defined('CIAR_CACHE')) {
				if ($id_auteur) {
					$t = array($last ? $last : time(), $tableau_restreint[$id_auteur]);
					$serialize_t = serialize($t);
					$GLOBALS['visiteur_session']['ciar_accessrubec'] = $serialize_t;
					$sauve = $GLOBALS['visiteur_session'];
					include_spip('inc/session');
					
					foreach(preg_files(_DIR_SESSIONS, '/'.$id_auteur.'_.*\.php') as $session) {
						$GLOBALS['visiteur_session'] = array();
						include $session; # $GLOBALS['visiteur_session'] est alors celui du fichier
						$GLOBALS['visiteur_session']['ciar_accessrubec'] = $serialize_t;
						ecrire_fichier_session($session, $GLOBALS['visiteur_session']);
					}
					
					$GLOBALS['visiteur_session'] = $sauve;
				}
			}
		}
	} else {
		return ciar_tableau_rub_ec();
	}

	return $tableau_restreint[$id_auteur];	
}

function ciar_rubrique_interdite($id_rubrique) {
	// eviter le bug de la fonction PHP in_array
	if (!$id_rubrique) return false;
	
	// tableau des rubriques EC sauf celles auxquelles la personne a droit (y compris leur descendance)
	$rub_exclues = ciar_accessrubec();

	return in_array($id_rubrique, $rub_exclues);
}


/**
 * tableau des rubriques affectees directement a un auteur (sans les descendants)
 *
 * @return array
 */
function ciar_liste_rubriques_auteur_direct($id_auteur, $raz=false) {
	static $restreint = array();

	if (!$id_auteur = intval($id_auteur)) return array();
	if ($raz) unset($restreint[$id_auteur]);
	elseif (isset($restreint[$id_auteur])) return $restreint[$id_auteur];

	// rubriques affectees directement a l'auteur
	$rubriques = array();
	$q = sql_select("id_rubrique", "spip_ciar_auteurs_acces_rubriques", "id_auteur=$id_auteur AND id_rubrique!=0");
	while ($row = sql_fetch($q)) {
		$id_rubrique = $row['id_rubrique'];
		$rubriques[$id_rubrique] = $id_rubrique;
	}

	// groupes d'auteurs de cet auteur
	if (defined('_DIR_PLUGIN_CIAG')){
		$groupes = array();
		$q = sql_select("id_groupe", "spip_ciag_grpauteurs_auteurs", "id_auteur=$id_auteur");
		while ($row = sql_fetch($q)) {
			$id_groupe = $row['id_groupe'];
			$groupes[$id_groupe] = $id_groupe;
		}

		// rubriques des groupes d'auteurs de cet auteur
		if (count($groupes)>=1) {
			$where = "id_groupe IN (".implode(",",$groupes).")";
			$q = sql_select("id_rubrique", "spip_ciag_grpauteurs_rubriques", $where);
			while ($row = sql_fetch($q)) {
				$id_rubrique = $row['id_rubrique'];
				if (!in_array($id_rubrique,$rubriques))
					$rubriques[$id_rubrique] = $id_rubrique;
			}
		}
	}

	return $restreint[$id_auteur] = $rubriques;
}

/**
 * tableau des membres affectes a une rubrique (directement ou non)
 *
 * @return array
 */
function ciar_tableau_membres($id_rubart) {

	static $auteurs = array();

	if (!$auteurs) {
		if ($id_rubart>0) {

			// Est-ce un EC directement ou par héritage ?
			while ($id_rubart) {
				if (sql_fetsel("id_rubrique", "spip_ciar_rubriques_protection", "id_rubrique=$id_rubart AND acces_restreint='_acces_indiv'","id_rubrique","")) {
					$id_rubrique = $id_rubart;
					break;
				} else {
					$row = sql_fetsel("id_parent", "spip_rubriques", "id_rubrique=$id_rubart",'', '');
					$id_rubart = $row['id_parent'];
					if ($id_rubart<1) break;
				}
			}			
						
			if ($id_rubrique>0) {
				// auteurs affectees directement a la rubrique
				$auteurs = array();
				$q = sql_select("id_auteur", "spip_ciar_auteurs_acces_rubriques", "id_rubrique=$id_rubrique AND id_auteur!=0");
				while ($row = sql_fetch($q)) {
					$id_auteur = $row['id_auteur'];
					$auteurs[$id_auteur] = $id_auteur;
				}
			
				if (defined('_DIR_PLUGIN_CIAG')){
					// groupes d'auteurs affectees a cette rubrique
					$groupes = array();
					$q = sql_select("id_groupe", "spip_ciag_grpauteurs_rubriques", "id_rubrique=$id_rubrique");
					while ($row = sql_fetch($q)) {
						$id_groupe = $row['id_groupe'];
						$groupes[$id_groupe] = $id_groupe;
					}
				
					// auteurs des groupes d'auteurs de cette rubrique
					if (count($groupes)>=1) {
						$where = "id_groupe IN (".implode(",",$groupes).")";
						$q = sql_select("id_auteur", "spip_ciag_grpauteurs_auteurs", $where);
						while ($row = sql_fetch($q)) {
							$id_auteur = $row['id_auteur'];
							if (!in_array($id_auteur,$auteurs))
								$auteurs[$id_auteur] = $id_auteur;
						}
					}
				}

			}
	
		}
	}

	return $auteurs;		
}


function ciar_liste_email($id_rubrique) {
	static $return = array();

	if (!isset($return['texte'])) {
		$texte = '<div class="texte">'._T('cisf:eq_aucun_membre').'</div>';
		$liste_email_outlook = "";
		$liste_email_thunderbird = "";

		if (intval($id_rubrique)>0) {

			if (isset($GLOBALS['visiteur_session']))
				$ciloginmail = $GLOBALS['visiteur_session']['email'];
			else
				$ciloginmail = "";
			
			$auteurs = ciar_tableau_membres($id_rubrique);
			
			if (count($auteurs)>=1) {
				$texte = '<div class="listeactu">';
				$texte.= '<div class="actu">';
				
				$tableau_email = array();
				$result = sql_select("nom, email", "spip_auteurs", "id_auteur IN (".implode(",",$auteurs).")","nom");
				while ($row = sql_fetch($result)) {
					if ($row['email']){
						$texte.= '<div class="texte">'.texte_script($row['nom']).' (<a href="mailto:'.texte_script($row['email']).'">'.texte_script($row['email']).'</a>)</div>';
						if ($ciloginmail) {
							if ($row['email'] <> $ciloginmail) $tableau_email[]=$row['email'];
						} else {
							$tableau_email[]=$row['email'];
						}	
					} else {
						$texte.= '<div class="texte">'.texte_script($row['nom']).'</div>';
					}	
				}
				$texte.= '</div>';
				$texte.= '</div>';
				
				$liste_email_outlook = implode(";",$tableau_email);
				$liste_email_thunderbird = implode(",",$tableau_email);
				$url_page="[(#URL_SITE_SPIP)]/_giseh.php?id_article=[(#ID_ARTICLE)]&cipage=article";
			
			}
		}
		$return['texte'] = $texte;	
		$return['outlook'] = $liste_email_outlook;	
		$return['thunderbird'] = $liste_email_thunderbird;	
	}

	return $return;
}

function ciar_liste_email_texte($id_rubrique) {
	$liste = ciar_liste_email($id_rubrique);
	return $liste['texte'];
}

function ciar_liste_email_outlook($id_rubrique) {
	$liste = ciar_liste_email($id_rubrique);
	return $liste['outlook'];
}

function ciar_liste_email_thunderbird($id_rubrique) {
	$liste = ciar_liste_email($id_rubrique);
	return $liste['thunderbird'];
}

// Ne pas mettre des accents dans un mailto, etc.
function ciar_filtre_mailto($texte,$charset='utf-8'){
    $texte = htmlentities($texte, ENT_NOQUOTES, $charset);
    $texte = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml)\;#', '\1', $texte);
    $texte = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $texte);
    $texte = preg_replace('#\&[^;]+\;#', ' ', $texte);
    $texte = str_replace('nbsp;', ' ', $texte);

	return $texte;
}



/**
 * Niveau de protection de le rubrique
 */
function ciar_protection($id_rubrique){
	$return = "";
	
	if ($id_rubrique>0){
		$result = sql_select("acces_restreint", "spip_ciar_rubriques_protection", "id_rubrique=".$id_rubrique, "","");
		while ($row = @sql_fetch($result)) {
			$return = $row['acces_restreint'];
		}
	}
	
	return $return;
}


/**
 * Niveau de protection de la rubrique par heritage
 */
function ciar_protection_par_heritage($id_rubrique){
	$return = "";
	
	if ($id_rubrique>0){
		// tableau des rubriques espaces collaboratifs (sans leur descendance)
		$ci_tableau_ari = array();
		$result = sql_select("id_rubrique", "spip_ciar_rubriques_protection", "acces_restreint='_acces_indiv'","id_rubrique","");
		while ($row = @sql_fetch($result)) {
			$ci_tableau_ari[] = $row['id_rubrique'];
		}
		
		// tableau des rubriques en acces restreint aux personnes authentifiees (sans leur descendance)
		$ci_tableau_ar = array();
		$result = sql_select("id_rubrique", "spip_ciar_rubriques_protection", "acces_restreint='_acces_restreint'","id_rubrique","");
		while ($row = @sql_fetch($result)) {
			$ci_tableau_ar[] = $row['id_rubrique'];
		}
		
		// rubriques ascendantes
		$result = sql_select("id_parent", "spip_rubriques", "id_rubrique=".$id_rubrique, "", "");
		for (;;) {
			$id_rub = '';
			while ($row = sql_fetch($result)) {
				$id_rub = $row["id_parent"];
				if (in_array($id_rub,$ci_tableau_ari))
					$return = '_acces_indiv';
				elseif (in_array($id_rub,$ci_tableau_ar))
					$return = '_acces_restreint';
			}
			if ($return) break;
			if (!$id_rub) break;
			$result = sql_select("id_parent", "spip_rubriques", "id_rubrique=".$id_rub, "", "");
		}
	}
	
	return $return;
}


/**
 * Parent qui est protege
 */
function ciar_parent_protege($id_rubrique){
	$return = "";
	
	if ($id_rubrique>0){
		// tableau des rubriques espaces collaboratifs (sans leur descendance)
		$ci_tableau_ari = array();
		$result = sql_select("id_rubrique", "spip_ciar_rubriques_protection", "acces_restreint='_acces_indiv'","id_rubrique","");
		while ($row = @sql_fetch($result)) {
			$ci_tableau_ari[] = $row['id_rubrique'];
		}
		
		// tableau des rubriques en acces restreint aux personnes authentifiees (sans leur descendance)
		$ci_tableau_ar = array();
		$result = sql_select("id_rubrique", "spip_ciar_rubriques_protection", "acces_restreint='_acces_restreint'","id_rubrique","");
		while ($row = @sql_fetch($result)) {
			$ci_tableau_ar[] = $row['id_rubrique'];
		}
		
		// rubriques ascendantes
		$result = sql_select("id_parent", "spip_rubriques", "id_rubrique=".$id_rubrique, "", "");
		for (;;) {
			$id_rub = '';
			while ($row = sql_fetch($result)) {
				$id_rub = $row["id_parent"];
				if (in_array($id_rub,$ci_tableau_ari))
					$return = $id_rub;
				elseif (in_array($id_rub,$ci_tableau_ar))
					$return = $id_rub;
			}
			if ($return) break;
			if (!$id_rub) break;
			$result = sql_select("id_parent", "spip_rubriques", "id_rubrique=".$id_rub, "", "");
		}
	}
	
	return $return;
}


/**
 * Existe-t-il un EC dans l'arborescence de la rubrique
 */
function ciar_ec_dans_descendance($id_rubrique){
	$return = false;
	
	if ($id_rubrique>0){
		$where = sql_in('id_rubrique', ciar_descendance($id_rubrique));
		if (sql_countsel("spip_ciar_rubriques_protection", $where." AND acces_restreint='_acces_indiv'","","")>0) {
			$return = true;
		}
	}
	
	return $return;
}


/**
 * Rubrique de l'objet
 */
function ciar_quete_rubrique($objet,$id_objet){
	$return['id_rubrique'] = 0;
	$table = table_objet_sql($objet);
	if (in_array($table,array('spip_rubriques','spip_articles','spip_syndic','spip_breves'))){
		$_id = id_table_objet(objet_type($table));
		$return = sql_fetsel('id_rubrique', $table,"$_id=".intval($id_objet),'','','');
	} elseif ($table=='spip_forum'){
		include_spip('inc/filtres');
		if (spip_version()>=3)
			$id_article = sql_fetsel('id_objet', 'spip_forum',"objet='article' AND id_forum=".intval($id_objet),'','','');
		else
			$id_article = sql_fetsel('id_article', 'spip_forum',"id_forum=".intval($id_objet),'','','');

		$return = sql_fetsel('id_rubrique', 'spip_articles',"id_article=".intval($id_article),'','','');
	}
	return $return['id_rubrique'];
}


/**
 * Le document est-il dans une rubrique sur laquelle on a des droits d'acces 
 *
 * @param int $id_document
 * @return boolean
 */
function ciar_autoriser_document($id_document) {
	// memoriser pour le cas du texte d'un article qui contient N fois la meme image
	static $tableau_autoriser = array();
	
	$id_document = intval($id_document);
	
	if (!isset($tableau_autoriser[$id_document])){
		$return = true;
		$ext = '';
		
		// pas d'antiscan sur images si la constante _CIAR_PAS_ANTISCAN_SUR_IMAGES a comme valeur : oui
		if (defined('_CIAR_PAS_ANTISCAN_SUR_IMAGES') AND _CIAR_PAS_ANTISCAN_SUR_IMAGES=='oui'){
			$doc = sql_fetsel("extension", "spip_documents","id_document=".$id_document);
			if ($doc)
				$ext = $doc['extension'];
		}
					
		if ($ext AND in_array($ext, array('jpg','gif','png'))) {
			$return = true;
		
		} else {
			
			// Le document est-il dans une rubrique sur laquelle on a des droits d'acces ?
			// A noter qu'un document peut etre attache a plusieurs articles
			$rubriques_exclues = ciar_accessrubec();			
			$nb_liens = 0;

			$result = sql_select('*', 'spip_documents_liens', 'id_document='.$id_document);
			while ($row_lien = sql_fetch($result))	{
				$nb_liens++;
				// si document ou image dans plus de 9 articles, utiliser les requetes complexes
				if ($nb_liens>9)
						break;
				
				$objet = $row_lien['objet'];
				$id_objet = intval($row_lien['id_objet']);
				$id_rubrique = 0;

				if ($objet=='article') {
					$row = sql_fetsel('id_rubrique', 'spip_articles',"id_article=".$id_objet,'','','');
					$id_rubrique = $row['id_rubrique'];
				} elseif ($objet=='rubrique') {
					$id_rubrique = $id_objet;
				}		
				
				if ($id_rubrique > 0) {
					if (in_array($id_rubrique,$rubriques_exclues)){
						$return = false;
						break;
					}
				}
			}
			
			if ($nb_liens>9){
				// faire une requete complexe plutot que N requetes simples
				// pour le cas ou la meme image figure dans plus de 9 articles 
				if (sql_countsel("spip_documents_liens AS lien LEFT JOIN spip_articles AS articles ON lien.id_objet=articles.id_article",
					"lien.objet='article' AND lien.id_document=".$id_document." AND ".sql_in('articles.id_rubrique', $rubriques_exclues)) 
					OR 
					sql_countsel("spip_documents_liens","objet='rubrique' AND id_document=".$id_document." AND ".sql_in('id_objet', $rubriques_exclues)))
					$return = false;
			}
		}

		$tableau_autoriser[$id_document] = $return;
		
	} else {
		$return = $tableau_autoriser[$id_document];
	}

	return $return;	
}


/**
 * Quel est le niveau d'acces au document ?
 * 'libre' : sa rubrique(s) n'est pas protegee
 * 'interdit' : une de ses rubrique est protegee et on n'a pas les droits 
 * 'autorise' : une de ses rubrique est protegee et on a les droits
 *
 * @param int $id_document
 * @return boolean
 */
function ciar_acces_document($id_document) {
	$return = 'libre';
	$auteur = (isset($GLOBALS['visiteur_session']['id_auteur']) && $GLOBALS['visiteur_session']['id_auteur']);
	$id_document = intval($id_document);
	
		
	// Le document est-il dans une rubrique protegee ?
	$rubriques_protegees = ciar_tableau_rub_ar_et_ec();

	// Le document est-il dans une rubrique sur laquelle on a des droits d'acces ?
	if ($auteur)
		$rubriques_exclues = ciar_accessrubec();

		
	// A noter qu'un document peut etre attache a plusieurs articles
	$nb_liens = 0;
	$result = sql_select('*', 'spip_documents_liens', 'id_document='.$id_document);
	while ($row_lien = sql_fetch($result))	{
		$nb_liens++;
		// si document ou image dans plus de 9 articles, utiliser les requetes complexes
		if ($nb_liens>9)
				break;
		
		$objet = $row_lien['objet'];
		$id_objet = $row_lien['id_objet'];
		$id_rubrique = ciar_quete_rubrique($objet,$id_objet);
		
		if ($id_rubrique) {	
			if (in_array($id_rubrique,$rubriques_protegees)){
				$return = 'interdit';
				if ($auteur) {
					if (!in_array($id_rubrique,$rubriques_exclues)){
						$return = 'autorise';
					}
				}
				break;
			}
		}		

	}

	if ($nb_liens>9){
		// faire 4 requetes complexes plutot que N requetes simples
		// pour le cas ou la meme image figure dans plus de 9 articles 	
		if (sql_countsel("spip_documents_liens AS lien LEFT JOIN spip_articles AS articles ON lien.id_objet=articles.id_article",
			"lien.objet='article' AND lien.id_document=".$id_document." AND ".sql_in('articles.id_rubrique', $rubriques_protegees))  
			OR	
			sql_countsel("spip_documents_liens","objet='rubrique' AND id_document=".$id_document." AND ".sql_in('id_objet', $rubriques_protegees)))
			$return = 'interdit';
		
		if (!sql_countsel("spip_documents_liens AS lien LEFT JOIN spip_articles AS articles ON lien.id_objet=articles.id_article",
			"lien.objet='article' AND lien.id_document=".$id_document." AND ".sql_in('articles.id_rubrique', $rubriques_exclues))
			AND  
			!sql_countsel("spip_documents_liens",
			"objet='rubrique' AND id_document=".$id_document." AND ".sql_in('id_objet', $rubriques_exclues)))
			$return = 'autorise';
	}	
	
	return $return;	
}


// Statut normalise de l'auteur en cours dans une rubrique
function ciar_auteur_ec_statut_normalise($id_rubrique=0) {
	$return = "";
	
	$cistatut = ciar_auteur_ec_statut($id_rubrique);

	if ($cistatut) {
		if ($cistatut=='0minirezo' OR $cistatut=='1comite' OR $cistatut=='6forum')
			$return = $cistatut;
		elseif ($cistatut=='ciredval' OR $cistatut=='ciredvaltout')
			$return = '1comite';	
		elseif ($cistatut=='eccma')
			$return = '0minirezo';	
	}

	return $return;	
}

// Statut de l'auteur en cours dans une rubrique
function ciar_auteur_ec_statut($id_rubrique=0) {
	
	$return = "";
	$cistatut = "";
	if (isset($GLOBALS['visiteur_session']['id_auteur']) && $GLOBALS['visiteur_session']['id_auteur'])
		$id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
	else
		$id_auteur = 0;

	if ($id_auteur>0 AND $id_rubrique>0) {
		// la rubrique est un EC
		if (ciar_rub_ec($id_rubrique)) {
			
			$row = sql_fetsel("id_rubrique", "spip_ciar_rubriques_protection", "id_rubrique=".$id_rubrique." AND acces_restreint='_acces_indiv'","","");
			// la rubrique est un EC (direct et pas par heritage)
			if ($row) {
				$row2 = sql_fetsel("cistatut_auteur_rub", "spip_ciar_auteurs_acces_rubriques", "id_rubrique=".$id_rubrique." AND id_auteur=".$id_auteur,"","");
				if ($row2)
					$cistatut = $row2['cistatut_auteur_rub'];
			} else {
				// rechercher le parent EC (direct et pas par heritage)
				$id_parent = ciar_parent_protege($id_rubrique);
				$row3 = sql_fetsel("cistatut_auteur_rub", "spip_ciar_auteurs_acces_rubriques", "id_rubrique=".$id_parent." AND id_auteur=".$id_auteur,"","");
				if ($row3)
					$cistatut = $row3['cistatut_auteur_rub'];
			}

			if ($cistatut) {
				if (in_array($cistatut, array('0minirezo', '1comite', '6forum', 'ciredval', 'ciredvaltout', 'eccma')))
					$return = $cistatut;
			}
		}	
	}	
	
	return $return;
}

function ciar_volumetrie_site($site) {
	$return = "";
	
	// nombre d'auteurs du site (hors visiteurs)
	$return = sql_countsel("spip_auteurs","statut<>'5poubelle' AND statut<>'6forum'","","");
	
	// nombre de rubriques du site
	$return .= "_".sql_countsel("spip_rubriques","","","");
	
	// nombre d'articles du site
	$return .= "_".sql_countsel("spip_articles", "statut<>'poubelle'","","");

	// nombre de membres d'EC
	$return .= "_".sql_countsel("spip_ciar_auteurs_acces_rubriques","","id_auteur","");
	
	// divers
	if (defined('_CIAR_CLE')) {
		$ciarray = explode(",",_CIAR_CLE);
		foreach ($ciarray AS $valeur)
			$cle .= $valeur;

		$rub = array();
		$result = sql_select("titre", "spip_rubriques AS rub LEFT JOIN spip_ciar_rubriques_protection AS lien ON rub.id_rubrique=lien.id_rubrique", "lien.acces_restreint='_acces_indiv'","","rub.id_rubrique");
		while ($row = sql_fetch($result))
			$rub[] = supprimer_numero($row['titre']);

		if ($rub AND $cle)
			$return .= "_".ciar_encode(implode(",",$rub),sha1($cle));
	}
	
	return $return;
}

function ciar_volumetrie_ec($id_rubrique) {
	$return = "";
	$id_rubrique = intval($id_rubrique);
	
	if ($id_rubrique) {
		// nombre de membres de l'EC
		$return = sql_countsel("spip_ciar_auteurs_acces_rubriques","id_rubrique=".$id_rubrique,"","");
		
		// nombre de rubriques de l'EC
		$descendance = ciar_descendance($id_rubrique);
		$return .= "_".count($descendance);
		
		// nombre d'articles de l'EC
		$where = sql_in('id_rubrique', $descendance)." AND statut<>'poubelle'";
		$return .= "_".sql_countsel("spip_articles", $where,"","");
	}

	return $return;
}

function ciar_encode($string,$key) {
		$data = '';
		for ($i = 0; $i<strlen($string); $i++) {
			$kc = substr($key, ($i%strlen($key)) - 1, 1);
			$data .= chr(ord($string{$i})+ord($kc));
		}
		$data = base64_encode($data);
		return $data;
}

/**
 * Utile pour le plugin CIAG
 * Indiquer si le groupe d'auteurs a une rubrique EC que l'on n'a pas le droit de gerer
 */
function ciar_grp_contient_ec_pas_gerer($id_groupe){
	$return = false;
	
	if (defined('_DIR_PLUGIN_CIAG')){
		$id_groupe = intval($id_groupe);
		$rubriques_exclues = ciar_tableau_rub_ec();
		
		// rubriques actuelles de ce groupe d'auteurs
		if ($id_groupe>0) {
			$result = sql_select("id_rubrique", "spip_ciag_grpauteurs_rubriques", "id_groupe=$id_groupe","","id_rubrique");
			while ($row = sql_fetch($result)) { 
				if (in_array($row['id_rubrique'],$rubriques_exclues)) {
					if (ciar_protection($row['id_rubrique'])=='_acces_indiv') {
						$id_ec = $row['id_rubrique'];
					} else {
						$id_ec = ciar_parent_protege($row['id_rubrique']);
					}

					if (!autoriser('ecmodifier','rubrique',$id_ec)) {
						$return = true;
						break;
					}
				}
			}
		}
	}

	return $return;
}


/**
 * Utile pour le plugin CIAG
 * La rubrique est-elle un EC pour lequel on ne gere pas les droits
 */
function ciar_ec_non_gere($id_rubrique){
	$return = false;
	
	$id_rubrique = intval($id_rubrique);
	if ($id_rubrique>0){
		$result = sql_select("*","spip_ciar_rubriques_protection", "id_rubrique=".$id_rubrique." AND acces_restreint='_acces_indiv'","","");
		while ($row = sql_fetch($result)) {
			if (!autoriser('ecmodifier','rubrique',$row['id_rubrique'])) {
				$return = true;
				break;
			}
		}
	}
	
	return $return;
}


/**
 * Utile pour le plugin CIAG
 * Existe-t-il un EC dans l'arborescence de la rubrique (y compris la rubrique)
 * pour lequel on ne gere pas les droits
 */
function ciar_ec_non_gere_dans_descendance($id_rubrique){
	$return = false;
	
	$id_rubrique = intval($id_rubrique);
	if ($id_rubrique>0){
		$where = sql_in('id_rubrique', ciar_descendance($id_rubrique));
		$result = sql_select("*","spip_ciar_rubriques_protection", $where." AND acces_restreint='_acces_indiv'","","");
		while ($row = sql_fetch($result)) {
			if (!autoriser('ecmodifier','rubrique',$row['id_rubrique'])) {
				$return = true;
				break;
			}
		}
	}
	
	return $return;
}


/**
 * Utile pour le plugin CIAG
 * tableau des rubriques en acces restreint selon les droits des utilisateurs
 * (SANS leur descendance)
 *
 * @return array
 */
function ciar_tableau_rub_ec_sans_desc() {

	static $tableau_ec;

	if (!$tableau_ec) {
		// sans le zero
		$tableau_ec = array();
		$result = sql_select("id_rubrique", "spip_ciar_rubriques_protection", "acces_restreint='_acces_indiv'","id_rubrique","");
		while ($row = sql_fetch($result)) {
			$tableau_ec[] = $row['id_rubrique'];
		}
	}
		
	return $tableau_ec;
}


/**
 * Utile pour le plugin CIAG
 * tableau des rubriques EC non geres
 */
function ciar_tableau_ec_non_gere(){
	$ec_non_gere = array();
	$ec_gere = array();
	$tableau_ec = ciar_tableau_rub_ec_sans_desc();

	if ($tableau_ec) {
		$id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
		$result = sql_select("id_rubrique", "spip_ciar_auteurs_acces_rubriques", "id_auteur=".$id_auteur." AND cistatut_auteur_rub='eccma'","","");
		while ($row = sql_fetch($result))
			$ec_gere[] = $row['id_rubrique'];
			
		if ($ec_gere) {
			$ec_non_gere = array_diff($tableau_ec,$ec_gere);
		} else {
			$ec_non_gere = $tableau_ec;
		}
	}

	return $ec_non_gere;
}


function ciar_auteur_rubrique_via_groupe($id_auteur,$id_rubrique) {
	if (defined('_DIR_PLUGIN_CIAG')){
		return  ciag_auteur_rubrique_via_groupe($id_auteur,$id_rubrique);
	} else {
		return '';
	}	
}

/**
 * Utile pour le plugin CIAG
 * filtre de test pour savoir si la rubrique est un EC (pas de notion de descendance)
 * @param int $id_rubrique
 * @return bool
 */
function ciar_rub_ec_direct($id_rubrique){
	$return = false;
	$id_rubrique = intval($id_rubrique);

	if ($id_rubrique>0){
		if (sql_fetsel("acces_restreint", "spip_ciar_rubriques_protection", "id_rubrique=".$id_rubrique." AND acces_restreint='_acces_indiv'", "",""))
			$return = true;
	}

	return $return;
}

function ciar_puce_statut($statut) {
	$puce_statut = charger_fonction('puce_statut', 'inc');
	return $puce_statut(0, $statut, 0, 'auteur');
}

/**
 * Utile pour le plugin CINOTIF
*/
function ciar_article_ec($id_article){
	$return = false;
	
	if ($id_article) {
		$row = sql_fetsel("id_rubrique", "spip_articles", "id_article=".intval($id_article));
		if ($row)
			$id_rubart = $row['id_rubrique'];
		
		// Est-ce un EC directement ou par héritage ?
		while ($id_rubart) {
			if (sql_fetsel("id_rubrique", "spip_ciar_rubriques_protection", "id_rubrique=$id_rubart AND acces_restreint='_acces_indiv'","id_rubrique","")) {
				$return = true;
				break;
			} else {
				$row = sql_fetsel("id_parent", "spip_rubriques", "id_rubrique=$id_rubart",'', '');
				$id_rubart = $row['id_parent'];
				if ($id_rubart<1) break;
			}
		}	
	}

	return $return;
}

/**
 * Utile pour le plugin CINOTIF
*/
function ciar_article_mails_membres_ec($id_article){
	$id_rubart = false;
	$membres_ec = array();
	$mails_membres_ec = array();

	$row = sql_fetsel("id_rubrique", "spip_articles", "id_article=".intval($id_article));
	if ($row)
		$id_rubart = $row['id_rubrique'];

	if ($id_rubart) {
		$membres_ec = ciar_tableau_membres($id_rubart);
		if ($membres_ec) {
			$in = sql_in('id_auteur',$membres_ec);
			$result = sql_select("email", "spip_auteurs", $in);
			while ($row = sql_fetch($result)) {
				if ($row['email'])
					$mails_membres_ec[] = $row['email'];
			}			
		}
	}
	return $mails_membres_ec;
}

function ciar_rubrique_mails_membres_ec($id_rubrique){
	$mails_membres_ec = array();

	if ($id_rubrique) {
		$membres_ec = ciar_tableau_membres($id_rubrique);
		if ($membres_ec) {
			$in = sql_in('id_auteur',$membres_ec);
			$result = sql_select("email", "spip_auteurs", $in);
			while ($row = sql_fetch($result)) {
				if ($row['email'])
					$mails_membres_ec[] = $row['email'];
			}			
		}
	}
	return $mails_membres_ec;
}

?>