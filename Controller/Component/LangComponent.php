<?php
App::uses('I18n', 'I18n');

class LangComponent extends Component {
	public $components = array('Cookie');
	public $lang = null;
	public $fields = null;
	public $catalog = array();
	public $langFromUrl = null;
	public $langFromCookie = null;
	public $includes = array();

	//called before Controller::beforeFilter()
	function initialize(&$controller) {
		$this->I18n = $controller->I18n = I18n::getInstance();
		$this->_makeCatalog();
		$this->langFromUrl = $this->_assertLanguage(isset($controller->params['language'])? $controller->params['language'] : "");
		$this->Cookie->key = Configure::read('Security.salt');
		$this->langFromCookie = $this->_assertLanguage($this->Cookie->read('lang'));
		if (($this->lang = $this->langFromUrl) || ($this->lang = $this->langFromCookie) || ($this->lang = $this->_autoLanguage())) {
			$this->_setLanguage($controller);
		}
		Configure::write('Config.locale', $this->catalog[$this->lang]['locale']);
		$this->_updateCookie($this->lang);
		setlocale(LC_TIME, $this->catalog[$this->lang]['_locale']);
 	}

	function _setLanguage($controller) {
		$return = $this->I18n->l10n->get($this->lang);
		$this->lang = $this->I18n->l10n->lang;
		if (!$this->langFromUrl) {
			$this->_redirectToLang($controller, $this->lang);
		}
	}

	function _makeCatalog() {
		$this->I18n->l10n->default = array_shift(array_keys($this->settings));
		$_map = $this->I18n->l10n->catalog(array_keys($this->settings));
		foreach($this->settings as $lang=>$locale) {
			$this->catalog[$lang] = $_map[$lang];
			$this->catalog[$lang]['_locale'] = $locale;
			$this->includes[$this->catalog[$lang]['locale']] = $this->catalog[$lang]['language'];
		}
	}

	protected function _autoLanguage() {
		$_detectableLanguages = CakeRequest::acceptLanguage();
		foreach ($_detectableLanguages as $key => $langKey) {
			if (isset($this->catalog[$langKey])) {
				return $langKey;
			} else if (strpos($langKey, '-') !== false) {
				$langKey = substr($langKey, 0, 2);
				if (isset($this->catalog[$langKey])) {
					return $langKey;
				}
			}
		}
		return false;
	}

	function _assertLanguage($lang) {
		return array_key_exists($lang, $this->catalog) ? $lang : null;
	}
	function _updateCookie($lang) {
		if ($this->langFromCookie != $lang)
			$this->Cookie->write('lang', $lang, null, '20 days');
	}
	function _redirectToLang($controller, $lang) {
		$params = $controller->params['pass'];
		$params['language'] = $lang;
		if ($controller->name != 'CakeError')
			$controller->redirect($params);
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