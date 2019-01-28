{**
 * plugins/generic/formHoneypot/pageTagForm.tpl
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * Form Honeypot Form Element page tag.
 *
 *}
	<div class="{$element|escape}">
		<label>
			<span class="label">
				{fieldLabel name="$element" key="plugins.generic.formHoneypot.leaveBlank" required="false"}
			</span>
		<input type="checkbox" name="{$element|escape}" id="{$element|escape}" value="1">
		</label>
	</div>
	