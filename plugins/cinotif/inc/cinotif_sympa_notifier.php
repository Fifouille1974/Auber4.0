<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Envoyer un mail a une ou plusieurs listes de diffusion SYMPA
 *
 * Si on utilise un gestionnaire de liste de diffusion autre que SYMPA, il est possible de surcharger cette fonction.
 *
 * Si on souhaite envoyer les notifications aux listes SYMPA, avec authentification sur le serveur smtp,
 * il est possible de surcharger cette fonction et d'envoyer le mail de maniere specifique.
 *
 * @param $email (adresse email de la (ou des) liste de diffusion SYMPA)
 * @param $sujet (sujet du mail)
 * @param $texte (texte du mail)
 * @param $adresse_proprio_liste_sympa (adresse du proprietaire de la liste de diffusion SYMPA)
 */
function inc_cinotif_sympa_notifier_dist($email,$sujet,$texte,$adresse_proprio_liste_sympa){

	$return = '';
		
	if ($email AND $sujet AND $texte AND $adresse_proprio_liste_sympa){
		
		$from = $adresse_proprio_liste_sympa;
		
		$headers = '';
		
		$envoyer_mail = charger_fonction('envoyer_mail', 'inc');
		$envoyer_mail($email, $sujet, $texte, $from, $headers);
	}
	
	return $return;
}

?>