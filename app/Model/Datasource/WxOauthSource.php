<?php
class WxOauthSource extends DataSource {

    /**
     * An optional description of your datasource
     */
    public $description = 'WX Oauth datasource';

    /**
     * Our default config options. These options will be customized in our
     * ``app/Config/database.php`` and will be merged in the ``__construct()``.
     */
    public $config = array(
        'apiKey' => '',
    );


    public $wx_curl_option_defaults = array(
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    );

    /**
     * Create our HttpSocket and handle any config tweaks.
     */
    public function __construct($config) {
        parent::__construct($config);
    }

    /**
     * Since datasources normally connect to a database there are a few things
     * we must change to get them to work without a database.
     */

    /**
     * listSources() is for caching. You'll likely want to implement caching in
     * your own way with a custom datasource. So just ``return null``.
     */
    public function listSources($data = null) {
        return null;
    }

    /**
     * describe() tells the model your schema for ``Model::save()``.
     *
     * You may want a different schema for each model but still use a single
     * datasource. If this is your case then set a ``schema`` property on your
     * models and simply return ``$model->schema`` here instead.
     */
    public function describe($model) {
        return $model->schema();
    }

    /**
     * calculate() is for determining how we will count the records and is
     * required to get ``update()`` and ``delete()`` to work.
     *
     * We don't count the records here but return a string to be passed to
     * ``read()`` which will do the actual counting. The easiest way is to just
     * return the string 'COUNT' and check for it in ``read()`` where
     * ``$data['fields'] === 'COUNT'``.
     */
    public function calculate(Model $model, $func, $params = array()) {
        return 'COUNT';
    }

    /**
     * Implement the R in CRUD. Calls to ``Model::find()`` arrive here.
     */
    public function read(Model $model, $queryData = array(),
                         $recursive = null) {
        /**
         * Here we do the actual count as instructed by our calculate()
         * method above. We could either check the remote source or some
         * other way to get the record count. Here we'll simply return 1 so
         * ``update()`` and ``delete()`` will assume the record exists.
         */
        if ($queryData['fields'] === 'COUNT') {
            return array(array(array('count' => 1)));
        }
        /**
         * Now we get, decode and return the remote data.
         */
        $queryData['conditions']['apiKey'] = $this->config['apiKey'];
        if ($queryData['method'] == 'auth_token') {
            $json = $this->auth_token($queryData);
        } else if ($queryData['method'] == 'get_access_token') {
            $json = $this->get_access_token($queryData);
        } else {
            throw new CakeException("not supported query type(" . $queryData['method'] . ")");
        }
        $res = json_decode($json, true);
        if (is_null($res)) {
            $error = json_last_error();
            throw new CakeException($error);
        }
        return array($model->alias => $res);
    }

    /**
     * Implement the C in CRUD. Calls to ``Model::save()`` without $model->id
     * set arrive here.
     */
    public function create(Model $model, $fields = null, $values = null) {
        $data = array_combine($fields, $values);
        $data['apiKey'] = $this->config['apiKey'];
        $json = $this->Http->post('http://example.com/api/set.json', $data);
        $res = json_decode($json, true);
        if (is_null($res)) {
            $error = json_last_error();
            throw new CakeException($error);
        }
        return true;
    }

    /**
     * Implement the U in CRUD. Calls to ``Model::save()`` with $Model->id
     * set arrive here. Depending on the remote source you can just call
     * ``$this->create()``.
     */
    public function update(Model $model, $fields = null, $values = null,
                           $conditions = null) {
        return $this->create($model, $fields, $values);
    }

    /**
     * Implement the D in CRUD. Calls to ``Model::delete()`` arrive here.
     */
    public function delete(Model $model, $id = null) {
        $json = $this->Http->get('http://example.com/api/remove.json', array(
            'id' => $id[$model->alias . '.id'],
            'apiKey' => $this->config['apiKey'],
        ));
        $res = json_decode($json, true);
        if (is_null($res)) {
            $error = json_last_error();
            throw new CakeException($error);
        }
        return true;
    }


    protected function get_access_token($conditions) {
        if (empty($conditions) || empty($conditions['code'])) {
            return null;
        }

        $curl = curl_init();
        $options = array(
            CURLOPT_URL => $this->config['api_wx_url'].'/sns/oauth2/access_token?appid='.WX_APPID.'&secret='.WX_SECRET.'&code='. $conditions['code'] .'&grant_type=authorization_code',
            CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS
            CURLOPT_POSTFIELDS => '',
        );
        curl_setopt_array($curl,($options + $this->wx_curl_option_defaults));
        return curl_exec($curl);
    }

    protected function auth_token($conditions) {
        if (empty($conditions) || empty($conditions['token']) || empty($conditions['openid'])) {
            return null;
        }

        $curl = curl_init();
        $options = array(
            CURLOPT_URL => $this->config['api_wx_url'].'/sns/auth?access_token='.$conditions['token'].'&openid='.$conditions['openid'],
            CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS
            CURLOPT_POSTFIELDS => '',
        );
        curl_setopt_array($curl,($options + $this->wx_curl_option_defaults));
        return curl_exec($curl);
    }
}