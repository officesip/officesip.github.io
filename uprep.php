<?php

$dir_uprep = getcwd() . "/uprep";
$show_debug = false;
$max_files_in_uprep = 100;
$max_uprep_file_size = 100000;
$uprep_name = "uprep";
$uprep_pass = "qeiusroi123woi3zf";
$uprep_mailto = "support@officesip.com";
$appver = $_GET['app'] . substr($_GET['ver'], 0, 3);

if($show_debug)
	echo "uprep.php results.\r\n";

function countFiles($dir)
{
	$count = 0;
	if (is_dir($dir)) {
	    if ($dh = opendir($dir)) {
	        while (($file = readdir($dh)) !== false) {
				if (/*is_file($file) && */$file !== '.' && $file !== '..')
					$count++;
	        }
	        closedir($dh);
	    }
	}
	return $count;
}

function changeAttr($filename)
{
	$filenametemp = $filename . ".temp";

	if(!copy($filename, $filenametemp))
		return false;
		
	if(!unlink($filename))
		return false;

	if(!copy($filenametemp, $filename))
		return false;
		
	if(!unlink($filenametemp))
		return false;
		
	return true;
}

function getIP()
{
	$ip;
	if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if(getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if(getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
	else
		$ip = "UNKNOWN";
	return $ip;
} 

if ($_SERVER['PHP_AUTH_USER'] != $uprep_name || $_SERVER['PHP_AUTH_PW'] != $uprep_pass ) {
    header('WWW-Authenticate: Basic realm="officesip.com"');
    header('HTTP/1.0 401 Unauthorized');
    echo "Forbidden!";
    exit;
}

if( countFiles($dir_uprep) >= $max_files_in_uprep )
{
	echo "#RESPONSE#Server busy, please try later (1001).#RESPONSE#";
}
else if( $appver == 'SRV3.3' || $appver == 'SRV3.4' )
{
	if($show_debug)
	{
		echo "Upload folder: " . $dir_uprep . "\r\n";
		echo "Files in folder: " . countFiles($dir_uprep) . "\r\n";
	}

	if(count($_FILES) <= 0)
		echo "#RESPONSE#No files (1006).#RESPONSE#";
	else
	{
		foreach( $_FILES as $file_id => $file_desc)
			break;
		
		if (($_FILES[$file_id]["type"] == "application/octet-stream") && ($_FILES[$file_id]["size"] < $max_uprep_file_size))
		{
			if ($_FILES[$file_id]["error"] > 0)
			{
				echo "#RESPONSE#Upload error (1002):" . $_FILES[$file_id]["error"] . "#RESPONSE#";
			}
			else
			{
				if($show_debug)
				{
					echo "Id: " . $file_id . "\r\n";
					echo "Upload: " . $_FILES[$file_id]["name"] . "\r\n";
					echo "Type: " . $_FILES[$file_id]["type"] . "\r\n";
					echo "Size: " . ($_FILES[$file_id]["size"] / 1024) . " Kb\r\n";
					echo "Temp file: " . $_FILES[$file_id]["tmp_name"] . "\r\n";
				}

				$filetime = $_GET['app'] . date("Y_m_d___H_i_s");
				$count = 0;
				$filename = "";
					
				do
				{
					$filename = $dir_uprep . "/" . $filetime . "___" . $count . ".txt";
					if( $count++ > 9 )
						break;
				}
				while( file_exists( $filename ) );

				if($show_debug)
					echo "Stored as: " . $filename . "\r\n";
				
				if( $count > 9 )
				{
					echo "#RESPONSE#Server busy, please try later (1003).#RESPONSE#";
				}
				else
				{
					if( move_uploaded_file($_FILES[$file_id]["tmp_name"], $filename) )
					{
						if(changeAttr($filename))
						{
							echo "#RESPONSE#OK#RESPONSE#";
							mail($uprep_mailto, "BUG " . $_GET['app'] . " " . $_GET['ver'] . "     " . getIP(), "\r\n\r\n" . $filename, "");
						}
						else
							echo "#RESPONSE#Internal error (1005).#RESPONSE#";
					}
					else
						echo "#RESPONSE#Internal Error (1004).#RESPONSE#";
				}
			}
		}
		else
		{
			echo "#RESPONSE#Invalid File (1007).#RESPONSE#";
		}
	}
}
else {
	echo "#RESPONSE#OK#RESPONSE#";
}

if($show_debug)
	echo "\r\nEnd.";

?>