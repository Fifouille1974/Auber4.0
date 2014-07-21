<?php
/**
 * Plugin ciimport : Importation d'auteurs et de mots-cles
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

/*
L'administrateur du site la possibilité d'importer une liste de mots-clés (le fichier est intitulé motscles.csv) afin d'éviter de les saisir manuellement.
Si un mot-clé a déjà été crée, il ne sera pas importé (la vérification porte sur le titre du mot et le titre du son groupe).
L'administrateur du site est informé du nombre de mots-clés importés et du nombre de mots-clés figurant dans le fichier à importer. Le fichier importé est supprimé à la fin du traitement.

Le fichier a importer sera obligatoirement intitulé motscles.csv et aura la structure suivante (La première ligne du fichier contient la définition des en-têtes) :
"Titre mot","Titre groupe","Descriptif","Texte"
"aménagement urbain";"Thèmes nationaux A - C";"";"";
"aménagement du territoire";"Thèmes nationaux A - C";"";"";

A noter que « Titre groupe » est le titre du groupe de mots auquel lemot-clé est rattaché.
*/

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/presentation');
include_spip('inc/actions');
include_spip('inc/autoriser');
include_spip('inc/documents');



function exec_ciimport_motscles_dist()
{
	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
		$titre = _T('ciimport:icone_import_motscles');
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page($titre, "configuration", "configuration");
		
		echo gros_titre($titre,'', false);
		
		echo debut_gauche('',true);
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);

		// importer et afficher le resultat
		if (_request('choiximporter')) {
		
			$ret = ci_importation_motscles();
			spip_log(_T('ciimport:mot_texte30').$ret['affected']." / ".$ret['total']);

			$res1 = debut_cadre_trait_couleur("", true, "", "");
			$res1 .= "<p align='center'><b>";
			$res1 .= _T('ciimport:mot_texte30').$ret['affected']._T('ciimport:mot_texte31').$ret['total'];
			$res1 .= "</b></p>";
			if ($ret['total'] > $ret['affected'])
				$res1 .= _T('ciimport:mot_texte32');
			$res1 .= fin_cadre_trait_couleur(true);
		
			// affiche les mots importés
			if ($ret['affected']>0 AND $ret['premierid']>0) {
				$i=0;
				$res1 .= "<div class='cadre'>";
				$res1 .= "<TABLE BORDER=0 CELLPADDING=3 CELLSPACING=0 WIDTH='100%' class='arial2'>\n";
				$res1 .= "<caption>"._T('ciimport:liste_mots')."</caption>";
				$res1 .= "<TR bgcolor='#DBE1C5'><TH id='titre'>"._T('ciimport:titre')."</TH><TH id='groupe'>"._T('ciimport:type')."</TH><TH id='descriptif'>"._T('ciimport:descriptif')."</TH></TR>";
				$result = sql_select("*", "spip_mots", "id_mot>=".$ret['premierid'],"","");
				while($row = sql_fetch($result)){
					$couleur = ($i % 2) ? '#FFFFFF' : '#EDF3FE';
					$i++;
					$res1 .= "<TR bgcolor='$couleur'><TD headers='titre'>".$row['titre']."</TD><TD headers='groupe'>".$row['type']."</TD><TD headers='descriptif'>".$row['descriptif']."</TD></TR>";
				}
				$res1 .= "</TABLE>\n";
				$res1 .= "</div>";
			}
	
			$res1 .= "<div class='cadre'>";
			if (spip_version()>=3)
				$res1 .= generer_form_ecrire('mots', $res, '', attribut_html(_T('ciimport:retour_liste_mots')));
			else
				$res1 .= generer_form_ecrire('mots_tous', $res, '', _T('ciimport:retour_liste_mots'));
			$res1 .= "</div>";
					
			echo $res1;
		
		
		// Afficher les explications et le formulaire d'upload	
		} else {	
		
			$res2 = debut_cadre_trait_couleur("", true, "", "");
			$res2 .= "<p align='center'><b>"._T('avis_attention')."</b></p><p align='justify'>"
					. http_img_pack('warning.gif', _T('info_avertissement'), "style='width: 48px; height: 48px; float: right;margin: 10px;'");
			$res2 .= _T('ciimport:mot_texte1')."</p>";
			$res2 .= "<p align='justify'>"._T('ciimport:mot_texte2')."</p>";
			$res2 .= fin_cadre_trait_couleur(true);
		
			echo $res2; 		
		
			ci_afficher_boite_upload_motscles();

		}
		echo fin_gauche(), fin_page();
	}
}


function ci_afficher_boite_upload_motscles() {

    $texteon = _T('ciimport:telecharger_mots');
    $texteoff = _T('ciimport:liste_mots_a_importer');
	$document = false;
	$id_document = 0;
	$fichier = "";
	$date = "";
	$nom_cible = 'motscles.csv';

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
			echo recuperer_fond('prive/squelettes/inclure/ciimport_joindre',array('id_document'=>$id_document,'fichier'=>$fichier,'redirect'=>'ciimport_motscles'));
		else
			echo ciimport_joindre_motscles();
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
		$lien = generer_url_ecrire('ciimport_motscles',"supprimer_doc=".$id_document);
		echo icone_horizontale($texte, $lien, $fond, "supprimer.gif", false);
		
		$res = "<br>";
		$res .= "<input type='hidden' name='show_docs' value=".$id_document." >";
		$res .= "<input type='hidden' name='choiximporter' value='oui'>";
	
		if (spip_version()>=3)
			echo generer_form_ecrire('ciimport_motscles', $res, '', attribut_html(_T('ciimport:bouton_import_mots')));
		else
			echo generer_form_ecrire('ciimport_motscles', $res, '', _T('ciimport:bouton_import_mots'));
		
		echo fin_cadre_trait_couleur(true);
	}		
}

