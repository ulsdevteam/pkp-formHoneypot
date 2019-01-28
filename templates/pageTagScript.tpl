{**
 * plugins/generic/formHoneypot/pageTagScript.tpl
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * Form Honeypot Script page tag.
 *
 *}
<!-- Form Honeypot -->
<script type="text/javascript">
	// add a placeholder to text inputs
	if ($("[name='{$element|escape}']").length === 1) {literal}{{/literal}
		var element = $("[name='{$element|escape}']")[0];
		element.setAttribute("placeholder", "{translate key='plugins.generic.formHoneypot.leaveBlank'}");
		element.setAttribute("tabIndex", "-1");
		element.setAttribute("autocomplete", "off");
		// hide the parent's parent element (input's div)
		element.parentNode.parentNode.style.display = 'none';
	{literal}}{/literal}
</script>
<!-- /Form Honeypot -->
