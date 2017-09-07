<?php
include_once('config.php');
use \Firebase\JWT\JWT;
$response = new Response('list');

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
	if (!in_array('list', $decoded->prm)) {
		$response->Error('Permission to upload not granted', 401);
	}
	
	$db = Database::getInstance();
	$query = 'SELECT pdf.ID id, Filename filename, UploadDate date, Username user FROM pdf INNER JOIN user ON pdf.UploadUserID = user.ID ORDER BY UploadDate ASC;';
	$stm = $db->prepare($query);
	
	if ($stm->execute()) {
		$response->Success($stm->fetchAll(PDO::FETCH_ASSOC));
	} else {
		$response->Error('Database error occured', 500);
	}
	
} catch (Exception $e) {
	$response->Error($e->getMessage(),500);
}
