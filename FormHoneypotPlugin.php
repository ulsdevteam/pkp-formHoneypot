<?php

/**
 * @file plugins/generic/formHoneypot/FormHoneypotPlugin.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @class FormHoneypotPlugin
 * @ingroup plugins_generic_formHoneypot
 *
 * @brief Form Honeypot plugin class
 */
namespace APP\plugins\generic\FormHoneypot;

use PKP\core\JSONMessage;
use PKP\plugins\Hook;
use PKP\plugins\GenericPlugin;
use PKP\config\Config;
use PKP\core\PKPApplication;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
//use PKP\facades\Locale;
use PKP\session\SessionManager;
use APP\core\Application;
use APP\core\Request;
use APP\plugins\generic\formHoneypot\FormHoneypotSettingsForm;
use APP\template\TemplateManager;


class FormHoneypotPlugin extends GenericPlugin {

	/**
	 * @var $settingNames array()
	 * This array represents the fields on the settings form
	 */
	public $settingNames = array(
		'formHoneypotMinimumTime' => 'int',
		'formHoneypotMaximumTime' => 'int',
	);

	/**
	 * @var $formTimerSetting string
	 * This is the name of the setting used to track a users time during registration
	 */
	public $formTimerSetting = 'registrationTimer';

	/** $var $elementNames array() */
	var $elementNames = array(
		's' => array('user', 'admin', 'form', 'tool', 'system'),
		'v' => array('Confirm', 'Validate', 'Assign', 'Agree', 'Add'),
		'p' => array('Terms', 'Options', 'Activity', 'Access', 'URL', 'Link')
	);

	/**
	 * @copydoc Plugin::isSitePlugin()
	 */
	function isSitePlugin() {
		return true;
	}

	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;

		if ($success && $this->getEnabled($mainContextId)) {

			// Attach to the page footer
			Hook::add('Templates::Common::Footer::PageFooter', array($this, 'insertTag'));
			// Attach to the registration form validation
			Hook::add('registrationform::validate', array($this, 'validateHoneypot'));
			// Attach to the registration form display
			Hook::add('registrationform::display', array($this, 'initializeTimer'));
			// Add custom field if desired
			Hook::add('TemplateManager::display', array($this, 'handleTemplateDisplay'));
			Hook::add('registrationform::readuservars', array($this, 'handleUserVar'));
			$element = $this->getSetting(PKPApplication::CONTEXT_SITE, 'customElement');
			if(!$element) {
				// generate new form field
				$this->updateSetting(PKPApplication::CONTEXT_SITE, 'customElement', $this->generateElementName());
			}

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
	function insertTag($hookName, $args) {
		$templateMgr = TemplateManager::getManager(Application::get()->getRequest());//Application::get()->getRequest()); 

		$element = $this->getSetting(PKPApplication::CONTEXT_SITE, 'customElement');

		// only operate on user registration
		if (method_exists('Application', 'get')) {
			// OJS 3.2 and later
			$request = Application::get()->getRequest(); //may need to move above
			$page = $request->getRequestedPage();
			$op = $request->getRequestedOp();
		}
		// } else {
		// 	// OJS 3.1.2 and earlier
		// 	$page = Request::getRequestedPage();
		// 	$op = Request::getRequestedOp();
		// }
		
		if (isset($element) && $page === 'user' && substr($op, 0, 8) === 'register') {
			$templateMgr->assign('element', $element);

			// Testing version once for conditionals below
			if (method_exists($this, 'getTemplateResource')) {
				// OJS 3.1.2 and later
				$output =& $args[2];
				$output .= $templateMgr->fetch($this->getTemplateResource('pageTagScript.tpl'));
			} else {
				// OJS 3.1.1 and earlier 3.x releases
				// true passed as the fourth argument causes the template manager to display the resource passed as argument 1.
				$templateMgr->fetch($this->getTemplatePath() . 'pageTagScript.tpl', null, null, true);
			}
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
		
		$element = $this->getSetting(PKPApplication::CONTEXT_SITE, 'customElement');
		$minTime = $this->getSetting(PKPApplication::CONTEXT_SITE, 'formHoneypotMinimumTime');
		$maxTime = $this->getSetting(PKPApplication::CONTEXT_SITE, 'formHoneypotMaximumTime');
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
				$elementName = 'plugins.generic.formHoneypot.leaveBlank';
				$message = __('plugins.generic.formHoneypot.doNotUseThisField', array('element' => __($elementName)));
				$form->addError(
					$element,
					$message
				);
			}
		}
		if ($form && $form->isValid() && ($minTime > 0 || $maxTime > 0)) {
			// Get the initial access to this form within this session
			$sessionManager = SessionManager::getManager(Application::get()->getRequest());
			$session = $sessionManager->getUserSession();
			$started = $session->getSessionVar($this->getName()."::".$this->formTimerSetting);
			$current = time();
			if (!$started || ($minTime > 0 && $current - $started < $minTime) || ($maxTime > 0 && $current - $started > $maxTime)) {
				$form->addError(
					'username',
					__('plugins.generic.formHoneypot.invalidSessionTime')
				);
				$session->setSessionVar($this->getName()."::".$this->formTimerSetting, time());
			} else {
				$session->unsetSessionVar($this->getName()."::".$this->formTimerSetting);
				$started = $session->unsetSessionVar($this->getName()."::".$this->formTimerSetting);
			}
		}
		return false;
	}

