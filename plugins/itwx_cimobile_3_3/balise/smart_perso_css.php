<?php

function SMART_PERSO_CSS($params){
	include_spip('inc/config');
	$itwxcsssmart = lire_config('itwx/idItwxcsssmart');
	
	if ($itwxcsssmart) {
		$params->code = "'$itwxcsssmart'";
		return $params;
	}
}

?>
