<?php
!defined('ROOT_PATH') && exit('Access Denied');

class U {
    /**
     * @var int user id
     */
    public $user_id = 0;
    /**
     * @var string username
     */
    public $truename = 'guest';
    /**
     * @var string phone
     */
    public $phone = '';
    /**
     * @var string email
     */
    public $email = '';
    /**
     * @var int group id
     */
    public $group_id = 0;
    /**
     * @var int expire time
     */
    public $expire = 0;
    /**
     * @var string password in cookie
     */
    public $password = '';

    /**
     * @var int online hold time
     */
    public $online_hold = 86400;

    /**
     * @var null cache open
     */
    private $cache_open = NULL;

    /**
     * @var bool field
     */
    private $field = false;

    /**
     * @var array field cache
     */
    private $field_cache = array();

    /**
     * @var array config
     */
    private $conf = array();

    /**
     * @var array table of user
     */
    private $tables = array(
        'user' => array(
            //'username',
            'phone',
            'email',
            'password',
            'salt',
        ),
        'user_field' => array(
            'group_id',
            'region_id',
            'region',
            'emailcheck',
            'phonecheck',
            'truename',
            'company',
            'job',
            'regtime',
            'regip',
            'logtime',
            'logip',
            'logcount',
            'isauth',
            'authtime',
            'authmessage',
            'investor',
            'meetnum',
            'feedprojectnum',
            'projectnum',
            'messagenum',
            'account3nd',
            'islock',
            'feednum',
            'fannum',
        ),
    );

    /**
     * @var string user field from json
     */
    private $json_fields = 'account3nd';

    /**
     * init user
     *
     * @param            $conf
     * @param bool|false $simple_check
     * @return bool
     */
    public function init(&$conf, $simple_check = false) {
        $this->conf = &$conf;
        if (isset($this->conf['online_hold'])) {
            $this->online_hold = max($this->conf['online_hold'], 60);
        }
        return $this->check_auth($simple_check);
    }

    /**
     * check cookie auth
     *
     * @param $simple_check
     * @return bool|void
     */
    private function check_auth($simple_check) {
        $auth_code = core::C('auth');
        if (!$auth_code) {
            return false;
        }
        // get cookie auth
        @list($user_id, $password, , $email, $phone, $group_id, $expire) = explode("\t", encode::decrypt($auth_code, $this->conf['cookie_key']));

        $user_id = intval($user_id);
        if (!$user_id) {
            return false;
        }
        $server_time = core::S('time');
        $this->user_id = $user_id;
        $this->group_id = $group_id;
        $this->password = $password;
        $this->expire = $expire;
        $this->email = $email;
        $this->phone = $phone;
        // 过期
        if ($server_time >= $expire) {
            $this->reset();
            return false;
        }

        if ($simple_check) {
            return $user_id > 0 ? true : false;
        } else {
            $is_login = false;
            $update_session = false;
            // check user_id
            if ($user_id) {
                //$username = '';
                $expire = 0;
                //check cache open
                if (CACHE::opened()) {
                    $user = CACHE::get('session_' . $user_id);
                } else {
                    $user = DB::select('session', array("user_id" => $user_id), 0, 0);
                }
                if ($user) {
                    // hit the cache and add expire
                    $expire = $user['expire'];
                } else {
                    // query db and set in cache
                    $user = DB::select('user', array("uid" => $user_id), 0, 0);
                    $update_session = true;
                    // md5 twice cookie
                    $user['password'] = md5($user['password']);
                    $email = $user['email'];
                    $phone = $user['phone'];
                }
                // find and match password
                if ($user && $user['password'] == $password) {
                    //$username = $user['username'];
                    $password = $user['password'];
                    $is_login = true;
                    // check ip change
                    if ($user['ip'] != core::ip()) {
                        $update_session = 1;
                    }
                }
            }
            // is login
            if ($is_login) {
                $this->user_id = $user_id;
                $this->password = $password;
                //$this->username = $username;
                $this->group_id = $this->field('group_id');
                // check online_hold
                if ($expire && $server_time - $expire > $this->online_hold - 600) {
                    $update_session = 1;
                }
                // check expire
                if ($update_session || $server_time % 5 == 0) {
                    // add expire and insert session
                    $expire = $server_time + $this->online_hold;
                    $this->expire = $expire;
                    $this->update_session(array(
                            'user_id' => $user_id,
                            'password' => $password,
                            'expire' => $expire,
                            //'username' => $username,
                            'phone' => $phone,
                            'email' => $email,
                            'group_id' => $this->group_id,
                            'ip' => core::ip()
                        )
                    );
                    //add 1 day
                    core::C('auth', $auth_code, 86400);
                }
            } else {
                $this->reset();
            }

            return $is_login;
        }
    }

