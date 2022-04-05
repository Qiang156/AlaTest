<?php
namespace AlaTest;

class Solution
{

    /**
     * @array array
     */
    private $rules;


    /**
     * @param $file
     * @throws \Exception
     */
    public function __construct()
    {
        //$priceList = $this->loadPriceDataFromFile($file);
        //$this->rules = $this->transform($priceList);
    }

    /**
     * @param $phone
     * @return string
     * @throws \Exception
     */
    private function filter($phone): string
    {
        $string = '';
        $len = strlen($phone);
        for($i = 0; $i < $len; $i ++) {
            if( is_numeric($phone[$i]) ) {
                $string .= $phone[$i];
            }
        }
        if( $string == '') {
            throw new \Exception('The phone number is not correct');
        }
        return $string;
    }

    /**
     * @param $file
     * @return $priceList
     * @throws \Exception
     */
    public function loadPriceDataFromFile($file)
    {
        $priceList = [];
        if( is_file($file) ) {
            $file = file($file);
        } else {
            exit('The file '.$file.' does not exist');
        }
        $operator = '';
        foreach($file as $line) {
            $line = trim($line);
            if($line[strlen($line)-1] == ':') {
                $operator = trim($line,":");
                $priceList[$operator] = [];
            } else {
                list($rule, $price) = explode("\t", $line);
                $priceList[$operator][] = ['patten' => trim($rule), 'price' => trim($price)];
            }
        }
        $this->rules = $this->transform($priceList);
        return $this->rules;
    }

    /**
     * @param $rules
     * @return array
     */
    private function transform($rules)
    {
        $result = [];
        foreach($rules as $operator => $items) {
            foreach( $items as $item ) {
                $result[$item['patten']][] = [
                    'operator' => $operator,
                    'price' => $item['price']
                ];
            }
        }
        return $result;
    }

    /**
     * Find out all the items which match with the phone number.
     * @return array
     */
    private function findOutAllItem($phone)
    {
        $result = [];
        foreach($this->rules as $patten => $val) {
            $target = null;
            $len = strlen($patten);
            if( strlen($phone) >= $len ) {
                if( substr($phone, 0, $len) == $patten) {
                    $target = $len > strlen($target) ? $patten : $target;
                }
            }
            if( !is_null($target) ) $result[$target] = $this->rules[$target];
        }
        return $result;
    }


    /**
     * get the only one price for specific phone number.
     * @return array
     */
    private function getOperatorPricesBy($phone)
    {
        $data = [];
        foreach($this->findOutAllItem($phone) as $key => $val) {
            foreach ($val as $item) {
                if( key_exists($item['operator'], $data) ) {
                    if(strlen($key) > strlen($data[$item['operator']]['patten'])) {
                        $data[$item['operator']] = [
                            'patten' => $key,
                            'price' => $item['price']
                        ];
                    }
                } else {
                    $data[$item['operator']] = [
                        'patten' => $key,
                        'price' => $item['price']
                    ];
                }
            }
        }
        return $data;
    }


    /**
     * @throws \Exception
     */
    public function cheapest($phone)
    {
        $phone = $this->filter($phone);
        $priceList = $this->getOperatorPricesBy($phone);
        $minPrice = [];
        if( empty($priceList) ) {
            return $minPrice;
            //throw new Exception('No operator can be used');
        }
        foreach($priceList as $key => $item) {
            if( empty($minPrice) || $item['price'] < $minPrice['price'] ) {
                $minPrice['operator'] = $key;
                $minPrice['price'] = $item['price'];
                $minPrice['patten'] = $item['patten'];
            }
        }
        return $minPrice;
    }

    public function output($phone, $data)
    {
        $str = "Phone number <%s> matches <%s> in <%s> Operator and the price is $%0.2f/minute\r\n";
        return sprintf($str, $phone, $data['patten'],$data['operator'], $data['price']);
    }


}


//$phone = '+46-73-212345';
//
//$solu = new Solution();
//$solu->loadPriceDataFromFile('./data.txt');
//$data = $solu->cheapest($phone);
//$solu->output($phone, $data);


