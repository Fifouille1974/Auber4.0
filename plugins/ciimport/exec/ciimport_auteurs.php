<?php
/**
 * Plugin ciimport : Importation d'auteurs et de mots-cles
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

/*
L'administrateur du site la possibilité d'importer une liste d'auteurs (le fichier est intitulé annuaire.csv) afin d'éviter de les saisir manuellement.
Une fois que ce fichier a été téléchargé, le traitement offre à l'administrateur du site le choix du statut par défaut qui sera affecté à ces auteurs ('visiteur', 'rédacteur', etc.).
Le login par défaut sera 'prenom.nom' (la partie de l'adresse électronique située à gauche de l'arobase).
Le mot de passe par défaut sera soit généré automatiquement, soit le nom de l'auteur (tel qu'il figure dans son adresse électronique) suivi d'une séquence de caractères que l'administrateur du site peut choisir dans une liste déroulante.
Si un auteur a déjà été crée, il ne sera pas importé (la vérification porte sur l'adresse électronique).
L'administrateur du site est informé du nombre d'auteurs importés et du nombre d'auteurs figurant dans le fichier à importer. Le fichier importé est supprimé à la fin du traitement.

Le fichier a importer sera obligatoirement intitulé annuaire.csv et aura la structure suivante (La première ligne du fichier contient la définition des en-têtes) :
"Prénom","Nom","Messagerie"
"Yves","Montand","yves.montand@gmail.com"
"Jean","Dupond","jean.dupond@yahoo.fr"
*/

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/presentation');
include_spip('inc/actions');
include_spip('inc/autoriser');
include_spip('inc/documents');
include_spip('inc/filtres');
include_spip('inc/texte');

// Parametrage par fichier
include_spip('inc/ciimport_commun');
ciimport_lire_meta();		

// Compatibilite avec le plugin CICAS
$cicaspur = false;
if (defined('_DIR_PLUGIN_CICAS')) {
	include_spip('inc/cicas_commun');
	// lire la configuration du plugin
	cicas_lire_meta();
	// authentification CAS
	if ($GLOBALS['ciconfig']['cicas']=='oui' AND $GLOBALS['ciconfig']['cicasurldefaut'])
		$cicaspur = true;
}


