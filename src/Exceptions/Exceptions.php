<?php

namespace Exceptions;

abstract class BaseException extends \Exception {
    protected $httpCode;
    protected $message;

    public function __construct($httpCode, $message) {
        parent::__construct($message);
        $this->httpCode = $httpCode;
    }

    public function getHttpCode() {
        return $this->httpCode;
    }
}

class GenericException extends BaseException {
    public function __construct() {
        parent::__construct(500, 'Something went wrong');
    }
}

class UserNotFoundException extends BaseException {
    public function __construct($userId) {
        parent::__construct(404, "User not found: $userId");
    }
}

class DuplicateResourceException extends BaseException {
    public function __construct($resource) {
        parent::__construct(409, "Duplicate resource: $resource");
    }
}

class InvalidCredentialsException extends BaseException {
    public function __construct($email) {
        parent::__construct(403, 'Invalid credentials.');
    }
}

class InvalidParameterException extends BaseException {
    public function __construct($parameter) {
        parent::__construct(400, "Invalid parameter: $parameter");
    }
}