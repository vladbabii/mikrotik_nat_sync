<?php
require_once('routeros_api.class.php');
$API = new RouterosAPI();
if($DEBUG===true){
   echo 'Debug: RouterOS API Error reporting enabled...',PHP_EOL;
   $API->debug = true;
}

function wLogin(){
   global $API;
   if($API->connected===false){
      echo "Trying to login...",PHP_EOL;
      global $HOST;
      global $USER;
      global $PASS;
      if ($API->connect($HOST, $USER, $PASS)) {
         echo "Connected to $HOST",PHP_EOL;
      }
   }
} 

function wGetNatList(){
   global $API;
   global $TAG;
   global $DEBUG;
   wLogin();
   if($API->connected===false) { return null; }
   $rules=$API->comm('/ip/firewall/nat/print');
   if(count($rules)==0) { return $rules; }
   $list=[];
   $taglen=strlen($TAG);
   foreach($rules as $rule){
      if(substr($rule['comment'],0,$taglen)==$TAG){
         $rule['_']=@json_decode(substr($rule['comment'],$taglen),true);
         if(isset($rule['_']['source']) && isset($rule['_']['id'])){
            if(!isset($list[$rule['_']['source']])){
               $list[$rule['_']['source']]=[];
            }
            unset($rule['bytes'], $rule['packets'], $rule['invalid'],$rule['dynamic']);
            $list[$rule['_']['source']][ $rule['_']['id'] ]=$rule;
         }
      }
   }
   /*var_dump($list); exit(1);*/
   if($DEBUG){
      echo 'Nat list: ',json_encode($list),PHP_EOL;
   }
   return $list;
}

function WAddNatRule($rule){
   global $API;
   echo 'Adding rule: ',json_encode($rule),PHP_EOL;
   $response = $API->comm('/ip/firewall/nat/add', $rule);
   echo 'Response to adding rule: ',json_encode($response),PHP_EOL;
   return $response;
}

function wRemoveNatRule($id){
   global $API;
   echo 'Removing rule: ',$id,PHP_EOL;
   $response = $API->comm('/ip/firewall/nat/remove', ['numbers'=>$id]);
   echo 'Response to removing rule: ',json_encode($response),PHP_EOL;
   return $response;
}

function wUpdateNatRule($rule,$id){
   global $API;
   $rulex=$rule;
   $rulex['.id']=$id;
   echo 'Updating rule: ',json_encode($rulex),PHP_EOL;
   $response = $API->comm('/ip/firewall/nat/set', $rulex);
   echo 'Response to updating rule: ',json_encode($response),PHP_EOL;
   return $response;
}

function wCompareArrays($array1, $array2) {
   $keys = array_merge(array_keys($array1), array_keys($array2));
   $result = [];
   foreach ($keys as $key) {
     if (!array_key_exists($key, $array1) || !array_key_exists($key, $array2) || $array1[$key] !== $array2[$key]) {
       $result[$key] = true;
     }
   }
   return $result;
 }

function wDisconnectShutdown(){
   echo 'Shutdown detected...',PHP_EOL;
   wDisconnect();
}

function wDisconnect(){
   global $API;
   if($API->connected===true){
      $API->disconnect();
      echo "Disconnected",PHP_EOL;
   }
}

register_shutdown_function('wDisconnectShutdown');