function ciimport_joindre_motscles() {
	include_spip('inc/cisf_commun');

	$id_document = 0;
	$id_article = 0;
	$type = "article";
	$script = 'ciimport_motscles';
	$icone = 'doc-24.gif';
	$mode = 'document';
    $titre = _T('ciimport:telecharger_mots');

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


function ci_importation_motscles() {
	global $choixstatut, $choixextpass,$passlight;
	
	include_spip('inc/charsets');
	
	$row = 0;
	$affected=0;
	$premierid=0;
	$return=array();
	
	$document = false;
	$id_document = 0;
	$fichier = "";
	$date = "";
	
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

		
		
		$handle = fopen(_DIR_RACINE . _NOM_PERMANENTS_ACCESSIBLES . $fichier, "r");
		if ($handle) {
			
			while ($data = fgetcsv($handle, 1000, ",")) {
				$idrow++;
				// La première ligne contient les en-tetes
				if ($idrow>1) {
					$mot = trim($data[0]);
					$groupe = trim($data[1]);
					if ($csv_charset) {
						$mot = importer_charset($mot, $csv_charset);
						$groupe = importer_charset($groupe, $csv_charset);
					}

					// Si pas de titre mot et/ou pas de titre groupe, ne pas importer
					if ($mot AND $groupe) {
						// Vérification que ce mot n'existe pas déjà
						$n = sql_countsel("spip_mots", "UPPER(titre)='".strtoupper($mot)."' AND UPPER(type)='".strtoupper($groupe)."'");	
						if (!($n > 0)) {
							// Préparation de la requete
							$descriptif = trim($data[2]);
							$texte = trim($data[3]);
							if ($csv_charset) {
								$descriptif = importer_charset($descriptif, $csv_charset);
								$texte = importer_charset($texte, $csv_charset);
							}
							
							$id_groupe = ci_id_groupe($groupe);

							// prendre le titre existant dans la base
							// en raison des majuscules / minuscules
							// et de la denormalisation de SPIP
							$groupe = ci_titre_groupe($id_groupe);
							
							$id_mot = sql_insertq("spip_mots", array(
								"titre"	=> $mot,
								"descriptif" => $descriptif,
								"texte"	=> $texte,
								"id_groupe"	=> $id_groupe,
								"type"	=> $groupe));

							if ($id_mot>0) {
								$affected++;

								// mémorise le premier id ajouté
								if (!$premierid)
									$premierid = $id_mot;
									
							}
						}	
					}
				}
			}
		fclose($handle);
		}
		
		// Suppression du fichier d'import
		sql_delete("spip_documents", "id_document=".$id_document);
		@unlink(_DIR_RACINE . _NOM_PERMANENTS_ACCESSIBLES . $fichier);
	}

	$return['affected'] = $affected;
	if ($idrow>0) {
		$idrow--;
		$return['total'] = $idrow;
	} else {
		$return['total'] = 0;
	}
	$return['premierid'] = $premierid;

	return $return;
}

function ci_id_groupe($type) {

	static $groupes = array();
	$type_maj = strtoupper($type);

	// securite
	if (!type)
		return 0;
	
	// pre remplissage
	if (!$groupes) {
		$result = sql_select("id_groupe, titre", "spip_groupes_mots", "","","");
		while ($row = sql_fetch($result)) {
			$groupes[strtoupper($row['titre'])] = $row['id_groupe'];
		}
	}

	// le cas echeant, creer le groupe
	if (!isset($groupes[$type_maj])) {
		$groupes[$type_maj] = sql_insertq("spip_groupes_mots", array(
			'titre' => $type,
			'unseul' => 'non',
			'obligatoire' => 'non',
			'tables_liees'=> 'articles',
			'minirezo' =>  'oui',
			'comite' =>  'oui',
			'forum' => 'non')) ;
	}
	
	return $groupes[$type_maj];
}

function ci_titre_groupe($id_groupe) {

	static $titres = array();

	// securite
	if (!$id_groupe)
		return '';
	
	// pre remplissage
	if (!$titres) {
		$result = sql_select("id_groupe, titre", "spip_groupes_mots", "","","");
		while ($row = sql_fetch($result)) {
			$titres[$row['id_groupe']] = $row['titre'];
		}
	}

	// le cas echeant, rechercher le titre du groupe
	if (!isset($titres[$id_groupe])) {
		$row = sql_fetsel("titre", "spip_groupes_mots", "id_groupe=".$id_groupe,"","");
		if ($row) {
			$titres[$id_groupe] = $row['titre'];
		}
	}

	if (!isset($titres[$id_groupe]))
		return '';

	return $titres[$id_groupe];
}

?>