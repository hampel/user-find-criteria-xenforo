<?php namespace Hampel\UserFindCriteria\XF\Api\Controller;

use XF\Mvc\Entity\Entity;
use XF\Mvc\ParameterBag;

class Users extends XFCP_Users
{
	/**
	 * @api-desc Finds a single user based on matching criteria
	 *
	 * @api-in int $user_id
	 * @api-in str $email
	 * @api-in str $username
	 *
	 * @api-out User $user The user that matched the user_id, or email or username
	 * @api-out Urls[] $urls A list of urls (api, public, admin) to this user's profile
	 */
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
		if (!$user && $email)
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
				'api' => $this->app->router('api')->buildLink('canonical:users', $user),
				'public' => $this->app->router('public')->buildLink('canonical:members', $user),
				'admin' => $this->app->router('admin')->buildLink('canonical:users/edit', $user),
			]
		];

		return $this->apiResult($result);
	}
}
