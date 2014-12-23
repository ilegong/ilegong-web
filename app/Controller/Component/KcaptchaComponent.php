<?php 

App::import('Vendor', 'kcaptcha', array('file' => 'kcaptcha'.DS.'kcaptcha.php'));
 
class KcaptchaComponent extends Component
{
    public $keyString;
    function startup(&$controller)
    {
        $this->controller = $controller;
    }

    function render()
    {
//        vendor('kcaptcha/kcaptcha');
        $kcaptcha = new KCAPTCHA();
        $this->keyString = $kcaptcha->getKeyString();
        $this->controller->Session->write('captcha', $this->keyString);

    }
}
?>
