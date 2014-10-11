<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/8/14
 * Time: 9:36 PM
 */

class OauthbindComponent extends Component {

    var $modelClass;
    var $controller;
    function startup(&$controller)
    {
        $this->controller = $controller;

    }
    function shutdown()
    {

    }
    /**
     * beforeRender
     *
     * @param object $controller instance of controller
     * @return void
     */
    function beforeRender(&$controller) {
        $this->controller =& $controller;
    }
}