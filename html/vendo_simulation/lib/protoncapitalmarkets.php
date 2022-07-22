<?php

namespace Brokers;

use Exception;
use Throwable;

//our exception class
class ProtonCapitalMarketsException extends Exception
{
    const PREFIX = 'PROTONEXCEPTION: ';

    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct(ProtonCapitalMarketsException::PREFIX . $message, $code, $previous);
    }
}

class ProtonCapitalMarketsBroker
{

    //TODO: throw exception -> optional?
    //MAYBE: getlasterror, unifiy to abstract super class (implement some statics there), etc.
    //MAYBE: factory...

    //parameter constants
    const PARAM_SERVERNAME = "servername";
    const PARAM_AUTHCODE = "authcode";
    const PARAM_QUERY = "query";
    const PARAM_WLCODE = "wlcode";
    const PARAM_WLID = "wlid";

    const SERVICE_API_BASE_URL = "https://protoncapitalmarkets.com/services/";

    const ACCTYPE_BASIC   = 7188;
    const ACCTYPE_PLUS    = 7331;
    const ACCTYPE_PRO     = 7332;
    const ACCTYPE_PROPLUS = 7333;

    private $authcode;
    private $servername;

    private $countries = array();

    public function __construct($servername, $authcode)
    {
        $params = [
            ProtonCapitalMarketsBroker::PARAM_SERVERNAME => $servername,
            ProtonCapitalMarketsBroker::PARAM_AUTHCODE => $authcode
        ];
        $this->init($params);
    }

// #################################### //
// ############ PUBLIC API ############ //

    // // action=CreateToken
    // // query=
    // // returncode=
    // // SUCCESSFUL RESULT
    // // {
    // //     'ResponseMsg':'SUCCESSFUL',
    // //     'ResponseMsg': Token Code,
    // // }
    // // ERROR RESULT
    // // {
    // //     'ResponseMsg':'ERROR',
    // //     'ResponseMsg': Error Desc.,
    // // }
    public function createToken()
    {
        $response = $this->sendRequest("CreateToken");
        if (!self::isResponseOk($response)) {
            throw new ProtonCapitalMarketsException($response['Result']);    
        }
        return $response['Result']; //token
    }

    // // action=Register
    // // query=Name|Surname|Email|Phonecode|Phone|Password(must be hash)|Token Code(Use CreateToken service)|Your Client ID|AccType
    // // returncode=
    // // SUCCESSFUL RESULT
    // // {
    // //     'ResponseMsg':'SUCCESSFUL',
    // //     'ResponseMsg': 'register_ok',
    // // }
    // // ERROR RESULT
    // // {
    // //     'ResponseMsg':'ERROR',
    // //     'ResponseMsg': Error Desc.,
    // // }
    public function register($name, $surname, $email, $phonecode, $phone, $password_hash, $dateofbirth, $housenumber, $street, $city, $postalcode, $country, $nationality, $yourclientid, $acctype)
    {
        $token = $this->createToken();
        if ($token) {
            //Name|Surname|Email|Password(must be hash)|Phonecode|Phone|dateofbirth Format(YYYY-MM-DD)|housenumber|street|city|postalcode|country|nationality (country code)|Token Code(Use CreateToken service)|Your Client ID|AccType
            $query = "$name|$surname|$email|$password_hash|$phonecode|$phone|$dateofbirth|$housenumber|$street|$city|$postalcode|$country|$nationality|$token|$yourclientid|$acctype";
            $response = $this->sendRequest("FullRegister", $query);
            if (!self::isResponseOk($response)) {
                throw new ProtonCapitalMarketsException($response['Result']);
            }
            return $response['Result'];
        }
        return false;
    }

    // // action=GenerateLoginToken
    // // query=Email|Password(must be hash)|Token Code(Use CreateToken service)|Your Client ID
    // // returncode=
    // // SUCCESSFUL RESULT
    // // {
    // //     'ResponseMsg':'SUCCESSFUL',
    // //     'Result': {
    // //         'token': "MN5Av83GIaOsTyTTm1BxYK312SfSKNNf5",
    // //         'response': 'token_created'
    // //     }
    // // }
    // // ERROR RESULT
    // // {
    // //     'ResponseMsg':'ERROR',
    // //     'ResponseMsg': Error Desc.,
    // // }
    public function generateLoginToken($email, $password_hash, $yourclientid)
    {
        $token = $this->createToken();
        if ($token) {
            //urlencode email needed for e.g. passou+1@gmail.com, etc.
            $query = urlencode($email) . "|$password_hash|$token|$yourclientid";
            $response = $this->sendRequest("GenerateLoginToken", $query);
            if (!self::isResponseOk($response)) {
                throw new ProtonCapitalMarketsException($response['Result']);
            }
            return $response['Result']['token'];
        }
        return false;
    }

