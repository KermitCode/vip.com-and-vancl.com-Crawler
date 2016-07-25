<?php

$q=isset($_GET['q'])?$_GET['q']:'';
if(!$q) $q=isset($_POST['q'])?$_POST['q']:'';
$q="女装";
if($q){
	$vancl = new VcSearch();
	$items = $vancl->searchVancl($q);
	echo '<pre>';print_r($items);echo '</pre>';
	#echo gzcompress(serialize($items));
}
class VcSearch {
    public function getCurl($id){
        $url='http://s.vancl.com/'.$id.'.html';
        $url="http://s.vancl.com/search?k={$id}&orig=3";
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

    public function searchVancl($id){
        $end = $this->getCurl($id);
        $data = array();
        //产品区
        $ras = preg_match('/id\s*=\s*"vanclproducts"\s*(.*?)\s*id=\s*"pager"\s*/s',$end,$matches);
        if($ras > 0){
        	$ra = preg_match_all('/(<li.*?<\/li>)/s',$matches[1],$items);        	
            foreach ($items[1] as $k => $item) {
                //市场价
        		$ra1 = preg_match('/class\s*=\s*"Mprice">市场价￥<strong>([\d\.]+)<\/strong><\/span>/s',$item,$mprice);
        		if($ra1 > 0){
                    $data['item'][$k]['mprice'] = $mprice[1];
        		}
                //折扣价
                $ra2 = preg_match('/(?:(?:class\s*=\s*"Sprice">售价￥\s*)|(?:class\s*=\s*"Sprice">售价￥\s*<strong>))([\d\.]+)/s',$item,$sprice);
                if($ra2 > 0){
                    $data['item'][$k]['sprice'] = $sprice[1];
                }
                //产品ID
                $ra3 = preg_match('/div\s*pop\s*=\s*"([\d\.]+)"\s*class\s*=\s*"pic"/s',$item,$id);
                if($ra3 > 0){
                    $data['item'][$k]['id'] = $id[1];
                }
                //产品名称和图片
                $ra4 = preg_match('/class\s*=\s*"productPhoto"\s*original="(.*?)"\s*alt="(.*?)"/s',$item,$name);
                if($ra4 > 0){
                    $data['item'][$k]['pic'] = $name[1];
                    $data['item'][$k]['name'] = $name[2];
                }
			}	
        }else{
                return "error id";
            }
        
		//筛选属性
        return $data;
    }



}