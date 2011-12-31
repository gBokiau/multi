<?php
/* SVN FILE: $Id$ */
/**
 * Short description for file.
 *
 * Long description for file
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
 * @subpackage    cake.cake.libs.model.behaviors
 * @since         CakePHP(tm) v 1.2.0.4525
 * @version       $Revision$
 * @modifiedby    $LastChangedBy$
 * @lastmodified  $Date$
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * @package       cake
 * @subpackage    cake.cake.libs.model.behaviors
 */
App::uses('Translate', 'Model/Behavior');

class TranslateAllBehavior extends TranslateBehavior {
/**
 * afterFind Callback
 *
 * @param array $results
 * @param boolean $primary
 * @return array Modified results
 * @access public
 */
	function afterFind(&$model, $results, $primary) {
		$translateAll = (isset($model->translateAll) && $model->translateAll);
		$locale = $this->_getLocale($model);
		$this->runtime[$model->alias]['fields'] = array();

		if (empty($locale) || empty($results) || empty($this->runtime[$model->alias]['beforeFind'])) {
			return $results;
		}
				
		if (!is_array($locale) && !$translateAll) {
			return parent::afterFind(&$model, $results, $primary);
		}
		$beforeFind = $this->runtime[$model->alias]['beforeFind'];
		foreach ($results as $key => $row) {
			$results[$key][$model->alias]['locale'] = (is_array($locale) && !$translateAll) ? @$locale[0] : $locale;
			foreach ($beforeFind as $field) {
				$translations = array();
				foreach ($locale as $_locale) {
					$translations[$_locale] = (!empty($results[$key]['I18n__'.$field.'__'.$_locale]['content'])) ? $results[$key]['I18n__'.$field.'__'.$_locale]['content'] : '';
					unset($results[$key]['I18n__'.$field.'__'.$_locale]);
				}
				$results[$key][$model->alias][$field] = $translations;
			}
		}
		return $results;
	}

}

?>