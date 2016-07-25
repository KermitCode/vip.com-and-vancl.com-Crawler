<?php
$q=isset($_POST['q'])?$_POST['q']:'';
$q="女装";
if($q){
	$vip = new VipSearch();
	$cats = $vip->searchVip('http://category.vip.com/'.$q);#search-1-0-1.html?q=3|7843 
	#echo gzcompress(serialize($cats));
	print_r($cats);
}else{
	echo gzcompress($erialize(array('')));
}
// 3|7843 3是目录等级 7843 目录ID
//search-1-3-1.html? 中间数字 4是价格降序 3是价格升序 0是默认 1是折扣降序 2是折扣升序 
class VipSearch {
    public function getCurl($url){
    
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0');
        curl_setopt($ch, CURLOPT_REFERER,'http://www.vip.com');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);//120

        $result=curl_exec($ch);
        curl_close($ch);
        return $result;
    
    }  

    public function searchVip($url)
    {
        $end = $this->getCurl($url);
        $data = array();

        $ra = preg_match('/data\s*:\s*\[(.*?)]\s*}]/s', $end, $item_js);

        if($ra > 0){

        	$ras = preg_match_all('/({.*?})/s', $item_js[1], $items);
        	if($ras > 0){
        		foreach ($items[1] as $value) {
        			$ra1 = preg_match('/index":(.*?)}/s', $value, $keys);
        			if($ra1 > 0){
        				preg_match('/{"id":(.*?),"/s', $value, $ids);
        				$data['item'][$keys[1]]['id'] = $ids[1];

        				preg_match('/"name":"(.*?)","/s', $value, $names);
        				$data['item'][$keys[1]]['name'] = $this->unicode_decode($names[1]);

        				preg_match('/"list_img":"(.*?)","/s', $value, $imgs);
        				$data['item'][$keys[1]]['img'] = $imgs[1];

        				preg_match('/discount":"(.*?)","/s', $value, $discount);
        				$data['item'][$keys[1]]['discount'] = $this->unicode_decode($discount[1]);

        				preg_match('/sell_price":(.*?),"/s', $value, $sell_price);
        				$data['item'][$keys[1]]['sell_price'] = $sell_price[1];

        				preg_match('/market_price":(.*?),"/s', $value, $market_price);
        				$data['item'][$keys[1]]['market_price'] = $market_price[1];

        				preg_match('/brand_id":(.*?),"/s', $value, $brand_id);
        				$data['item'][$keys[1]]['brand_id'] = $brand_id[1];

        				preg_match('/show_to":(.*?),"/s', $value, $show_to);
        				$data['item'][$keys[1]]['show_to'] = $show_to[1];

        				preg_match('/brand_name":"(.*?)","/s', $value, $brand_name);
        				$data['item'][$keys[1]]['brand_name'] = $this->unicode_decode($brand_name[1]);

        				preg_match('/show_title":"(.*?)","/s', $value, $show_title);
        				$data['item'][$keys[1]]['show_title'] = $this->unicode_decode($show_title[1]);
        			}
        			
        		}
        	}
        }
        $ra = preg_match_all('/(<figure\s*class\s*=\s*"cat-list-item">.*?<\/figure>)/s', $end, $items);

        if($ra > 0){
            foreach ($items[0] as $key => $value) {
				preg_match('/alt="(.*?)"/s', $value, $names);
				if($names[1] !== '{$name}'){

                    preg_match('/<a\s*target="_blank"\s*href="http:\/\/www.vip.com\/detail-(.*?)-(.*?).html"\s*title="(.*?)">(.*?)<\/a>/s', $value, $id);
                    $data['item'][$key]['id'] = $id[2];

					$data['item'][$key]['name'] = $names[1];
					$data['item'][$key]['show_title'] = $names[1];
					preg_match('/data-original="(.*?)"/s', $value, $imgs);
					$data['item'][$key]['img'] = $imgs[1];

					preg_match('/class="salebg2">(.*?)<\/span>/s', $value, $discount);
					$data['item'][$key]['discount'] = $this->unicode_decode($discount[1]);

					preg_match('/class="cat-pire-nub">(.*?)<\/span>/s', $value, $sell_price);
					$data['item'][$key]['sell_price'] = $sell_price[1];

					preg_match('/class="cat-pire-before">￥(.*?)<\/del>/s', $value, $market_price);
					$data['item'][$key]['market_price'] = $market_price[1];

					preg_match('/href="http:\/\/www.vip.com\/show-(.*?).html">/s', $value, $brand_id);
					$data['item'][$key]['brand_id'] = $brand_id[1];

					preg_match('/data-time="(.*?)"><\/span>/s', $value, $show_to);
					$data['item'][$key]['show_to'] = $show_to[1];
					$data['item'][$key]['time'] = date('Y-m-d H:i:s',$show_to[1]);
					preg_match('/class="cat-inf-title">\s*<a\s*target="_blank"\s*href="(.*?)">(.*?)<\/a>\s*<\/p>/s', $value, $brand_name);
					$data['item'][$key]['brand_name'] = $brand_name[2];
				}
            }
        }
     	$ra = preg_match('/class="oper-txt-tips">\s*<em>(.*?)<\/em>\s*个商品\s*<\/span>/s', $end, $nums);
     	if($ra > 0){
     		$data['num'] = $nums[1];
     	}

     	$ra = preg_match_all('/(<dl\s*class="cat-oper-sec.*?)<\/dl>/s', $end, $filters);
     	if($ra > 0){
     		foreach ($filters[1] as $key => $value) {
     			preg_match('/class="oper-sec-tit">(.*?)：<\/dt>/s', $value,$name);
     			$data['filter'][$key]['name'] = $name[1];

     			$ra1 = preg_match_all('/title="(.*?)"/s', $value, $title);
     			if($ra1 > 0){
     				foreach ($title[1] as $k => $v) {
     					$data['filter'][$key][$k]['title'] = $v;
     				}
     			}

				$ra2 = preg_match_all('/data-id="(.*?)"/s', $value, $id);
     			if($ra2 > 0){
     				foreach ($id[1] as $k => $v) {
     					$data['filter'][$key][$k]['id'] = $v;
     				}
     			}

				$ra3 = preg_match_all('/src="(.*?)"\s*\/>/s', $value, $url);
     			if($ra3 > 0){
     				foreach ($url[1] as $k => $v) {
     					$data['filter'][$key][$k]['url'] = $v;
     				}
     			}
     		}
     	}
     	//search-1-3-1.html?
     	//&price_start=1&price_end=30 价格筛选
        return $data;
    }

    function unicode_decode($name)
	{
	    // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
	    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
	    preg_match_all($pattern, $name, $matches);
	    if (!empty($matches))
	    {
	        $name = '';
	        for ($j = 0; $j < count($matches[0]); $j++)
	        {
	            $str = $matches[0][$j];
	            if (strpos($str, '\\u') === 0)
	            {
	                $code = base_convert(substr($str, 2, 2), 16, 10);
	                $code2 = base_convert(substr($str, 4), 16, 10);
	                $c = chr($code).chr($code2);
	                $c = iconv('UCS-2', 'UTF-8', $c);
	                $name .= $c;
	            }
	            else
	            {
	                $name .= $str;
	            }
	        }
	    }
	    return $name;
	}
}