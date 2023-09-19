<?php
/**
 * @author Edwards
 * @copyright 2022
 * 
 *  based on: https://github.com/Voronenko/PHPOTP/tree/master/code
 * https://github.com/thepapanoob/PHPOTP/blob/master/PHPOTP.php 
 * https://github.com/sonata-project/GoogleAuthenticator/blob/2.x/src/GoogleAuthenticator.php
 * 
 * 	
	$secretkey = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
	$currentcode = '571427';

	if (TokenAuth6238::verify($secretkey,$currentcode)) {
		echo "Code is valid\n";
	} else {
		echo "Invalid code\n";
	}

  print sprintf('<img src="%s"/>',TokenAuth6238::getBarCodeUrl('','',$secretkey,'My%20App'));
  print TokenAuth6238::getTokenCodeDebug($secretkey,0); 
  
 */
namespace Compago\Tools;


class TokenAuth6238{
    /* See RFC 4226 and RFC 6238 */
    public static function getGenerator($secretkey=null, $issuer='', $username='', $digits=6){
        
        if ($secretkey === null){
            $str = "$secretkey, $issuer, $username, $digits" . time();
            $secretkey = md5($str);
        }
        return new TokenAuth6238_item($secretkey, $issuer, $username, $digits);
    }
}
class TokenAuth6238_item{
    private $secretkey = '';
    private $issuer = '';
    private $username = '';
    private $type = 'totp';
    private $digits = 6;
    private $interval = 30;
    private $algorithm = 'sha1';
    private $pinModulo = 1;
    private $instanceTime = 0;
    public function __construct($secretkey, $issuer, $username, $digits=6, $interval=30, $algorithm='sha1') {
        $this->secretkey = Base32static::encode($secretkey,false);
        $this->setIssuer($issuer);
        $this->setAlgorithm($algorithm);
        $this->setUsername($username);
        $this->setCodeLength($digits);
        $this->interval = (int)$interval;
        $this->instanceTime = time();
    }
    /**
     * @param string $code
     * @param int    $discrepancy
     */
    public function verify($code, $discrepancy = 1)
    {
        /**
         * Discrepancy is the factor of periodSize ($discrepancy * $periodSize) allowed on either side of the
         * given codePeriod. For example, if a code with codePeriod = 60 is generated at 10:00:00, a discrepancy
         * of 1 will allow a periodSize of 30 seconds on either side of the codePeriod resulting in a valid code
         * from 09:59:30 to 10:00:29.
         *
         * The result of each comparison is stored as a timestamp here instead of using a guard clause
         * (https://refactoring.com/catalog/replaceNestedConditionalWithGuardClauses.html). This is to implement
         * constant time comparison to make side-channel attacks harder. See
         * https://cryptocoding.net/index.php/Coding_rules#Compare_secret_strings_in_constant_time for details.
         * Each comparison uses hash_equals() instead of an operator to implement constant time equality comparison
         * for each code.
         */
        $time = $this->instanceTime;
        $interval = $this->interval;
        for($i = -$discrepancy; $i <= $discrepancy; $i++){
            $t = $time + ($interval * $i);
            if (hash_equals($this->getCode($t), $code)){
                return true;
            }
        }
        return false;
    }
    public function getTokenCodes($count = 3, $back = -1){
        $codes = [];
        if ($back > 0){
            $back = 0;
        }
        $time = $this->instanceTime;
        $interval = $this->interval;
        $end = $count + $back;
        for($i = $back; $i < $end; $i++){
            $t = $time + ($interval * $i);
            $codes[] = $this->getCode($t);
        }
        return $codes;
    }
    
    public function getGoogleQrUrl() {
      $url = "https://chart.apis.google.com/chart?chs=200x200&chld=M|0&cht=qr&chl=";
      $url = $url.rawurlencode($this->getUrl());
      return $url;
    }
    public function getUrl() {
      $url = 'otpauth://' . $this->type . '/';
      if ($this->issuer){
          $url .= rawurlencode($this->issuer);
          if ($this->username){
            $url .= ':'.rawurlencode($this->username);
          }
      } else {
            $url .= rawurlencode($this->username);
      }
      
      
      $url .= '?secret=' . $this->secretkey;
      if ($this->algorithm){
        $url .= '&algorithm=' . $this->algorithm;
      }
      if ($this->digits){
        $url .= '&digits=' . $this->digits;
      }
      
      if ($this->interval){
        if ($this->type == 'hotp'){
            $url .= '&count=';
        } else {
            $url .= '&period=';
        }
         $url .= $this->interval;
      }
      
      if ($this->issuer){
        $url .= '&issuer=' . rawurlencode($this->issuer);
      }
      return $url;
    }
    public function getType() {
        return $this->type;
    }
    public function setType($type) {
        $type = strtolower($type);
        if ($type == 'totp' || $type == 'hotp'){
            $this->type = $type;
        }
    }
    public function getIssuer() {
        return $this->issuer;
    }
    public function setIssuer($value) {
        $this->issuer = str_replace(':','', $value);
    }
    public function getUsername() {
        return $this->username;
    }
    public function setUsername($value) {
        $this->username = str_replace(':','', $value);
    }
    public function getInterval() {
        return $this->interval;
    }
    public function setInterval($value) {
        $this->interval = (int)$value;
        if ($this->interval < 1){
            $this->interval = 1;
        }
    }
    public function getCodeLength() {
        return $this->digits;
    }
    public function setCodeLength($value) {
        $this->digits = (int)$value;
        if ($this->digits < 4){
            $this->digits = 4;
        }
        $this->pinModulo = 10 ** $this->digits;
    }
    
