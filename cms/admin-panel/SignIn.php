<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use const Eleanor\SITEDIR;

/** Sign in page to admin panel
 * @var string $hcaptcha hcaptcha key (if empty - off) */

Link('//cdn.jsdelivr.net');

$nonce=Nonce();
$site=is_array(CMS::$config['site']['title']) ? L10n::Item(CMS::$config['site']['title']) : CMS::$config['site']['title'];
$l10n=new L10n('sign-in',__DIR__.'/l10n/');
?>
<!DOCTYPE html>
<html lang="<?=L10n::$code?>">
<head>
	<base href="<?=SITEDIR?>">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="none">
	<title><?=$l10n['admin-panel'],' :: ',$site?></title>

	<link rel="icon" href="favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="static/admin-panel/style.min.css">
	<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7/css/all.min.css">

	<script src="//cdn.jsdelivr.net/combine/npm/jquery@4/dist/jquery.slim.min.js,npm/vue@3/dist/vue.global.prod.min.js,npm/@coreui/coreui@5/dist/js/coreui.bundle.min.js" nonce="<?=$nonce?>" defer></script>
	<script src="static/admin-panel/sign-in.js" nonce="<?=$nonce?>" defer data-container="#sign-in" data-template="#sign-in-tpl" data-hcaptcha="<?=$hcaptcha?>"></script>
<?php
	if($hcaptcha)
	{
		$hl=L10n::$code;

		echo<<<HTML
	<script src="//js.hcaptcha.com/1/api.js?recaptchacompat=off&render=explicit&hl=$hl" nonce="$nonce" defer></script>
HTML;
	}
?>
</head>
<body>
<div class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-4">
				<main class="card-group d-block d-md-flex row" id="sign-in"></main>
				<script id="sign-in-tpl" type="text/x-template">
					<div class="card col-md-7 p-4 mb-0">
						<form class="card-body" @submit.prevent="Submit">
							<h1 class="h2"><?=$l10n['admin-panel']?></h1>
							<div class="input-group mb-2">
								<label class="input-group-text" for="username"><i class="fa-solid fa-user-tie"></i></label>
								<input tabindex="1" class="form-control" :class="{'is-invalid':wrong_username}" type="text" id="username" ref="username" v-model.trim="username" autocomplete="username" placeholder="<?=$l10n['username']?>" :disabled="loading" autofocus required>
								<div class="invalid-feedback"><?=$l10n['NOT_FOUND']?></div>
							</div>
							<fieldset>
								<legend v-if="recovery" class="mb-0"><?=$l10n['2of3']?></legend>
								<div class="input-group mb-2">
									<label class="input-group-text" for="password"><i class="fa-solid fa-lock"></i></label>
									<input tabindex="1" class="form-control" :class="{'is-invalid':wrong_password}" type="password" id="password" ref="password" v-model="password" autocomplete="current-password" placeholder="<?=$l10n['password']?>" :disabled="loading" :required="required || !recovery">
									<div class="invalid-feedback"><?=$l10n['WRONG_PASSWORD']?></div>
								</div>
								<div class="input-group mb-2" v-if="totp_field || recovery">
									<label class="input-group-text" for="totp"><i class="fa-solid fa-key"></i></label>
									<input tabindex="1" class="form-control" :class="{'is-invalid':wrong_totp}" ref="totp" id="totp" v-model="totp" autocomplete="off" minlength="6" maxlength="8" inputmode="numeric" pattern="\d+" placeholder="<?=$l10n['totp']?>" :disabled="loading" :required="required || totp_required">
									<div class="invalid-feedback"><?=$l10n['WRONG_TOTP']?></div>
								</div>
								<div class="input-group mb-2" v-if="recovery">
									<label class="input-group-text" for="recovery_code"><i class="fa-solid fa-person-through-window"></i></label>
									<input tabindex="1" class="form-control" id="recovery_code" v-model.trim="recovery_code" autocomplete="off" placeholder="<?=$l10n['recovery_code']?>" :disabled="loading" :required>
								</div>
							</fieldset>
							<div v-if="hcaptcha" ref="hcaptcha" class="mt-1 mb-2" data-tabindex="1"></div>
							<div class="row">
								<div class="col-auto">
									<button tabindex="1" type="submit" class="btn btn-primary bg-gradient px-4" :disabled="loading"><?=$l10n['sign-in']?></button>
								</div>
								<div class="col" :class="{'text-center':recovery,'text-end':!recovery}">
									<button tabindex="1" type="button" class="btn btn-outline-secondary" v-if="recovery" @click="Back"><?=$l10n['back']?></button>
									<button tabindex="1" type="button" class="btn btn-outline-secondary" v-else @click="Recovery"><?=$l10n['recovery']?></button>
								</div>
								<div class="col-auto text-end" v-if="recovery">
									<button tabindex="1" type="button" class="btn btn-outline-secondary" @click="Forgot" title="<?=$l10n['forgotten']?>"><i class="fa-solid fa-lock-open"></i></button>
								</div>
							</div>
						</form>
					</div>
					<dialog class="modal fade bg-transparent" ref="alert" data-coreui-backdrop="static" tabindex="-1">
						<div class="modal-dialog modal-dialog-centered">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" v-text="alert_title"></h5>
								</div>
								<div class="modal-body" v-html="alert" style="white-space:pre-wrap"></div>
								<div class="modal-footer">
									<button type="button" class="btn btn-primary bg-gradient px-4" data-coreui-dismiss="modal" tabindex="0">Ok</button>
								</div>
							</div>
						</div>
					</dialog>
				</script>
			</div>
		</div>
	</div>
</div>

</body>
</html>
