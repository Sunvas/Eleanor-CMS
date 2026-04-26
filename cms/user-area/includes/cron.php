<?php
namespace CMS;

/** Script to run cron. Runs maximum 10 times. */

return CMS::$T->cron ? '(function F(n){ fetch("cron.php").then(async r=>r.status==200 && n-->0 ? setTimeout(F,1000*(await r.text()),n) : 0); })(10);' : '';