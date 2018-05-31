<?php

/**
 * @file plugins/generic/formHoneypot/FormHoneypotSettingsForm.inc.php
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @class FormHoneypotSettingsForm
 * @ingroup plugins_generic_formHoneypot
 *
 * @brief Form for journal managers to modify Form Honeypot plugin settings
 */


import('lib.pkp.classes.form.Form');

class FormHoneypotSettingsForm extends Form {

	/** @var $journalId int */
	var $journalId;

	/** @var $plugin object */
	var $plugin;

	/**
	 * Constructor
	 * @param $plugin object
	 * @param $journalId int
	 */
	function FormHoneypotSettingsForm(&$plugin, $journalId) {
		$this->journalId = $journalId;
		$this->plugin =& $plugin;
		
		parent::Form($plugin->getTemplatePath() . 'settingsForm.tpl');

		$this->addCheck(new FormValidatorCustom($this, 'minimumTime', 'optional', 'plugins.generic.formHoneypot.manager.settings.minimumTimeNumber', create_function('$s', 'return ($s === "0" || $s > 0);')));
		$this->addCheck(new FormValidatorCustom($this, 'maximimTime', 'optional', 'plugins.generic.formHoneypot.manager.settings.maximumTimeNumber', create_function('$s', 'return ($s === "0" || $s > 0);')));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$journalId = $this->journalId;
		$plugin =& $this->plugin;
		foreach (array_keys($this->plugin->settingNames) as $k) {
			$this->_data[$k] = $plugin->getSetting($journalId, $k);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array_keys($this->plugin->settingNames));
	}

	/**
	 * Save settings.
	 */
	function execute() {
		$plugin =& $this->plugin;
		$journalId = $this->journalId;
		foreach ($this->plugin->settingNames as $k => $v) {
			$plugin->updateSetting($journalId, $k, $this->getData($k), $v);
		}
	}
	
}

?>
