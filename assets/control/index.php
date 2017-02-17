<?php


error_reporting(0);

if (!function_exists('getallheaders')) {
	$headers = array();
	foreach ($_SERVER as $name => $value) {
		if (substr($name, 0, 5) == 'HTTP_') {
			$headers[str_replace(' ', '-',
				ucwords(strtolower(str_replace(
					'_', ' ', substr($name, 5)))))] = $value;
		}
	}
}
$headers = getallheaders();

if (!isset($_GET['no-cache'])) {
	$HashID = md5($_SERVER['REQUEST_URI']);
	$ExpireTime = 86400*360;
	header('Cache-Control: max-age=' . $ExpireTime);
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + $ExpireTime).' GMT');
	header('ETag: ' . $HashID);
	if (strpos(@$headers['If-None-Match'], $HashID) !== false) {
		header('HTTP/1.1 304 Not Modified');
		exit();
	}
}

require_once 'controller.php';

$file_ext = (string) substr(strrchr($_GET['file'], '.'), 1);


$mimes = file_get_contents(dirname(__FILE__) . "/mime.types");
$mimes = explode("\n", $mimes);
$file_mime = null;
foreach ($mimes as $mime) {
	list($type, $exts) = explode("\t", $mime);
	$exts = explode(" ", $exts);
	if (preg_grep("/$file_ext/i", $exts)) {
		header("Content-Type: " . $type);
		$file_mime = $type;
		break;
	}
}

if (is_null($file_mime)) {
	header("Content-Type: application/octet-stream");
	new _AssetsController($_GET['file'], $file_ext);
	exit();
}

list($file_category) = explode("/", $file_mime);
new _AssetsController($_GET['file'], $file_ext, $file_mime, $file_category);

?>