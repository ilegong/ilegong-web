<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/5/14
 * Time: 4:17 PM
 */

class PysAuthenticate extends FormAuthenticate {

    public function __construct(ComponentCollection $collection, $settings) {
        parent::__construct($collection, $settings);
        $this->settings['fields']['username'] = 'mobilephone';
    }

    public function authenticate(CakeRequest $request, CakeResponse $response) {
        $request->data['User']['mobilephone'] = $request->data['User']['username'];
        $result = parent::authenticate($request, $response);
        unset($request->data['User']['mobilephone']);
        return $result;
    }

}