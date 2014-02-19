<?php
/**
 * HTML Parser
 *
 * @author Ciprian Mocanu <http://mbe.ro/> <ciprian@mbe.ro>
 *
 * Class that transforms html to array as best as it's able
 */
class htmlParser {

	//your very own separator
	//do not enter characters such as < or >
	private $separator = '-{}-';
	//the tags that don't have any innerHTML in them
	//feel free to add some if I missed any
	private $singleTags = 'meta|img|hr|br|link|!--|!DOCTYPE|input';

	//-- Don't edit below this --

	private $html,$level;
	public $levelArray;

	function __construct($html='') {
		$this->html=$this->removeWhiteSpace($html);
		$this->level=-1;
		$this->levelArray=array();
	}
	function __destruct() {
		//nothing yet;
	}
	private function getElement($value) {
		$ar = explode($this->separator,$value);
		$ar = explode('-',$ar[1]);
		return $this->levelArray[$ar[0]][$ar[1]];
	}
	private function parseToHTML($str,$level) {
		$ar=$this->getArrayOfReplacements($str);
		foreach ($ar as $item) {
			$elem = $this->getElement($item);
			$str=str_replace($item,($level==0?$elem['htmlText']:'<'.$elem['tag'].$elem['attr'].'>'.$elem['htmlText'].'</'.$elem['tag'].'>'),$str);
		}
		return $str;
	}
	private function replaceSingleTags() {
		//tags like img, input etc
		$result=preg_match_all('/<('.$this->singleTags.')(.[^><]*)?>/is', $this->html, $m);
		if ($result>0) {
			foreach ($m[0] as $id => $value) {
				$this->html = str_replace($value,'',$this->html);
			}
		}
	}
	private function replaceSimpleTags() {
		//tags that only have text in them (no other content)
		$result=preg_match_all('/<(.[^\s]*)(.[^><]*)?>(.[^<]*)?<\/\1>/is', $this->html, $m);
		if ($result>0) {
			$this->level++;
			$oneLevel=array();
			foreach ($m[0] as $id => $value) {
				if ($this->level==0) $htmlText=$value;
				else $htmlText=$this->parseToHTML($m[3][$id],$this->level-1);

				$oneLevel []= array('str' => $value, 'rep' => $this->separator.$this->level.'-'.$id.$this->separator, 'tag' => $m[1][$id], 'level' => $this->level, 'text' => $m[3][$id], 'attr' => $m[2][$id] , 'htmlText' => $htmlText);

				$this->html = str_replace($value,$this->separator.$this->level.'-'.$id.$this->separator,$this->html);
			}
			$this->levelArray [$this->level] = $oneLevel;
		}
	}
	private function replaceRemainingTags() {
		//tags that remain after everything
		$result=preg_match_all('/<(.[^\s]*)(.[^><]*)?>(.*)?<\/\1>/is', $this->html, $m);
		if ($result>0) {
			$this->level++;
			$oneLevel=array();
			foreach ($m[0] as $id => $value) {
				if ($this->level==0) $htmlText=$m[3][$id];
				else $htmlText=$this->parseToHTML($m[3][$id],$this->level-1);

				$oneLevel []= array('str' => $value, 'rep' => $this->separator.$this->level.'-'.$id.$this->separator, 'tag' => $m[1][$id], 'level' => $this->level, 'text' => $m[3][$id], 'attr' => $m[2][$id] , 'htmlText' => $htmlText);

				$this->html = str_replace($value,$this->separator.$this->level.'-'.$id.$this->separator,$this->html);
			}
			$this->levelArray [$this->level] = $oneLevel;
		}
	}
	private function existSimpleTags() {
		$result=preg_match('/<(.[^\s]*)(.[^><]*)?>(.[^<]*)?<\/\1>/is', $this->html);
		return $result>0;
	}
	private function existSingleTags() {
		$result=preg_match('/<('.$this->singleTags.')(.[^><]*)?>/is', $this->html);
		return $result>0;
	}
	private function removeWhiteSpace ($string) {
		$string = str_replace(array("\n","\r",'&nbsp;',"\t"),'',$string);
		return preg_replace('|  +|', ' ', $string);
	}
	public function toArray($html='') {

		//first part: coding
		if ($html!='') {
			$this->html = $this->removeWhiteSpace($html);
		}
		while ($this->existSimpleTags() || $this->existSingleTags()) {
			$this->replaceSingleTags();
			$this->replaceSimpleTags();
		}
		$this->replaceRemainingTags();

		//now decoding
		$ar=$this->getArray($this->html);

		return $ar;
	}
	private function getArrayOfReplacements($str) {
		$final=array();
		$ar=explode($this->separator,$str);
		for ($i=0;$i<(count($ar)-1)/2;$i++) {
			$final []= $this->separator.$ar[$i*2+1].$this->separator;
		}
		return $final;
	}
	private function startsWithText($str) {
		$first=substr(trim(str_replace(array("\n","\r"),'',$str)),0,1);
		if ($first=='<' || $first=='>') return false;
		return true;
	}
	private function strInArray($array,$str) {
		foreach ($array as $item) {
			if (strpos($str,$item)!==false)
				return true;
		}
		return false;
	}
	private function getArray($html, $father='') {
		$final=array();
		if (strpos($html,$this->separator)!==false) {
			$r=$this->getArrayOfReplacements($html);
			foreach ($r as $i) {

				$ar = explode($this->separator,$i);
				$ar = explode('-',$ar[1]);
				$elem = $this->levelArray[$ar[0]][$ar[1]];
				$this->levelArray[$ar[0]][$ar[1]]['father'] = $father;

				$final []= array( 'tag' => $elem['tag'], 'innerHTML' => $elem['htmlText'], 'repl' => $elem['rep'],'stratr' => $elem['attr'], 'level' => $elem['level'], 'father' => $father, 'childNodes' => $this->getArray($elem['text'],$i));
			}
		}
		return $final;
	}
	public function loadNode($rep) {
		$elem = $this->getElement($rep);
		return array( 'tag' => $elem['tag'], 'innerHTML' => $elem['htmlText'], 'repl' => $elem['rep'],'stratr' => $elem['attr'], 'level' => $elem['level'], 'father' => $elem['father']);
	}
}