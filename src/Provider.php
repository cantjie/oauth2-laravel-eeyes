<?php

namespace Cantjie\Oauth2;


class Provider{
    /**
     * @var integer
     */
    protected $clientID;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var string
     */
    protected $responseType = 'code';

    /**
     * @var string
     */
    protected $clientSecret ;

    /**
     * @var string
     */
    protected $state = null;

    /**
     * @var array
     */
    protected $scope = ['info-username.read','info-user_id.read','info-name.read'];

    /**
     * @var string
     */
    protected $authorizationUrl = 'https://account.eeyes.net/oauth/authorize';

    /**
     * @var string
     */
    protected $accessTokenUrl = 'https://account.eeyes.net/oauth/token';

    protected $scopeSeparator = ' ';

    /**
     * Provider constructor.
     * @param $options array
     */
    public function __construct($options = null)
    {
        $default = [
            'OAUTH_CLIENT_ID' => config('oauth.client.id'),
            'OAUTH_CLIENT_URI' => config('oauth.redirect_uri'),
            'OAUTH_CLIENT_SECRET' => config('oauth.client.secret'),
            'state' => url()->current(),
        ];
        $options = array_merge($default,$options);

        $this->clientID = $options['OAUTH_CLIENT_ID'];
        $this->redirectUri = $options['OAUTH_CLIENT_URI'];
        $this->clientSecret = $options['OAUTH_CLIENT_SECRET'];
        $this->state = $options['state'];
    }

    /**
     * only for web develop, not for api
     *
     * @return ResourceOwner|\Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed|null
     */
    public function getResourceOwner()
    {
        if(session('oauth2user')){
            return session('oauth2user');
        }elseif(isset($_GET['code'])){
            if(!($this->checkState())) {
                return null;
            }

            $token = $this->getAccessToken($_GET['code']);
            $user = static::createResourceOwnerArray($token);

            if(!isset($user['username'])){ //如果出错了，就重新登录
                $this->redirectToAuthorizationUrl();
            }else{
                $user['prePage'] = session('oauth2state');
                $resource_owner = new ResourceOwner($user);
                session(['oauth2user' => $resource_owner]);
                session(['oauth2state' => null]);
                return $resource_owner;
            }
        }else{
            $this->redirectToAuthorizationUrl();
        }

    }

    public function checkState(){
        if($_GET['state'] === session('oauth2state')){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param $refresh_token string
     * @return array
     */
    public function refreshToken($refresh_token)
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->post('https://account.eeyes.net/oauth/token',[
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh_token,
                'client_id' => $this->clientID,
                'client_secret' => $this->clientSecret,
                'scope' => $this->getDefaultScopes(),
            ]
        ]);

        return json_decode((string)$response->getBody(),true);

    }

    /**
     * @param array|string $token
     * @return array
     */
    public static function createResourceOwnerArray($token)
    {
        $client = new \GuzzleHttp\Client(['base_uri'=>'https://account.eeyes.net/api/user']);

        if(is_array($token)){
            $token = $token['access_token'];
        }

        $response = $client->request('GET','',[
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '. $token,
            ]
        ]);

        return json_decode((string)$response->getBody(),true);
    }

    /**
     * @param $code string
     * @return array
     */
    public function getAccessToken($code)
    {
        $http = new \GuzzleHttp\Client();

        $response = $http->post($this->accessTokenUrl,[
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'client_id'     => $this->clientID,
                'client_secret' => $this->clientSecret,
                'redirect_uri'  => $this->redirectUri,
                'code'          => $code,
            ]
        ]);

        return json_decode((string)$response->getBody(),true);
    }

    public function redirectToAuthorizationUrl()
    {
        session(['oauth2state'=> $this->state]);
        $url = $this->buildAuthorizationUrl();
        header('Location: '.$url);
    }

    protected function buildAuthorizationUrl()
    {
        $query = http_build_query([
            'client_id' => $this->clientID,
            'redirect_uri' => $this->redirectUri,
            'response_type' => $this->responseType,
            'scope' => $this->getDefaultScopes(),
            'state' => $this->state,
        ]);
        return $this->authorizationUrl.'?'.$query;
    }

    protected function getDefaultScopes()
    {
        return implode($this->scopeSeparator,$this->scope);
    }

    public static function logout()
    {
//        session([
//            'oauth2state'=>null,
//            'oauth2user'=>null,
//        ]);
        session()->flush();
        session()->save();
        redirect('https://cas.xjtu.edu.cn/logout');
    }

    public static function getResourceOwnerFromSession()
    {
        return session('oauth2user');
    }
}