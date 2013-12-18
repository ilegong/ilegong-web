<?php
/**
 * TranslateHook Component
 *
 * PHP version 5
 *
 * @category Component
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class TranslateHookComponent extends Object {
/**
 * Models to be translated
 *
 * @var array
 * @access public
 */
    var $translateModels = array(
        'Node' => array(
            'title' => 'titleTranslation',
            'excerpt' => 'excerptTranslation',
            'body' => 'bodyTranslation',
        ),
        'Link' => array(
            'title' => 'titleTranslation',
        ),
        'Block' => array(
            'title' => 'titleTranslation',
            'body' => 'bodyTranslation',
        ),
    );
/**
 * Called after activating the hook in ExtensionsHooksController::admin_toggle()
 *
 * @param object $controller Controller
 * @return void
 */
    function onActivate(&$controller) {
        $controller->SaeCMS->addAco('Translate');
        $controller->SaeCMS->addAco('Translate/admin_index');
        $controller->SaeCMS->addAco('Translate/admin_edit');
        $controller->SaeCMS->addAco('Translate/admin_delete');
    }
/**
 * Called after deactivating the hook in ExtensionsHooksController::admin_toggle()
 *
 * @param object $controller Controller
 * @return void
 */
    function onDeactivate(&$controller) {
        $controller->SaeCMS->removeAco('Translate');
    }
/**
 * Called after the Controller::beforeFilter() and before the controller action
 *
 * @param object $controller Controller with components to startup
 * @return void
 */
    function startup(&$controller) {
        foreach ($this->translateModels AS $translateModel => $fields) {
            if (isset($controller->{$translateModel})) {
                $controller->{$translateModel}->Behaviors->attach('SaeCMSTranslate', $fields);
            }
        }
    }
/**
 * Called after the Controller::beforeRender(), after the view class is loaded, and before the
 * Controller::render()
 *
 * @param object $controller Controller with components to beforeRender
 * @return void
 */
    function beforeRender(&$controller) {
        $modelAliases = array_keys($this->translateModels);
        $singularCamelizedControllerName = Inflector::camelize(Inflector::singularize($controller->params['controller']));
        if (in_array($singularCamelizedControllerName, $modelAliases)) {
            Configure::write('Admin.rowActions.Translations', 'plugin:translate/controller:translate/action:index/:id/'.$singularCamelizedControllerName);
        }
    }
    
}
?>