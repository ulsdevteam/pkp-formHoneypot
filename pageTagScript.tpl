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
	document.getElementById("{$element|escape}").setAttribute("placeholder", "{translate key='plugins.generic.formHoneypot.leaveBlank'}");
	// hide the parent's parent element (input's td's tr)
	document.getElementById("{$element|escape}").parentNode.parentNode.style.display = 'none';
</script>
<!-- /Form Honeypot -->
