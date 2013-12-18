<?php


App::uses( 'TrieSplit','Lib');
/**
 * 分词组建，对内容进行分词，获取关键字
 * $obj->segment($content);
 */
class WordSegmentComponent extends Component {
	/* component configuration */
	public $name = 'WordSegmentComponent';
	
	private $dict = array();
	private $segment ;

	/**
	 * Contructor function
	 * @param Object &$controller pointer to calling controller
	 */
	public function startup($controller) {
		/**
	     * 生成关键词的数组，并缓存
	     */
		$this->dict = array();
        $this->dict = Cache::read('keyword_dict');
        if ($this->dict === false) {
            $controller->loadmodel('Keyword');
            $words = $controller->Keyword->find('all');
            foreach ($words as $val) {
                $this->dict[$val['Keyword']['id']] = $val['Keyword']['value'];
            }
            Cache::write('keyword_dict', $this->dict);
        }
        /**
	     * 生成Trie词典对象
	     */
		$this->segment = Cache::read('keyword_dict_split_object');
        if ($this->segment === false) {
            $this->segment = new TrieSplit();
            $this->segment->insertWord($this->dict);
            Cache::write('keyword_dict_split_object', $this->segment);
        }
	}

	/**
	 *
	 * @param type $content  需要分词的内容
	 */
	public function segment($content){		
        $words = $this->segment->search($content);
        $return_words = array();
        foreach ($this->dict as $key => $value) {
            if (in_array($value, $words)) {
                $return_words[$key] = $value;
            }
        }
        return $return_words;
	}

}
?>