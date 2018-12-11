<?php

require_once('../../service/Config.php');
require_once('../../service/Database.php');
require_once('../../service/User.php');
require_once('../../service/Score.php');
require_once('../WebApi.php');

$configService = new Service\Config\ConfigService();
$databaseService = new Service\Database\DatabaseService($configService);
$userService = new Service\User\UserService($databaseService);
$scoreService = new Service\Score\ScoreService($databaseService, $userService);

$api = new WebApi('GET', $scoreService, 'topFifty', [
    'levelId',
]);

$api->process();