    /**
     *
     * reset user
     */
    public function reset() {
        $this->user_id = 0;
        //$this->username = 0;
        $this->group_id = 0;
        $this->expire = 0;
        $this->password = '';
        $this->email = '';
        $this->phone = '';

    }

    /**
     * get user field
     *
     * @param string $field
     * @param int    $user_id
     * @return bool
     */
    public function field($field = '', $user_id = 0) {
        if (!$user_id) {
            $user_id = $this->user_id;
        }


        if (isset($this->field_cache[$user_id])) {
            $user = $this->field_cache[$user_id];
        } else {
            //$user = Q::query(array('table' => 'user_field', 'where' => "user_id='" . $user_id . "'", 'perpage' => 0), 'user_id_' . $user_id, 0);
            $sql = 'SELECT * FROM ' . DB::table('user') . ' u LEFT JOIN ' . DB::table('user_field') . ' f ON u.uid=f.user_id WHERE u.uid=\'' . $user_id . '\' LIMIT 1';
            $user = DB::query($sql, 1);
            // format user
            $this->format($user);
            // writer user cache
            $this->field_cache[$user_id] = $user;
        }
        if ($field) {
            return $user[$field];
        } else {
            return $user;
        }
    }

    /**
     * format user fields
     *
     * @param $user
     */
    public function format(&$user) {
        if (!$user) return;
        $user['regdate'] = empty($user['regdate']) ? '0000-00-00 00:00' : date('Y-m-d H:i', $user['regdate']);
        $user['regip'] = long2ip($user['regip']);
        $user['logtime'] = empty($user['logindate']) ? '0000-00-00 00:00' : date('Y-m-d H:i', $user['logindate']);
        $user['logip'] = isset($user['loginip']) ? long2ip($user['loginip']) : '';

        //json decode
        if ($this->json_fields) {
            foreach (explode(',', $this->json_fields) as $field) {
                $user[$field] = json_decode($user[$field], 1);
            }
        }
        // hook usre_model_format_after.php
    }

    /**
     * update session
     *
     * @param $user
     */
    public function update_session($user) {
        if ($this->user_id < 1) {
            return;
        }
        //convert ip to int
        $user['ip'] = ip2long($user['ip']);
        if (CACHE::opened()) {
            CACHE::set('session_' . $user['user_id'], $user, $this->online_hold);
        } else {
            DB::replace('session', $user);
            $time = core::S('time');
            if ($time % 10 == 0) {
                DB::delete('session', 'expire<=' . $time);
            }
        }
    }

