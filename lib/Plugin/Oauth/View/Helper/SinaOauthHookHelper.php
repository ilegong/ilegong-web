<?php

class SinaOauthHookHelper extends AppHelper {

    public $helpers = array('Html', 'Session',);

    /**
     * 在页面上显示oauth登录链接
     */
    public function oauthLoginLink() {
    	$sina = $this->Html->link(
        	$this->Html->image($this->url('/img/oauth/sina.png')), 
        	array('plugin' => 'oauth', 'controller' => 'sina', 'action' => 'login'), 
        	array('escape' => false)
        );
    	$qq = '<li>'.$this->Html->link(
	        $this->Html->image($this->url('/img/oauth/qq_login.gif')), 
	        array('plugin' => 'oauth', 'controller' => 'qq', 'action' => 'login'), 
        	array('escape' => false)
        ).'</li>';
    	$taobao = '<li>'.$this->Html->link(
	        $this->Html->image($this->url('/img/oauth/taobao_login.png')), 
	        array('plugin' => 'oauth', 'controller' => 'top', 'action' => 'login'), 
	        array('escape' => false)
        ).'</li>';
        return '<div id="auth-login-group" class="btn-group">
				    <a class="btn btn-small" href="#">第三方帐号登录</a>
				    <button class="btn btn-small dropdown-toggle" data-toggle="dropdown">
				    <span class="caret"></span>
				    </button>
				    <ul class="dropdown-menu" style="margin-top: 0px;"><li>'.$sina.$qq.$taobao.'</li></ul>
				</div>';
    }

    /**
     * 页面登录后，用户来源信息
     */
    function oauthLoggedInfo() {
        //$this->Session->read('SinaOauthKeys');
        $oauths = $this->Session->read('Auth.Oauthbind');
        if (is_array($oauths) && !empty($oauths)) {
            foreach ($oauths as $auth) {
                if ($auth['Oauthbind']['source'] == 'sina') {
                    return $this->Html->image('/img/oauth/sina_logo.png');
                }
                if ($auth['Oauthbind']['source'] == 'qq') {
                	return $this->Html->image('/img/oauth/qq_logo.gif');
                }
                if ($auth['Oauthbind']['source'] == 'top') {
                	return $this->Html->image('/img/oauth/taobao_logo.png');
                }
            }
        }
        return '';
    }

    function userLeftmenu($type) {
        if ($this->Session->read('SinaOauthKeys')) {
            ${'class_' . $type} = 'class="ui-state-active"';
            $links = '<li><h3>' . __('新浪微博') . '</h3><ul>';
            $links .= '<li ' . $class_friends . '>' . $this->Html->link('微博首页', array('plugin' => 'oauth', 'controller' => 'sina', 'action' => 'sina', 'friends')) . '</li>';
            $links .= '<li ' . $class_index . '>' . $this->Html->link('我的微博', array('plugin' => 'oauth', 'controller' => 'sina', 'action' => 'sina', 'action' => 'index', '')) . '</li>';
            $links .= '<li ' . $class_batchdelete . '>' . $this->Html->link('批量删除', array('plugin' => 'oauth', 'controller' => 'sina', 'action' => 'batchdelete', '')) . '</li>';
            $links .= '<li ' . $class_atme . '>' . $this->Html->link('@提到我的', array('plugin' => 'oauth', 'controller' => 'sina', 'action' => 'sina', 'atme')) . '</li>';
            $links .= '<li ' . $class_favs . '>' . $this->Html->link('我的收藏', array('plugin' => 'oauth', 'controller' => 'sina', 'action' => 'sina', 'favs')) . '</li>';
            $links .= '<li ' . $class_comments . '>' . $this->Html->link('我的评论', array('plugin' => 'oauth', 'controller' => 'sina', 'action' => 'sina', 'comments')) . '</li>';
            $links .= '</ul><li>';
            return $links;
        }
        if ($this->Session->read('qqOauthKeys')) {
        	${
        		'class_qq_' . $type} = 'class="ui-state-active"';
        		$links = '<li><h3>' . __('腾讯微博') . '</h3><ul style="display:none;">';
        		$links .= '<li ' . $class_qq_friends . '>' . $this->Html->link('微博首页', array('plugin' => 'oauth', 'controller' => 'qq', 'action' => 'qq', 'friends')) . '</li>';
        		$links .= '<li ' . $class_qq_index . '>' . $this->Html->link('我的微博', array('plugin' => 'oauth', 'controller' => 'qq', 'action' => 'qq', 'action' => 'index', '')) . '</li>';
        		$links .= '<li ' . $class_qq_batchdelete . '>' . $this->Html->link('批量删除', array('plugin' => 'oauth', 'controller' => 'qq', 'action' => 'batchdelete', '')) . '</li>';
        		$links .= '<li ' . $class_qq_atme . '>' . $this->Html->link('@提到我的', array('plugin' => 'oauth', 'controller' => 'qq', 'action' => 'qq', 'atme')) . '</li>';
        		$links .= '<li ' . $class_qq_favs . '>' . $this->Html->link('我的收藏', array('plugin' => 'oauth', 'controller' => 'qq', 'action' => 'qq', 'favs')) . '</li>';
        		$links .= '<li ' . $class_qq_comments . '>' . $this->Html->link('我的评论', array('plugin' => 'oauth', 'controller' => 'qq', 'action' => 'qq', 'comments')) . '</li>';
        		$links .= '</ul><li>';
        		return $links;
        }
    }

}

?>