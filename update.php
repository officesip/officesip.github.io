<?php
	$fname = "download/OfficeSIP-Server-3.1.zip";

	if (file_exists($fname))
		echo '{"Version":"3.1.4437.33090","Date":"\\/Date(' . filectime($fname) * 1000 . ')\\/","Url":"http://www.officesip.com/download/OfficeSIP-Server-3.1.zip"}';
?>