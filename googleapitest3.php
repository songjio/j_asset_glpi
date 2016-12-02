<?php
/*
 * Copyright 2011 Google Inc.
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*     http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/
require_once dirname(__FILE__).'/GoogleClientApi/Google_Client.php';
require_once dirname(__FILE__).'/GoogleClientApi/contrib/Google_Oauth2Service.php';

session_start();

$client = new Google_Client();
$client->setAccessType('online'); // default: offline
$client->setApplicationName('glpi-150907');
$client->setClientId('680873363959-i3rhet8s1psarm4nh1l95rnin0k7mt53.apps.googleusercontent.com');
$client->setClientSecret('MNxJxxwmiX8z4FAYY0-MvUOD');
$client->setRedirectUri('http://localhost/asset_glpi/googleapitest.php');
$client->setDeveloperKey('AIzaSyAwrWGvFnQyfxVqn34DXuUluMc1GDUjFpc'); // API key

$oauth2 = new Google_Oauth2Service($client);

if (isset($_GET['code'])) {
	$client->authenticate($_GET['code']);
	$_SESSION['token'] = $client->getAccessToken();
	$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
	return;
}
if (isset($_SESSION['token'])) {
	$client->setAccessToken($_SESSION['token']);
}
if (isset($_REQUEST['logout'])) {
	unset($_SESSION['token']);
	$client->revokeToken();
}
if ($client->getAccessToken()) {
	$user = $oauth2->userinfo->get();  // �������� ������. �Ʒ� 6���� ������ �׳� ���� ���ϰ� �����аŰ�, �� �ڼ��Ѱ� �����忡...;;
	$gguser_id            = $user['id'];
	$gguser_name          = filter_var($user['name'], FILTER_SANITIZE_SPECIAL_CHARS);
	$gguser_email         = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
	$profile_url          = filter_var($user['link'], FILTER_VALIDATE_URL);
	$profile_image_url    = filter_var($user['picture'], FILTER_VALIDATE_URL);
	$personMarkup         = "<div><img src='$profile_image_url?sz=50'></div>";
	// These fields are currently filtered through the PHP sanitize filters.
	// See http://www.php.net/manual/en/filter.filters.sanitize.php
	$email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
	$img = filter_var($user['picture'], FILTER_VALIDATE_URL);
	$personMarkup = "$email<div><img src='$img?sz=50'></div>";
	// The access token may have been updated lazily.
	$_SESSION['token'] = $client->getAccessToken();
} else {
	$authUrl = $client->createAuthUrl();
}
?>
<!doctype html>
<html>
<head>
 <meta charset="utf-8">
 <title>Google+ Login test</title> 
</head>
<body>
<header><h1>Google UserInfo Sample App</h1></header>
<?php if(isset($personMarkup)): ?>
<?php print $personMarkup ?>
<?php endif ?>
<?php
  if(isset($authUrl)) {
    print "<a class='login' href='$authUrl'>Connect Me!</a>";
  } else {
   print "<a class='logout' href='?logout'>Logout</a><br>";

 echo '<pre>';
 print_r($user);  // user�� �޾ƿ� ������ ���� �����.  id, email, verified_email, name, given_name, family_name, link, picture, gender, birthday, locale ���� �ִ�. ������ ��� $gguser_id = $user['id'] ó��, $user['id'], $user['email'], $user['name'] �̷������� �迭���� ���� �������� �ȴ�.
 echo '</pre>';
  }
?>
</body></html>