{**
 * plugins/generic/clamav/settingsForm.tpl
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * ClamAV plugin settings
 *
 *}
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#formHoneypotSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="formHoneypotSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="formHoneypotSettingsFormNotification"}

	<div id="description">{translate key="plugins.generic.formHoneypot.manager.settings.description"}</div>

	{fbvFormArea id="settingsFormArea" description="plugins.generic.formHoneypot.manager.settings.description"}
        {fbvElement type="select" id="element" from=$elementList selected=$element label="plugins.generic.formHoneypot.manager.settings.element"}
    
    
    {fbvElement type="text" id="clamavPath" name="clamavPath" value="$clamavPath" label="plugins.generic.clamav.manager.settings.clamavPath"}
        <div id=clamVersion">
            <label for="clamavVersion">{translate key="plugins.generic.clamav.manager.settings.version"}</label>
            <input disabled="disabled" type="text" id="clamavVersion" value="{$clamavVersion}" />
            <input type="submit" name="test" value="{translate key="plugins.generic.clamav.manager.settings.test"}" />
        </div>
	{/fbvFormArea}
        
	{fbvFormArea id="clamdSettingsFormArea" title="plugins.generic.clamav.manager.settings.daemon"}
        {fbvFormSection description="plugins.generic.clamav.manager.settings.daemon.description" list=true}
            {fbvElement type="checkbox" id="clamavUseSocket" name="clamavUseSocket" value="1" checked="$clamavUseSocket" label="plugins.generic.clamav.manager.settings.clamavUseSocket"}
            {fbvElement type="text" id="clamavSocketPath" name="clamavSocketPath" value="$clamavSocketPath" label="plugins.generic.clamav.manager.settings.clamavSocketPath"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons}

    
</form>
