<?php

$vancl = new VcComment();

$comment = $vancl->commentVancl('6373887','10');//5364325 //2811249
print_r($comment);
class VcComment {
    public function getCurl($id,$page){
      
        $url='http://item.vancl.com/styles/AjaxStyleAssesses.aspx?styleId='. $id .'&pageindex='. $page .'&type=0';
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

    public function commentVancl($id,$num)
    {
        
        $limit='10';
        $page=round($num/$limit);
        $end = $this->getCurl($id,$num);

        $commnets = array();
        $ra = preg_match_all('/class="plZCO">(.*?)class="Sdanpyyimg">/s', $end,$commnet);
        if($ra > 0){
            foreach ($commnet[1] as $key => $value) {
                //评论图片
                $ra = preg_match('/border="0"\s*src="(.*?)""\s*\/><\/a>/s', $value,$image);
                if($ra > 0){
                    $commnets[$key]['image'] = $image[1];
                }
                //评论名
                $ra = preg_match('/<span\s*class="blank10"><\/span>\s*<a\s*target="_blank"\s*href="(.*?)">\s*(.*?)<\/a>/s', $value,$name);
                if($ra > 0){
                    $commnets[$key]['name'] = $name[2];
                }
                //评论地区
                $ra = preg_match('/style="color: #666666;">\s*(.*?)<\/span>\s*<span\s*class="blank10">\s*<\/span>/s', $value,$location);
                if($ra > 0){
                    $commnets[$key]['location'] = $location[1];
                }
                //评论颜色尺寸
                $ra = preg_match_all('/<ul>(.*?)<p\s*class="clear">/s', $value, $select);
                if($ra > 0){
                    $ra = preg_match_all('/(<li>.*?<\/li>)/s', $select[1][0],$selects);

                    foreach ($selects[1] as $k => $v) {
                        $ra = preg_match('/<li>\s*(.*?)：<span>(.*?)<\/span><\/li>/s', $v, $sel);
                        if($ra > 0){
                            $commnets[$key]['select'][$k]['name'] = $sel[1];
                            $commnets[$key]['select'][$k]['value'] = $sel[2];
                        }
                    }
                }
                //评论内容
                $ra = preg_match('/<span>评价：<\/span>\s*<label>\s*(.*?)<\/label>/s', $value, $comment);
                if($ra > 0){
                    $commnets[$key]['comment'] = $comment[1];
                }
                //评论时间
                $ra = preg_match('/<span>评论时间\s*(.*?)<\/span>/s', $value, $time);
                if($ra > 0){
                    $commnets[$key]['time'] = $time[1];
                }
            }
        }
        return $commnets;
    }



}