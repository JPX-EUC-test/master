<?php
# database connection
$db = pg_connect('host=34.146.46.87 user=baishin password=baishin38697 dbname=master');

# authentication
$password_hash = json_decode(file_get_contents(__DIR__ . '/password_hash.json'), TRUE);

if(isset($password_hash))
{
	if(!($_SERVER['PHP_AUTH_USER'] === 'baishin' && password_verify($_SERVER['PHP_AUTH_PW'], $password_hash)))
	{
		http_response_code(401);
		header('WWW-Authenticate: Basic realm="Baishin Server"');
		
		echo '<h1>認証できません</h1><p>閲覧にはユーザー認証が必要です。</p>';
		
		return;
	}
}
else
{
	http_response_code(302);
	header('Location: ./config.php');
}

# configuration
$base = $_GET['base'];

if($base === NULL)
{
  $base = date('Y-m-d');
}

echo '<h1>担当者マスタ管理システム</h1>';

echo '<table border="1">';

echo '<tr><td colspan="2">担当者一覧表</td><td><a href="./master.php">確認</a></td><td></td></tr>';

echo '<tr><td colspan="2">担当者レンジ</td><td><a href="./management.php">確認</a></td><td></td></tr>';

echo '<tr><td colspan="2">担当者差分表</td><td><a href="./difference.php">確認</a></td><td></td></tr>';

echo '<tr><td colspan="2">編成表マスタ</td><td><a href="./persons.php">確認</a></td><td><a href="./persons_import.php">取込</a></td></tr>';

echo '<tr><td colspan="2">市場グループマスタ</td><td><a href="./markets.php">確認</a></td><td><a href="./markets_import.php">取込</a></td></tr>';

echo '<tr><td rowspan="2">銘柄レコード</td><td>全区分</td><td><a href="./securities.php">確認</a></td><td rowspan="2"><a href="./securities_import.php">取込</a></td></tr>';

echo '<tr><td>株式のみ</td><td><a href="./companies.php">確認</a></td></tr>';

echo '</table>';

echo '<br />';

echo '<table border="1">';

echo '<tr><td colspan="2">部員マスタ</td><td><a href="./members.php">確認</a></td><td><a href="./members_import.php">取込</a></td></tr>';

echo '<tr><td colspan="2">パスワード変更</td><td colspan="2"><a href="./config.php">変更</a></td></tr>';

echo '</table>';
