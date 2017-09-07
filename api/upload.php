<?php
include_once('config.php');
use \Firebase\JWT\JWT;
$response = new Response('upload');

function saveFile($filename,$userId,$url) {
	$db = Database::getInstance();
	$query = 'INSERT INTO pdf (Filename, UploadDate, FileUrl, UploadUserID) VALUES (:file, :date, :url, :user);';
	$params = array(
		':file' => $filename,
		':date' => date('Y-m-d H:i:s', time()),
		':url' => $url,
		':user' => $userId );
	$stm = $db->prepare($query);
	return $stm->execute($params);
}

try {
	$headers = getallheaders();
	if (!isset($headers['Authorization'])) {
		$response->Error('Authorization header not set',401);
	}
	// Extract token
	$jwt = explode(' ', $headers['Authorization'])[1];
	
	// Decode token
	$decoded = JWT::decode($jwt, SECURITY_KEY, array('HS256'));
	
	// Check token expiration
	if ($decoded->exp < time()) {
		$response->Error('Token expired. Refresh the page to authorize again', 401);
	}
	// Check if upload permission is granted
	//var_dump($decoded);
	if (!in_array('upload', $decoded->prm)) {
		$response->Error('Permission to upload not granted', 401);
	}
	$files = $_FILES['Files'];
	for ($i = 0; $i < count($files['name']); $i++) {
		$tmpName = $files['tmp_name'][$i];
		// hash filename to obfuscate
		$newName = md5($tmpName . time()) . '.pdf';
		// save to relative folder
		move_uploaded_file($tmpName, 'pdf'. DS . $newName);
		// commit data to DB
		$success = saveFile(
			$files['name'][$i],
			$decoded->usr,
			PDF_URL . $newName);
		if (!$success) { $response->Error('Database error occured', 500); }
	}
	
} catch (Exception $e) {
	$response->Error($e->getMessage(),500);
}
