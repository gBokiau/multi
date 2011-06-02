<?php
class LangComponent extends Object {
	var $components = array('Cookie');
	var $lang = null;
	var $fields = null;
	var $catalog = array();
	var $langFromUrl = null;
	var $langFromCookie = null;
	var $includes = array();

	//called before Controller::beforeFilter()
	function initialize(&$controller, $settings = array()) {
		if (!class_exists('I18n')) {
			App::import('Core', 'i18n');
		}
		$this->i18n = $controller->i18n = I18n::getInstance();
		$this->_makeCatalog($settings);
		
		$this->langFromUrl =@ $this->_assertLanguage(isset($controller->params['language'])? $controller->params['language'] : "");
		$this->Cookie->key = Configure::read('Security.salt');
		$this->langFromCookie = $this->_assertLanguage($this->Cookie->read('lang'));
		$this->controller =& $controller;
		$this->lang = Configure::read('Config.language');
		if ($this->lang == false)
			$this->_setLanguage();
		$controller->lang = $this->lang;
		Configure::write('Config.locale', $this->catalog[$this->lang]['locale']);
		$this->_updateCookie($this->lang);
		setlocale(LC_TIME, $this->catalog[$this->lang]['_locale']);
		$this->_attachHelper();
	}

	function _setLanguage() {
		$this->i18n->l10n->get($this->langFromUrl);
		$this->lang = $this->i18n->l10n->lang;

		if (!$this->langFromUrl) {
			if ($this->langFromCookie)
				$this->lang = $this->langFromCookie;
			$this->_redirectToLang($this->lang);
		}
	}
	
	function _assertLanguage($lang) {
		return array_key_exists($lang, $this->catalog) ? $lang : null;
	}
	function _updateCookie($lang) {
		if ($this->langFromCookie != $lang)
			$this->Cookie->write('lang', $lang, null, '20 days');
	}
	function _redirectToLang($lang) {
		$this->controller->params['pass']['language'] = $lang;
		if ($this->controller->name != 'CakeError')
			$this->controller->redirect($this->controller->params['pass']);
	}
	function _makeCatalog($languages) {
		$this->i18n->l10n->default = array_shift(array_keys($languages));
		foreach($languages as $lang=>$locale) {
			$this->catalog[$lang] = $this->i18n->l10n->__l10nCatalog[$lang];
			$this->catalog[$lang]['_locale'] = $locale;
			$this->includes[$this->catalog[$lang]['locale']] = $this->catalog[$lang]['language'];
		}
		$this->i18n->l10n->__l10nCatalog = $this->catalog;
	}
	function _detectFields() {
		if($this->controller->name == 'CakeError') {
			return null;
		}
		$model = $this->controller->{$this->controller->modelClass};
		$this->controller->{$this->controller->modelClass}->includes['locales'] = $this->includes;
		if(array_key_exists('Multi.TranslateAll', (array)$model->actsAs)) {
			return $model->actsAs['Multi.TranslateAll'];
		}
		return null;
	}
	function _attachHelper() {
		$fields = $this->_detectFields();
		$this->controller->helpers['Multi.Multi'] = array('locales'=>array(), 'fields'=>$fields, 'lang'=>$this->lang, 'catalog'=>$this->catalog);
		foreach($this->catalog as $lang => $locale) {
			extract($locale);
			$this->controller->helpers['Multi.Multi']['locales'][$locale] = $language;
		}
	}
	function getLocales() {
		$out = array();
		foreach($this->catalog as $lang)
			$out[] = $lang['locale'];
		return $out;
	}
	function prepopulate($data=array(), $pass=array()) {
		$add = array_keys($this->includes);
		if (count($data)) {
			$ignore = Set::classicExtract($data, '{n}.locale');
			$add = array_diff($add, $ignore);
		}
		$empty = array_merge(array('user_id'=>1, 'model'=>$this->controller->modelClass, 'page_number'=>1), $pass);
		foreach ($add as $locale) {
			$data[] = $empty + array('locale'=>$locale);
		}
		return $data;
	}
}
?>