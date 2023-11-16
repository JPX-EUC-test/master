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

# show persons list
$que = pg_query_params($db, 'SELECT * FROM persons WHERE base = (SELECT max(base) FROM persons WHERE base <= $1) ORDER BY in_charge, market_group, code_from, code_to', array($base));

echo '<h1>編成表マスタ</h1>';

echo '<a href="./persons.php' . '?base=' . date('Y-m-d', ($_GET['base'] ? strtotime($_GET['base']) : time()) - 86400) . '">前日</a>&nbsp;&nbsp;';

echo '<a href="./persons.php' . '">最新</a>&nbsp;&nbsp;';

echo '<a href="./persons.php' . '?base=' . date('Y-m-d', ($_GET['base'] ? strtotime($_GET['base']) : time()) + 86400) . '">翌日</a><br /><br />';

echo '<a href="./persons_export.php' . ($_GET['base'] ? '?base=' . $_GET['base'] : NULL) . '">ダウンロード</a><br /><br />';

echo '<a href="./' . '">戻る</a><br /><br />';

echo '<table border="1">';
echo '<tr><td>';
echo '基準日';
echo '</td><td>';
echo '区分';
echo '</td><td>';
echo '市場グループ';
echo '</td><td>';
echo '開始コード';
echo '</td><td>';
echo '終端コード';
echo '</td><td>';
echo '担当者';
echo '</td></tr>';
while($res = pg_fetch_object($que))
{
  echo '<tr><td>';
  echo $res->base;
  echo '</td><td>';
  echo $res->in_charge;
  echo '</td><td>';
  echo $res->market_group;
  echo '</td><td>';
  echo $res->code_from;
  echo '</td><td>';
  echo $res->code_to;
  echo '</td><td>';
  echo $res->person;
  echo '</td></tr>';
}
echo '</table>';
