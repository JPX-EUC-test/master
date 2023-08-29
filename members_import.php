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
$filename = $_FILES['file']['tmp_name'];

# import members
if($filename)
{
	if(($handle = fopen($filename, 'r')) !== FALSE)
	{
		$row = 0;
		$buffer = [];
		$flag = [];
		
		while(($data = fgetcsv($handle)) !== FALSE)
		{
			if(count($data) === 5)
			{
				$base = mb_convert_encoding($data[0], 'UTF-8', 'SJIS');
				$member_code = mb_convert_encoding($data[1], 'UTF-8', 'SJIS');
				$member_name = mb_convert_encoding($data[2], 'UTF-8', 'SJIS');
				$manager_code = mb_convert_encoding($data[3], 'UTF-8', 'SJIS');
				$manager_name = mb_convert_encoding($data[4], 'UTF-8', 'SJIS');
				
				if(preg_match('/^[0-9A-Z]{4,5}$/i', $member_code))
				{
					$buffer[] = [$base, $member_code, $member_name, $manager_code, $manager_name];
					
					if(!$flag[$base])
					{
						$que = pg_query_params($db, 'DELETE FROM members WHERE base = $1', array($base));
						
						$flag[$base] = TRUE;
					}
				}
			}
		}
		
		foreach($buffer as $data)
		{
			$que = pg_query_params($db, 'INSERT INTO members (base, member_code, member_name, manager_code, manager_name) VALUES ($1, $2, $3, $4, $5)', $data);
			
			if(pg_last_error())
			{
				echo $member_code . ': ' . $member_name . '(' . $manager_code . ': ' . $manager_name . '): ' . pg_last_error() . '<br />';
			}
			else
			{
				$row++;
			}
		}
		
		fclose($handle);
	}
}

echo '<h1>部員マスタ取込</h1>';

echo '<a href="./' . '">戻る</a><br /><br />';

# show result
if($row !== NULL)
{
	echo '<p>' . $row . '件のレコードを取り込みました。' . '</p>';
}

echo '<form method="POST" enctype="multipart/form-data">';
echo '<input type="file" name="file" />';
echo '<button type="submit">取込</button>';
echo '</form>';

$que = pg_query_params($db, 'SELECT base, count(*) FROM members GROUP BY base ORDER BY base DESC LIMIT 10', array());

echo '<table border="1">';
echo '<tr><td>';
echo '基準日';
echo '</td><td>';
echo '部員マスタ件数';
echo '</td></tr>';
while($res = pg_fetch_object($que))
{
	echo '<tr><td>';
	echo $res->base;
	echo '</td><td>';
	echo '<a href="./members.php?base=' . $res->base . '">' . $res->count . '</a>';
	echo '</td></tr>';
}
echo '</table>';
