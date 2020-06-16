<?php namespace Hampel\UserFindCriteria\XF\Api\Controller;

use XF\Mvc\Entity\Entity;
use XF\Mvc\ParameterBag;

class Users extends XFCP_Users
{
	public function actionGetFindCriteria(ParameterBag $params)
	{
		$user_id = $this->filter('user_id', 'int');
		$email = $this->filter('email', 'str');
		$username = $this->filter('username', 'str');

		/** @var \XF\Entity\User $user */
		$user = null;

		// try user_id first
		if ($user_id)
		{
			/** @var \XF\Finder\User $finder */
			$finder = $this->finder('XF:User');

			$user = $finder->with('api')
			               ->where('user_id','=', $user_id)
			               ->fetchOne();
		}

		// if we didn't succeed with user_id, try email, if we have it
		if (!$user)
		{
			/** @var \XF\Finder\User $finder */
			$finder = $this->finder('XF:User');

			$user = $finder->with('api')
			               ->where('email','=', $email)
			               ->fetchOne();
		}

		// still no luck, let's try username, if we have it
		if (!$user && $username)
		{
			/** @var \XF\Finder\User $finder */
			$finder = $this->finder('XF:User');

			$user = $finder->with('api')
			               ->where('username','=', $username)
			               ->fetchOne();
		}

		// no user found - fail now
		if (!$user)
		{
			throw $this->exception(
				$this->notFound(\XF::phrase('requested_page_not_found'))
			);
		}

		$result = [
			'user' => $user->toApiResult(Entity::VERBOSITY_VERBOSE),
			'urls' => [
				'api' => $this->buildLink('full:users', $user),
				'public' => $this->app->router('public')->buildLink('full:members', $user),
				'admin' => $this->app->router('admin')->buildLink('full:users/edit', $user),
			]
		];

		return $this->apiResult($result);
	}
}
