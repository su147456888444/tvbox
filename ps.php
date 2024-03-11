<?php
error_reporting(E_ERROR | E_PARSE);//屏蔽错误
header('Content-Type: application/json');
define("YM",'https://www.aliyundrive.com/s/');//定义前缀
define('HOST','https://www.pansearch.me/');//定义常量。
$time = msectime();//13位时间戳


$ac = isset($_GET['ac']) ? $_GET['ac'] : '';
$wd = isset($_GET['wd']) ? $_GET['wd'] : '';
$play = isset($_GET['play']) ? $_GET['play'] : '';

$result = null;

switch ($ac) {
    case '':       
        if ($wd != '') {
            $result =search($wd);
        }elseif($play){
	        $result = play($play);
        }else{
            $result = home();
        }
        break;
    case 'detail':
        $result = detail();
        break;
    default:
        $result = [
            'code' => -1,
            'msg' => '错误请求'
        ];
        break;
}

echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);


function home(){
	$url = 'https://gitcafe.net/alipaper/home.json?v=1692110598065';//后面是13位时间戳，可以不用msectime()
    $html = GET($url);
	
	$json = json_decode($html, true);
    $array = $json['data'];
	
    // 将对象转换为数组，并获取所有对象的键名
    $class = array_keys((array)$array);  
	
    for($i=0;$i<count($class);$i++){
		$id = $class[$i];		
    	if(!empty($id)){
    	    $vod['type_id'] = $array[$id]['code'];
		    $vod['type_name'] = $array[$id]['name'];
			if(strstr($vod['type_name'],'软件')!==false){
				break;
			}
    	    $clas[] =$vod;
    	}
    }	
    $result['class'] = $clas;
	$result['list'] = [];
    return $result;
}

function category($t,$pg){
    $result = [
        'list' => [],
    ];
    
    $url = 'https://gitcafe.net/alipaper/home.json?v=1692110598065';//后面是13位时间戳，可以不用msectime()
    $html = GET($url);
	
	$json = json_decode($html, true);
    $array = $json['data'];
	
    // 将对象转换为数组，并获取所有对象的键名
    $class = array_keys((array)$array);

    foreach ($array[$t]['catdata'] as $row) {
        $vod = [  
            'vod_id' => 'push://'.YM.$row['alikey'],
            'vod_name' => $row['title'],
            'vod_pic' => 'https://api.isoyu.com/mm_images.php',
            'vod_remarks' => '纸条VIP'
        ];
	    
        $result['list'][] = $vod;  
    }
    return $result;
}



//详情类不需要
 function detail(){
	 $result = [
        'list' => [],
    ];
    $t = isset($_GET['t']) ? $_GET['t'] : '';
    if ($t != '') {
        $pg = intval(isset($_GET['pg']) ? $_GET['pg'] : '1');
        return category($t, $pg);
    }
		
	$wd = isset($_GET['wd']) ? $_GET['wd'] : '';
    if ($wd != '') {
        return search($wd);
    }
	
	 return $result;
	
 }
 
 //玩偶站搜索
function search($wd){
	$result = [
        'list' => [],
    ];
	
	/*取360数据*/
    require "data.php";
    $data360 = data(array("act" => "search","word" => $wd,"page" => 1));
    $list360 = $data360['list'];
    foreach($list360 as $item360){	
    	if($item360['title'] === $wd){
    		$title = $item360['title'];
    		$pic = $item360['pic'];
    		$id = $item360['id'];
    	}
    }
/*取360数据结束*/

	$wd = urlencode($wd);		        
    $api = HOST.'/_next/data/j2kDT2pUbuQX-JEO93lDw/search.json?keyword='.$wd.'&pan=aliyundrive';
    $data = GET($api);
	
    $json = json_decode($data,true);
    $list = $json['pageProps']['data']['data'];    
        if($list){
            foreach ($list as $item) {
                $picurl = $item['image'];				
				$data0 = $item['content'];
				$name = tourl($data0)[0];
				$link = tourl($data0)[1];
                $vod = array(
				    'vod_id' =>'push://'.$link,
				    'vod_name' => $name,
				    'vod_pic'=> $picurl,
				    'vod_remarks' => '盘搜索'
				);
                $vods[] = $vod;					   
			}
            $result['list'] = $vods;   
        }
	 return $result;	
}

function tourl($data){
	global $wd;
	$data = str_replace(["<span class='highlight-keyword'>","</span>","名称："],"",$data);//先去掉杂质
	$array = explode("\n",$data);
	//echo $data;
	//exit;
	$namedx = forch($array,urldecode($wd));
	$urldx = forch($array,"https://");
	if($namedx!==1000){		
		//$name = $array[$namedx];
		$name = $array[0];
		$name = str_replace(["名称：<span class='highlight-keyword'>","</span>","名称："],"",$name);
	}
	if($urldx!==1000){
		$urls = $array[$urldx];
		preg_match("/href=\"(.*?)\"/", $urls, $pp);
		$url = $pp[1];
	}	
	$newarray = array($name,$url);
	return $newarray;
}

function forch($list,$search){
    for ($i=0; $i<count($list); $i++) {//数组循环,寻找和名称一致的组。   
       if(strstr($list[$i],$search) != false){//匹配到和名称一样的组
           $index=$i;//找到名字返回名字所在的索引
	       break;
	   }else{
	      $index=1000; //没找到，返回假
	   }
	}
	return $index;
}

function GET($url) {//get函数
	$curl = curl_init();
	$header = array( 
	    "X-FORWARDED-FOR:".long2ip(mt_rand(1884815360, 1884890111)),
	     "CLIENT-IP:".long2ip(mt_rand(1884815360, 1884890111)),
	     "X-Real-IP:".long2ip(mt_rand(1884815360, 1884890111)),
	    "Connection: Keep-Alive",
	    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586"
	);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($curl, CURLOPT_HEADER,0);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	$output = curl_exec($curl);		 
	curl_close($curl); // 结束 Curl
	return $output;
}

function msectime() {//13位
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return intval($msectime);
}
