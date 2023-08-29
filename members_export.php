<?php
# database connection
$db = pg_connect('user=baishin password=baishin3 dbname=master');

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

$content = '';

# show members list
$que = pg_query_params($db, 'SELECT * FROM members WHERE base = (SELECT max(base) FROM members WHERE base <= $1) ORDER BY member_code', array($base));

$content .= '基準日';
$content .= ',';
$content .= '担当者コード';
$content .= ',';
$content .= '担当者名';
$content .= ',';
$content .= '上長コード';
$content .= ',';
$content .= '上長名';
$content .= "\r\n";
while($res = pg_fetch_object($que))
{
  $content .= $res->base;
  $content .= ',';
  $content .= $res->member_code;
  $content .= ',';
  $content .= $res->member_name;
  $content .= ',';
  $content .= $res->manager_code;
  $content .= ',';
  $content .= $res->manager_name;
  $content .= "\r\n";
}

$content = mb_convert_encoding($content, 'SJIS', 'UTF-8');

header('Content-Type: text/csv; charset=Shift-JIS');
header('Content-Length: ' . strlen($content));
header('Content-Disposition: attachment; filename="members_' . str_replace('-', '', $base) . '.csv"');
echo $content;
