<?php

namespace App\Controllers;

class ApiController extends \Compago\Controller
{
    
    public function __invoke ($REQ,$RES,$ROUTE){
        $x = func_get_args();
        unset($x[0],$x[1],$x[2]);
        $matched_data = $ROUTE->getMatchedData();
        return ['status'=>404,'message'=>'not found', 'matched_data'=>$matched_data];
    }
    
    private function invalidToken($data){
        $P = new \Compago\Tools\CastablePropertyBag($data);
        if (!$P->token){
            return ['status'=>401,'message'=>'token required'];
        }
        
        
        return false;
    }
    public function listUsers ($REQ,$RES,$ROUTE){
        if ($ret = self::invalidToken($_GET)){
            return $ret;
        }
        $data = array_merge($_GET, $ROUTE->getMatchedData());
        
        
        $items = [];
        
        
        return ['status'=>200,'items'=>$items];
    }
    
    public function viewUser ($REQ,$RES,$ROUTE){
        if ($ret = self::invalidToken($_GET)){
            return $ret;
        }
        $data = array_merge($_GET, $ROUTE->getMatchedData());
        
        $items = [];
        $items[] = ['username'=>'name'];
    
        return ['status'=>200,'items'=>$items];
    }
    
    
    public function testAuth ($REQ,$RES,$ROUTE){
        $P = new \Compago\Tools\CastablePropertyBag($_GET);
        $d = [];
        if ($P->username){
            $d['username'] = $P->username;
        }
        $d['ts'] = time();
        $d['app_key'] = md5('ZYROGO');
        ksort($d);
        $q = http_build_query($d);
        return ['payload'=>base64_encode($q)];        
    }    
    
    
}
