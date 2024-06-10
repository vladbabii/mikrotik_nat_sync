<?php
$HOST='127.0.0.1';
$USER='admin'; 
$PASS='adminadmin'; 
$TAG='automanaged';
$SOURCE='./files/list.json';
$DEBUG='';
$DELAY=5;

if(isset($_ENV['HOST'])){ $HOST=$_ENV['HOST']; }
if(isset($_ENV['USER'])){ $USER=$_ENV['USER']; }
if(isset($_ENV['PASS'])){ $PASS=$_ENV['PASS']; }
if(isset($_ENV['TAG' ])){ $TAG=$_ENV['TAG']; }
if(isset($_ENV['DEBUG'])){ $DEBUG=$_ENV['DEBUG']; }

if(isset($_SERVER['HOST'])){ $HOST=$_SERVER['HOST']; }
if(isset($_SERVER['USER'])){ $USER=$_SERVER['USER']; }
if(isset($_SERVER['PASS'])){ $PASS=$_SERVER['PASS']; }
if(isset($_SERVER['TAG' ])){ $TAG=$_SERVER['TAG']; }
if(isset($_SERVER['DEBUG'])){ $DEBUG=$_SERVER['DEBUG']; }

if(strlen($DEBUG)>0){
  $DEBUG=true;
  echo 'Debug: TRUE',PHP_EOL;
}else{
  $DEBUG=false;
  echo 'Debug: FALSE',PHP_EOL;
}

if($DEBUG===true){
  echo 'Debug: PHP Error reporting enabled...',PHP_EOL;
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
}

echo "Using HOST=$HOST, USER=$USER, PASS=";
if($DEBUG){
  echo $PASS;
}else{
  echo str_repeat('*',13);
}
echo ", TAG=$TAG",PHP_EOL;

require_once('wrapper.php');
wLogin();
if($API->connected===false){
  echo 'Failed to login...',PHP_EOL;
  exit(1);
}
$current=wGetNatList();

if( 
  !isset($current['template']) 
  || !isset($current['template']['template'])
){
  echo 'Template not found...',PHP_EOL;
  exit(1);
}

while(1){
  if(substr($SOURCE,0,4)=='http'){
    echo 'Loading list from URL... ',$SOURCE,PHP_EOL;
    try{
      $data=@file_get_contents($SOURCE);
      if($data===false){
        echo 'Failed to load list...',PHP_EOL;
        $list=null;
      }else{
        $list=@json_decode($data,true);
      }
    }catch(Exception $e){
      echo 'Failed to load list...',PHP_EOL;
      var_dump($e);
      $list=null;
    }
  }else{
    echo 'Reading list from file... ',$SOURCE,PHP_EOL;
    try{
      $data=@file_get_contents($SOURCE);
      if($data===false){
        echo 'Failed to load list...',PHP_EOL;
        $list=null;
      }else{
        $list=@json_decode($data,true);
      }
    }catch(Exception $e){
      echo 'Failed to load list...',PHP_EOL;
      var_dump($e);
      $list=null;
    }
  }

  if(is_array($list) ){
    echo 'Refereshing current...',PHP_EOL;
    $current=wGetNatList();
    echo '... current refreshed!'.PHP_EOL;

    echo 'List loaded...',json_encode($list),PHP_EOL;
    foreach($list as $source=>$rules){
      foreach($rules as $id=>$rule){
        $original=$rule;
        $rule=$current['template']['template'];
        unset($rule['.id'],$rule['_']);
        $rule['comment']=$TAG.json_encode(['source'=>$source,'id'=>$id]);
        $rule['disabled']="false";
        foreach($original as $key=>$value){
          if($key!=='.id' && $key!=='_'){
            $rule[$key]=$value;
          }
        }
        $list[$source][$id]=$rule;
      }
    }
    
    echo 'List prepared...',json_encode($list),PHP_EOL;
    echo 'Generating action list...'.PHP_EOL;
    $actions = [];

    foreach ($list as $source => $rules) {
      foreach ($rules as $id => $rule) {
        if (isset($current[$source][$id])) {
          // Rule exists in current, check for updates
          $fields = wCompareArrays($rule, $current[$source][$id]);
          if(isset($current[$source][$id]['.id'])){
            $lastid=$current[$source][$id]['.id'];
          }
          unset($fields['.id'],$fields['_']);
          if (count($fields)>0) {
            $actions[] = ['action' => 'update', 'source' => $source, 'id' => $id, '.id'=>$lastid, 'rule' => $rule, 'reason'=>'fields: '.implode(',',array_keys($fields))];
          }
        } else {
          // Rule does not exist in current, add it
          $actions[] = ['action' => 'add', 'source' => $source, 'id' => $id, 'rule' => $rule, 'reason'=>'does not exist in current'];
        }
      }
    }

    foreach ($current as $source => $rules) {
      foreach ($rules as $id => $rule) {
      if ( ($source!=='template' && $id!=='template') && !isset($list[$source][$id])) {
        // Rule exists in current but not in list, remove it
        $actions[] = ['action' => 'remove', 'source' => $source, 'id' => $id, 'rule' => $rule, 'reason'=>'exists in current but not in list'];
      }
      }
    }

    if(count($actions)>0){
      echo 'Actions (',count($actions),'): ',json_encode($actions),PHP_EOL;
      foreach($actions as $action){
        echo 'Action: ',$action['action'],' Source: ',$action['source'],' ID: ',$action['id'],' Reason: ',$action['reason'],PHP_EOL;
      }
    

      // Perform actions based on the list
      foreach ($actions as $action) {
        switch ($action['action']) {
        case 'add':
          $response = wAddNatRule($action['rule']);
          break;
        case 'remove':
          // Perform remove action
          $response = wRemoveNatRule($action['rule']['.id']);
          break;
        case 'update':
          // Perform update action
          //var_dump($action); exit(1);
          $response = wUpdateNatRule($action['rule'],$action['.id']);
          break;
        }
      }

    }else{
      echo 'No actions to perform...',PHP_EOL;
    }
  }else{
    echo 'List is wrong format...',PHP_EOL;
  }
  sleep($DELAY);
}

