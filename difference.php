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

$refer = $_GET['refer'];

if($refer === NULL)
{
  $refer = date('Y-m-d');
}

# show companies list (base)
$que = pg_query_params($db, 'SELECT base, substr(code, 1, 4) AS code, market_name, company_name FROM securities WHERE base = (SELECT max(base) FROM securities WHERE base <= $1) AND code LIKE $2 ORDER BY code', array($base, '____0'));

$buffer = array();

while($res = pg_fetch_object($que))
{
  $buffer[$res->code] = array();
  
  $buffer[$res->code]['market'] = $res->market_name;
  $buffer[$res->code]['name'] = $res->company_name;
}

# show companies list (refer)
$que = pg_query_params($db, 'SELECT base, substr(code, 1, 4) AS code, market_name, company_name FROM securities WHERE base = (SELECT max(base) FROM securities WHERE base <= $1) AND code LIKE $2 ORDER BY code', array($refer, '____0'));

$buffer2 = array();

while($res = pg_fetch_object($que))
{
  $buffer2[$res->code] = array();
  
  $buffer2[$res->code]['market'] = $res->market_name;
  $buffer2[$res->code]['name'] = $res->company_name;
}

# show markets list (base)
$que = pg_query_params($db, 'SELECT * FROM markets WHERE base = (SELECT max(base) FROM markets WHERE base <= $1) ORDER BY code_from, code_to', array($base));

$markets = array();

while($res = pg_fetch_object($que))
{
  if(!is_array($markets[$res->market]))
  {
    $markets[$res->market] = array();
  }
  
  $markets[$res->market][$res->code_from] = array();
  
  $markets[$res->market][$res->code_from]['code_to'] = $res->code_to;
  $markets[$res->market][$res->code_from]['market_group'] = $res->market_group;
}

# show markets list (refer)
$que = pg_query_params($db, 'SELECT * FROM markets WHERE base = (SELECT max(base) FROM markets WHERE base <= $1) ORDER BY code_from, code_to', array($refer));

$markets2 = array();

while($res = pg_fetch_object($que))
{
  if(!is_array($markets2[$res->market]))
  {
    $markets2[$res->market] = array();
  }
  
  $markets2[$res->market][$res->code_from] = array();
  
  $markets2[$res->market][$res->code_from]['code_to'] = $res->code_to;
  $markets2[$res->market][$res->code_from]['market_group'] = $res->market_group;
}

# show members list (base)
$que = pg_query_params($db, 'SELECT * FROM members WHERE base = (SELECT max(base) FROM members WHERE base <= $1) ORDER BY member_code', array($base));

$members = array();

while($res = pg_fetch_object($que))
{
  if(!is_array($members[$res->member_code]))
  {
    $members[$res->member_code] = array();
  }
  
  if(!is_array($members[$res->member_name]))
  {
    $members[$res->member_name] = array();
  }
	
  $members[$res->member_code]['member_code'] = $res->member_code;
  $members[$res->member_code]['member_name'] = $res->member_name;
  $members[$res->member_code]['manager_code'] = $res->manager_code;
  $members[$res->member_code]['manager_name'] = $res->manager_name;
	
  $members[$res->member_name]['member_code'] = $res->member_code;
  $members[$res->member_name]['member_name'] = $res->member_name;
  $members[$res->member_name]['manager_code'] = $res->manager_code;
  $members[$res->member_name]['manager_name'] = $res->manager_name;
}

# show members list (refer)
$que = pg_query_params($db, 'SELECT * FROM members WHERE base = (SELECT max(base) FROM members WHERE base <= $1) ORDER BY member_code', array($refer));

$members2 = array();

while($res = pg_fetch_object($que))
{
  if(!is_array($members2[$res->member_code]))
  {
    $members2[$res->member_code] = array();
  }
  
  if(!is_array($members2[$res->member_name]))
  {
    $members2[$res->member_name] = array();
  }
	
  $members2[$res->member_code]['member_code'] = $res->member_code;
  $members2[$res->member_code]['member_name'] = $res->member_name;
  $members2[$res->member_code]['manager_code'] = $res->manager_code;
  $members2[$res->member_code]['manager_name'] = $res->manager_name;
	
  $members2[$res->member_name]['member_code'] = $res->member_code;
  $members2[$res->member_name]['member_name'] = $res->member_name;
  $members2[$res->member_name]['manager_code'] = $res->manager_code;
  $members2[$res->member_name]['manager_name'] = $res->manager_name;
}

