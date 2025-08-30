<?php
namespace CMS;

use Eleanor\Classes\L10n;
use const Eleanor\SITEDIR;

/** Sign in page to dashboard
 * @var string $hcaptcha hcaptcha key (if empty - off) */

$nonce=Nonce();
$site=is_array(CMS::$config['site']['name']) ? L10n::Item(CMS::$config['site']['name']) : CMS::$config['site']['name'];
$l10n=new L10n('sign-in',__DIR__.'/l10n/');
?>
<!DOCTYPE html>
<html lang="<?=L10n::$code?>">
<head>
	<base href="<?=SITEDIR?>">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="none">
	<title><?=$l10n['dashboard'],' :: ',$site?></title>

	<link rel="shortcut icon" href="favicon.ico">
	<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/@coreui/coreui@5/dist/css/coreui.min.css">
	<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7/css/all.min.css">

	<script src="//cdn.jsdelivr.net/combine/npm/jquery@3/dist/jquery.slim.min.js,npm/vue@3/dist/vue.global.prod.min.js,npm/@coreui/coreui@5/dist/js/coreui.bundle.min.js" nonce="<?=$nonce?>" defer></script>
	<script src="static/dashboard/sign-in.js" nonce="<?=$nonce?>" defer data-container="#sign-in" data-template="#sign-in-tpl" data-hcaptcha="<?=$hcaptcha?>"></script>
<?php
	if($hcaptcha)
	{
		$hl=\Eleanor\Classes\L10n::$code;

		echo<<<HTML
	<script src="//js.hcaptcha.com/1/api.js?recaptchacompat=off&render=explicit&hl={$hl}" nonce="{$nonce}" defer></script>
HTML;
	}
?>
</head>
<body>
<div class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-4">
				<div class="card-group d-block d-md-flex row" id="sign-in"></div>
				<script id="sign-in-tpl" type="text/x-template">
					<div class="card col-md-7 p-4 mb-0">
						<form class="card-body" @submit.prevent="Submit">
							<h1 class="h2"><?=$l10n['dashboard']?></h1>
							<div class="input-group mb-2">
								<label class="input-group-text" for="username"><i class="fa-solid fa-user-tie"></i></label>
								<input tabindex="1" class="form-control" type="text" id="username" placeholder="<?=$l10n['username']?>" autocomplete="username" v-model.trim="username" :disabled="loading" autofocus required>
							</div>
							<div class="input-group mb-3">
								<label class="input-group-text" for="password"><i class="fa-solid fa-lock"></i></label>
								<input tabindex="1" class="form-control" type="password" id="password" placeholder="<?=$l10n['password']?>" autocomplete="current-password" v-model="password" :disabled="loading" required>
							</div>
							<div v-if="hcaptcha" ref="hcaptcha" class="mb-2" data-tabindex="1"></div>
							<div class="row">
								<div class="col-6">
									<button tabindex="1" type="submit" class="btn btn-primary bg-gradient px-4" :disabled="loading"><?=$l10n['sign-in']?></button>
								</div>
								<div class="col-6 text-end">
									<button tabindex="1" type="button" class="btn btn-link px-0" @click="Forgot"><?=$l10n['forgotten']?></button>
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