    public function getAlgorithm() {
        return $this->algorithm;
    }
    public function setAlgorithm($algorithm) {
        $algorithm = strtolower($algorithm);
        if ($algorithm == 'sha1' || $algorithm == 'sha256'){
            $this->algorithm = $algorithm;
        }
    }
    public function getSecret() {
        return $this->secretkey;
    }
    
    private function hashToInt(string $bytes, int $start)
    {
        return unpack('N', substr(substr($bytes, $start), 0, 4))[1];
    }
    public function getCode( /* \DateTimeInterface */ $time = null) 
    {
        if (null === $time) {
            $time = $this->instanceTime;
        }

        if ($time instanceof \DateTimeInterface) {
            $timeForCode = $time->getTimestamp();
        } else {
            $timeForCode = $time;
        }
        $timeForCode = floor($timeForCode / $this->interval);

        $secret = base32static::decode($this->secretkey);

        $timeForCode = str_pad(pack('N', $timeForCode), 8, \chr(0), \STR_PAD_LEFT);

        $hash = hash_hmac($this->algorithm, $timeForCode, $secret, true);
        $offset = \ord(substr($hash, -1));
        $offset &= 0xF;

        $truncatedHash = $this->hashToInt($hash, $offset) & 0x7FFFFFFF;

        return str_pad((string) ($truncatedHash % $this->pinModulo), $this->digits, '0', \STR_PAD_LEFT);
    }
    
}



/**
 * Encode in Base32 based on RFC 4648.
 * Requires 20% more space than base64  
 * Great for case-insensitive filesystems like Windows and URL's  (except for = char which can be excluded using the pad option for urls)
 *
 * @package default
 * @author Bryan Ruiz
 **/
class Base32Static {

    private static $map = array(
       'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
       'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
       'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
       'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
       '='  // padding character
     );
     
    private static $flippedMap = array(
       'A'=>'0', 'B'=>'1', 'C'=>'2', 'D'=>'3', 'E'=>'4', 'F'=>'5', 'G'=>'6', 'H'=>'7',
       'I'=>'8', 'J'=>'9', 'K'=>'10', 'L'=>'11', 'M'=>'12', 'N'=>'13', 'O'=>'14', 'P'=>'15',
       'Q'=>'16', 'R'=>'17', 'S'=>'18', 'T'=>'19', 'U'=>'20', 'V'=>'21', 'W'=>'22', 'X'=>'23',
       'Y'=>'24', 'Z'=>'25', '2'=>'26', '3'=>'27', '4'=>'28', '5'=>'29', '6'=>'30', '7'=>'31'
     );
     
     /**
      * Use padding false when encoding for urls
      *
      * @return base32 encoded string
      * @author Bryan Ruiz
      **/
     public static function encode($input, $padding = true) {
       if(empty($input)) return "";
       
       $input = str_split($input);
       $binaryString = "";
       
       for($i = 0; $i < count($input); $i++) {
         $binaryString .= str_pad(base_convert(ord($input[$i]), 10, 2), 8, '0', STR_PAD_LEFT);
       }
       
       $fiveBitBinaryArray = str_split($binaryString, 5);
       $base32 = "";
       $i=0;
       
       while($i < count($fiveBitBinaryArray)) {    
         $base32 .= self::$map[base_convert(str_pad($fiveBitBinaryArray[$i], 5,'0'), 2, 10)];
         $i++;
       }
       
       if($padding && ($x = strlen($binaryString) % 40) != 0) {
           if($x == 8) $base32 .= str_repeat(self::$map[32], 6);
           else if($x == 16) $base32 .= str_repeat(self::$map[32], 4);
           else if($x == 24) $base32 .= str_repeat(self::$map[32], 3);
           else if($x == 32) $base32 .= self::$map[32];
       }
       
       return $base32;
     }
     
     public static function decode($input) {
       if(empty($input)) return;
       
       $paddingCharCount = substr_count($input, self::$map[32]);
       $allowedValues = array(6,4,3,1,0);
       
       if(!in_array($paddingCharCount, $allowedValues)) return false;
       
       for($i=0; $i<4; $i++){ 
         if($paddingCharCount == $allowedValues[$i] && 
           substr($input, -($allowedValues[$i])) != str_repeat(self::$map[32], $allowedValues[$i])) return false;
       }
       
       $input = str_replace('=','', $input);
       $input = str_split($input);
       $binaryString = "";
       
       for($i=0; $i < count($input); $i = $i+8) {
         $x = "";
         
         if(!in_array($input[$i], self::$map)) return false;
         
         for($j=0; $j < 8; $j++) {
           $x .= str_pad(base_convert(@self::$flippedMap[@$input[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
         }
         
         $eightBits = str_split($x, 8);
         
         for($z = 0; $z < count($eightBits); $z++) {
           $binaryString .= ( ($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48 ) ? $y:"";
         }
       }
       
       return $binaryString;
     }
 } 
 
