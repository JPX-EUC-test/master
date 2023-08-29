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
	if(!($_SERVER['PHP_AUTH_USER'] === 'baishin' && $_SERVER['PHP_AUTH_PW'] === 'password'))
	{
		http_response_code(401);
		header('WWW-Authenticate: Basic realm="Baishin Server"');
		
		echo '<h1>認証できません</h1><p>閲覧にはユーザー認証が必要です。</p>';
		
		return;
	}
}

# save hash file
if(filter_input(INPUT_POST, 'password'))
{
  $password_hash = password_hash(filter_input(INPUT_POST, 'password'), PASSWORD_DEFAULT);
  
  file_put_contents(__DIR__ . '/password_hash.json', json_encode($password_hash));
}

echo '<h1>パスワード変更</h1>';

if(filter_input(INPUT_POST, 'password'))
{
  echo '<p>パスワードを変更しました。</p>';
}

echo '<form method="POST">';
echo '<input type="password" name="password" />';
echo '<button type="submit">設定</button>';
echo '</form>';

echo '<a href="./' . '">戻る</a><br /><br />';
