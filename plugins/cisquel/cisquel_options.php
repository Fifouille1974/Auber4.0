<?php

// Ignorer l'authentification par auth http
$ignore_auth_http = true;

// Pour le javascript, trois modes : (-1) le refuse partout, (0), (1)
// Si on met (-1) la barre d'outil typo des forums fonctionne sous SPIP 2.1 mais pas sous SPIP 2.0
// $filtrer_javascript = -1;

// Ne pas creer un paragraphe si le texte en contient un seul
$toujours_paragrapher =  false;

// retour a la ligne
define('_TRAITEMENT_RACCOURCIS', 'propre(cisquel_post_autobr(%s), $connect)');

?>