# show persons list (base)
$que = pg_query_params($db, 'SELECT * FROM persons WHERE base = (SELECT max(base) FROM persons WHERE base <= $1) ORDER BY code_from, code_to', array($base));

$master = array();

while($res = pg_fetch_object($que))
{
  if(!is_array($master[$res->market_group]))
  {
    $master[$res->market_group] = array();
  }
  
  if(!is_array($master[$res->market_group][$res->in_charge]))
  {
    $master[$res->market_group][$res->in_charge] = array();
  }
  
  $master[$res->market_group][$res->in_charge][$res->code_from] = array();
  
  $master[$res->market_group][$res->in_charge][$res->code_from]['code_to'] = $res->code_to;
  $master[$res->market_group][$res->in_charge][$res->code_from]['person_name'] = $res->person;
}

# show persons list (refer)
$que = pg_query_params($db, 'SELECT * FROM persons WHERE base = (SELECT max(base) FROM persons WHERE base <= $1) ORDER BY code_from, code_to', array($refer));

$master2 = array();

while($res = pg_fetch_object($que))
{
  if(!is_array($master2[$res->market_group]))
  {
    $master2[$res->market_group] = array();
  }
  
  if(!is_array($master2[$res->market_group][$res->in_charge]))
  {
    $master2[$res->market_group][$res->in_charge] = array();
  }
  
  $master2[$res->market_group][$res->in_charge][$res->code_from] = array();
  
  $master2[$res->market_group][$res->in_charge][$res->code_from]['code_to'] = $res->code_to;
  $master2[$res->market_group][$res->in_charge][$res->code_from]['person_name'] = $res->person;
}

echo '<h1>担当者差分表</h1>';

echo '基準日:&nbsp;&nbsp;';

echo '<a href="./difference.php' . '?base=' . date('Y-m-d', ($_GET['base'] ? strtotime($_GET['base']) : time()) - 86400) . ($_GET['refer'] ? '&refer=' . $_GET['refer'] : NULL) . '">前日</a>&nbsp;&nbsp;';

echo '<a href="./difference.php' . ($_GET['refer'] ? '?refer=' . $_GET['refer'] : NULL) . '">最新</a>&nbsp;&nbsp;';

echo '<a href="./difference.php' . '?base=' . date('Y-m-d', ($_GET['base'] ? strtotime($_GET['base']) : time()) + 86400) . ($_GET['refer'] ? '&refer=' . $_GET['refer'] : NULL) . '">翌日</a><br /><br />';

echo '参照日:&nbsp;&nbsp;';

echo '<a href="./difference.php' . '?' . ($_GET['base'] ? 'base=' . $_GET['base'] . '&' : NULL) . 'refer=' . date('Y-m-d', ($_GET['refer'] ? strtotime($_GET['refer']) : time()) - 86400) . '">前日</a>&nbsp;&nbsp;';

echo '<a href="./difference.php' . ($_GET['base'] ? '?base=' . $_GET['base'] : NULL) . '">最新</a>&nbsp;&nbsp;';

echo '<a href="./difference.php' . '?' . ($_GET['base'] ? 'base=' . $_GET['base'] . '&' : NULL) . 'refer=' . date('Y-m-d', ($_GET['refer'] ? strtotime($_GET['refer']) : time()) + 86400) . '">翌日</a><br /><br />';

echo '<a href="./difference_export.php' . ($_GET['base'] || $_GET['refer'] ? '?' : NULL) . ($_GET['base'] ? 'base=' . $_GET['base'] : NULL) . ($_GET['base'] && $_GET['refer'] ? '&' : NULL) . ($_GET['refer'] ? 'refer=' . $_GET['refer'] : NULL) . '">ダウンロード</a><br /><br />';

echo '<a href="./' . '">戻る</a><br /><br />';

$temp = [];
$temp2 = [];

