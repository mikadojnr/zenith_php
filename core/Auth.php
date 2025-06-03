<?php

namespace Core;

use App\Models\User;
use Google_Client;
use Google_Service_Oauth2;

class Auth
{
    private static ?User $user = null;

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            self::login($user);
            return true;
        }
        
        return false;
    }

    public static function login(array $user): void
    {
        Session::set('user_id', $user['id']);
        self::$user = new User($user);
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$user = null;
    }

    public static function user(): ?User
    {
        if (self::$user === null && Session::has('user_id')) {
            $userData = User::find(Session::get('user_id'));
            if ($userData) {
                self::$user = new User($userData);
            }
        }
        
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function getGoogleClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $client->addScope('email');
        $client->addScope('profile');
        
        return $client;
    }

    public static function handleGoogleCallback(string $code): bool
    {
        $client = self::getGoogleClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);
        
        if (isset($token['error'])) {
            return false;
        }
        
        $client->setAccessToken($token);
        $service = new Google_Service_Oauth2($client);
        $userInfo = $service->userinfo->get();
        
        // Find or create user
        $user = User::findByEmail($userInfo->email);
        
        if (!$user) {
            $userId = User::create([
                'name' => $userInfo->name,
                'email' => $userInfo->email,
                'google_id' => $userInfo->id,
                'avatar' => $userInfo->picture,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $user = User::find($userId);
        }
        
        self::login($user);
        return true;
    }
}