    // // action=GetInfo
    // // query=Your Client ID
    // // returncode=
    // // SUCCESSFUL RESULT
    // // {
    // //     'ResponseMsg':'SUCCESSFUL',
    // //     "Result": {
    // //         "AccountNumber": "71481435",
    // //         "Status": "ReadyForTrade"
    // //     }
    // // }
    // // ERROR RESULT
    // // {
    // //     'ResponseMsg':'ERROR',
    // //     'ResponseMsg': Error Desc.,
    // // }
    public function getInfo($yourclientid)
    {
        $response = $this->sendRequest("GetInfo", $yourclientid);
        if (!self::isResponseOk($response)) {
            throw new ProtonCapitalMarketsException($response['Result']);
        }
        return $response['Result'];
    }

    // // action=GetTradeInfo
    // // query=Your Client ID
    // // returncode=
    // // SUCCESSFUL RESULT
    // // {
    // //     'ResponseMsg':'SUCCESSFUL',
    // //     "Result": {
    // //         "tradingacc": [
    // //             {
    // //                 "id": "",
    // //                 "account_number": "",
    // //                 "account_type": "",
    // //                 "account_type_cmp": "",
    // //                 "accountcurrency": "",
    // //                 "leverage": "",
    // //                 "group": "",
    // //                 "free_margin": "",
    // //                 "balance": "",
    // //                 "TradeInfo": {
    // //                     "Equity": "",
    // //                     "Balance": "",
    // //                     "Credit": "",
    // //                     "Margin": "",
    // //                     "Margin Free": "",
    // //                     "Margin Level": ""
    // //                 },
    // //                 "Orders": [
    // //                     "OpenOrders":[]
    // //                     "PendingOrders":[]
    // //                 ]
    // //             }
    // //         ]
    // //     }
    // // }
    // // ERROR RESULT
    // // {
    // //     'ResponseMsg':'ERROR',
    // //     'ResponseMsg': Error Desc.,
    // // }
    public function getTradeInfo($yourclientid) {
        $response = $this->sendRequest("GetTradeInfo", $yourclientid);
        if (!self::isResponseOk($response)) {
            throw new ProtonCapitalMarketsException($response['Result']);
        }

        //we have only 1 trading account per 'client' (user) => return it directly
        return $response['Result']['tradingacc'][0];
    }

    // // action=GetTradeHistory
    // // query=Your Client ID|begin(Format: YYYY-MM-DD)|end(Format: YYYY-MM-DD)
    // // returncode=
    // // SUCCESSFUL RESULT
    // // {
    // //     'ResponseMsg':'SUCCESSFUL',
    // //     "Result": [
    // //         {
    // //             "Order": "",
    // //             "Cmd": "",
    // //             "Symbol": "",
    // //             "Volume": "",
    // //             "Open Price": "",
    // //             "SL": "",
    // //             "TP": "",
    // //             "Close Price": "",
    // //             "Open Time": "",
    // //             "Commission": "",
    // //             "Profit": "",
    // //             "Storage": "",
    // //             "Comment": ""
    // //         },
    // //         {
    // //             "Order": "",
    // //             "Cmd": "",
    // //             "Open Time": "",
    // //             "Profit": "",
    // //             "Comment": ""
    // //         }
    // //     ],
    // //     "TradingAcc": "71481435"
    // // }
    // // ERROR RESULT
    // // {
    // //     'ResponseMsg':'ERROR',
    // //     'ResponseMsg': Error Desc.,
    // // }
    public function getTradeHistory($yourclientid, $dateBegin, $dateEnd)
    {
        $query = "$yourclientid|$dateBegin|$dateEnd";
        $response = $this->sendRequest("GetTradeHistory", $query);
        if (!self::isResponseOk($response)) {
            throw new ProtonCapitalMarketsException($response['Result']);
        }
        
        //response result contains no "TradingAcc" -> wrong API doc?! (AP-ID:16875)
        //we only have array of orders in "Result"
        return $response['Result'];
    }

    // // action=GetCountries
    // // query=
    // // returncode=
    public function getCountries()
    {
        //lazy initialization
        if (empty($this->countries)) {
            $response = $this->sendRequest("GetCountries");
            if (!self::isResponseOk($response)) {
                throw new ProtonCapitalMarketsException($response['Result']);
            }
            $this->countries = $response['Result']['countries'];
        }
        return $this->countries;
    }

