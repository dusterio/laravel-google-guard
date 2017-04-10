<?php

namespace Dusterio\LaravelGoogleGuard;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Session\SessionInterface;
use Laravel\Socialite\Facades\Socialite;

class GoogleGuard implements StatefulGuard {
    use GuardHelpers;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * Hydrate a dummy instance of this glass
     *
     * @var string
     */
    protected $userClass;

    /**
     * Remember user for this amount of seconds.
     *
     * @var int
     */
    protected $timeout;

    /**
     * List of allowed user ids.
     *
     * @var array
     */
    protected $whitelist;

    /**
     * GoogleGuard constructor.
     * @param SessionInterface $session
     * @param int $timeout
     * @param string $userClass
     * @param array $whitelist
     */
    public function __construct(SessionInterface $session, $timeout = 3600, $userClass = '\App\User', $whitelist = [])
    {
        $this->session = $session;
        $this->timeout = $timeout;
        $this->userClass = $userClass;
        $this->whitelist = $whitelist;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return ! $this->guest();
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        $this->checkTimeouts();

        return ! $this->session->has('socialite_token');
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        $this->checkTimeouts();

        if (! $this->session->has('socialite_token')) return null;
        if ($this->session->has('google_guard_user')) return $this->session->get('google_guard_user');

        try {
            $user = Socialite::driver('google')->userFromToken($this->session->get('socialite_token'));
        } catch (\Exception $e) {
        }

        if (! isset($user) || ! $user) return $this->flushSession();

        $userModel = $this->hydrateUserModel($user);
        $this->session->set('google_guard_user', $userModel);

        return $userModel;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        return $this->user()->getEmail();
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return true;
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return void
     */
    public function setUser(Authenticatable $user)
    {
        $this->session->set('google_guard_user', $user);
        $this->session->set('socialite_token', $user->token);
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array $credentials
     * @param  bool $remember
     * @param  bool $login
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false, $login = true)
    {
        return ! empty(Socialite::driver('google')->userFromToken($credentials['token']));
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param  array $credentials
     * @return bool
     */
    public function once(array $credentials = [])
    {
        $this->user = Socialite::driver('google')->userFromToken($credentials['token']);
    }

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  bool $remember
     * @return void
     */
    public function login(Authenticatable $user, $remember = false)
    {
        $this->session->set('google_guard_user', $user);
        $this->session->set('socialite_token', $user->token);

        $remember ? $this->session->set('google_guard_signed_at', time()) : $this->session->remove('google_guard_signed_at');
    }

    /**
     * @param $user
     * @throws AuthorizationException
     */
    public function loginUsingSocialite($user)
    {
        if (! empty($this->whitelist) && ! in_array($user->getEmail(), $this->whitelist)) throw new AuthorizationException('You are not allowed to access this page');

        $this->login($this->hydrateUserModel($user));
    }

    /**
     * @param $user
     * @return mixed
     */
    public function hydrateUserModel($user)
    {
        return (new $this->userClass)->fill([
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'token' => $user->token
        ]);
    }

    /**
     * Log the given user ID into the application.
     *
     * @param  mixed $id
     * @param  bool $remember
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function loginUsingId($id, $remember = false)
    {
        return false;
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param  mixed $id
     * @return bool
     */
    public function onceUsingId($id)
    {
        return false;
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember()
    {
        return true;
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $this->flushSession();
    }

    /**
     * @return null
     */
    private function flushSession()
    {
        $this->session->remove('socialite_token');
        $this->session->remove('google_guard_user');
        $this->session->remove('google_guard_signed_at');
    }

    /**
     * @return null
     */
    private function checkTimeouts()
    {
        if ($this->session->has('google_guard_signed_at')
            && time() - $this->session->get('google_guard_signed_at') >= $this->timeout) {
            $this->flushSession();
        }
    }
}