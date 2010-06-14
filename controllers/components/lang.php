<?php
class LangComponent extends Object {
	var $components = array('Cookie');
	var $lang = null;
	var $catalog = array();
	var $langFromUrl = null;
	var $langFromCookie = null;

	//called before Controller::beforeFilter()
	function initialize(&$controller, $settings = array()) {
		if (!class_exists('I18n')) {
			App::import('Core', 'i18n');
		}
		$this->i18n = $controller->i18n = I18n::getInstance();
		$this->_makeCatalog($settings);

		$this->langFromUrl =@ $this->_assertLanguage(isset($controller->params['language'])? $controller->params['language'] : "");
		$this->langFromCookie = $this->_assertLanguage($this->Cookie->read('lang'));
		$this->controller =& $controller;

		$this->lang = Configure::read('Config.language');
		if ($this->lang == false)
			$this->_setLanguage();
		$this->_updateCookie($this->lang);
		setlocale(LC_ALL, $this->catalog[$this->lang]['_locale']);
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
		}
		$this->i18n->l10n->__l10nCatalog = $this->catalog;
	}
	
	function getLocales() {
		$out = array();
		foreach($this->catalog as $lang)
			$out[] = $lang['locale'];
		return $out;
	}
}
?>