function exec_ciimport_auteurs_dist()
{
	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
		$titre = _T('ciimport:icone_import_auteurs');
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page($titre, "configuration", "configuration");
		
		echo "<br />\n";
		echo gros_titre($titre,'', false);
		
		echo debut_gauche('',true);
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);

		// importer et afficher le resultat
		if (_request('choixstatut')) {
		
			$ret = ci_importation_auteurs();
			spip_log(_T('ciimport:auteur_texte30').$ret['affected']." / ".$ret['total']);

			$res1 = debut_cadre_trait_couleur("", true, "", "");
			$res1 .= "<p align='center'><b>";
			$res1 .= _T('ciimport:auteur_texte30').$ret['affected']._T('ciimport:auteur_texte31').$ret['total'];
			$res1 .= "</b></p>";
			if ($ret['total'] > $ret['affected'])
				$res1 .= _T('ciimport:auteur_texte32');
			$res1 .= fin_cadre_trait_couleur(true);
		
			// affiche les auteurs importés
			if ($ret['affected']>0 AND $ret['premierid']>0) {
				$i=0;
				$res1 .= _T('ciimport:auteur_texte33');

				// Compatibilite avec le plugin CICAS
				if ($cicaspur) {	
					// Avertissement en cas d'authentification CAS
					$res1 .= "<br>&nbsp;<br>";
					$res1 .= debut_cadre_trait_couleur("", true, "", "");
					$res1 .= "<p align='center'><b>"._T('avis_attention')."</b></p><P align='justify'>"
					. http_img_pack('warning.gif', _T('info_avertissement'), "style='width: 48px; height: 48px; float: right;margin: 10px;'");
					$res1 .= _T('ciimport:auteur_texte_cas');
					$res1 .= "</p>";
					$res1 .= fin_cadre_trait_couleur(true);
				}

				$res1 .= "<br>&nbsp;<br>";
				$res1 .= "<TABLE BORDER=0 CELLPADDING=3 CELLSPACING=0 WIDTH='100%' class='arial2'>\n";
				$res1 .= "<caption>"._T('ciimport:liste_auteurs')."</caption>";
				$res1 .= "<TR bgcolor='#DBE1C5'><TH id='auteur'>"._T('ciimport:auteur')."</TH><TH id='login'>"._T('ciimport:login')."</TH><TH id='password'>"._T('ciimport:password')."</TH></TR>";
				$result = sql_select("*", "spip_auteurs", "id_auteur>=".$ret['premierid'],"","");
				while($row = sql_fetch($result)){
					$couleur = ($i % 2) ? '#FFFFFF' : '#EDF3FE';
					$i++;
					$res1 .= "<TR bgcolor='$couleur'><TD headers='auteur'>".$row['nom']."</TD><TD headers='login'>".$row['login']."</TD><TD headers='password'>".$ret['tableaupass'][$row['id_auteur']]."</TD></TR>";
				}
				$res1 .= "</TABLE>\n";
			}
		
			$res1 .= "<br>";
			if (spip_version()>=3)
				$res1 .= generer_form_ecrire('auteurs', $res, '', attribut_html(_T('ciimport:retour_liste_auteurs')));
			else
				$res1 .= generer_form_ecrire('auteurs', $res, '', _T('ciimport:retour_liste_auteurs'));
		
			echo $res1;
		
		
		// Afficher les explications et le formulaire d'upload	
		} else {			
			// parametrage pour annuaire.intra.i2 ?
			if ($GLOBALS['ciconfig']['ciimportannuaire']=='oui')
				$ciimportannuaire = true;
			else
				$ciimportannuaire = false;

			// explication generale
			$res2 = debut_cadre_trait_couleur("", true, "", "");
			$res2 .= "<p align='center'><b>"._T('avis_attention')."</b></p><p align='justify'>"
					. http_img_pack('warning.gif', _T('info_avertissement'), "style='width: 48px; height: 48px; float: right;margin: 10px;'");
			$res2 .= ($ciimportannuaire?_T('ciimport:auteur_texte1'):_T('ciimport:auteur_texte11'));
			$res2 .= "</p>";

			// Compatibilite avec le plugin CICAS
			$res2 .= "<p align='justify'>";
			if ($cicaspur)
				$res2 .= _T('ciimport:auteur_texte_cas');
			else
				$res2 .= ($ciimportannuaire?_T('ciimport:auteur_texte2'):_T('ciimport:auteur_texte12'));
			$res2 .= "</p>";

			// explication de la verification
			$res2 .= "<p align='justify'>"._T('ciimport:auteur_verification');
			
			// si parametrage mail compatible
			if ($cimailcompatible = $GLOBALS['ciconfig']['ciimportmailcompatible']) {
				$res2 .= _T('ciimport:domaine_compatible');
				foreach ($cimailcompatible as $domaine1=>$domaine2) {
					$res2 .= "<br>".interdire_scripts(entites_html($domaine1." => ".$domaine2));			
				}
			}
			$res2 .= "</p>";

			// inviter a respecter la structure de fichier
			$res2 .= "<p align='justify'>";
			$res2 .= ($ciimportannuaire?_T('ciimport:auteur_texte3'):_T('ciimport:auteur_texte13'));
			$res2 .= "</p>";
			
					
			if (!$ciimportannuaire) {
				
				// detail de la structure de fichier
				if ($config = $GLOBALS['ciconfig']['ciimportauteur']) {
					if (is_array($config)){
						// la premiere ligne contient les en tetes
						// et on ajoute deux exemples
						$entete = '';
						$exemple = '';
						$exemple2 = '';
						while (list($key, $val) = each($config)) {
						    $entete .= '"' . interdire_scripts(entites_html($key)) . '",';
							if ($key=='prenom') {
								$exemple .= '"Yves",';
								$exemple2 .= '"Jean",';
							} elseif ($key=='nom') {
								$exemple .= '"Montand",';
								$exemple2 .= '"Dupond",';
							} elseif ($key=='messagerie') {
								$exemple .= '"yves.montand@gmail.com",';
								$exemple2 .= '"jean.dupond@yahoo.fr",';
							} else {
								$exemple .= '"",';
								$exemple2 .= '"",';
							}
						}
						$res2 .= substr($entete,0,-1)."<br>".substr($exemple,0,-1)."<br>".substr($exemple2,0,-1);
						
					}
				}
			}
		
			$res2 .= fin_cadre_trait_couleur(true);
		
			echo $res2; 		
		
			ci_afficher_boite_upload_auteurs();
		
		}
		echo fin_gauche(), fin_page();
	}
}


