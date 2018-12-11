<?php

namespace Service\User;

$base_dir = $_SERVER['DOCUMENT_ROOT'];
require_once("$base_dir/scoreboard/src/Exceptions/Exceptions.php");

class UserService {
    private $db;

    /**
     * @param databaseService An instance of a database service that provides a query method.
     */
    public function __construct($databaseService) {
        $this->db = $databaseService;
    }

    /**
     * Creates a new user based on the username, email, and password given.
     *
     * @param args Should be an instance of \stdClass that has the properties username, email, and password.
     *
     * @throws \Exceptions\DuplicateResourceException
     * @throws \Exceptions\GenericException
     */
    public function create($args) {
        $username = $args->username;
        $email = $args->email;
        $password = $args->password;

        $this->validateEmail($email);

        // begin DB interaction
        $statement = 'INSERT INTO users (username, email, password)
                      VALUES (:username, :email, :password)';
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        try {
            $result = $this->db->query($statement, [
                'username' => $username,
                'email' => $email,
                'password' => $password_hash,
            ]);

            // Get the newly created user ID and assemble an instance of stdClass to pass to the get function
            $id = new \stdClass;
            $id->id = $this->db->connection()->lastInsertId();

            // Return the newly created user
            return $this->get($id);
        } catch (\PDOException $e) {
            // This is the MySql error code for a duplicate key/index
            if ($e->getCode() == '23000') {
                // Return an http error code to indicate what went wrong
                throw new \Exceptions\DuplicateResourceException('user email');
            } else {
                // If anything else went wrong then assume it's a server error
                throw new \Exceptions\GenericException;
            }
        }
        return $result;
    }

    private function getUser($field, $value) {
        // using the raw field var here should be safe, since this function should never
        // see outside use
        $statement = "SELECT * FROM users WHERE $field = :value";
        $result = $this->db->query($statement, [
            'value' => $value,
        ]);
        if (!$result) {
            return false;
        }

        return $result[0];
    }

    /**
     * Get a single user from the database by email
     *
     * @param args An instance of \stdClass with the property id.
     *
     * @throws \Exceptions\UserNotFoundException
     */
    public function getByEmail($email) {
        $this->validateEmail($email);

        $user = $this->getUser('email', $email);

        // This function is intended for external use, the data could go anywhere,
        // so we unset the password here; it should never leave this object.
        if (!$user) {
            throw new \Exceptions\UserNotFoundException($email);
        }
        unset($user->password);
        return $user;
    }

    /**
     * Get a single user from the database
     *
     * @param args An instance of \stdClass with the property id.
     *
     * @throws \Exceptions\UserNotFoundException
     */
    public function get($args) {
        $id = $args->id;
        $user = $this->getUser('id', $id);

        // This function is intended for external use, the data could go anywhere,
        // so we unset the password here; it should never leave this object.
        if (!$user) {
            throw new \Exceptions\UserNotFoundException($id);
        }
        unset($user->password);
        return $user;
    }

    /**
     * Attempts to log in the user with the email given using the password given.
     *
     * @param args Instance of \stdClass with properties email and password.
     *
     * @throws \Exceptions\InvalidCredentialsException
     */
    public function login($args) {
        $email = $args->email;
        $password = $args->password;

        $user = $this->getUser('email', $email);

        if (!$user) {
            throw new \Exceptions\UserNotFoundException($email);
        }

        if (password_verify($password, $user->password)) {
            unset($user->password);
            return $user;
        } else {
            throw new \Exceptions\InvalidCredentialsException($email);
        }
    }

    public function validateEmail($email) {
       // Validate email: effective max length is 254, also validated by regex
       if (strlen($email) > 254 || !preg_match('/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/', $email)) {
           throw new \Exceptions\InvalidParameterException($email);
       }
    }
}
