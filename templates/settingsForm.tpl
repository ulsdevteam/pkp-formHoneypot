{**
 * plugins/generic/formHoneypot/settingsForm.tpl
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * Form Honeypot plugin settings
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
        {fbvElement type="select" id="element" from=$elementList selected=$element label="plugins.generic.formHoneypot.manager.settings.element" size=$fbvStyles.size.SMALL inline="inline"}
		{fbvElement type="text" id="formHoneypotMinimumTime" name="formHoneypotMinimumTime" value="$formHoneypotMinimumTime" label="plugins.generic.formHoneypot.manager.settings.minimumTime" size=$fbvStyles.size.SMALL inline="inline"}
		{fbvElement type="text" id="formHoneypotMaximumTime" name="formHoneypotMaximumTime" value="$formHoneypotMaximumTime" label="plugins.generic.formHoneypot.manager.settings.maximumTime" size=$fbvStyles.size.SMALL inline="inline"}
	{/fbvFormArea}

	{fbvFormButtons}
    
</form>
