<?php
/***
	Gives out an access token to a user registered within the database
****/
include_once('config.php');
use \Firebase\JWT\JWT;

$response = new Response('auth');
$user = null;
if (isset($_POST['Username']) && $_POST['Username'] !== '' &&
	isset($_POST['Password']) && $_POST['Password'] !== '') {
	try {
		$db = Database::getInstance();
		$query = 'SELECT * FROM user WHERE Username = :usr AND Password = :pwd;';
		$stm = $db->prepare($query);
		
		$params = array(':usr' => $_POST['Username'],
						':pwd' => sha1($_POST['Password']));
		
		if ($stm->execute( $params )) {
			if ($stm->rowCount() === 1) {
				$user = $stm->fetchObject();
			} else {
				$response->Error('Invalid username or password', 400);
			}
		} else {
			throw new Exception('Database connection failed');
		}
	} catch (Exception $e) {
		$response->Error('Database connection error', 503);
	}
	
	if (isset($user)) {
		$token = array(
			'usr' => $user->ID,
			'iat' => time(),
			'exp' => time() + TOKEN_EXPIRATION_TIME,
			'prm' => json_decode($user->Permissions)
		);

		$jwt = JWT::encode($token, SECURITY_KEY);
		// $decoded = JWT::decode($jwt, $key, array('HS256'));

		$response->Success( array('jwt' => $jwt) );
	}
} else {
	$response->Error('Credentials missing', 400);
}