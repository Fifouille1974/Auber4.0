<?php

// This is a SPIP language file  --  Ceci est un fichier langue de SPIP

if (!defined("_ECRIRE_INC_VERSION")) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(


//-- Squelette
'retour' => 'Retour',
'siteclassique' => 'Version classique du site',
'pageclassique' => 'Version classique',
'infos'=> 'Infos',
'inscription' => 'Inscription',
'message' => 'Message',
'messages' => 'Messages',
'noevent' => 'Aucun &eacute;v&eacute;nement',
'evenements' => 'Tous les &eacute;v&eacute;nements',
'lirearticle' => 'Lire l&acute;article',
'copyright' =>'<a href="http://twxdesign.com/">iTwX Mobile &copy; 2012</a>',

  // iTwX questions
'smart_question' => 'Voulez-vous naviguer sur la version Smartphone du site?',
'mob_question' => 'Voulez-vous naviguer sur la version Mobile du site?',
'tab_question' => 'Voulez-vous naviguer sur la version Tablette du site?',
'smart_squel' => 'Consulter la version pour Smartphone',
'mob_squel' => 'Consulter la version pour Mobile',
'tab_squel' => 'Consulter la version pour Tablette',



//-- Prive 

  // Plugin
'itwx_description' => 'Le plugin iTwX propose 3 jeux de squelettes adapt&eacute;s pour tout type de mobile et pour tablette. Pour proc&eacute;der &agrave; l\'installation voir <a href="http://twxdesign.com/spip.php?article18">les consignes</a>.',  

  // Configuration iTwX
'config_titre' => 'iTwX Mobile',
'config_itwx' => 'Configuration des squelettes iTwX Mobile',
'config_explication' => '<span style="font-size:0.9em">Voir <a href="http://twxdesign.com/spip.php?aticle18">Documentation</a></span>.',
'config_player' => '<b>Gestion des lecteurs flash pour iPhone / iPod / iPad (exp&eacute;rimentale) :</b><br /><br />Compatible avec les lecteurs flash du plugin <b>Lecteur Multimedia</b>.<br /><br /><span style="font-size:0.9em">Mettre les codes d\'importation entre les div:</span><br /><b>&lt;div class="twx-player"&gt;</b>codes<b>&lt;/div&gt;</b><br /><br /><span style="font-size:0.9em">Pour afficher le lecteur iPhone, iPod, iPad pour les documents de format mp4 :</span> <br /><b>&lt;docXX|iplayer&gt;</b>',
'vignette_player' => '<b>Vignettes iPlayer :</b><br />Pour personnaliser les vignettes vid&eacute;o, coller <a href="../plugins/itwxmobile_3_2/prive/img/iPlayer_through.zip">iPlayer_through</a> sur une image de format 300x188.',
'liens' => '<b>Acc&egrave;s directs</b> /!\ <small>(cookie)</small>:<br />',
'smartphone' => '<a href="../?cimobile=smartphone">Version Smartphone</a>',
'mobile' => '<a href="../?cimobile=mobile">Version Mobile</a>',
'tablette' => '<a href="../?cimobile=tablette">Version Tablette</a>',
'web' => '<a href="../?cimobile=web">Retour version classique</a>',

  // Theme
'config_legend' => 'S&eacute;lectionner le th&egrave;me',
'affiche' => 'Cliquer sur les images pour agrandir.',
'agrandir' => 'Agrandir',
'bleu' => 'Bleu',
'noirblanc' => 'Noir &amp; Blanc',
'vert' => 'Vert',
'mauve' => 'Mauve',
'rouge' => 'Rouge',
'orange' => 'Orange',

  // Squelette
'skel_titre' => 'Configuration du switcher',
'redirection' => 'Mode de redirection :',
'automatique' => 'Automatique',
'notification' => 'Par notification',
'nosmart' => '&nbsp;Rediriger tous les Smartphones vers la version Mobile du site',
'notab' => '&nbsp;Rediriger toutes les Tablettes vers la version classique du site',
'retour_sommaire' => '&nbsp;Afficher les liens de retour vers les pages classiques',
'switcher_comment' => '&bull; Pour rediriger les mobiles et tablettes depuis le site classique vers les squelettes iTwX, coller dans votre r&eacute;pertoire squelettes :<br />&nbsp; &ndash; dans sommaire.html :<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>&lt;INCLURE{fond=itwx/redirection}&gt;</b><br />&nbsp; &ndash; dans rubrique.html :<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>&lt;INCLURE{fond=itwx/redirection}{id_rubrique}&gt;</b><br />&nbsp; &ndash; dans les pages article.html, breve.html, site.html, mot.html et auteur.html, remplacer avec l\'objet <b>id_</b> correspondant.<br />&bull; Style avec la class <b>.itwx_alerte</b>',


  // CFG redirection
'itwxnom_titre' => '&nbsp;Personnaliser les squelettes (optionnel)',
'itwxnom_explication' => 'Si vide, reviendra au d&eacute;faut.',
'itwxnom_comment' => 'Par d&eacute;faut&sbquo; le nom de votre site affich&eacute; en sommaire iTwX est limit&eacute; &agrave; 35 caract&egrave;res (sauf tablettes). Aussi&sbquo; si vous le souhaitez ou pour toute autre raison&sbquo; vous avez la possibilit&eacute; de modifier ce nom ainsi que le descriptif pour les squelettes iTwX.',
'auteurs_sommaire' => '&nbsp;Afficher le lien vers la liste des auteurs dans le sommaire',
'rubriques_sommaire' => '&nbsp;Afficher la liste des rubriques dans le sommaire (sauf tablettes)',
'mots_sommaire' => '&nbsp;Afficher le lien vers la liste des mots cl&eacute;s dans le sommaire',
'css_perso' => '&nbsp;Activer la surcharge css',
'css_itwx_perso' => '&nbsp;CSS iTwX perso',
'css_perso_explication' => '&nbsp;Pour surcharger uniquement sur squelettes \'Mobile\', commencer le css par <b>#mob</b>, pour \'Smartpphone\' commencer par <b>#smart</b>, et pour \'Tablette\' commencer par <b>#tab</b>.',
'itwxsocialtags' => '&nbsp;Activer le s&eacute;lecteur <b>#socialtags</b>',
'itwxgoogleplus1' => '&nbsp;Activer le s&eacute;lecteur <b>#googleplus1</b>',
'appletouchactiv' => '&nbsp;Activer l\'Apple Touch Icon',
'noicon' => '&nbsp;Non',
'icondefaut' => '&nbsp;D&eacute;faut',
'iconprecompose' => '&nbsp;Pr&eacute;compos&eacute;',

// -- Alerte fin formulaire
'config_alerte' => '<b>Vider le cache</b> (images) apr&egrave;s chaque modification !',

);

?>
