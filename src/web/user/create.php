<?php

require_once('../../service/Database.php');
require_once('../../service/User.php');
require_once('../../service/Config.php');
require_once('../WebApi.php');

// Get the services needed for the operation to follow
$configService = new Service\Config\ConfigService();
$databaseService = new Service\Database\DatabaseService($configService);
$userService = new Service\User\UserService($databaseService);

$api = new WebApi('POST', $userService, 'create', [
    'username',
    'email',
    'password',
]);

$api->process();
