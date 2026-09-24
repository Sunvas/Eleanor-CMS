<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

/** Weekly cleanup of the users' sign-in log
 * @var ?array $remnant Remnant from previous run
 * @return array|int */

CMS::$Db->Query('DELETE FROM `users_signin_log` WHERE `date`<NOW() - INTERVAL 1 WEEK');

return 86400*7;