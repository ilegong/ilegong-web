<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/9/14
 * Time: 10:44 PM
 */

class WxPaymentComponent extends Component {

    /**
     * @return Notify_pub
     */
    public function createNotify() {
        // load vendor classes
        App::import('Vendor', 'WxPayPubHelper/WxPayPubHelper');
        return new Notify_pub();

    }

    public function createJsApi() {
        // load vendor classes
        App::import('Vendor', 'WxPayPubHelper/WxPayPubHelper');
        return new JsApi_pub();
    }

} 