<?php

$vip = new VipCateList();

// $ids = array('8399','8669','8670','8648','8142','8435','8053','8072','7874','8134','8155','8336','8027','7919','7885','7795','7817','7830','7856','7954');
// foreach ($ids as $id) {
//     $cats['url'] = 'http://category.vip.com/search-1-0-1.html?q=1|'. $id .'|';
//     $cats[$id] = $vip->listVip($cats['url']);
// }

$cats = $vip->listVip();


print_r($cats);
class VipCateList {
    public function getCurl($url){
      

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0');
        curl_setopt($ch, CURLOPT_REFERER,'http://www.vip.com/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);//120
        
        $result=curl_exec($ch);
        curl_close($ch);
        return $result;
    
    }  

    public function listVip()
    {
        $end = $this->getCurl('http://category.vip.com');

        $ra = preg_match_all('/href="\/search-1-0-1.html?q=2|(.*?)|"/s', $end, $first_menu);

        if($ra > 0){
            foreach ($first_menu[1] as $key => $value) {
                $menu['first'] = $value;
            }
        }
        return $menu;
    }



}