	/**
	 * Start monitoring for timing for form completion
	 * @param $hookName string Name of hook calling function
	 * @return boolean
	 */
	function initializeTimer($hookName) {
		/*
		 * remember when this form was initialized for the user
		 * we'll store it as a user setting on form execution
		 */
		$sessionManager = SessionManager::getManager(Application::get()->getRequest());
		$session = $sessionManager->getUserSession();
		$started = $session->getSessionVar($this->getName()."::".$this->formTimerSetting);
		if (!$started) {
			$session->setSessionVar($this->getName()."::".$this->formTimerSetting, time());
		}
		return false;
	}

	/**
	 * @copydoc Plugin::manage()
	 */
	function manage($args, $request) {
		switch ($request->getUserVar('verb')) {
			case 'settings':
				//AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON,  LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_PKP_USER);
				//$templateMgr = TemplateManager::getManager($request);
				//$templateMgr->registerPlugin('', 'plugin_url', array($this, 'smartyPluginUrl'));

				//$this->import('FormHoneypotSettingsForm');
				$form = new FormHoneypotSettingsForm($this, PKPApplication::CONTEXT_SITE);

				// This assigns select options
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));
			default:
				// Unknown management verb
				assert(false);
				return false;
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
	 * @copydoc Plugin::getActions()
	 */
	function getActions($request, $verb) {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			):array(),
			parent::getActions($request, $verb)
		);
	}

	/**
	 * @copydoc PKPPlugin::getTemplatePath
	 */
	function getTemplatePath($inCore = false) {
		if(method_exists($this, 'getTemplateResource')) {
			// OJS 3.1.2 and later
			return parent::getTemplatePath($inCore);
		} else {
			// OJS 3.1.1 and earlier 3.x releases
			return parent::getTemplatePath($inCore) . 'templates' . DIRECTORY_SEPARATOR;
		}
	}

	/**
	 * Hook callback: register output filter to add a new registration field
	 * @see TemplateManager::display()
	 */
	function handleTemplateDisplay($hookName, $args) {
		
		$templateMgr = $args[0];
		
		$template = $args[1];

		switch ($template) {
			case 'frontend/pages/userRegister.tpl':
					$customElement = $this->getSetting(PKPApplication::CONTEXT_SITE, 'customElement');
					if (!empty($customElement)) {
						if (method_exists($templateMgr, 'registerFilter')) {
							// OJS 3.1.2 and later (Smarty 3)
							$templateMgr->registerFilter("output", array($this, 'addCustomElement'));
						} else {
							// OJS 3.1.1 and earlier (Smarty 2)
							$templateMgr->register_outputfilter(array($this, 'addCustomElement'));
						}
					}
				break;
		}
		return false;
	}

	/**
	 * Hook callback: assign user variable within Registration form
	 * @see Form::readUserVars()
	 */
	function handleUserVar($hookName, $args) {
		$form = $args[0];

		$element = $this->getSetting(PKPApplication::CONTEXT_SITE, 'customElement');
		$args[1][] = $element;
		return false;
	}

	/**
	 * Output filter to create a new element in a registration form
	 * @param $output string
	 * @param $templateMgr TemplateManager
	 * @return $string
	 */
	function addCustomElement($output, $templateMgr) {
		/* 
		 * Testing if we have a form#register here? A way of confirming the template. (yes, a regular expression is not the ideal way to do this,
		 * but with only one attribute, a regular expression should work okay here)
		*/ 
		if (preg_match('/<form[^>]+id="register"[^>]+>/', $output, $matches, PREG_OFFSET_CAPTURE) === 1) {
			$matches = array();
			if (preg_match_all('/(\s*<div[^>]+class="fields"[^>]*>\s*)/', $output, $matches, PREG_OFFSET_CAPTURE/*, $formStart*/)) {
				$placement = rand(0, count($matches[0])-1);
				
				$templateMgr = TemplateManager::getManager(Application::get()->getRequest());

				$element = $this->getSetting(PKPApplication::CONTEXT_SITE, 'customElement');
				$templateMgr->assign('element', $element);
				$offset = $matches[0][$placement][1] + trim(mb_strlen($matches[0][$placement][0]));
				$newOutput = substr($output, 0, $offset);
				
				if (method_exists($this, 'getTemplateResource')) {
					// OJS 3.1.2 and later
					$newOutput .= $templateMgr->fetch($this->getTemplateResource('pageTagForm.tpl'));
				} else {
					// OJS 3.1.1 and earlier
					$newOutput .= $templateMgr->fetch($this->getTemplatePath() . 'pageTagForm.tpl');
				}

				$newOutput .= substr($output, $offset);
				$output = $newOutput;
			}
		}
		return $output;
	}

	/**
	 * Output filter to create a new element in a registration form
	 * @param $output string
	 * @param $templateMgr TemplateManager
	 * @return $string
	 */
	function generateElementName () {
		return $this->elementNames['s'][rand(0, count($this->elementNames['s'])-1)] .
				$this->elementNames['v'][rand(0, count($this->elementNames['v'])-1)] .
				$this->elementNames['p'][rand(0, count($this->elementNames['p'])-1)];
	}
}
?>