// process (base)
$market_group = NULL;
foreach($buffer as $code => $data)
{
  foreach($markets[$data['market']] as $code_from => $market_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $market_data['code_to']))
      {
        $market_group = $market_data['market_group'];
      }
    }
  }
  foreach($master[$market_group]['内部者担当'] as $code_from => $master_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $master_data['code_to']))
      {
        $person_name1 = $master_data['person_name'];
      }
    }
  }
  foreach($master[$market_group]['株価担当'] as $code_from => $master_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $master_data['code_to']))
      {
        $person_name2 = $master_data['person_name'];
      }
    }
  }
  $temp[$code] = [$base, $code, $market_group, $data['name'], $members[$person_name1]['member_code'], $person_name1, $members[$person_name1]['manager_code'], $members[$person_name1]['manager_name'], $members[$person_name2]['member_code'], $person_name2, $members[$person_name2]['manager_code'], $members[$person_name2]['manager_name']];
}

// process (refer)
$market_group2 = NULL;
foreach($buffer2 as $code => $data)
{
  foreach($markets2[$data['market']] as $code_from => $market_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $market_data['code_to']))
      {
        $market_group2 = $market_data['market_group'];
      }
    }
  }
  foreach($master2[$market_group2]['内部者担当'] as $code_from => $master_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $master_data['code_to']))
      {
        $person_name1 = $master_data['person_name'];
      }
    }
  }
  foreach($master2[$market_group2]['株価担当'] as $code_from => $master_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $master_data['code_to']))
      {
        $person_name2 = $master_data['person_name'];
      }
    }
  }
  $temp2[$code] = [$refer, $code, $market_group2, $data['name'], $members2[$person_name1]['member_code'], $person_name1, $members2[$person_name1]['manager_code'], $members2[$person_name1]['manager_name'], $members2[$person_name2]['member_code'], $person_name2, $members2[$person_name2]['manager_code'], $members2[$person_name2]['manager_name']];
}

echo '<table border="1">';
echo '<tr><td rowspan="2">';
echo '基準日';
echo '</td><td rowspan="2">';
echo 'コード';
echo '</td><td rowspan="2">';
echo '市場グループ';
echo '</td><td rowspan="2">';
echo '銘柄名';
echo '</td><td colspan="4">';
echo '内部者担当';
echo '</td><td colspan="4">';
echo '株価担当';
echo '</td><td rowspan="2">';
echo '比較';
echo '</td><td rowspan="2">';
echo '参照日';
echo '</td><td rowspan="2">';
echo 'コード';
echo '</td><td rowspan="2">';
echo '市場グループ';
echo '</td><td rowspan="2">';
echo '銘柄名';
echo '</td><td colspan="4">';
echo '内部者担当';
echo '</td><td colspan="4">';
echo '株価担当';
echo '</td></tr>';
echo '<tr><td>';
echo '担当者コード';
echo '</td><td>';
echo '担当者名';
echo '</td><td>';
echo '上長コード';
echo '</td><td>';
echo '上長名';
echo '</td><td>';
echo '担当者コード';
echo '</td><td>';
echo '担当者名';
echo '</td><td>';
echo '上長コード';
echo '</td><td>';
echo '上長名';
echo '</td><td>';
echo '担当者コード';
echo '</td><td>';
echo '担当者名';
echo '</td><td>';
echo '上長コード';
echo '</td><td>';
echo '上長名';
echo '</td><td>';
echo '担当者コード';
echo '</td><td>';
echo '担当者名';
echo '</td><td>';
echo '上長コード';
echo '</td><td>';
echo '上長名';
echo '</td></tr>';

