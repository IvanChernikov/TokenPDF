<?php
include_once('config.php');
use \Firebase\JWT\JWT;

$response = new Response('view');

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
	if (!in_array('view', $decoded->prm)) {
		$response->Error('Permission to upload not granted', 401);
	}
	if (!isset($_POST['id'])) {
		$response->Error('No file ID specified');
	}
	$db = Database::getInstance();
	$query = 'SELECT FileUrl FROM pdf WHERE ID = :id;';
	$params = array(':id' => $_POST['id']);
	$stm = $db->prepare($query);
	
	if ($stm->execute($params)) {
		$pdf = $stm->fetch(PDO::FETCH_ASSOC);
		$response->Success($pdf['FileUrl']);
		
	} else {
		$response->Error('Database error occured',500);
	}
	
} catch (Exception $e) {
	$response->Error($e->getMessage(),500);
}
