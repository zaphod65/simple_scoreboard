<?php
// The intention in this endpoint is that a cron job should be set up to call it via
// cURL every 4 hours to trigger sending the top scores.

require_once('../../service/Config.php');
require_once('../../service/Database.php');
require_once('../../service/Email.php');
require_once('../../Service/User.php');
require_once('../../service/Score.php');
require_once('../WebApi.php');

$configService = new Service\Config\ConfigService();
$databaseService = new Service\Database\DatabaseService($configService);
$userService = new Service\User\UserService($databaseService);
$emailService = new Service\Email\EmailService();
$scoreService = new Service\Score\ScoreService($databaseService, $userService, $emailService);

$api = new WebApi('GET', $scoreService, 'emailTop', []);

$api->process();
