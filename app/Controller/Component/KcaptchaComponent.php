<?php 

App::import('Vendor', 'kcaptcha', array('file' => 'kcaptcha'.DS.'kcaptcha.php'));
 
class KcaptchaComponent extends Component
{
    function startup(&$controller)
    {
        $this->controller = $controller;
    }

    function render()
    {
//        vendor('kcaptcha/kcaptcha');
        $kcaptcha = new KCAPTCHA();
        $this->controller->Session->write('captcha', $kcaptcha->getKeyString());
    }
}
?>
