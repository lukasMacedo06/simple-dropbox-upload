<?php

require_once "vendor/autoload.php";

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Kunnu\Dropbox\Exceptions\DropboxClientException;

// Check if a file is sent
if (!isset($_FILES['fileToUploadDpx'])) {
	$data = [
		'error' => [
			'message' => 'Não foi encontrado nenhum arquivo para upload.'
		]
	];
	$json = json_encode($data);
	echo $json;
	return $json;
}

// Check if appens some PHP file error
if ($_FILES['fileToUploadDpx']['error'] != 0) {
	$json = json_encode(['error' => ['message' => 'O arquivo excedeu o tamanho limite do servidor.', 'internal_message' => 'UPLOAD_ERR_CODE : '.$_FILES['fileToUploadDpx']['error'].', check https://www.php.net/manual/pt_BR/features.file-upload.errors.php, to check the problem']]);
	echo $json;
	return $json;
}

// You can get the data below by going to https://www.dropbox.com/developers/apps and creating a new application
$app_key = "app_key";
$app_secret = "app_secret";
$token = "access_token";
$app = new DropboxApp($app_key, $app_secret, $token);
$dropbox = new Dropbox($app);

// Uploading file to Dropbox
try {
	$pathToLocalFile = $_FILES['fileToUploadDpx']['tmp_name'];
	$localFileName = $_FILES['fileToUploadDpx']['name'];
	$pathDropbox = "/uploads-form/";
	$dropboxFile = new DropboxFile($pathToLocalFile);

	$fileUploaded = $dropbox->upload($dropboxFile, $pathDropbox . $localFileName, ['autorename' => true]);
} catch (Exception $e) {
	http_response_code(400);
	$data = [
		'error' => [
			'message' => 'Erro ao tentar fazer o upload do arquivo.',
			'internal_message' => $e->getMessage(),
		]
	];
	$json = json_encode($data);

	echo $json;
	return $json;
}

// Creating shared link to recet uploaded file
try {
	$pathToDpxFile = $fileUploaded->getPathDisplay();
	$response = $dropbox->postToAPI("/sharing/create_shared_link_with_settings", [
	    "path" => $pathToDpxFile
	]);
	$data = $response->getDecodedBody();

	$json = json_encode($data);
	echo $json;
	return $json;

} catch (DropboxClientException $DpxCliexp) {
	$ret = json_decode($DpxCliexp->getMessage());
	$key = ".tag";
	if (isset($ret->error->{$key}) && $ret->error->{$key} == 'shared_link_already_exists') {
		$json = json_encode($ret->error->shared_link_already_exists->metadata);
		echo $json;
		return $json;
	}

	http_response_code(400);
	$data = [
		'error' => [
			'message' => 'Erro ao tentar gerar o link compartilhado.',
			'internal_message' => $DpxCliexp->getMessage(),
		]
	];
	$json = json_encode($data);

	echo $json;
	return $json;
}
