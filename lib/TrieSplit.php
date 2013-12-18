<?php
/**
 * @todo Trie字典树,分词
 * Trie字典树
 *
 */
class TrieSplit
{
	private $trie; 
 
	function __construct()
	{
		 $trie = array('children' => array(),'isword'=>false);
	}
 
	/**
	 * 把词加入词典,
	 *
	 * @param String $key，
	 * @param $type为word时以字为单位建立索引，为ascii时以字节为单位建索引
	 */
	function insertWord($word='',$type='word')
	{
		if(is_array($word))
		{
			foreach($word as $w)
			{
				$this->insertWord($w);
			}
			return ;
		}
		else
		{
			$trienode = &$this->trie;
			for($i = 0;$i < strlen($word);$i++)
			{
				$character = $word[$i];
				if($type=='word')
				{
					$value = ord($word[$i]);
			        if($value > 127)
			        {
						if($value >= 192 && $value <= 223)
						{
							$character = $word[$i].$word[$i+1];
							$i++;
						}
						else if($value >= 224 && $value <= 239)
						{
							$character = $word[$i].$word[$i+1].$word[$i+2];
							$i = $i + 2;
						}
						else if($value >= 240 && $value <= 247)
						{
							$character = $word[$i].$word[$i+1].$word[$i+2].$word[$i+3];
							$i = $i + 3;
						}	
						else if($value >= 248 && $value <= 251)
						{
							$character = $word[$i].$word[$i+1].$word[$i+2].$word[$i+3];
					        $i = $i + 4;
						}
						else if($value >= 252 && $value <= 253)
						{
							$character = $word[$i].$word[$i+1].$word[$i+2].$word[$i+3].$word[$i+4];
						    		$i = $i + 5;
						}
						else
						{
							//die('Not a UTF-8 compatible string');
						}
			        }
				}
				//echo $character.'====';
				if(!isset($trienode['children'][$character]))
				{
					$trienode['children'][$character] = array('isword'=>false);
				}
				if($i == strlen($word)-1)
				{
						$trienode['children'][$character] = array('isword'=>true);
				}
				$trienode = &$trienode['children'][$character];
			}
		}
	}
 
	/**
	 * 判断参数传入的单词是否出现在词典中
	 *
	 * @param String $word
	 * @return bool true/false
	 */
	function isWord($word)
	{
		$trienode = &$this->trie;
		for($i = 0;$i < strlen($word);$i++)
		{
			$character = $word[$i];
			if(!isset($trienode['children'][$character]))
			{
				return false;
			}
			else 
			{
				//判断词结束
				if($i == (strlen($word)-1) && $trienode['children'][$character]['isword'] == true)
				{
					return true;
				}
				elseif($i == (strlen($word)-1) && $trienode['children'][$character]['isword'] == false)
				{
					return false;
				}
				$trienode = &$trienode['children'][$character];	
			}
		}
	}
 
 
	/**
	 * 在文本$text找词出现的位置
	 *
	 * @param String $text
	 * @return array array('position'=>$position,'word' =>$word);
	 */
	function search($text="",$type='word')
	{
		$textlen = strlen($text);
		$trienode = $tree = $this->trie;
		$find = array();
		$wordrootposition = 0;//词根位置
		$prenode = false;//回溯参数,当词典ab,在字符串aab中，需要把$i向前回溯一次
		$word = '';
		for ($i = 0; $i < $textlen;$i++)
		{
			$character = $text[$i];
			$step=1;
			if($type=='word')
			{
				$value = ord($text[$i]);		
		        if($value > 127)
		        {
					if($value >= 192 && $value <= 223)
					{
						$character = $text[$i].$text[$i+1];
						$i = $i + 1;$step=2;
					}
					else if($value >= 224 && $value <= 239)
					{
						$character = $text[$i].$text[$i+1].$text[$i+2];
						$i = $i + 2;$step=3;
					}
					else if($value >= 240 && $value <= 247)
					{
						$character = $text[$i].$text[$i+1].$text[$i+2].$text[$i+3];
						$i = $i + 3;$step=4;
					}	
					else if($value >= 248 && $value <= 251)
					{
						$character = $text[$i].$text[$i+1].$text[$i+2].$text[$i+3].$text[$i+4];
				        $i = $i + 4;$step=5;
					}
					else if($value >= 252 && $value <= 253)
					{
						$character = $text[$i].$text[$i+1].$text[$i+2].$text[$i+3].$text[$i+4].$text[$i+5];
					    $i = $i + 5;$step=6;
					}
					else
					{
						continue;
						//die('Not a UTF-8 compatible string');
					}
		        }
			}
			
			if(isset($trienode['children'][$character]))
			{
				$word = $word .$character;
				$trienode = $trienode['children'][$character];
				if($prenode == false)
				{
					$wordrootposition = $i; // 词的开始的位置，中间位置$prenode为true
				}
				$prenode = true;
				if($trienode['isword'])
				{
					//$find[] = array('position'=>$wordrootposition,'word' =>$word);
					if(isset($find[$word]))
					{
						$find[$word]++;
					}
					else
					{
						$find[$word]=1;
					}
				}
			}
			else 
			{
				$trienode = $tree;
				$word = '';
				if($prenode)
				{
					// $prenode为true，成功匹配了一个字或多个字，
					// 一直到最后一个没有匹配上的，退回到这个字的位置继续匹配。
					// 如匹配了中国人解放军解放全中国。匹配到“中国人解放军”会继续下一字查找字典是否存在“中国人解放军解”，没有查到时，退回到一个位置到“解”字，否则接着的解放会匹配不出来
					$i = $i - $step; //这里不回溯了，退回到最后匹配失败的位置继续匹配
					// $i = $wordrootposition + $len; 是否回溯，退回到什么位置继续开始。
					//一般应该退回到$wordrootposition开始的下一个字，继续开始查找，如匹配完“中国人”之后继续匹配“国人”
					
					$prenode = false;
				}
			}
		}
		arsort($find); // 出现频率最高的排前面
		return array_keys($find);
	}
}
/*
$trie = new TrieSplit();
$trie->insertWord('中国');
$trie->insertWord('中国人');
$trie->insertWord('伟大');
$trie->insertWord('军队');
$trie->insertWord('中国人民');
$trie->insertWord('中国人民解放军');
$trie->insertWord('解放军');
$trie->insertWord('解放');
$words = $trie->search('伟大的中国人民解放军解放了全中国,是很伟大的军队');
foreach ($words as $word)
{
	echo '位置:'.$word['position'].'-'.(strlen($word['word'])+$word['position']);
	echo '  词:'.$word['word']."\n";
}
*/