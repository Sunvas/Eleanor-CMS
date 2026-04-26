<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com

/** @var $l10n array Translation */

return<<<VUE
<dialog class="modal fade bg-transparent" ref="confirm" tabindex="-1" data-coreui-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" v-text="confirm_title"></h5>
				<button type="button" class="btn-close" tabindex="-1" data-coreui-dismiss="modal"></button>
			</div>
			<div class="modal-body" v-text="confirm"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary bg-gradient px-4" data-coreui-dismiss="modal" ref="confirm_dismiss" tabindex="2">{$l10n['no']}</button>
				<button type="button" class="btn btn-primary bg-gradient px-4" data-coreui-dismiss="modal" @click="Confirmed" tabindex="1">{$l10n['yes']}</button>
			</div>
		</div>
	</div>
</dialog>
VUE;