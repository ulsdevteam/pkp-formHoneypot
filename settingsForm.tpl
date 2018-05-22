{**
 * plugins/generic/formHoneypot/settingsForm.tpl
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * Form Honeypot plugin settings
 *
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.formHoneypot.manager.formHoneypotSettings"}
{include file="common/header.tpl"}
{/strip}
<div id="formHoneypotSettings">
<div id="description">{translate key="plugins.generic.formHoneypot.manager.settings.description"}</div>

<div class="separator"></div>

<br />

<form method="post" action="{plugin_url path="settings"}">
{include file="common/formErrors.tpl"}
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formHoneypotElement" required="true" key="plugins.generic.formHoneypot.manager.settings.element"}</td>
		<td width="80%" class="value">
			<select class="selectMenu" name="element" id="element">
				{html_options_translate options=$elementOptions selected=$element}
			</select>
		</td>
	</tr>
</table>

<br/>

<input type="submit" name="save" class="button defaultButton" value="{translate key="common.save"}"/><input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>
</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</div>
{include file="common/footer.tpl"}
