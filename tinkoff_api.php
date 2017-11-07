<?php

namespace G24_Payment;

define('TINKOFF_TERMINAL_KEY', getenv('TINKOFF_TERMINAL_KEY') );
define('TINKOFF_SECRET_KEY', getenv('TINKOFF_SECRET_KEY') );

class Tinkoff_API {
    const REST_URL = "https://securepay.tinkoff.ru/rest";

    public $error = null;

    private $_url = null;
    private $_method = null;
    private $_request = null;
    private $_trailer = null;
    private $_raw_response = null;
    private $_response = null;

    /**
     * Gets customer card list associated with the terminal
     */
    public function get_card_list($params=[]){
        $this->_url = self::REST_URL . "/GetCardList";
        $this->_method = "POST";
        $valid_params = [
            'CustomerKey', // String(36) required
            'IP', // String(40)
        ];
        return $this->_api_request($valid_params, $params);
    }

    /**
     * Removes customer data associated with the terminal
     */
    public function remove_customer($params=[]){
        $this->_url = self::REST_URL . "/RemoveCustomer";
        $this->_method = "POST";
        $valid_params = [
            'CustomerKey', // String(36) required
            'IP', // String(40)
        ];
        return $this->_api_request($valid_params, $params);
    }

    /**
     * Gets customer data associated with the terminal
     */
    public function get_customer($params=[]){
        $this->_url = self::REST_URL . "/GetCustomer";
        $this->_method = "POST";
        $valid_params = [
            'CustomerKey', // String(36) required
            'IP', // String(40)
        ];
        return $this->_api_request($valid_params, $params);
    }

    /**
     * Binds customer data to the terminal
     */
    public function add_customer($params=[]){
        $this->_url = self::REST_URL . "/AddCustomer";
        $this->_method = "POST";
        $valid_params = [
            'CustomerKey', // String required
            'IP', // String
            'Email', // String
            'Phone', // String (+71234567890)
        ];
        return $this->_api_request($valid_params, $params);
    }

    /**
     * Performs subsequent recurrent payment
     */
    function payment_charge($params=[]){
        $this->_url = self::REST_URL . "/Charge";
        $this->_method = "POST";
        $valid_params = [
            'PaymentId', // Number(20) required
            'IP', // String(40) Client IP
            'RebillId', // Number(20) required
        ];
        return $this->_api_request($valid_params, $params);
    }

    /**
     * Begins ordinary or first recurrent payment
     */
    function payment_init($params=[]){
        $this->_url = self::REST_URL . "/Init";
        $this->_method = "POST";
        $valid_params = [
            'Amount', // Number(10) required
            'OrderId', // String(50) required
            'IP', // String(40) Client IP
            'Description', // String(250)
            'Currency', // Number(3)
            'Language', // String(2)
            'CustomerKey', // String(36) required if Recurrent = Y
            'Recurrent', // String(1)
            'RedirectDueDate', // Datetime YYYY-MM-DDTHH24:MI:SS+GMT (2016-08-31T12:28:00+03:00)
            'DATA', // JSON Object
            'Receipt', // JSON Object
        ];

        $this->_trailer = [];
        if (!empty($params['Receipt']) ) {
            $this->_trailer['Receipt'] = $params['Receipt'];
            unset($params['Receipt']);
        }
        if (!empty($params['DATA']) ) {
            $this->_trailer['DATA'] = $params['DATA'];
            unset($params['DATA']);
        }

        return $this->_api_request($valid_params, $params);
    }

    private function _api_request($valid_params, $params){
        $this->_bind_params($valid_params, $params);

        if (!$this->_get_response() ) {
            return false;
        }

        return $this->_response;
    }

    private function _bind_params($valid_params, $params){
        if (empty($params) || !is_array($params) ) {
            return;
        }

        $this->_request = [];
        foreach ($params as $key=>$value) {
            if (in_array($key, $valid_params) ) {
                $this->_request[$key] = $value;
            }
        }
    }

    private function _get_response(){
        if (empty($this->_request) ){
            $this->error = "Empty request parameters.";
            return false;
        }

        $this->_request['TerminalKey'] = TINKOFF_TERMINAL_KEY;
        $this->_sign_request();

        if(!empty($this->_trailer) ){
            foreach($this->_trailer as $key=>$value){
                $this->_request[$key] = $value;
            }
        }

        if (!$this->_do_request() ) {
            return false;
        }

        $this->_response = json_decode($this->_raw_response);
        if (empty($this->_response) ) {
            $this->error = "Invalid response JSON.";
            return false;
        }

        if (!$this->_response->Success) {
            $message = !empty($this->_response->Message) ? $this->_response->Message : 'no message';
            $details = !empty($this->_response->Details) ? $this->_response->Details : 'no details';
            $error_code = isset($this->_response->ErrorCode) ? $this->_response->ErrorCode : 'n/a';
            $this->error = "Transaction error: #{$error_code} :: {$message} :: {$details}";
            return false;
        }

        return true;
    }

    private function _sign_request(){
        $this->_request['Password'] = TINKOFF_SECRET_KEY;
        ksort($this->_request);
        $data = implode('', array_values($this->_request) );
        $this->_request['Token'] = hash('sha256', $data);
        unset($this->_request['Password']);
    }

    private function _do_request(){
        $postfields = "";
        if ($this->_method == "POST") {
            foreach ($this->_request as $key=>$value) {
                $postfields .= !empty($postfields) ? '&' : '';
                $postfields .= sprintf("%s=%s", $key, rawurlencode($value) );
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_url);
        if ($this->_method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->_raw_response = curl_exec($ch);
        if ($errno = curl_errno($ch) ) {
            $error = curl_strerror($errno);
            $this->error = printf("CURL error: %s\n", $error);
            return false;
        }
        curl_close($ch);

        return true;
    }
}
