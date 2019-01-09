{**
 * plugins/generic/formHoneypot/pageTagScript.tpl
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * Form Honeypot Script page tag.
 *
 *}
<!-- Form Honeypot -->
<script type="text/javascript">
	// add a placeholder to text inputs
	if (document.getElementById("{$element|escape}")) {literal}{{/literal}
		document.getElementById("{$element|escape}").setAttribute("placeholder", "{translate key='plugins.generic.formHoneypot.leaveBlank'}");
		document.getElementById("{$element|escape}").setAttribute("tabIndex", "-1");
		document.getElementById("{$element|escape}").setAttribute("autocomplete", "off");
		// hide the parent's parent element (input's div)
		document.getElementById("{$element|escape}").parentNode.style.display = 'none';
	{literal}}{/literal}
</script>
<!-- /Form Honeypot -->
