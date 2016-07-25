<?php
$id=isset($_GET['id'])?$_GET['id']:'';
if(!$id) $id=isset($_POST['id'])?$_POST['id']:'';

#测试加值
$id = 6373887;

if($id){
	$vancl = new VcData();
	$item = $vancl->itemVancl($id);
	echo '<pre>';print_r($item);echo '</pre>';
	#echo gzcompress(serialize($item));
}
class VcData{
    public function getCurl($id){
        $url='http://item.vancl.com/'.$id.'.html';
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

    public function itemVancl($id){
        $end = $this->getCurl($id);
        $data = array();
        //产品标题
        $ra = preg_match('/<title>(.*?)- VANCL 凡客诚品<\/title>/s', $end , $title);
        if($ra > 0){
            $data['title'] = $title[1];
        }
        //产品关键词
        $ra = preg_match('/meta\s*name="keywords"\s*content="(.*?)"\s*\/>/s', $end, $keywords);
        if($ra > 0){
            $data['keywords'] = $keywords[1];
        }
        //产品简介
        $ra = preg_match('/meta\s*name="description"\s*content="(.*?)"\s*\/>/s', $end, $description);
        if($ra > 0){
            $data['description'] = $description[1];
        }
        //店铺
        $ra = preg_match('/name="item-item-store-0"\s*href="(.*?)"\s*target="_blank">(.*?)<\/a>/s', $end, $shop);
        if($ra > 0){
            $data['shop_link'] = $shop[1];
            $data['shop_name'] = $shop[2];
        }
        //品牌
        $ra = preg_match('/class=\'track\'\s*name=\'item-item-brand-0\'\s*href="(.*?)"\s*target="_blank">(.*?)<\/a>/s', $end, $brands);
        if($ra > 0){
            $data['brand_link'] = $brands[1];
            $data['brand_name'] = $brands[2];
        }
        //折扣价
        $ra = preg_match('/(?:(?:class="tehuiMoney"\s*>\s*<span>￥<strong>)|(?:class="tehuiMoney"\s*>\s*售价：<span>￥<strong>))([\d\.]+)/s', $end, $prom_price);
        if($ra > 0){
            $data['prom_price'] = $prom_price[1];
        }
        //原价
        $ra = preg_match('/class="MSpriceArea">\s*<span>市场价：￥<strong>([\d\.]+)<\/strong><\/span>/s', $end, $price);
        if($ra > 0){
            $data['price'] = $price[1];
        }
		#取特惠价,如果有特惠价，则折扣价即为特惠价 2015-1-10
        $ra = preg_match('/(?:(?:特惠价：<span>￥<strong>))([\d\.]+)/s', $end,$prom_price);
        if($ra > 0){
			$data['prom_price'] = $prom_price[1];
        }
		#断码价：，如有断码价，则不用特惠价
		$ra = preg_match('/(?:(?:断码价：<span>￥<strong>))([\d\.]+)/s', $end,$prom_price);
        if($ra > 0){
			$data['prom_price'] = $prom_price[1];
        }		
	
        //评论数
        $ra = preg_match('/name="item-item-select-tofeedback"\s*class="track">\(([\d\.]+)人已评论\)<\/a>/s', $end, $comment_num);
        if($ra > 0){
            $data['comment_num'] = $comment_num[1];
        }
        //尺寸
        $ra = preg_match('/class="selSize"(.*?)class="goodsNum"/s', $end, $size_data);
        if($ra > 0){

            $ra = preg_match_all('/(<li.*?<\/li>)/s', $size_data[1], $sizes);
            if($ra > 0){
                foreach ($sizes[1] as $k => $v) {
                    $ra = preg_match('/onclick="ChooseThisSize\(this,\'(.*?)\',(.*?)\)"\s*name="(.*?)"\s*>\s*<p>\s*(.*?)<\/p>/s', $v, $size);
                    if($ra > 0){
                        $data['size'][$k]['id'] = $size[1];
                        $data['size'][$k]['quantity'] = $size[2];
                        $data['size'][$k]['name'] = $size[4];
                    }
                }
            }
        }

        //http://i.vanclimg.com/joinimages.ashx?d=/36/product/&href=2/8/1/281124/78331,2/8/1/281124/87317,2/8/1/281124/96066
        //产品颜色配图地址 加载在CSS里
        //http://i.vanclimg.com/joinimages.ashx?d=/尺寸 36 & 70 等/product/&href=2/8/1/281124/78331,2/8/1/281124/87317,2/8/1/281124/96066
        $ra = preg_match('/class="selColor"(.*?)<\/ul>/s', $end, $color_data);
        if($ra > 0){

            $ra = preg_match_all('/(<li.*?<\/li>)/s', $color_data[1], $colors);
            if($ra > 0){
			 	#$ra = preg_match('/type="text\/css">.SpriteColors{background-image:\s*url\((.*?)\);/s', $end,$color_img);
				#2015-1-10 修改规则，并移动位置，发现抓取不到图片路径,如。http://item.vancl.com/0786773.html#ref=s-s-c_rs_27532-1_1-0786773_Sort10_qb_000-v
				$ra = preg_match('/\.SpriteColors{background-image: url\((.*?)\);/s',$end,$color_img);				
				if($ra>0){
					$data['colorimg']= $color_img[1];
				}  
                foreach ($colors[1] as $k => $v) {
					#2015-1-10 添加style匹配
                    $ra = preg_match('/href=\'(.*?)\'\s*>\s*<span\s*class="SpriteColors" style="(.*?)">&nbsp;<\/span>\s*<p>\s*(.*?)<\/p>/s', $v, $color);
                    if($ra > 0){
                        $data['color'][$k]['height'] = $k * 36; 
                        $data['color'][$k]['id'] = substr($color[1],0,strpos($color[1],'.html'));#修改ID值
						$data['color'][$k]['style'] = $color[2];
                        $data['color'][$k]['name'] = $color[3];
                    }
                }
            }
        }
        //http://p2.vanclimg.com/product/2/8/1/2811247/big/2811247-0md201411022235288331.jpg
        //产品大图 中图 小图 三种尺寸 big / mid /small
        $ra = preg_match_all('/class="SpriteSmallImgs"\s*name="(.*?)"/s', $end,$images);

        if($ra > 0){
            foreach ($images[1] as $key => $value) {
                $data['images'][$key]['small'] = $value;
                $data['images'][$key]['big'] = str_replace('small','big',$value);
                $data['images'][$key]['mid'] = str_replace('small','mid',$value);
            }
        }
        //产品描述
        $ra = preg_match('/class="productMS"\s*style="line-height:\s*20px">(.*?)<\/p>/s', $end,$desc);
        if($ra > 0){
            $data['desc'] = str_replace(' ','',$desc[1]);
        }
        //产品属性
        $ra = preg_match('/class="dpShuXing">(.*?)<\/ul>/s', $end,$atts);

        if($ra > 0){

            $ra = preg_match_all('/(<li.*?<\/li>)/s', $atts[1], $att);

            if($ra > 0){
                foreach ($att[1] as $key => $value) {
                    $ra = preg_match('/title="点击查看同类商品"><span>(.*?)：<\/span><a\s*class=\'track\'\s*name=\'item-item-info-attrib\'\s*href=\'(.*?)\'\s*target=\'_blank\'>(.*?)<\/a><\/li>/s', $value, $v);
                    if($ra > 0){
                        $data['att'][$key]['name'] =strip_tags($v[1]);
                        $data['att'][$key]['value'] = strip_tags($v[3]);
                    }
                    
                }
            }
        }
        //尺寸表
        $ra = preg_match('/(class="sizeList">.*?<\/TABLE>)/s', $end,$sizeList);
        if($ra > 0){
            $data['sizelist'] = $sizeList[1];
        }else{
            $data['sizelist'] = '';
        }
        //产品详情
        $ra = preg_match('/(class="editarea">.*?<\/TABLE>)/s', $end,$editArea);
        if($ra > 0){
            $ra = preg_match_all('/original="(.*?\.jpg)"/s', $editArea[1], $area_imgs);
            if($ra > 0){
                foreach ($area_imgs[1] as $key => $value) {
                    $data['area_imgs'] .= '<img src="'.$value.'" /></br>';
                }
            }
           
        }else{
            $data['area_imgs'] = '';
        }
        //好评率
        $ra = preg_match('/<dt>好评率<em>(.*?)%<\/em><\/dt>/s', $end, $rate);
        if($ra > 0){
            $data['rate'] = $rate[1];
        }
        //评论ID
        $ra = preg_match('/href="\/\/my.vancl.com\/comment\/Appraisetransfer\/(.*?)"\s*name="item-item-comment-mine"\s*target="_blank">我要评论<\/a><\/span>/s', $end, $comment_id);
        if($ra > 0){
            $data['comment_id'] = $comment_id[1];
        }


        return $data;
    }



}