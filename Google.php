<?php
/**
 * Created by Svyatoslav Svitlychnyi <svitlychnyi@samsonos.com>
 * on 11.02.14 at 11:35
 */

namespace samson\social\google;

/**
 *
 * @author Svyatoslav Svitlychnyi <svitlychnyi@samsonos.com>
 * @copyright 2013 SamsonOS
 * @version
 */
class Google extends \samson\social\network\Network
{
    public $id = 'google';

    public $dbIdField = 'gp_id';

    public $socialURL = 'https://accounts.google.com/o/oauth2/auth';

    public $tokenURL = 'https://accounts.google.com/o/oauth2/token';

    public $userURL = 'https://www.googleapis.com/oauth2/v1/userinfo';
	
	public $requirements = array('socialnetwork');

    public function __HANDLER()
    {
        // Send http get request to retrieve VK code
        $this->redirect($this->socialURL, array(
            'client_id'     => $this->appCode,
            'redirect_uri'  => $this->returnURL(),
            'response_type' => 'code',
             'scope'        => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile' // vk - no scope; fb has scope, google has, but another
        ));
    }

    public function __token()
    {
        $code = & $_GET['code'];
        if (isset($code)) {

            // Send http get request to retrieve VK code
            $token = $this->post($this->tokenURL, array(
                'client_id' => $this->appCode,
                'client_secret' => $this->appSecret,
                'code' => $code,
                'redirect_uri' => $this->returnURL(),
                'grant_type'    => 'authorization_code' // google add grant type
            ));
            // take user's information using access token
            if (isset($token['access_token'])) {
                $userInfo = $this->get($this->userURL, array(
                    'access_token' => $token['access_token']
                ));
                $this->setUser($userInfo);
            }
        }
        parent::__token();
    }

    protected function setUser(array $userData, & $user = null)
    {
        $user = new \samson\social\User();

        $user->birthday = isset($userData['birthday'])?$userData['birthday']:0;
        $user->email = $userData['email'];
        $user->gender = $userData['gender'];
        $user->locale = $userData['locale'];
        $user->name = $userData['given_name'];
        $user->surname = $userData['family_name'];
        $user->socialID = $userData['id'];
        $user->photo =  $userData['picture'];


        parent::setUser($userData, $user);
    }
}
 