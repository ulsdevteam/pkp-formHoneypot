<?php

/**
 * @file plugins/generic/formHoneypot/FormHoneypotPlugin.inc.php
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @class FormHoneypotPlugin
 * @ingroup plugins_generic_formHoneypot
 *
 * @brief Form Honeypot plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class FormHoneypotPlugin extends GenericPlugin {

	/**
	 * @var $availableElements array()
	 *  This array this the possible input elements
	 */
	public $availableElements = array(
			'userUrl' => 'user.url',
			'phone' => 'user.phone',
			'fax' => 'user.fax',
			'gender' => 'user.gender',
			'mailingAddress' => 'common.mailingAddress',
			'affiliation' => 'user.affiliation',
			'signature' => 'user.signature',
			'biography' => 'user.biography',
	);
	
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled()) {
			// Attach to the page footer
			HookRegistry::register('Templates::Common::Footer::PageFooter', array($this, 'insertHtml'));
			// Attach to the registration form validation
			HookRegistry::register('registrationform::validate', array($this, 'validateHoneypot'));
		}
		return $success;
	}
	
	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.generic.formHoneypot.displayName');
	}

	/**
	 * Get a description of the plugin.
	 * @return String
	 */
	function getDescription() {
		return __('plugins.generic.formHoneypot.description');
	}

	/**
	 * Set the page's breadcrumbs, given the plugin's tree of items
	 * to append.
	 * @param $isSubclass boolean
	 */
	function setBreadcrumbs($isSubclass = false) {
		$templateMgr =& TemplateManager::getManager();
		$pageCrumbs = array(
			array(
				Request::url(null, 'user'),
				'navigation.user'
			),
			array(
				Request::url(null, 'manager'),
				'user.role.manager'
			)
		);
		if ($isSubclass) {
			$pageCrumbs[] = array(
				Request::url(null, 'manager', 'plugins'),
				'manager.plugins'
			);
			$pageCrumbs[] = array(
				Request::url(null, 'manager', 'plugins', 'generic'),
				'plugins.categories.generic'
			);
		}

		$templateMgr->assign('pageHierarchy', $pageCrumbs);
	}


	/**
	 * Display verbs for the management interface.
	 * @return array of verb => description pairs
	 */
	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array('settings', __('manager.plugins.settings'));
		}
		return parent::getManagementVerbs($verbs);
	}

	/**
	 * Insert Form Honeypot page tag to footer, if page is the user registration
	 * @param $hookName string Name of hook calling function
	 * @param $params array of smarty and output objects
	 * @return boolean
	 */
	function insertHtml($hookName, $params) {
		$output =& $params[2];
		$templateMgr =& TemplateManager::getManager();
		
		// journal is required to retreive settings
		$currentJournal = $templateMgr->get_template_vars('currentJournal');
		// element is required to set the honeypot
		if (isset($currentJournal)) {
			$element = $this->getSetting($currentJournal->getId(), 'element');
		}
		// only operate on user registration
		$page = Request::getRequestedPage();
		$op = Request::getRequestedOp();
		if (isset($element) && $page === 'user' && substr($op, 0, 8) === 'register') {
			$templateMgr->assign('element', $element);
			$output .= $templateMgr->fetch($this->getTemplatePath() . 'pageTagScript.tpl');
		}
		return false;
	}

	/**
	 * Add honeypot validation to the user registration form
	 * @param $hookName string Name of hook calling function
	 * @param $params array of field, requirement, and message
	 * @return boolean
	 */
	function validateHoneypot($hookName, $params) {
		$journal =& Request::getJournal();
		if (isset($journal)) {
			$element = $this->getSetting($journal->getId(), 'element');
		}
		$form = $params[0];
		// If we have an element selected as a honeypot, check it 
		if (isset($element) && isset($form)) {
			$value = $form->getData($element);
			// Is it localized?
			if (is_array($value)) {
				$value = implode('', array_values($value));
			}
			// If not empty, flag an error
			if (!empty($value)) {
				$message = __('plugins.generic.formHoneypot.doNotUseThisField', array('element' => __($this->availableElements[$element])));
				$form->addError(
					$element,
					$message
				);
			}
		}
		return false;
	}

	/**
	 * Execute a management verb on this plugin
	 * @param $verb string
	 * @param $args array
	 * @param $message string Result status message
	 * @param $messageParams array Parameters for the message key
	 * @return boolean
	 */
	function manage($verb, $args, &$message, &$messageParams) {
		if (!parent::manage($verb, $args, $message, $messageParams)) {
			// If enabling this plugin, go directly to the settings
			if ($verb == 'enable') {
				$verb = 'settings';
			} else {
				return false;
			}
		}

		switch ($verb) {
			case 'settings':
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
				$journal =& Request::getJournal();

				$this->import('FormHoneypotSettingsForm');
				$form = new FormHoneypotSettingsForm($this, $journal->getId());
				// This assigns select options
				$templateMgr->assign('elementOptions', $this->availableElements);
				if (Request::getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						$user =& Request::getUser();
						import('classes.notification.NotificationManager');
						$notificationManager = new NotificationManager();
						$notificationManager->createTrivialNotification($user->getId());
						Request::redirect(null, 'manager', 'plugins', 'generic');
						return false;
					} else {
						$this->setBreadCrumbs(true);
						$form->display();
					}
				} else {
					$this->setBreadCrumbs(true);
					$form->initData();
					$form->display();
				}
				return true;
			default:
				// Unknown management verb
				assert(false);
				return false;
		}
	}
	
}
?>
