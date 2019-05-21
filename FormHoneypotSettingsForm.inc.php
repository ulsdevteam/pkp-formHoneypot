<?php

/**
 * @file plugins/generic/formHoneypot/FormHoneypotSettingsForm.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @class FormHoneypotSettingsForm
 * @ingroup plugins_generic_formHoneypot
 *
 * @brief Form for journal managers to modify Form Honeypot plugin settings
 */


import('lib.pkp.classes.form.Form');

class FormHoneypotSettingsForm extends Form {

	/** @var $contextId int */
	var $_contextId;

	/** @var $plugin object */
	var $_plugin;


	/**
	 * Constructor
	 * @param $plugin object
	 * @param $journalId int
	 */
	function __construct($plugin, $contextId) {
		$this->_contextId = $contextId;
		$this->_plugin = $plugin;
		
		if (method_exists($plugin, 'getTemplateResource')) {
			// OJS 3.1.2 and later
			parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
		} else {
			// OJS 3.1.1 and earlier
			parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
		}


//		parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');

		$this->addCheck(new FormValidatorCustom($this, 'formHoneypotMinimumTime', FORM_VALIDATOR_OPTIONAL_VALUE, 'plugins.generic.formHoneypot.manager.settings.minimumTimeNumber', create_function('$s', 'return ($s === "0" || $s > 0);')));
		$this->addCheck(new FormValidatorCustom($this, 'formHoneypotMaximimTime', FORM_VALIDATOR_OPTIONAL_VALUE, 'plugins.generic.formHoneypot.manager.settings.maximumTimeNumber', create_function('$s', 'return ($s === "0" || $s > 0);')));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$plugin = $this->_plugin;

		foreach (array_keys($this->_plugin->settingNames) as $k) {
			$this->setData($k, $plugin->getSetting($this->_contextId, $k));
		}
	}

	/**
	 * Fetch the form.
	 * @copydoc Form::fetch()
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array_keys($this->_plugin->settingNames));
	}

	/**
	 * Save settings.
	 */
	function execute() {
		$contextId = $this->_contextId;
		foreach ($this->_plugin->settingNames as $k => $v) {
			$this->_plugin->updateSetting($contextId, $k, $this->getData($k), $v);
		}
	}
	
}

?>
