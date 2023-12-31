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

# show companies list
$que = pg_query_params($db, 'SELECT base, substr(code, 1, 4) AS code, market_name, company_name FROM securities WHERE base = (SELECT max(base) FROM securities WHERE base <= $1) AND code LIKE $2 ORDER BY code', array($base, '____0'));

$buffer = array();

while($res = pg_fetch_object($que))
{
  $buffer[$res->code] = array();
  
  $buffer[$res->code]['market'] = $res->market_name;
  $buffer[$res->code]['name'] = $res->company_name;
}

# show markets list
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

# show members list
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

# show persons list
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

$content = '';

$content .= '基準日';
$content .= ',';
$content .= 'コード';
$content .= ',';
$content .= '市場グループ';
$content .= ',';
$content .= '銘柄名';
$content .= ',';
$content .= '内部者担当/担当者コード';
$content .= ',';
$content .= '内部者担当/担当者名';
$content .= ',';
$content .= '内部者担当/上長コード';
$content .= ',';
$content .= '内部者担当/上長名';
$content .= ',';
$content .= '株価担当/担当者コード';
$content .= ',';
$content .= '株価担当/担当者名';
$content .= ',';
$content .= '株価担当/上長コード';
$content .= ',';
$content .= '株価担当/上長名';
$content .= "\r\n";
$market_group = NULL;
foreach($buffer as $code => $data)
{
  $content .= $base;
  $content .= ',';
  $content .= $code;
  $content .= ',';
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
  $content .= $market_group;
  $content .= ',';
  $content .= $data['name'];
  $content .= ',';
  foreach($master[$market_group]['内部者担当'] as $code_from => $master_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $master_data['code_to']))
      {
        $content .= $members[$master_data['person_name']]['member_code'];
      }
    }
  }
  $content .= ',';
  foreach($master[$market_group]['内部者担当'] as $code_from => $master_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $master_data['code_to']))
      {
        $content .= $master_data['person_name'];
      }
    }
  }
  $content .= ',';
  foreach($master[$market_group]['内部者担当'] as $code_from => $master_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $master_data['code_to']))
      {
        $content .= $members[$master_data['person_name']]['manager_code'];
      }
    }
  }
  $content .= ',';
  foreach($master[$market_group]['内部者担当'] as $code_from => $master_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $master_data['code_to']))
      {
        $content .= $members[$master_data['person_name']]['manager_name'];
      }
    }
  }
  $content .= ',';
  foreach($master[$market_group]['株価担当'] as $code_from => $master_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $master_data['code_to']))
      {
        $content .= $members[$master_data['person_name']]['member_code'];
      }
    }
  }
  $content .= ',';
  foreach($master[$market_group]['株価担当'] as $code_from => $master_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $master_data['code_to']))
      {
        $content .= $master_data['person_name'];
      }
    }
  }
  $content .= ',';
  foreach($master[$market_group]['株価担当'] as $code_from => $master_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $master_data['code_to']))
      {
        $content .= $members[$master_data['person_name']]['manager_code'];
      }
    }
  }
  $content .= ',';
  foreach($master[$market_group]['株価担当'] as $code_from => $master_data)
  {
    if(0 >= strcmp($code_from, $code))
    {
      if(0 >= strcmp($code, $master_data['code_to']))
      {
        $content .= $members[$master_data['person_name']]['manager_name'];
      }
    }
  }
  $content .= "\r\n";
}

$content = mb_convert_encoding($content, 'SJIS', 'UTF-8');

header('Content-Type: text/csv; charset=Shift-JIS');
header('Content-Length: ' . strlen($content));
header('Content-Disposition: attachment; filename="master_' . str_replace('-', '', $base) . '.csv"');
echo $content;
