<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Envoyer un email a SYMPA pour ajouter ou supprimer un abonne
 *
 * Si on utilise un gestionnaire de liste de diffusion autre que SYMPA, il est possible de surcharger cette fonction.
 *
 * Si on souhaite envoyer les commandes à SYMPA avec authentification sur le serveur smtp,
 * il est possible de surcharger cette fonction et d'envoyer le mail de maniere specifique.
 *
 * @param $action ('abonner' ou 'desabonner')
 * @param $email (mail de la personne a abonner ou supprimer de la liste de diffusion SYMPA)
 * @param $adresse_liste_sympa (adresse de la liste de diffusion SYMPA)
 * @param $adresse_sympa (adresse de SYMPA)
 * @param $adresse_proprio_liste_sympa (adresse du proprietaire de la liste de diffusion SYMPA)
 */
function inc_cinotif_sympa_gerer_abonne_dist($action,$email,$adresse_liste_sympa,$adresse_sympa,$adresse_proprio_liste_sympa){
	$return = '';
		
	if (in_array($action,array('abonner','desabonner')) AND $email AND $adresse_liste_sympa AND $adresse_sympa AND $adresse_proprio_liste_sympa){

		// on met la partie gauche de l'email comme nom 
		$tableau_email = explode('@',$email);
		$nom_mail = strtolower($tableau_email[0]);

		// syntaxe de SYMPA si l'expediteur est le proprietaire de la liste de diffusion SYMPA
		// cf. https://groupes.renater.fr/sympa/help/mail_commands
		$prefixe = 'ADD';
		if ($action=='desabonner')
			$prefixe = 'DEL';
		
		$sujet = $prefixe.' '.$adresse_liste_sympa.' '.$email.' '.$nom_mail;

		$from = $adresse_proprio_liste_sympa;
		
		$headers = '';
		
		$envoyer_mail = charger_fonction('envoyer_mail', 'inc');
		$envoyer_mail($adresse_sympa, $sujet, $texte, $from, $headers);
	}
	
	return $return;
}

?>