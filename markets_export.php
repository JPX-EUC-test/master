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

$content = '';

# show markets list
$que = pg_query_params($db, 'SELECT * FROM markets WHERE base = (SELECT max(base) FROM markets WHERE base <= $1) ORDER BY market, code_from, code_to', array($base));

$content .= '基準日';
$content .= ',';
$content .= '市場区分';
$content .= ',';
$content .= '開始コード';
$content .= ',';
$content .= '終端コード';
$content .= ',';
$content .= '市場グループ';
$content .= "\r\n";
while($res = pg_fetch_object($que))
{
  $content .= $res->base;
  $content .= ',';
  $content .= $res->market;
  $content .= ',';
  $content .= $res->code_from;
  $content .= ',';
  $content .= $res->code_to;
  $content .= ',';
  $content .= $res->market_group;
  $content .= "\r\n";
}

$content = mb_convert_encoding($content, 'SJIS', 'UTF-8');

header('Content-Type: text/csv; charset=Shift-JIS');
header('Content-Length: ' . strlen($content));
header('Content-Disposition: attachment; filename="markets_' . str_replace('-', '', $base) . '.csv"');
echo $content;
