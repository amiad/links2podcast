<?php

class PodcastCreatorFromLinks {
	private $update_hours=5;
	private $type='mp3';
	private $image;
	private $delStr;
	public function __construct($page) {
		$this->page=$page;
    	}
    	
    	public function getFeed(){
    		if ($this->get_cache()) return;
		else {
			header('Content-type: application/xml');
			echo $this->processFeed();
		}
	}
	
	public function setType($type){
		$this->type=$type;
	}
	
	public function setUpdateHours($update_hours){
		$this->update_hours=$update_hours;
	}
	
	public function setImage($image){
		$this->image=$image;
 	}
	
	public function setDelStr($str){
		$this->delStr=$str;
	}
	public function setTitle($title){
		$this->title=$title;
	}
	public function setDesc($desc){
		$this->desc=$desc;
	}
	public function setExcludeLink($str){
		$this->ExcludeLink=$str;
	}

	public function processFeed() {
		$page=$this->page;
		$type=$this->type;
		$page_str=file_get_contents($page);
		$links=$this->findLinks($page_str,$type);
		$feed_str="<?xml version=\"1.0\"  ?>
		<rss version=\"2.0\">
		<channel>
		<title>$this->item</title>
		<description>$this->desc</description>
		<link>$page</link>
		</channel>
		</rss>";
		$sxe = new SimpleXMLElement($feed_str);
		foreach ($links as $link) {
			if($this->ExcludeLink && strpos($link['name'],$this->ExcludeLink)===0) continue;
			$item=$sxe->addChild('item');
			$file = $link['url'];
			if($this->delStr) $file=str_replace($this->delStr,'',$file);
			if(strpos($file,'http://')!==0) $file=$page.'/'.$file;
			$item->addChild('title',$link['name']);
			$item->addChild('link',$file);
			$enclosure=$item->addChild('enclosure');
			$enclosure->addAttribute('url',$file);
			$enclosure->addAttribute('type',$type);
		}
		if($this->image) {
			$image=$sxe->channel->addChild('image');
			$image->addChild('url',$this->image);
			$image->addChild('title',$sxe->channel->title.' Logo');
			$image->addChild('link',$sxe->channel->link);
		}
		$xml=$sxe->asXML();
		$this->save_cache($xml);
		return $xml;
	}
    
	public function findLinks($page,$type) {
		$dom = new DOMDocument();
		@$dom->loadHTML($page);
		$xpath = new DOMXPath($dom);
		$hrefs = $xpath->evaluate("/html/body//a");
		for ($i = 0; $i < $hrefs->length; $i++) {
			$href = $hrefs->item($i);
			$url = $href->getAttribute('href');
			$name=$this->get_inner_html($href);
			if(strtolower(substr($url,-4))=='.'.$type) {
				$links[]=array('name'=>$name,'url'=>$url);
			}
		}
		return $links;
	}
	private function get_cache(){
		$feed=$this->feed;
    		$update_seconds=$this->update_hours*3600;
    		$cache_file='cache/'.md5($feed);
		if ((file_exists($cache_file)) && ((time()-filemtime($cache_file)<$update_seconds))){
			ob_clean();
    			flush();
    			header('Content-type: application/xml');
	    		readfile($cache_file);
	    		return true;
		}
		else return false;
	}
	private function save_cache($xml){
		$cache_file='cache/'.md5($this->feed);
		if(!file_exists('cache')) mkdir('cache');
		file_put_contents($cache_file,$xml);
	}
	private function get_inner_html( $node ) {
	$innerHTML= '';
	$children = $node->childNodes;
	foreach ($children as $child) {
		$innerHTML .= $child->ownerDocument->saveXML( $child );
	}
	return $innerHTML;
	} 
}