    /**
     * get salt
     *
     * @return string
     */
    public function get_salt() {
        $string = "0123456789abcdefghijklmnopqrstuvwxyz~@#$%^&*(){}[]|";
        $count = strlen($string) - 1;
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $string[rand(0, $count)];
        }
        return $code;
    }

    /**
     * get password  by pass & salt
     *
     * @param $pass
     * @param $salt
     * @return string
     */
    public function get_pass($pass, $salt) {
        return md5(md5($pass) . $salt);
    }

    /**
     * check cache is open
     *
     * @return bool|null
     */
    public function cache_open() {
        if (is_null($this->cache_open)) {
            $this->cache_open = CACHE::instance() === false ? false : true;
        }
        return $this->cache_open;
    }

    /**
     * check username valid
     *
     * @param $username
     * @return string
     */
    public function check_username(&$username) {
        $username = trim($username);
        if (empty($username)) {
            return '用户名不能为空哦！';
        } elseif (mb_strlen($username) < 2) {
            return '用户名不能小于2位哦！';
        } elseif (strlen($username) > 16) {
            return '用户名不能大于16位哦！';
        } elseif (str_replace(array("\t", "\r", "\n", ' ', '　', ',', '，', '-', '"', "'", '\\', '/', '&', '#', '*', '@'), '', $username) != $username) {
            return '用户名中含有非法字符！';
        } elseif (htmlspecialchars($username) != $username) {
            return '用户名中不能含有&quot;、&lt;和&gt;符号！';
        }
        return '';
    }

    /**
     * check password valid
     *
     * @param $password
     * @return string
     */
    public function check_password(&$password) {
        if (empty($password)) {
            return '密码不能为空哦！';
        } elseif (utf8::strlen($password) < 6) {
            return '密码不能小于6位哦！';
        } elseif (utf8::strlen($password) > 32) {
            return '密码不能大于32位哦！';
        }
        return '';
    }

    /**
     * get user in member table
     *
     * @param $username
     * @return mixed
     */
    public function get_by_username($username) {
        return DB::select('user', array('username' => $username), 0, 0);
    }

    /**
     * get user by email
     *
     * @param $email
     * @return mixed
     */
    public function get_by_email($email) {
        return DB::select('user', array('email' => $email), 0, 0);
    }

    /**
     * get user by phone
     *
     * @param $email
     * @return mixed
     */
    public function get_by_phone($phone) {
        return DB::select('user', array('phone' => $phone), 0, 0);
    }

    /**
     * get user by user_id
     *
     * @param $user_id
     * @return mixed
     */
    public function get($user_id) {
        return DB::select('user', array('uid' => $user_id), 0, 0);
    }

    /**
     * get user auth
     *
     * @param $user_id
     * @param $password
     * @return mixed
     */
    public function get_auth($user_id, $password) {
        $fields = $this->field('', $user_id);
        $fields['expire'] = core::S('time') + $this->online_hold;
        // md5 twice
        $password = md5($password);
        $auth_str = $user_id . "\t" . $password . "\t" /*. $fields['username']*/
            . "\t" . $fields['email'] . "\t" . $fields['phone'] .
            "\t" . $fields['group_id'] . "\t" . $fields['expire'];
        return encode::encrypt($auth_str, $this->conf['cookie_key']);
    }

    /**
     * update user field
     *
     * @param     $fields
     * @param int $user_id
     * @return bool
     */
    public function update($fields, $user_id = 0) {
        if (!$user_id && !$this->user_id) {
            return false;
        }
        if (!$user_id) {
            $user_id = $this->user_id;
        }
        $selecttables = array();
        $membertabls = &$this->tables;
        //取得要更新的表和字段
        foreach ($fields as $field => $value) {
            foreach ($membertabls as $table => $_fields) {
                if (in_array($field, $_fields)) {
                    if (strpos(',' . $this->json_fields . ',', ',' . $field . ',') !== false) {
                        $selecttables[$table][$field] = core::json_encode($value);
                    } else {
                        $selecttables[$table][$field] = $value;
                    }
                }
            }
        }

        //更新
        foreach ($selecttables as $table => $fieldarr) {
            if ($table == 'user') {
                $uid_field = 'uid';
            } else {
                $uid_field = 'user_id';
            }
            DB::update($table, $fieldarr, array($uid_field => $user_id));
        }
        //delete query cache
        Q::delete_query('user_id_' . $user_id);
        //delete session
        $this->delete_session($user_id);
        //
        if ($user_id == $this->user_id) {
            $this->field = false;
            $this->field = $this->field('', $user_id);
        } else {
            unset($this->field_cache[$user_id]);
        }
        return true;
    }

    /**
     * remove session
     *
     * @param $user_id
     */
    public function delete_session($user_id) {
        if (CACHE::opened()) {
            CACHE::delete('session_' . $user_id);
        } else {
            DB::delete('session', array('user_id' => $user_id));
        }
    }

    /**
     * register user
     *
     * @param $username
     * @param $email
     * @param $pass
     * @param $salt
     * @return bool|mixed
     */
    public function register($email, $phone, $pass, $salt) {

        $member = array(
            'password' => $pass,
            'email' => $email,
            'phone' => $phone,
            'salt' => $salt,
        );

        $id = DB::insert('user', $member, 1);
        if (!$id) {
            return false;
        }

        $member_field = array(
            'user_id' => $id,
            'group_id' => 0,
            'emailcheck' => $email ? 1 : 0,
            'phonecheck' => $phone ? 1 : 0,
            'regtime' => core::S('time'),
            'regip' => core::ip(),
            'logcount' => 0,
        );
        DB::insert('user_field', $member_field);

        return $id;

    }
}

?>