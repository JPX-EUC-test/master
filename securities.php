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

# show securities list
$que = pg_query_params($db, 'SELECT * FROM securities WHERE base = (SELECT max(base) FROM securities WHERE base <= $1) ORDER BY code', array($base));

echo '<h1>銘柄レコード(全区分)</h1>';

echo '<a href="./securities.php' . '?base=' . date('Y-m-d', ($_GET['base'] ? strtotime($_GET['base']) : time()) - 86400) . '">前日</a>&nbsp;&nbsp;';

echo '<a href="./securities.php' . '">最新</a>&nbsp;&nbsp;';

echo '<a href="./securities.php' . '?base=' . date('Y-m-d', ($_GET['base'] ? strtotime($_GET['base']) : time()) + 86400) . '">翌日</a><br /><br />';

echo '<a href="./securities_export.php' . ($_GET['base'] ? '?base=' . $_GET['base'] : NULL) . '">ダウンロード</a><br /><br />';

echo '<a href="./' . '">戻る</a><br /><br />';

echo '<table border="1">';
echo '<tr><td>';
echo '基準日';
echo '</td><td>';
echo 'コード';
echo '</td><td>';
echo '市場区分';
echo '</td><td>';
echo '銘柄名';
echo '</td></tr>';
while($res = pg_fetch_object($que))
{
  echo '<tr><td>';
  echo $res->base;
  echo '</td><td>';
  echo $res->code;
  echo '</td><td>';
  echo $res->market_name;
  echo '</td><td>';
  echo $res->company_name;
  echo '</td></tr>';
}
echo '</table>';
