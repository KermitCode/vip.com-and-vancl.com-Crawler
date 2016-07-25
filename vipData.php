<?php
$id=isset($_POST['id'])?$_POST['id']:0;
$id='329826-42941398';
error_reporting(0);
if($id){
	#http://www.vip.com/detail-318445-41776772.html
	$vip = new VipData();
	$item = $vip->itemVip("http://www.vip.com/detail-{$id}.html"); //中间数字是brand_id 后边是产品ids
	echo '<pre>';print_r($item);echo '</pre>';exit;
	echo gzcompress(serialize($item));
}else{
	echo gzcompress(serialize(array()));
	}
// $id = '40801361';
// $brand_id = '313525';
// $urls = array(
// 	'0' => 'http://www.vip.com/detail-'.$brand_id.'-'. $id .'.html', 
// 	'1' => 'http://resys.vip.com/?fields=img,agio,name,link,foreword,vipshop_price,market_price&ps=50&pid='. $id .'&page=100304&method=product.alsolike&styletype=C', 
// 	'2' => 'http://resys.vip.com/?fields=img,agio,name,link,foreword,vipshop_price,market_price&ps=20&pid='. $id .'&page=100307&method=user.product.alsotag&min_ps=3&max_tab=6', 
// 	'3' => 'http://resys.vip.com/?fields=img,agio,name,link,foreword,vipshop_price,market_price&ps=20&pid='. $id .'&page=100305&method=user.product.history', 
// 	);
// $item = $vip->itemVip($url); 
#print_r($item);
class VipData {
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

