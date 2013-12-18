<?php
/**
 * Translate Controller
 *
 * PHP version 5
 *
 * @category Controller
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class TranslateController extends TranslateAppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
    var $name = 'Translate';
/**
 * Models used by the Controller
 *
 * @var array
 * @access public
 */
    var $uses = array(
        'Setting',
        'Language',
    );

    function admin_index($id = null, $modelAlias = null) {
        if ($id == null || $modelAlias == null) {
            $this->Session->setFlash(__('Invalid ID.', true));
            $this->redirect(array(
                'plugin' => null,
                'controller' => Inflector::pluralize($modelAlias),
                'action' => 'index',
            ));
        }

        if (!isset($this->SaeCMS->TranslateHook->translateModels[$modelAlias])) {
            $this->Session->setFlash(__('Invalid model.', true));
            $this->redirect(array(
                'plugin' => null,
                'controller' => Inflector::pluralize($modelAlias),
                'action' => 'index',
            ));
        }

        $model =& ClassRegistry::init($modelAlias);
        $model->Behaviors->attach('SaeCMSTranslate', $this->SaeCMS->TranslateHook->translateModels[$modelAlias]);
        $record = $model->findById($id);
        if (!isset($record[$modelAlias]['id'])) {
            $this->Session->setFlash(__('Invalid record.', true));
            $this->redirect(array(
                'plugin' => null,
                'controller' => Inflector::pluralize($modelAlias),
                'action' => 'index',
            ));
        }
        $this->pageTitle = __('Translations: ', true) . $record[$modelAlias]['title'];

        $runtimeModel =& $model->translateModel();
        $runtimeModelAlias = $runtimeModel->alias;
        $translations = $runtimeModel->find('all', array(
            'conditions' => array(
                $runtimeModelAlias.'.model' => $modelAlias,
                $runtimeModelAlias.'.foreign_key' => $id,
                $runtimeModelAlias.'.field' => 'title',
            ),
        ));

        $this->set(array('runtimeModelAlias'=>$runtimeModelAlias, 'translations'=>$translations, 'record'=>$record, 'modelAlias'=>$modelAlias, 'id'=>$id));
    }

    function admin_edit($id = null, $modelAlias = null) {
        if (!$id && empty($this->data)) {
            $this->Session->setFlash(__('Invalid ID.', true));
            $this->redirect(array(
                'plugin' => null,
                'controller' => Inflector::pluralize($modelAlias),
                'action' => 'index',
            ));
        }

        if (!isset($this->params['named']['locale'])) {
            $this->Session->setFlash(__('Invalid locale', true));
            $this->redirect(array(
                'plugin' => null,
                'controller' => Inflector::pluralize($modelAlias),
                'action' => 'index',
            ));
        }

        $language = $this->Language->find('first', array(
            'conditions' => array(
                'Language.alias' => $this->params['named']['locale'],
                'Language.status' => 1,
            ),
        ));
        if (!isset($language['Language']['id'])) {
            $this->Session->setFlash(__('Invalid Language', true));
            $this->redirect(array(
                'plugin' => null,
                'controller' => Inflector::pluralize($modelAlias),
                'action' => 'index',
            ));
        }

        $model =& ClassRegistry::init($modelAlias);
        $model->Behaviors->attach('SaeCMSTranslate', $this->SaeCMS->TranslateHook->translateModels[$modelAlias]);
        $record = $model->findById($id);
        if (!isset($record[$modelAlias]['id'])) {
            $this->Session->setFlash(__('Invalid record.', true));
            $this->redirect(array(
                'plugin' => null,
                'controller' => Inflector::pluralize($modelAlias),
                'action' => 'index',
            ));
        }

        $this->pageTitle  = __('Translate content:', true) . ' ';
        $this->pageTitle .= $language['Language']['title'] . ' (' . $language['Language']['native'] . ')';

        $model->id = $id;
        $model->locale = $this->params['named']['locale'];
        $fields = $model->getTranslationFields();
        if (!empty($this->data)) {
            if ($model->saveTranslation($this->data)) {
                $this->Session->setFlash(__('Record has been translated', true));
                $this->redirect(array(
                    'action' => 'index',
                    $id,
                    $modelAlias,
                ));
            } else {
                $this->Session->setFlash(__('Record could not be translated. Please, try again.', true));
            }
        }
        if (empty($this->data)) {
            $this->data = $model->read(null, $id);
        }
        $this->set(array('fields'=>$fields, 'language'=>$language, 'modelAlias'=>$modelAlias, 'id'=>$id));
    }

    function admin_delete($id = null, $modelAlias = null, $locale = null) {
        if ($locale == null || $id == null) {
            $this->Session->setFlash(__('Invalid Locale or ID', true));
            $this->redirect(array(
                'plugin' => null,
                'controller' => Inflector::pluralize($modelAlias),
                'action' => 'index',
            ));
        }

        if (!isset($this->SaeCMS->TranslateHook->translateModels[$modelAlias])) {
            $this->Session->setFlash(__('Invalid model.', true));
            $this->redirect(array(
                'plugin' => null,
                'controller' => Inflector::pluralize($modelAlias),
                'action' => 'index',
            ));
        }

        $model =& ClassRegistry::init($modelAlias);
        $model->Behaviors->attach('SaeCMSTranslate', $this->SaeCMS->TranslateHook->translateModels[$modelAlias]);
        $record = $model->findById($id);
        if (!isset($record[$modelAlias]['id'])) {
            $this->Session->setFlash(__('Invalid record.', true));
            $this->redirect(array(
                'plugin' => null,
                'controller' => Inflector::pluralize($modelAlias),
                'action' => 'index',
            ));
        }

        $runtimeModel =& $model->translateModel();
        $runtimeModelAlias = $runtimeModel->alias;
        if ($runtimeModel->deleteAll(array(
                $runtimeModelAlias.'.model' => $modelAlias,
                $runtimeModelAlias.'.foreign_key' => $id,
                $runtimeModelAlias.'.locale' => $locale,
            ))) {
            $this->Session->setFlash(__('Translation for the locale deleted successfully.', true));
        } else {
            $this->Session->setFlash(__('Translation for the locale could not be deleted.', true));
        }

        $this->redirect(array(
            'action' => 'index',
            $id,
            $modelAlias,
        ));
    }

}
?>