function ci_afficher_boite_upload_auteurs() {

    $texteon = _T('ciimport:telecharger_auteurs');
    $texteoff = _T('ciimport:liste_auteurs_a_importer');
	$document = false;
	$id_document = 0;
	$fichier = "";
	$date = "";
	$nom_cible = 'annuaire.csv';

	$id_document = intval(_request("show_docs"));
	if ($id_document) {
		$row = sql_fetsel("*", "spip_documents", "id_document=".$id_document,"","");
		if ($row)
			$document = $row;
	}

	if ($document) {
		$id_document = $document['id_document'];
		$date = $document['date'];
		$fichier = $document['fichier'];
		$extension = $document['extension'];

		if ($extension!='csv') {
			// supprimer le fichier si l'extension n'est pas CSV
			$row = sql_fetsel("*", "spip_documents_liens", "id_document=".$id_document,"","");
			if (!$row) {
				@unlink(_DIR_RACINE . _NOM_PERMANENTS_ACCESSIBLES . $fichier);
				sql_delete("spip_documents", "id_document=".$id_document);
			}
			$document = false;
			echo '<div class="formulaire_spip"><p class="reponse_formulaire reponse_formulaire_erreur">'._T("ciimport:erreur_format").'</p></div>';

		} else {
			// imposer le nom et l'emplacement du fichier
			if ($fichier!=$nom_cible) {
				$row = sql_fetsel("*", "spip_documents_liens", "id_document=".$id_document,"","");
				if (!$row) {
					$source = _DIR_RACINE . _NOM_PERMANENTS_ACCESSIBLES . $fichier;
					$destination = _DIR_RACINE . _NOM_PERMANENTS_ACCESSIBLES . $nom_cible;
					if (file_exists($destination))
						@unlink($destination);
					@rename($source, $destination);
					sql_updateq("spip_documents", array("fichier" => $nom_cible), "id_document=$id_document");
					$document['fichier'] = $nom_cible;
					$fichier = $nom_cible;
				}
			}
		}
	}
	
	// l'utilisateur veut supprimer le fichier
	if ($id_supprdoc = intval(_request('supprimer_doc'))) {
		$row = sql_fetsel("*", "spip_documents_liens", "id_document=".$id_supprdoc,"","");
		if (!$row) {
			$row2 = sql_fetsel("*", "spip_documents", "id_document=".$id_supprdoc,"","");
			if ($row2) {
				$fichier_suppr = $row2['fichier'];
				if ($fichier_suppr==$nom_cible) {
					@unlink(_DIR_RACINE . _NOM_PERMANENTS_ACCESSIBLES . $fichier_suppr);
					sql_delete("spip_documents", "id_document=".$id_supprdoc);
					$document = false;
				}
			}
		}
	}

	
	if (!$document) {
		if (spip_version()>=3)
			echo recuperer_fond('prive/squelettes/inclure/ciimport_joindre',array('id_document'=>$id_document,'fichier'=>$fichier,'redirect'=>'ciimport_auteurs'));
		else
			echo ciimport_joindre_auteurs();

	} else {
		echo debut_cadre_trait_couleur("", true, "", $texteoff);	
		echo "<div style='text-align: center;'>";
		if (spip_version()>=3) {
			echo quete_logo_document($document, generer_url_entite($id_document, 'document', '', '', true), '', '', '', '');
		} else {	
			echo document_et_vignette($document, '', true);
		}
		echo "</div>\n<div style='text-align: center;'>"
		. $fichier
		."</div>\n";
		
		$fond = 'doc-24.gif';
		$texte = _T('icone_supprimer_document');	
		$lien = generer_url_ecrire('ciimport_auteurs',"supprimer_doc=".$id_document);
		echo icone_horizontale($texte, $lien, $fond, "supprimer.gif", false);
		echo fin_cadre_trait_couleur(true);

		if (spip_version()>=3)
			echo '';
		else
			debut_cadre_relief();

		$res = '';
			
		$res .= spip_version()>=3 ? "<fieldset><legend>"._T('info_statut_utilisateurs_1')."</legend>" : "<div class='cadre cadre-r cadre_padding'>";
		$res .= _T('ciimport:auteur_texte20');
		$res .= " <select name='choixstatut' SIZE=1 CLASS='fondl'>";
		$res .= "<option value='0minirezo'>"._T('item_administrateur_2')."</option>";	
		$res .= "<option value='1comite' selected='selected'>"._T('intem_redacteur')."</option>";
		$res .= "<option value='6forum'>"._T('item_visiteur')."</option>";	
		$res .= "</select></center>\n";
		$res .= spip_version()>=3 ? "</fieldset>" : "</div>";

		// compatibilite avec le plugin cirv
		if (defined('_DIR_PLUGIN_CIRV')) {
			$res .= spip_version()>=3 ? "<fieldset><legend>"._T('info_redacteurs')."</legend>" : "<div class='cadre cadre-r cadre_padding'>";
			$res .= "<input type='checkbox' name='cistatut' id='cistatut' value='ciredval' /><label for='cistatut'>"._T('ciimport:ciredval_oui')."</label>";
			$res .= spip_version()>=3 ? "</fieldset>" : "</div>";
		}

		// compatibilite avec le plugin ciar
		if (defined('_DIR_PLUGIN_CIAR')) {	
			$res .= spip_version()>=3 ? "<fieldset><legend>"._T('info_administrateurs')."</legend>" : "<div class='cadre cadre-r cadre_padding'>";
			$res .= "<input type='radio' class='radio' name='cioption' id='1' value='ecadminsite' checked='checked' />
			<label for='1' style='font-weight: bold;'>"._T('ciimport:cioption_ecadminsite')."</label><br />
			<input type='radio' class='radio' name='cioption' id='2' value='eccma' />
			<label for='2'>"._T('ciimport:cioption_eccma')."</label><br />
			<input type='radio' class='radio' name='cioption' id='3' value='ecadminrestreint' />
			<label for='3'>"._T('ciimport:cioption_ecadminrestreint')."</label>";
			$res .= spip_version()>=3 ? "</fieldset>" : "</div>";
		}

		// compatibilite avec le plugin ciag
		if (defined('_DIR_PLUGIN_CIAG')) {	
			$res .= spip_version()>=3 ? "<fieldset><legend>"._T('ciag:titre_groupes_auteurs')."</legend>" : "<div class='cadre cadre-r cadre_padding'>";
			$res .= "<p>"._T('ciimport:groupe_auteur_texte20')."</p>";
			$res .= " <select name='choix_id_groupe' SIZE=1 CLASS='fondl'>";
			$res .= "<option value='0'> </option>";	
			$result = sql_select("*", "spip_ciag_grpauteurs","","","titre");
			while($row = sql_fetch($result))
				$res .= "<option value='".$row['id_groupe']."'>".htmlspecialchars($row['titre'])."</option>";				

			$res .= "</select></center>\n";
			$res .= spip_version()>=3 ? "</fieldset>" : "</div>";
		}		

		$res .= spip_version()>=3 ? "<fieldset><legend>"._T('entree_passe_ldap')."</legend>" : "<div class='cadre cadre-r cadre_padding'>";
		$res .= "<input type='radio' name='passlight' value='oui' id='label_passlight' checked>";
		$res .= "<label for='label_passlight'>"._T('ciimport:passlight')."</label>";
		$res .= " <SELECT NAME='choixextpass' SIZE=1 CLASS='fondl'>";	
		$res .= "<OPTION SELECTED>_1";	
		$res .= "<OPTION >_2";
		$res .= "<OPTION >_3";
		$res .= "<OPTION >_a";
		$res .= "<OPTION >_m";
		$res .= "<OPTION >_p";
		$res .= "</SELECT><br>\n";
		$res .= "<input type='radio' name='passlight' value='non' id='label_passlourd'>";
		$res .= "<label for='label_passlourd'>"._T('ciimport:passlourd')."</label>";
		$res .= spip_version()>=3 ? "</fieldset>" : "</div>";
		
		$res .= "<br />";
		$res .= "<input type='hidden' name='show_docs' value=".$id_document." >";
		
		if (spip_version()>=3) echo '<div class="formulaire_spip formulaire_configurer formulaire_configurer_articles formulaire_configurer_articles-new">';
		echo generer_form_ecrire('ciimport_auteurs', $res, '', _T('ciimport:bouton_import_auteurs'));
		if (spip_version()>=3) echo '</div>';
		
		fin_cadre_relief();
	}	
	
}

