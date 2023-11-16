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
$filename = $_FILES['file']['tmp_name'];

# import securities
if($filename)
{
	if(($handle = fopen($filename, 'r')) !== FALSE)
	{
		$row = 0;
		$buffer = [];
		$flag = [];
		
		while(($data = fgetcsv($handle)) !== FALSE)
		{
			if(count($data) === 16)
			{
				$base = mb_convert_encoding($data[2], 'UTF-8', 'SJIS');
				$code = mb_convert_encoding($data[1], 'UTF-8', 'SJIS');
				$market_name = mb_convert_encoding($data[4], 'UTF-8', 'SJIS');
				$company_name = mb_convert_encoding($data[5], 'UTF-8', 'SJIS');
				
				if(preg_match('/^[0-9A-Z]{4,9}$/i', $code))
				{
					$buffer[] = [$base, $code, $market_name, $company_name];
					
					if(!$flag[$base])
					{
						$que = pg_query_params($db, 'DELETE FROM securities WHERE base = $1', array($base));
						
						$flag[$base] = TRUE;
					}
				}
			}
		}
		
		foreach($buffer as $data)
		{
			$que = pg_query_params($db, 'INSERT INTO securities(base, code, market_name, company_name) VALUES ($1, $2, $3, $4)', $data);
			
			if(pg_last_error())
			{
				echo $data[3] . '(' . $data[1] . '): ' . pg_last_error() . '<br />';
			}
			else
			{
				$row++;
			}
		}
		
		fclose($handle);
	}
}

echo '<h1>銘柄レコード取込</h1>';

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

$buffer = NULL;

$que = pg_query_params($db, 'SELECT base, count(*) FROM securities GROUP BY base ORDER BY base DESC LIMIT 10', array());

echo '<table border="1">';
echo '<tr><td>';
echo '基準日';
echo '</td><td>';
echo '銘柄レコード件数';
echo '</td><td>';
echo '担当者一覧表';
echo '</td><td>';
echo '担当者レンジ';
echo '</td><td>';
echo '担当者差分表(前日比較)';
echo '</td></tr>';
while($res = pg_fetch_object($que))
{
	if($buffer)
	{
		echo '<a href="./difference.php?base=' . $buffer . '&refer=' . $res->base . '">担当者差分表(前日比較)</a>';
		echo '</td></tr>';
	}
	
	echo '<tr><td>';
	echo $res->base;
	echo '</td><td>';
	echo '<a href="./securities.php?base=' . $res->base . '">' . $res->count . '</a>';
	echo '</td><td>';
	echo '<a href="./master.php?base=' . $res->base . '">担当者一覧表</a>';
	echo '</td><td>';
	echo '<a href="./management.php?base=' . $res->base . '">担当者レンジ</a>';
	echo '</td><td>';
	$buffer = $res->base;
}
if($buffer)
{
	echo '</td></tr>';
}
echo '</table>';
