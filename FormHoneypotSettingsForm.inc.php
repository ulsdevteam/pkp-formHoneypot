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

		$this->addCheck(new FormValidator($this, 'element', 'required', 'plugins.generic.formHoneypot.manager.settings.elementRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$journalId = $this->journalId;
		$plugin =& $this->plugin;
		$this->_data['element'] = $plugin->getSetting($journalId, 'element');
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('element'));
	}

	/**
	 * Save settings.
	 */
	function execute() {
		$plugin =& $this->plugin;
		$journalId = $this->journalId;
		$plugin->updateSetting($journalId, 'element', $this->getData('element'), 'string');
	}
	
}

?>
