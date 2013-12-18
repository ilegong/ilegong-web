<?php
/**
 * Machine Component 机器组件，
 * 获取服务器的mac地址等信息
 * @author sina
 *
 */
class MachineComponent extends Component {
	
	private $mac_array = array();
	
	public function __construct($options = array()) {        
        return parent::__construct($options);
    }
	
    public function getmac($os_type = PHP_OS){    	
    	switch ( strtolower($os_type) ){
    		case "linux":
    			$this->_forLinux();
    			break;
    		case "solaris":
    			break;
    		case "unix":
    			break;
    		case "aix":
    			break;
    		default:
    			$this->_forWindows();
    			break;
    	}
    	$temp_array = array();
    	$mac_addr = '';
    	foreach( $this->mac_array as $value ){
    		if (preg_match("/[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f]/i",$value,$temp_array )){
    			$mac_addr = $temp_array[0];
    			break;
    		}
    	}
    	unset($temp_array);
    	return $mac_addr;
    }
    
    /**
     * windows服务器下执行ipconfig命令
     */
    private function _forWindows(){
    	@exec("ipconfig /all", $this->mac_array);
    	if ( $this->mac_array )
    		return $this->mac_array;
    	else{
    		$ipconfig = $_SERVER["WINDIR"]."\system32\ipconfig.exe";
    		if ( is_file($ipconfig) )
    			@exec($ipconfig." /all", $this->mac_array);
    		else
    			@exec($_SERVER["WINDIR"]."\system\ipconfig.exe /all", $this->mac_array);
    		return $this->mac_array;
    	}
    }
    /**
     * Linux服务器下执行ifconfig命令
     */
    private function _forLinux(){
    	@exec("ifconfig -a", $this->mac_array);
    	return $this->mac_array;
    }
}