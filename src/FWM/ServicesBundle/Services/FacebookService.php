<?php

namespace FWM\ServicesBundle\Services;

class FacebookService
{

	public static function getUserFriends($facebook)
	{
		return $facebook->api('/me/friends');
	}

	/*
	 * 	FacebookService::publishStream( array(
	 * 				'message' => 'Check out this funny article',
	 * 				'link' => 'http://www.example.com/article.html',
	 * 				'picture' => 'http://jchutchins.net/site/wp-content/uploads/2009/06/facebook-logo.jpg',
	 * 				'name' => 'Article Title',
	 * 				'caption' => 'Caption for the link',
	 * 				'description' => 'Longer description of the link'
	 * 	));
	 */

	public static function publishStream($facebook, array $data, $user = 'me')
	{

		return $statusUpdate = $facebook->api('/' . $user . '/feed', 'post', $data);
	}

 	

	public static function userLike($facebook, $pageId, $userId)
	{
		$data = $facebook->api('' . $pageId . '/members/' . $userId . '');
		if (array_key_exists('0', $data['data']))
		{
			if (count($data['data'][0]) == 2)
				return true;
		}
		return false;
	}

        
        public static function getRequests($requestsId, $accesToken)
	{
		$ch = curl_init();
		$url = 'https://graph.facebook.com/?ids=' . $requestsId . '&method=get&access_token='.$accesToken;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $object = json_decode(curl_exec($ch));

		return $array = \FWM\ServicesBundle\Services\ArrayService::objectToArray($object);
	}

	public static function sendRequest($facebook, $data)
	{
		return $facebook->api(urlencode($data['userId']) . '/apprequests?message=' . urlencode($data['message']) . '&data=' . urlencode($data['data']) . '&method=post');
	}

	public static function deleteRequest($requestId, $appId, $secret)
	{
		return file_get_contents('https://graph.facebook.com/' . $requestId . '?' . implode('=', \FWM\ServicesBundle\Services\FacebookService::getAdminAccessToken($appId, $secret)) . '&method=delete');
	}

	public static function getUserRequests($facebook, $data)
	{
		return $facebook->api('/' . urlencode($data['userId']) . '/apprequests?&method=post');
	}
        
        public static function getAdminAccessToken($appId, $secret)
	{
		$args = array(
			'grant_type' => 'client_credentials',
			'client_id' => $appId,
			'client_secret' => $secret
		);

		$ch = curl_init();
		$url = 'https://graph.facebook.com/oauth/access_token';
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
		$data = curl_exec($ch);
		return $access = explode('=', $data);
	}

}
