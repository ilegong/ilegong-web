<?php

class OrderExpressComponent extends Component {


    //http://www.kuaidi100.com/autonumber/autoComNum?text=1201916550091

    //http://www.kuaidi100.com/query?type=yuantong&postid=700074134800&id=1&valicode=&temp=0.9023877156432718

    public function get_com_by_code($ip, $ship_code) {
        $url = 'http://www.kuaidi100.com/autonumber/autoComNum?text=' . $ship_code;
        $result = $this->get($url, $ip);
        $companies = $result->auto[0];
        if (!empty($companies)) {
            return $companies->comCode;
        }
        return null;
    }

    public function get_express_info($ship_code, $ip) {
        $ship_code = str_replace('-', '', $ship_code);
        $ship_code = preg_replace('/\s+/', '', $ship_code);
        $ship_codes = explode('/', $ship_code);
        $ship_code = $ship_codes[0];
        $company = $this->get_com_by_code($ip, $ship_code);
        $temp = $this->random();
        if (!empty($company)) {
            $url = 'http://www.kuaidi100.com/query?type=' . $company . '&postid=' . $ship_code . '&id=1&valicode=&temp=' . $temp;
            $result = $this->get($url, $ip);
            return $result;
        }
        return array();
    }

    private function get($url, $ip, $data_type = 'json') {
        $cl = curl_init();
        if (stripos($url, 'https://') !== FALSE) {
            curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($cl, CURLOPT_SSLVERSION, 1);
        }
        $header = array(
            'CLIENT-IP:' . $ip,
            'X-FORWARDED-FOR:' . $ip,
            'Host:www.kuaidi100.com',
            'X-Requested-With:XMLHttpRequest'
        );
        curl_setopt($cl, CURLOPT_URL, $url);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cl, CURLOPT_REFERER, 'http://www.kuaidi100.com/');
        curl_setopt($cl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.71 Safari/537.36');

        $content = curl_exec($cl);
        $status = curl_getinfo($cl);
        curl_close($cl);
        if (isset($status['http_code']) && $status['http_code'] == 200) {
            if ($data_type == 'json') {
                $content = json_decode($content);
            }
            return $content;
        } else {
            return FALSE;
        }
    }

    private function random() {
        return (float)rand() / (float)getrandmax();
    }
}