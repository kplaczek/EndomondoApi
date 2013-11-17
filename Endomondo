<?php

Class Endomondo {

    public $country = "PL";
    public $device_id = null;
    public $os = "Android";
    public $app_version = "7.1";
    public $app_variant = "M-Pro";
    public $os_version = "2.3.6";
    public $model = "GT-B5512";
    public $auth_token = null;
    public $user_agent = null;
    private $curl_handler = null;
    public $language = 'EN';
    private $email = null;
    private $password = null;

    # Authentication url. Special case.
    const URL_AUTH = 'https://api.mobile.endomondo.com/mobile/auth';
    # Page for latest workouts.
    const URL_WORKOUTS = 'http://api.mobile.endomondo.com/mobile/api/workout/list';
    # Single workout
    const URL_WORKOUT = 'http://api.mobile.endomondo.com/mobile/api/workout/get';
    # Running track
    const URL_TRACK = 'http://api.mobile.endomondo.com/mobile/readTrack';
    # Music track
    const URL_PLAYLIST = 'http://api.mobile.endomondo.com/mobile/api/workout/playlist';
    # User feed
    const URL_FEED = 'http://api.mobile.endomondo.com/mobile/api/feed';
    # Notification list
    const URL_NOTIFICATION = 'http://api.mobile.endomondo.com/mobile/api/notification/list';

    public function __construct($email = null, $password = null) {
        $this->device_id = Uuid::uuid5(Uuid::NAMESPACE_DNS, gethostname());
        $this->user_agent = sprintf("Dalvik/1.4.0 (Linux; U; %s %s; %s Build/GINGERBREAD)", $this->os, $this->os_version, $this->model);

        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Function returnns authentication token. If token is not assigned  
     * by hand it is acquired by sending request to endomondo server with 
     * valid email ald password 
     * 
     * @return string authentication token
     */
    final protected function get_auth_token() {
        if (!isset($this->auth_token[0]))
            $this->auth_token = $this->request_auth_token($this->email, $this->password);
        return $this->auth_token;
    }

    /**
     * Returns quick info about workout. It's ';' separated values. Some of the values are empty, Donno why, dont ask me.
     * 
     * @param int $trackId
     * @return string
     */
    public function read_track($trackId) {
        $params = array(
            'authToken' => $this->get_auth_token(),
            'trackId' => $trackId
        );
        return $this->get_site(self::URL_TRACK, $params);
    }

    /**
     * Returns lists of brief informations about latest workouts. When no parameter 
     * is passed then it returns workouts of current user but you can pass id of anyone.
     * 
     * @param int $maxResults
     * @return array of workouts
     */
    public function workout_list($userId = null, $maxResults = 40) {
        $params = array(
            'authToken' => $this->get_auth_token(),
            'language' => $this->language,
            'fields' => 'basic,pictures,tagged_users,points,playlist,interval',
            'maxResults' => $maxResults
        );
        if (isset($userId))
            $params['userId'] = $userId;
        $workouts = json_decode($this->get_site(self::URL_WORKOUTS, $params), 1);
        return $workouts['data'];
    }

    /**
     * Returns list of 15 most recent notifications (someone replied, someone likes workout etc.)
     * @return array
     */
    public function notification_list() {
        $params = array('authToken' => $this->get_auth_token());
        $notification = $this->get_site(self::URL_NOTIFICATION, $params);
        return json_decode($notification, 1);
    }

    /**
     * Returns informations about single workout. Information can be limited to: basic 
     * informations (speed, altitude, hydration, calories etc.), attached pictures, 
     * tagged users, gps points, and intervals. Or combinations of them. 
     * @param int $workoutId
     * @return array
     */
    public function workout($workoutId) {
        $params = array(
            'authToken' => $this->get_auth_token(),
            'fields' => 'basic,points,pictures,tagged_users,points,playlist,interval',
            'workoutId' => $workoutId
        );
        $workout = $this->get_site(self::URL_WORKOUT, $params);
        return json_decode($workout, 1);
    }

    /**
     * Returns list of 15 most recent users actions. Paging is done by passing 
     * beforeid (workout id or some other action id) and it's time. Then function will return 
     * 15 actions before that actions date.
     * 
     * @param int $userId
     * @param int $beforeId
     * @param string $before
     * @return array
     */
    public function feed_list($userId = null, $beforeId = null, $before = null) {
        $params = array(
            'authToken' => $this->get_auth_token(),
            'maxResults' => 15,
            'language' => $this->language
        );
        if ($userId)
            $params['userId'] = $userId;
        if ($beforeId)
            $params['beforeId'] = $beforeId;
        if ($before)
            $params['before'] = $before;

        $data = $this->get_site(self::URL_FEED, $params);
        echo '<pre>';
        print_r($data);
        echo '</pre>';

        return json_decode($data, 1);
    }

    public function request_auth_token($email, $password) {
        $params = array(
            'email' => $email,
            'password' => $password,
            'country' => $this->country,
            'deviceId' => $this->device_id,
            'os' => $this->os,
            'appVersion' => $this->app_version,
            'appVariant' => $this->app_variant,
            'osVersion' => $this->os_version,
            'model' => $this->model,
            'v' => 2.4,
            'action' => 'PAIR'
        );

        $data = $this->get_site(self::URL_AUTH, $params);
        if (substr($data, 0, 2) == "OK") {
            $lines = explode("\n", $data);
            $authLine = explode('=', $lines[2]);
            return $authLine[1];
        }
    }

    /**
     * Getting all workouts of any user. Workouts must be visible for others. 
     * Beforeid an before are optional. These 2 parameters are used to set a limit. 
     * When set, only workouts lower than beforeId and older than before are returned.
     * 
     * @param type $userId id user we wont get workouts of 
     * @param type $beforeId id of workout
     * @param type $before time of workout
     * @return type
     */
    public function get_all_workouts($userId, $beforeId = null, $before = null) {
        $feed_list = $this->feed_list($userId, $beforeId, $before);
        $end = count($feed_list['data']) - 1;
        if ($end < 0)
            return array();
        foreach ($feed_list['data'] as $k => $v) {
            if ($v['type'] == 'workout')
                $workouts[$v['id']] = $v;
            if ($k == $end) {
                $workouts = array_merge($workouts, $this->get_all_workouts($userId, $v['id'], $v['order_time']));
            }
        }
        return $workouts;
    }

    public function get_site($site, $fields = NULL) {
        $data = $this->get_curl_data($fields);
        $url = $site . '?' . $data;
        if ($this->curl_handler == null)
            $this->curl_handler = curl_init();

        if (substr($url, 0, 8) == "https://")
            curl_setopt($this->curl_handler, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->curl_handler, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($this->curl_handler, CURLOPT_HEADER, false);
        curl_setopt($this->curl_handler, CURLOPT_URL, $url);
        curl_setopt($this->curl_handler, CURLOPT_REFERER, $url);
        curl_setopt($this->curl_handler, CURLOPT_RETURNTRANSFER, TRUE);

        $data = curl_exec($this->curl_handler);

        curl_close($this->curl_handler);
        return $data;
    }

    /**
     * Function glues keys and parameters in one long urlsafe link using & signs between
     * 
     * @param array $fields parameters
     * @return type
     */
    private function get_curl_data(array $fields) {
        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        return rtrim($fields_string, '&');
    }

}

?>
