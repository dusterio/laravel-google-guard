<?php

class DummyUser {
    public function getName()
    {
        return 'Denis';
    }

    public function getEmail()
    {
        return 'admin@.';
    }

    public $token = 'AAA';
}

class UserModel implements \Illuminate\Contracts\Auth\Authenticatable {
    public function fill() {
        return $this;
    }

    public function getAuthIdentifierName()
    {
    }

    public function getAuthIdentifier()
    {
    }

    public function getAuthPassword()
    {
    }

    public function getRememberToken()
    {
    }

    public function setRememberToken($value)
    {
    }

    public function getRememberTokenName()
    {
    }

    public $token = 'AAA';
}


/**
 * Class GoogleGuardTest
 */
class GoogleGuardTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function user_is_logged_out_after_timeout()
    {
        $session = new \Illuminate\Session\Store('aaa', new SessionHandler());
        $guard = new \Dusterio\LaravelGoogleGuard\GoogleGuard($session, 100, UserModel::class);
        $guard->loginUsingSocialite(new DummyUser());
        $this->assertTrue($guard->check());

        $session->set('google_guard_signed_at', time() - 10000);
        $this->assertTrue($guard->guest());
        $this->assertFalse($session->has('google_guard_user'));
    }

    /**
     * @test
     */
    public function only_whitelisted_users_are_allowed_if_whitelist_is_not_empty()
    {
        $session = new \Illuminate\Session\Store('aaa', new SessionHandler());
        $guard = new \Dusterio\LaravelGoogleGuard\GoogleGuard($session, 100, UserModel::class, ['aaa@hotmail.com']);
        $this->setExpectedException(\Illuminate\Auth\Access\AuthorizationException::class);
        $guard->loginUsingSocialite(new DummyUser());
        $this->assertFalse($guard->check());
    }
}