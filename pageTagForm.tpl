{**
 * plugins/generic/formHoneypot/pageTagForm.tpl
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * Form Honeypot Form Element page tag.
 *
 *}
	
	<tr valign="top">
			<td class="label">{fieldLabel name="$element" key="plugins.generic.formHoneypot.leaveBlank" required="true"}</td>
			<td class="value"><input type="checkbox" name="{$element|escape}" id="{$element|escape}" value="1"></td>
		</tr>
	