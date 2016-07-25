<?php

$vancl = new VcList();

$cats = $vancl->listVancl('http://www.vancl.com/map/');
print_r($cats);
class VcList {
    public function getCurl($url){
      

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0');
        curl_setopt($ch, CURLOPT_REFERER,'http://www.vancl.com/map/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);//120
        
        $result=curl_exec($ch);
        curl_close($ch);
        return $result;
    
    }  

    public function listVancl($url)
    {
        $end = $this->getCurl($url);
        $data = array();
        $ra = preg_match_all('/class="scBtnArea">(.*?)<span\s*class="blank15"/s', $end, $bigmenu);

        if($ra > 0){
            foreach ($bigmenu[0] as $key => $value) {
                $ra = preg_match('/target="_blank"\s*href="http:\/\/s.vancl.com\/([\d\.]+).html">(.*?)<\/a><\/h4>/s', $value,$title);
                if($ra > 0){
                    $menu[$key]['id'] = $title[1];
                    $menu[$key]['title'] = $title[2];
                }
                $ra = preg_match_all('/class="oneRow">(.*?)<\/table>/s', $value, $rows);

                foreach ($rows[1] as $k => $v) {
                    $ra = preg_match('/target="_blank"\s*href="http:\/\/s.vancl.com\/([\d\.]+).html">(.*?)<\/a>/s', $v,$row);
                    if($ra > 0){
                        $menu[$key][$k]['row_id'] = $row[1];
                        $menu[$key][$k]['row_name'] = $row[2];
                    }
                }
            }
        }
        return $menu;
    }



}