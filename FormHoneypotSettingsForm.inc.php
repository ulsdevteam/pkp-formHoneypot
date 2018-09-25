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

	/** $var $elementNames array() */
	var $elementNames = array(
		's' => array('user', 'admin', 'form', 'tool', 'system'),
		'v' => array('Confirm', 'Validate', 'Assign', 'Agree', 'Add'),
		'p' => array('Terms', 'Options', 'Activity', 'Access')
	);

	/**
	 * Constructor
	 * @param $plugin object
	 * @param $journalId int
	 */
	function __construct($plugin, $contextId) {
		$this->_contextId = $contextId;
		$this->_plugin = $plugin;

		parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');

		$this->addCheck(new FormValidatorCustom($this, 'minimumTime', 'FORM_VALIDATOR_OPTIONAL_VALUE', 'plugins.generic.formHoneypot.manager.settings.minimumTimeNumber', create_function('$s', 'return ($s === "0" || $s > 0);')));
		$this->addCheck(new FormValidatorCustom($this, 'maximimTime', 'FORM_VALIDATOR_OPTIONAL_VALUE', 'plugins.generic.formHoneypot.manager.settings.maximumTimeNumber', create_function('$s', 'return ($s === "0" || $s > 0);')));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$plugin = $this->_plugin;

        foreach (array_keys($this->_plugin->settingNames) as $k) {
			$this->setData($k, $plugin->getSetting(CONTEXT_SITE, $k));
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
		if ($this->getData('element') === 'createNewElement') {
			$element = $this->elementNames['s'][rand(0, count($this->elementNames['s'])-1)] . $this->elementNames['v'][rand(0, count($this->elementNames['v'])-1)] . $this->elementNames['p'][rand(0, count($this->elementNames['p'])-1)];
			$plugin->updateSetting(
				$journalId,
				'customElement',
				$element,
				'string'
			);
		}
	}
	
}

?>