    public function itemVip($url)
    {
        $end = $this->getCurl($url);
        $data = array();

        $ra = preg_match('/merchandise\s*:\s*{\s*id\s*:\s*(.*?),\s*/s', $end, $id);
        if($ra > 0){
        	$data['item']['id'] = $id[1];
        }

        $ra = preg_match('/class="pib_title_detail">(.*?)<\/p>/s', $end, $title);
        if($ra > 0){
            $data['item']['title'] = $title[1];
        }
    	//产品标题
        $ra = preg_match('/href="http:\/\/brand.vip.com\/(.*?)\/"\s*target="_blank">(.*?)\s*<\/a>/s', $end, $brand);
        if($ra > 0){
            $data['item']['brand_id'] = $brand[1];
            $data['item']['brand_name'] = $brand[2];
        }
        $ra = preg_match('/\'brandLogo\'\s*:\s*\'(.*?)\',/s', $end, $brand_logo);
        if($ra > 0){
        	$data['item']['brand_logo'] = $brand_logo[1];
        }
        //品牌ID 品牌名
        $ra = preg_match('/class="pbox_price">\s*<i\s*class="pbox_yen">&yen;<\/i>\s*<em>\s*(.*?)\s*<\/em>\s*<\/span>/s', $end, $sale_price);
        if($ra > 0){
            $data['item']['sale_price'] = $sale_price[1];
        }
        //折扣价
        $ra = preg_match('/class="pbox_market">&yen;<del>(.*?)<\/del>\s*<\/span>/s', $end, $market_price);
        if($ra > 0){
            $data['item']['market_price'] = $market_price[1];
        }
        //原价
        $ra = preg_match('/class="pbox_off">(.*?)折<\/span>/s', $end, $discount);
        if($ra > 0){
            $data['item']['discount'] = $discount[1];
        }
        //折扣
        $ra = preg_match('/<dt\s*class="color_name">颜色\s*：\s*<\/dt>/s', $end);
        if($ra > 0){
        	$ra1 = preg_match_all('/class="color_list_item">\s*(.*?)\s*<\/li>/s', $end, $colors);
        	if($ra1 > 0){
        		foreach ($colors[0] as $k => $v) {
        			preg_match('/alt="(.*?)"/s', $v,$title);
        			$data['item']['color'][$k]['title'] = $title[1];

        			preg_match('/data-original="(.*?)"/s', $v,$img);
        			$data['item']['color'][$k]['img'] = $img[1];

        			preg_match('/href="\/detail-(.*?)-(.*?).html"/s', $v,$ids);
        			$data['item']['color'][$k]['brand_id'] = $ids[1];
        			$data['item']['color'][$k]['item_id'] = $ids[2];

        			$ra2 = preg_match('/class="selected"/s', $v);
        			if($ra2 > 0){
        				$data['item']['color'][$k]['selected'] = true;
        			}else{
        				$data['item']['color'][$k]['selected'] = false;
        			}
        		}
        	}
        }
        //如果有颜色 $data['item']['color']

        $ra = preg_match('/<dt\s*class="size_name">尺码：<\/dt>/s', $end);
        if($ra > 0){
        	$ra1 = preg_match_all('/id="J_cartAdd_sizeID_(.*?)\s*<\/li>/s', $end, $sizes);
        	foreach ($sizes[0] as $key => $value) {
        		preg_match('/data-size-name="(.*?)"/s', $value, $name);
        		$data['item']['size'][$key]['name'] = $name[1];

        		preg_match('/data-id="(.*?)"/s', $value, $id);
        		$data['item']['size'][$key]['id'] = $id[1];
        		
        	}
        }
		
		$data['item']['images'] = array();
		$ra = preg_match_all('/_img"><img src="(.*?)" width="57"/s', $end, $images);
        if($ra){
			$images=$images[1];
			foreach($images as $k=>$v){
				$images[$k]=str_replace('_2.jpg','.jpg',$v);
				}
			$data['item']['images']=$images;
		}
		
        //如果有尺寸 $data['item']['size']
        $ra = preg_match('/class\s*=\s*"dc_img_con">(.*?)<\/p><\/div>/s', $end,$imgs);
        if($ra > 0){
        	$data['item']['desc'] = $imgs[1];
			$data['item']['desc']=trim(str_replace(
				array('src="//s2.vipstatic.com/img/te/blank.png"','<div class = "dc_img_detail">','</div>',"\n",'    '),
				array(' src="" ','','','',''),$data['item']['desc']));
        }

		
		if($ra){
			foreach ($all[0] as $key => $v) {
				preg_match('/class\s*=\s*"dc_table_tit">(.*?)：\s*<\/td>/s', $v,$title);
				$data['item']['detail'][$key]['title'] = $title[1];
	
				preg_match('/<\/td>\s*<td>(.*?)<\/td>/s', $v,$value);
				$data['item']['detail'][$key]['value'] = $value[1];
			}
		}
		
        $ra = preg_match('/class\s*=\s*"dc_img_detail">(.*?)<\/div>/s', $end,$img);
        if($ra > 0){
        	$data['item']['desc'] .= $img[1];
        }

        $ra = preg_match('/id="J_proParam_scroll">商品参数(.*?)<\/div>/s', $end,$detail);
        if($ra > 0){
        	$ra1 = preg_match_all('/<tr>\s*(.*?)\s*<\/tr>/s', $detail[1], $all);
        	foreach ($all[0] as $key => $v) {
        		preg_match('/class\s*=\s*"dc_table_tit">(.*?)：\s*<\/td>/s', $v,$title);
        		$data['item']['detail'][$key]['title'] = $title[1];

        		preg_match('/<\/td>\s*<td>(.*?)<\/td>/s', $v,$value);
        		$data['item']['detail'][$key]['value'] = $value[1];
        	}
        }

//http://resys.vip.com/?fields=img,agio,name,link,foreword,vipshop_price,market_price&ps=50&pid=40801361&page=100304&method=product.alsolike&styletype=C

//产品推荐 &styletype=C  &ps=50 50条结果 最后一条没用

//http://resys.vip.com/?fields=img,agio,name,link,foreword,vipshop_price,market_price&ps=20&pid=40801361&page=100307&method=user.product.alsotag&min_ps=3&max_tab=6

//用户推荐 有tab 不同品类 &min_ps=3&max_tab=6 &ps=20 32条结果

//http://resys.vip.com/?fields=img,agio,name,link,foreword,vipshop_price,market_price&ps=20&pid=40801361&page=100305&method=user.product.history

//用户浏览历史 &method=user.product.history &page=100305 产品id &pid=40801361  6条结果

        return $data;
    }


}