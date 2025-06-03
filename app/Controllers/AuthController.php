<?php

namespace App\Controllers;

use Core\Auth;
use Core\Session;
use App\Models\User;

class AuthController extends BaseController
{
    public function showLogin()
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
        
        $this->view('auth.login', [
            'title' => 'Login',
            'googleUrl' => Auth::getGoogleClient()->createAuthUrl()
        ]);
    }

    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (Auth::attempt($email, $password)) {
            $this->redirect('/');
        } else {
            Session::flash('error', 'Invalid credentials');
            $this->redirect('/login');
        }
    }

    public function showRegister()
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
        
        $this->view('auth.register', [
            'title' => 'Register'
        ]);
    }

    public function register()
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Basic validation
        if (empty($name) || empty($email) || empty($password)) {
            Session::flash('error', 'All fields are required');
            $this->redirect('/register');
            return;
        }

        // Check if user exists
        if (User::findByEmail($email)) {
            Session::flash('error', 'Email already exists');
            $this->redirect('/register');
            return;
        }

        // Create user
        $userId = User::create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $user = User::find($userId);
        Auth::login($user);
        
        $this->redirect('/');
    }

    public function googleCallback()
    {
        $code = $_GET['code'] ?? null;
        
        if ($code && Auth::handleGoogleCallback($code)) {
            $this->redirect('/');
        } else {
            Session::flash('error', 'Google authentication failed');
            $this->redirect('/login');
        }
    }

    public function logout()
    {
        Auth::logout();
        $this->redirect('/login');
    }
}