function ciimport_joindre_auteurs() {
	include_spip('inc/cisf_commun');

	$id_document = 0;
	$id_article = 0;
	$type = "article";
	$script = 'ciimport_auteurs';
	$icone = 'doc-24.gif';
	$mode = 'document';
    $titre = _T('ciimport:telecharger_auteurs');

	$joindre = charger_fonction('joindre', 'inc');
	$res = $joindre(array(
		'cadre' => 'relief',
		'icone' => $icone,
		'fonction' => 'creer.gif',
		'titre' => $titre,
		'script' => $script,
		'args' => "id_$type=$id_article",
		'id' => $id_article,
		'intitule' => _T('info_telecharger_ordinateur'),
		'mode' => $mode,
		'type' => $type,
		'ancre' => '',
		'id_document' => $id_document,
		'iframe_script' => ''
	));
	
	$res = str_replace("size='15'","size='55'",$res);
	$res = str_replace("name='url'","name='url' size='55'",$res);
	
	return $res;
}


function ci_importation_auteurs() {
	
	include_spip('inc/acces');
	include_spip('inc/charsets');
	
	$row = 0;
	$affected = 0;
	$premierid = 0;
	$tableauemail = array();
	$tableaupass = array();
	$return = array();
	
	$document = false;
	$id_document = 0;
	$fichier = "";
	$date = "";
	$choixstatut = _request('choixstatut');
	$choixextpass = _request('choixextpass');
	$passlight = _request('passlight');
	
	// compatibilite avec le plugin ciag
	$ciag_auteurs = array();
	$choix_id_groupe = 0;
	if (defined('_DIR_PLUGIN_CIAG'))
		$choix_id_groupe = intval(_request('choix_id_groupe'));
	
	
	$id_document = intval(_request("show_docs"));
	if ($id_document) {
		$row = sql_fetsel("*", "spip_documents", "id_document=".$id_document,"","");
		if ($row)
			$document = $row;
	}

	if ($document) {
		$id_document = $document['id_document'];
		$date = $document['date'];
		$fichier = $document['fichier'];
	}	
	
	if ($document) {
		$statut="1comite";
		if ($choixstatut) {
			if (in_array($choixstatut,array('0minirezo','1comite','6forum')))
				$statut = $choixstatut;
		}
		$extpass="_1";
		if ($choixextpass) {
			if (in_array($choixextpass,array('_1','_2','_3','_a','_m','_p')))
				$extpass = $choixextpass;
		}	
		
		// encodage des caracteres du fichier csv
		$csv_charset = "";
		$f = _DIR_RACINE . _NOM_PERMANENTS_ACCESSIBLES . $fichier;
		$contenu = @file($f);
		if (is_array($contenu))
			$r = join('', $contenu);
		else
			$r = (string)$contenu;
		
		if ($GLOBALS['meta']['charset'] == 'utf-8' AND !is_utf8($r))
			$csv_charset= 'iso-8859-1';
		if ($GLOBALS['meta']['charset'] == 'iso-8859-1' AND is_utf8($r))
			$csv_charset= 'utf-8';

		
		// compatibilite avec le plugin cirv
		$cistatut = '';
		if (defined('_DIR_PLUGIN_CIRV')) {
			if (_request('cistatut') AND _request('choixstatut')) {
				if (_request('cistatut')=='ciredval' AND _request('choixstatut')=='1comite')
					$cistatut = 'ciredval';
			}
		}
		
		// compatibilite avec le plugin ciar
		$cioption = '';
		if (defined('_DIR_PLUGIN_CIAR')) {
			if (_request('cioption') AND _request('choixstatut')) {
				if (_request('choixstatut')=='0minirezo') {
					if (in_array(_request('cioption'),array('eccma','ecadminsite','ecadminrestreint')))
						$cioption = _request('cioption');
				}
			}
		}
		
		
		$handle = fopen(_DIR_RACINE . _NOM_PERMANENTS_ACCESSIBLES . $fichier, "r");
		if ($handle) {
			
			while ($data = fgetcsv($handle, 1000, ",")) {
				$idrow++;
				// La première ligne contient les en-tetes
				if ($idrow>1) {
					
					// structure du fichier annuaire.csv (parametrable par fichier)
					$rang_prenom = 0;
					$rang_nom = 1;
					$rang_email = 2;
					if ($config = $GLOBALS['ciconfig']['ciimportauteur']) {
						if (is_array($config)){
							if (isset($config['prenom']))
								$rang_prenom = intval($config['prenom']);
							if (isset($config['nom']))
								$rang_nom = intval($config['nom']);
							if (isset($config['messagerie']))
								$rang_email = intval($config['messagerie']);
						}
					}
					
					$email=$data[$rang_email];

					// si pas d'email on n'importe pas
					if ($p = strpos($email, '@')) {
						$email = strtolower(trim($email));
						
						// Vérification que cet email n'existe pas déjà
						$n = sql_countsel("spip_auteurs", "UPPER(email)='".strtoupper($email)."'");	
						
						// compatibilite avec les anciennes adresses email
						if (!($n > 0)) {
							if ($cimailcompatible = $GLOBALS['ciconfig']['ciimportmailcompatible']) {
								$ci_tableau_email = explode('@',$email);
								$ci_nom_mail = strtolower($ci_tableau_email[0]);
								$ci_domaine_mail = strtolower($ci_tableau_email[1]);
		
								foreach ($cimailcompatible as $cle=>$valeur) {
									if ($ci_domaine_mail==strtolower($valeur)) {
										$emailcompatible = $ci_nom_mail.'@'.$cle;
										$n = sql_countsel("spip_auteurs", "UPPER(email)='".strtoupper($emailcompatible)."'");	
										if ($n > 0)
											break;
									}
								}
							}
						}

						if (!($n > 0)) {
							// Préparation de la requête
							$prenom_nom = substr($email, 0, $p);
							$login = $prenom_nom;
							if ($p = strpos($prenom_nom, '.')) {
								$nom_email = substr($prenom_nom, $p+1);
							} else {
								// s'il n'y a pas de point
								$nom_email = $prenom_nom;
							}
							if ($passlight=='non'){
								$new_pass = creer_pass_aleatoire(8, $email);
							} else {	
								$new_pass = $nom_email.$extpass;
							}
							$htpass = generer_htpass($new_pass);
							$alea_actuel = creer_uniqid();
							$alea_futur = creer_uniqid();
							$pass = md5($alea_actuel.$new_pass);

							$nom = $data[$rang_nom].' '.$data[$rang_prenom];
							if ($csv_charset)
								$nom = importer_charset($nom, $csv_charset);
								
							$tableau = array(
									'nom' => $nom, 'email'=>$email,
									'statut' => $statut, 'login'=>$login,								
									'pass' => $pass, 'htpass'=>$htpass,
									'alea_actuel' => $alea_actuel, 'alea_futur'=>$alea_futur
								);
		
							// compatibilite avec le plugin ciar
							if ($cioption)
								$tableau['cioption'] = $cioption;
								
							// compatibilite avec le plugin cirv
							if ($cistatut)
								$tableau['cistatut'] = $cistatut;

							// inserer	
							$id_auteur = sql_insertq('spip_auteurs', $tableau);

							if ($id_auteur>0) {
								$affected++;

								// mémorise le mot de passe (non crypté)
								$tableaupass[$id_auteur]=$new_pass;
								$tableauemail[$id_auteur]=$email;
								
								// mémorise le premier id ajouté
								if (!$premierid)
									$premierid = $id_auteur;
									
								// compatibilite avec le plugin ciag
								if ($choix_id_groupe>0)
									$ciag_auteurs[] = $id_auteur;
	
							}
						}	
					}
				}
			}
			fclose($handle);
			
			// compatibilite avec le plugin ciag
			if ($choix_id_groupe>0 AND $ciag_auteurs) {
				include_spip('inc/ciag_commun');					
				$pas = count($ciag_auteurs);
				ciag_modifier_auteurs_dans_grpauteurs($ciag_auteurs,$choix_id_groupe,false,'',$pas,$premierid);
			}

			// offrir un pipeline
			$cipipeline = true;
			if (@is_readable($charger = _CACHE_PIPELINES)){
				include_once($charger);
				if (!function_exists('execute_pipeline_ciimport_auteurs'))
					$cipipeline = false;
			}

			if ($cipipeline)
				pipeline('ciimport_auteurs',array('args'=>$fichier,'data'=>$tableauemail,'premierid'=>$premierid,'dernierid'=>$id_auteur));

		}
		
		// Suppression du fichier d'import
		@unlink(_DIR_RACINE . _NOM_PERMANENTS_ACCESSIBLES . $fichier);
		sql_delete("spip_documents", "id_document=".$id_document);
	}

	$return['affected'] = $affected;
	if ($idrow>0) {
		$idrow--;
		$return['total'] = $idrow;
	} else {
		$return['total'] = 0;
	}
	$return['premierid'] = $premierid;
	$return['tableaupass'] = $tableaupass;
	
	return $return;
}

?>