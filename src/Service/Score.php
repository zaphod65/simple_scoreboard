<?php

namespace Service\Score;

$base_dir = $_SERVER['DOCUMENT_ROOT'];
require_once("$base_dir/scoreboard/src/Exceptions/Exceptions.php");

class ScoreService {
    private $db;
    private $userService;
    private $emailService;

    public function __construct($databaseService, $userService, $emailService = null) {
        $this->db = $databaseService;
        $this->userService = $userService;
        $this->emailService = $emailService;
    }

    /**
     * Stores a single user's score for a given level
     *
     * @param args Instance of \stdClass with the properties userId, levelId and time.
     *
     * @throws \Exceptions\UserNotFoundException
     * @throws \Exceptions\GenericException
     */
    public function store($args) {
        $userId = $args->userId;
        $levelId = $args->levelId;
        $time = $args->time;

        $query = "INSERT INTO scores (userId, levelId, time) VALUES (
            :userId,
            :levelId,
            :time
        )";

        try {
            $this->db->query($query, [
                'userId' => $userId,
                'levelId' => $levelId,
                'time' => $time,
            ]);
        } catch (\PDOException $e) {
            switch ($e->getCode()) {
                case 23000:
                    throw new \Exceptions\UserNotFoundException($userId);
                default:
                    throw new \Exceptions\GenericException;
            }
        }

        return ['success' => true];
    }

    /**
     * Gets the top fifty scores for a level.
     *
     * @param args Instance of \stdClass with the property levelId
     *
     * @throws \Exceptions\GenericException
     */
    public function topFifty($args) {
        $levelId = $args->levelId;

        $query = 'SELECT u.username, s.time
                      FROM burke_best_scores.users u
                        JOIN burke_best_scores.scores s
                          ON u.id = s.userId
                    WHERE levelId = :levelId
                    ORDER BY s.time
                    LIMIT 50';

        try {
            $results = $this->db->query($query, [
                $levelId,
            ]);
        } catch (\PDOException $e) {
            throw new \Exceptions\GenericException;
        }

        foreach ($results as $i => $score) {
            $score->rank = $i +1;
        }

        return $results;
    }

    /**
     * Gets the top fifty scores for a level, around a particular user's best score.
     *
     * @param args Instance of \stdClass with the properties levelId and userId
     *
     * @throws \Exceptions\GenericException
     */
    public function fiftyContext($args) {
        $email = $args->email;
        $levelId = $args->levelId;

        $this->userService->validateEmail($email);

        // get all the scores for the given level in order of time
        $query = 'SELECT id, username, time, email
                  FROM scores s
                    JOIN users u
                      ON u.id = s.userId
                  WHERE levelId = :levelId
                  ORDER BY time';
        try {
            $results = $this->db->query($query, [
                'levelId' => $levelId,
            ]);
        } catch (\PDOException $e) {
            throw new \Exceptions\GenericException;
        }

        // Find the position of the lowest (i.e. best) score the user passed in has achieved
        $filtered = array_filter($results, function($data) use ($email) {
            return $data->email == $email;
        });

        if (!$filtered) {
            throw new \Exceptions\UserNotFoundException($email);
        }
        // array_filter preserves keys so to get the position in the full result list
        // we can get the first of the keys that are left
        $keys = array_keys($filtered);
        $listPosition = array_shift($keys);

        // adjust the bottom of the range to return for underflow
        $possibleBottom = $listPosition - 25;
        $bottom = $possibleBottom >= 0 ? $possibleBottom : 0;

        // Adjust the bottom of the range again for overflow
        if ($listPosition + 25 >= count($results)) {
            $diff = 25 - (count($results) - $listPosition);
            $bottom -= $diff;
        }

        // Actually get the context around the user's result
        $context = array_slice($results, $bottom, 50);

        // Give each score its actual position in the list
        foreach ($context as $score) {
            $score->rank = $bottom + 1;
            unset($score->id);
            unset($score->email);
            $bottom++;
        }

        return $context;
    }

    public function emailTop() {
        if (!$this->emailService) {
            throw new \Exceptions\GenericException;
        }

        $levels = [1,2,3];
        $scores = [];
        foreach ($levels as $levelId) {
            $levelObj = new \stdClass;
            $levelObj->levelId = $levelId;
            $scores[$levelId] = $this->topFifty($levelObj);
        }

        // TODO: format this data better.
        $scoreString = json_encode($scores);
        $date = date("Y-m-d H:i:s");
        $this->emailService->send('dg268@kentforlife.net', 'Top scores ' . $date, $scoreString);
    }
}
