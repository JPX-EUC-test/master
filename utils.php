<?php
#証券コードの末尾を1減算する
function endcode_subtraction($code)
{
  #末尾が英文字でも減算したいため、36→10進変換で減算を行い、10→36進数に再変換した値を返す
  $end_code = base_convert($code, 36, 10);
  $end_code = $end_code - 1;
  $ret = base_convert($end_code, 10 , 36);

  #36→10進変換で英文字が小文字になっているため、返却時に大文字で返す
  return strtoupper($ret);
}
?>
