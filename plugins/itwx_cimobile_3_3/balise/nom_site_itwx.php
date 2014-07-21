<?php

function balise_NOM_SITE_ITWX($params){
	include_spip('inc/config');
	$itwxnom = lire_config('itwx/idItwxnom');
	
	if (!$itwxnom) {
		$params->code = "\$GLOBALS['meta']['nom_site']";
		return $params;
	}
	else{
		$params->code = "'$itwxnom'";
		return $params;
	}
}

?>
