<?php

namespace Cantjie\Oauth2;

class ResourceOwner{

    protected $username;

    protected $userID;

    protected $name;

    protected $prePage = null;

    /**
     * ResourceOwner constructor.
     * @param $user_info array
     */
    public function __construct($user_info)
    {
        foreach ($user_info as $key => $value){
            $this->{$key} = $value;
        }
    }

    /**
     * get prePage and set it null
     *
     * @return mixed
     */
    public function getPrePage()
    {
        $prePage = $this->prePage;
        $this->prePage = null;
        return $prePage;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userID;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}