#基準日と参照日のキー(証券コード)からユニークキー配列を生成
#ユニークキーでループを行い、差分箇所についてテーブル形式で出力
$loop_key = [];
$loop_key = uniq_code($temp, $temp2);
foreach($loop_key as $i)
{
  if($temp[$i] !== NULL && $temp2[$i] !== NULL)
  {
    if($temp[$i][2] !== $temp2[$i][2] || $temp[$i][3] !== $temp2[$i][3] || $temp[$i][4] !== $temp2[$i][4] || $temp[$i][5] !== $temp2[$i][5] || $temp[$i][6] !== $temp2[$i][6] || $temp[$i][7] !== $temp2[$i][7] || $temp[$i][8] !== $temp2[$i][8] || $temp[$i][9] !== $temp2[$i][9] || $temp[$i][10] !== $temp2[$i][10] || $temp[$i][11] !== $temp2[$i][11])
    {
      echo '<tr><td>';
      echo $temp[$i][0];
      echo '</td><td>';
      echo $temp[$i][1];
      echo '</td><td>';
      echo $temp[$i][2];
      echo '</td><td>';
      echo $temp[$i][3];
      echo '</td><td>';
      echo $temp[$i][4];
      echo '</td><td>';
      echo $temp[$i][5];
      echo '</td><td>';
      echo $temp[$i][6];
      echo '</td><td>';
      echo $temp[$i][7];
      echo '</td><td>';
      echo $temp[$i][8];
      echo '</td><td>';
      echo $temp[$i][9];
      echo '</td><td>';
      echo $temp[$i][10];
      echo '</td><td>';
      echo $temp[$i][11];
      echo '</td><td>';
      echo '*';
      echo '</td><td>';
      echo $temp2[$i][0];
      echo '</td><td>';
      echo $temp2[$i][1];
      echo '</td><td>';
      echo $temp2[$i][2];
      echo '</td><td>';
      echo $temp2[$i][3];
      echo '</td><td>';
      echo $temp2[$i][4];
      echo '</td><td>';
      echo $temp2[$i][5];
      echo '</td><td>';
      echo $temp2[$i][6];
      echo '</td><td>';
      echo $temp2[$i][7];
      echo '</td><td>';
      echo $temp2[$i][8];
      echo '</td><td>';
      echo $temp2[$i][9];
      echo '</td><td>';
      echo $temp2[$i][10];
      echo '</td><td>';
      echo $temp2[$i][11];
      echo '</td></tr>';
    }
  }
  else if($temp[$i] !== NULL || $temp2[$i] !== NULL)
  {
    if($temp[$i] !== NULL)
    {
      echo '<tr><td>';
      echo $temp[$i][0];
      echo '</td><td>';
      echo $temp[$i][1];
      echo '</td><td>';
      echo $temp[$i][2];
      echo '</td><td>';
      echo $temp[$i][3];
      echo '</td><td>';
      echo $temp[$i][4];
      echo '</td><td>';
      echo $temp[$i][5];
      echo '</td><td>';
      echo $temp[$i][6];
      echo '</td><td>';
      echo $temp[$i][7];
      echo '</td><td>';
      echo $temp[$i][8];
      echo '</td><td>';
      echo $temp[$i][9];
      echo '</td><td>';
      echo $temp[$i][10];
      echo '</td><td>';
      echo $temp[$i][11];
      echo '</td><td>';
      echo '+';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td></tr>';
    }
    else
    {
      echo '<tr><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '</td><td>';
      echo '-';
      echo '</td><td>';
      echo $temp2[$i][0];
      echo '</td><td>';
      echo $temp2[$i][1];
      echo '</td><td>';
      echo $temp2[$i][2];
      echo '</td><td>';
      echo $temp2[$i][3];
      echo '</td><td>';
      echo $temp2[$i][4];
      echo '</td><td>';
      echo $temp2[$i][5];
      echo '</td><td>';
      echo $temp2[$i][6];
      echo '</td><td>';
      echo $temp2[$i][7];
      echo '</td><td>';
      echo $temp2[$i][8];
      echo '</td><td>';
      echo $temp2[$i][9];
      echo '</td><td>';
      echo $temp2[$i][10];
      echo '</td><td>';
      echo $temp2[$i][11];
      echo '</td></tr>';
    }
  }
}
echo '</table>';

function uniq_code($temp, $temp2)
{
  $loop_key = [];
  $dummy = array_keys($temp);
  $dummy2 = array_keys($temp2);
  #基準日のキーを取得
  for($i=0; $i < count($dummy); $i++)
  {
    array_push($loop_key, $dummy[$i]);
  }
  
  #基準日のキーに存在しない参照日のキーがある場合、キーを追加
  for($i=0; $i < count($dummy2); $i++)
  {
    if(false == in_array($dummy2[$i], $loop_key, true))
    {
      array_push($loop_key, $dummy2[$i]);
    }
  }
  
  #ソートした値を返却
  ksort($loop_key);
  return $loop_key;
}