<?php
/*
 * Use of this file is subject to Kakupay terms of use
 */
    class b2bAutoloader{

        const BASE_URL      = GIVEN_BASE_URL; // KakuPay API endpoint
        const PRIVATE_KEY   = GIVEN_PRIVATE_KEY; // Provided by KakuPay Team
        const API_KEY       = GIVEN_KAKUPAY_API_KEY; // Provided by KakuPay Team
        const API_USER      = GIVEN_API_USER; // Provided by KakuPay Team
        const AES_METHOD    = "AES-256-CBC"; // Encryption method
        private $error      = false;
        private $secret     = null;
        private $secretHash = null;

        function __construct(){

        }

        private function checkConfig($key,$obj){
            $obj = (Object) $obj;
            if(!isset($obj->$key)) return false;
            if(empty($obj->$key)) return false;
            return true;
        }

        function disbursement(array $config){
            if($this->error) return $this->error;
            if(!$this->checkConfig("amount",$config)){
                return $this->setMessage("100","Amount cannot be empty");
            }
            elseif(!$this->checkConfig("recipient",$config)){
                return $this->setMessage("100","Recipient cannot be empty");
            }
            elseif(!$this->checkConfig("reference",$config)){
                return $this->setMessage("100","Reference cannot be empty");
            }else {
                if(null !== $this->secret) {
                    foreach ($config as $key => $value){
                        $config[$key] = $this->encrypt($this->secret,$value);
                    }
                    $extraHeaders = ["Authorization: Bearer {$this->secretHash}"];
                    $response =  $this->HTTPTransport('disbursement', $config,$extraHeaders);
                    if(is_object($response)) {
                        return $this->setMessage(
                            $response->code,
                            $response->message,
                            $response->feedback,
                            (int) $response->code === 200 ? "success" : "error"
                        );
                    }
                    return false;
                }
            }
            return $this->setMessage("100","Could not establish secure connection");
        }

        function auth(){
            $token = $this->HTTPTransport("auth",[]);
            if((int) $token->code === 200){
                $this->secret       = $this->decrypt($token->feedback->hash);
                $this->secretHash   = $token->feedback->hash;
            }
            else{
                $this->setMessage($token->code,$token->message);
            }
            return $this;
        }

        function status(array $config){
            if($this->error) return $this->error;
            if(!$this->checkConfig("reference",$config)){
                return $this->setMessage("100","Reference cannot be empty");
            }
            elseif(null !== $this->secret) {
                $config['reference'] = $this->encrypt($this->secret,$config['reference']);
                $extraHeaders = ["Authorization: Bearer {$this->secretHash}"];
                $response =  $this->HTTPTransport('status', $config,$extraHeaders);
                if(is_object($response)) {
                    return $this->setMessage(
                        $response->code,
                        $response->message,
                        $response->feedback,
                        (int) $response->code === 200 ? "success" : "error"
                    );
                }
                return false;
            }
            return $this->setMessage("100","Could not establish secure connection");
        }

        function encrypt($privateKey,$content){
            return @openssl_encrypt($content, self::AES_METHOD, $privateKey);
        }

        function decrypt($hash){
            return @openssl_decrypt($hash, self::AES_METHOD, self::PRIVATE_KEY);
        }

        function retry(array $config){
            if($this->error) return $this->error;
            if(!$this->checkConfig("reference",$config)){
                return $this->setMessage("100","Reference number cannot be empty");
            }
            elseif(null !== $this->secret) {
                $config['reference'] = $this->encrypt($this->secret,$config['reference']);
                $extraHeaders = ["Authorization: Bearer {$this->secretHash}"];
                $response =  $this->HTTPTransport('retry', $config,$extraHeaders);
                if(is_object($response)) {
                    return $this->setMessage(
                        $response->code,
                        $response->message,
                        $response->feedback,
                        (int) $response->code === 200 ? "success" : "error"
                    );
                }
                return false;
            }
            return $this->setMessage("100","Could not establish secure connection");
        }

        function refund(){

        }

        function isAccount(array $config){
            if($this->error) return $this->error;
            if(!$this->checkConfig("recipient",$config)){
                return $this->setMessage("100","Account number cannot be empty");
            }
            elseif(null !== $this->secret) {
                $config['recipient'] = $this->encrypt($this->secret,$config['recipient']);
                $extraHeaders = ["Authorization: Bearer {$this->secretHash}"];
                $response =  $this->HTTPTransport('isAccount', $config,$extraHeaders);
                if(is_object($response)) {
                    return $this->setMessage(
                        $response->code,
                        $response->message,
                        $response->feedback,
                        (int) $response->code === 200 ? "success" : "error"
                    );
                }
                return false;
            }
            return $this->setMessage("100","Could not establish secure connection");
        }

        protected function setMessage($code,$message,$feedback=[],$status="info"){
            return $this->error = (Object) ([
                "code"      => (int) $code,
                "message"   => $message,
                "status"    => $status,
                "feedback"  => $feedback
            ]);
        }

        private function HTTPTransport($endpoint,$array=[],$headers=[]){
            $base = self::BASE_URL."/{$endpoint}";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $base,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 1,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>json_encode($array),
                CURLOPT_HTTPHEADER => array_merge([
                    "k-api-key:".self::API_KEY."",
                    "k-api-id:".self::API_USER."",
                    "Content-Type: text/plain"
                ],$headers)
            ));
            $response = curl_exec($curl);
            if(curl_error($curl)){
                return $this->setMessage("100","No network connection");
            }

            curl_close($curl);

//            if($endpoint!='auth') {
//                echo "{$endpoint}";
//                echo $response;
//            }

            $json = json_decode($response);
            if(isset($json->command)){
                if((int) $json->command===1){
                    $json->feedback = json_decode($this->decrypt($json->feedback));
                }
            }
            return $json;
        }

        private function setCookie(){
            if(!isset($_COOKIE['kC'])) {
                $value = rand(1, 10000);
                $minute_in_seconds = 60;
                $time = time() + (20 * $minute_in_seconds) + (10 * 60);
                setcookie("kC", $value, $time);
            }
            return isset($_COOKIE['kC']) ? $_COOKIE['kC']:false;
        }

    }