    // // action=UpdateClientInfo
    // // query=name|surname|dateofbirth Format(YYYY-MM-DD)|address|city|postalcode|country (Please Use Id in GetCountries Action)|nationality (Please Use Id in GetCountries Action)|phonecode|phone|Your Client ID|AccType
    // // returncode=
    // // SUCCESSFUL RESULT
    // // {
    // //     'ResponseMsg':'SUCCESSFUL',
    // //     'ResponseMsg': 'client_updated',
    // // }
    // // ERROR RESULT
    // // {
    // //     'ResponseMsg':'ERROR',
    // //     'ResponseMsg': Error Desc.,
    // // }
    public function updateClientInfo($name, $surname, $dateofbirth, $housenumber, $address, $city, $postalcode, $country_2_letter_iso, $nationality_2_letter_iso, $phonecode, $phone, $yourclientid, $acctype)
    {
        
        $country_id = "";
        if (!empty($country_2_letter_iso)) {
            $country_id = $this->getCountryByIsoCode($country_2_letter_iso)['id'];
        }
        
        $nationality_id = "";
        if (!empty($nationality_2_letter_iso)) {
            $nationality_id = $this->getCountryByIsoCode($nationality_2_letter_iso)['id'];
        }
        
        // assure all fields are set to "some" value - prevent exception "fill_fields" (no clientID and Accounttype)
        self::AssureNotEmpty($name, $surname, $dateofbirth, $housenumber, $address, $city, $postalcode, $country_2_letter_iso, $nationality_2_letter_iso, $phonecode, $phone);

        //actual update user info request
        $query = "$name|$surname|$dateofbirth|$housenumber|$address|$city|$postalcode|$country_id|$nationality_id|$phonecode|$phone|$yourclientid|$acctype";
        $response = $this->sendRequest("UpdateClientInfo", $query);
        if (!self::isResponseOk($response)) {
            throw new ProtonCapitalMarketsException($response['Result']);
        }
        return $response['Result'];
    }
    
    private static function AssureNotEmpty(&...$Values) {
        foreach ($Values as &$singleValue) {
            if(empty($singleValue)) {
                $singleValue = "NA";
            }
        }
    }

    // // action=UpdateClientPassword
    // // query=password (must be hash)|Your Client ID
    // // returncode=
    // // SUCCESSFUL RESULT
    // // {
    // //     'ResponseMsg':'SUCCESSFUL',
    // //     'ResponseMsg': 'client_updated',
    // // }
    // // ERROR RESULT
    // // {
    // //     'ResponseMsg':'ERROR',
    // //     'ResponseMsg': Error Desc.,
    // // }
    public function updateClientPassword($password_hash, $yourclientid)
    {
        $query = "$password_hash|$yourclientid";
        $response = $this->sendRequest("UpdateClientPassword", $query);
        if (!self::isResponseOk($response)) {
            throw new ProtonCapitalMarketsException($response['Result']);
        }
        return $response['Result'];
    }

// ############ END (PUBLIC API) ############ //
// ########################################## //

    protected function init($params = array())
    {
        $this->authcode = $params[ProtonCapitalMarketsBroker::PARAM_AUTHCODE];
        $this->servername = $params[ProtonCapitalMarketsBroker::PARAM_SERVERNAME];
    }

    protected function sendRequest($action, $query = "")
    {
        if (empty($action)) {
            throw new ProtonCapitalMarketsException("Invalid request (no action provided)!");
        }

        if (!empty($action)) {
            try {
                $service_api_address = self::SERVICE_API_BASE_URL . $action;

                //generate postdata 
                $params = [
                    ProtonCapitalMarketsBroker::PARAM_AUTHCODE => $this->authcode,
                    ProtonCapitalMarketsBroker::PARAM_SERVERNAME => $this->servername,
                ];

                //query?
                if (!empty($query)) {
                    $params[ProtonCapitalMarketsBroker::PARAM_QUERY] = $query;
                }

                $curlopts = [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                ];

                $response = self::sendRawRequest($service_api_address, $params, $curlopts);
                return json_decode($response, true);
            
            } catch (Exception $e) {
                //wrap in our exception
                throw new ProtonCapitalMarketsException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return false;
    }

    protected static function isResponseOk($response)
    {
        if (!empty($response) && is_array($response)) {
            if (!empty($response['ResponseMsg']) && ($response['ResponseMsg'] == 'SUCCESSFUL')) {
                return true;
            }
        }
        return false;
    }

    protected static function sendRawRequest($url, $params = array(), $curlopts = array())
    {
        try {

            if (!empty($params)) {
                $data = http_build_query($params);
                $header = array(
                    'Content-Type: application/x-www-form-urlencoded'
                );

                $curlopts[CURLOPT_HTTPHEADER] = $header;
                $curlopts[CURLOPT_POSTFIELDS] = $data;
            }

            //curl session
            $ch = curl_init($url);
            if ($ch) {
                if (!empty($curlopts)) {
                    if (!curl_setopt_array($ch, $curlopts)) {
                        return false;
                    }
                }
                $response = curl_exec($ch);
                curl_close($ch);

                return $response;
            }
        } catch (Exception $e) {
            //wrap in our exception
            throw new ProtonCapitalMarketsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getCountryByIsoCode($two_letter_iso_code)
    {
        $countries = $this->getCountries();
        for ($i = 0; $i < count($countries); $i++) {
            if (strtoupper($two_letter_iso_code) == strtoupper($countries[$i]['iso'])) {
                return $countries[$i];
            }
        }
        return false;
    }
}
