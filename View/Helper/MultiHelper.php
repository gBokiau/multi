<?php
/* SVN FILE: $Id$ */
/**
 * Short description for file.
 *
 * This file is application-wide helper file. You can put all
 * application-wide helper-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake
 * @since         CakePHP(tm) v 0.2.9
 * @version       $Revision$
 * @modifiedby    $LastChangedBy$
 * @lastmodified  $Date$
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::uses('AppHelper', 'View/Helper');
/**
 * This is a placeholder class.
 * Create the same file in app/app_helper.php
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       cake
 * @subpackage    cake.cake
 */
class MultiHelper extends AppHelper {
	var $helpers = array('Form');
	var $locales = array();
	var $fields = array();
	var $catalog = array();
	var $lang = null;
	function __construct(View $View, $settings = array()) {
		foreach(array_keys($settings) as $setting) {
			$this->{$setting} = $settings[$setting];
		}
		parent::__construct($View, $settings);
	}
	function inputs($fields = null, $locales = null) {
		if ($fields == null) {
			$fields = $this->fields;
		}
		if ($locales == null) {
			$locales = $this->locales;
		}
		$out = '';
		foreach ($locales as $lang => $language) {
			$Fields = array();
			$Fields['legend'] = __($language, true);
			foreach ($fields as $name => $options) {
				if (is_numeric($name) && !is_array($options)) {
					$name = $options;
					$options = array('label'=>__(Inflector::humanize(Inflector::underscore($name)), true));
				}
				$Fields["$name.$lang"] = $options;
			}
			$out .= $this->Form->inputs($Fields);
		}
		return $out;
	}
	function catalog($trim = false) {
		$out = array();
		foreach ($this->catalog as $lang => $language) {
			if ($trim && $pos = strpos($language['language'], '('))
				$language['language'] = substr($language['language'], 0, $pos);
			$out[] = array(
				'code' => $lang,
				'language' => $language['language'],
				'active' => ($lang == $this->lang)
			);
		}
		return $out;
	}
	
	function element($name, $params = array(), $loadHelpers = false) {
		return $this->_View->element($name.'/'.$this->lang, $params, $loadHelpers);
	}
}
?>