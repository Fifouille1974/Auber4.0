<?php

if (!defined("_ECRIRE_INC_VERSION")) return;


if (defined('_DIR_PLUGIN_CINOTIF'))
	include_spip('notifications/cinotif_instituerarticle');
else
	include_spip('notifications/ciar_instituerarticle');

?>