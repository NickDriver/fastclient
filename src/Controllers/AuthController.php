<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Models\User;

class AuthController
{
    public function showLogin(): string
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function login(): string
    {
        if (!verify_csrf()) {
            flash('error', 'Invalid request. Please try again.');
            redirect('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation
        $errors = [];
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        }
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }

        if (!empty($errors)) {
            $_SESSION['old'] = ['email' => $email];
            $_SESSION['errors'] = $errors;
            redirect('/login');
        }

        if (Auth::attempt($email, $password)) {
            unset($_SESSION['old'], $_SESSION['errors']);

            // Redirect to intended URL or dashboard
            $intended = $_SESSION['intended_url'] ?? '/dashboard';
            unset($_SESSION['intended_url']);

            flash('success', 'Welcome back!');
            redirect($intended);
        }

        $_SESSION['old'] = ['email' => $email];
        flash('error', 'Invalid email or password.');
        redirect('/login');
    }

    public function showRegister(): string
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }

        return view('auth.register');
    }

    public function register(): string
    {
        if (!verify_csrf()) {
            flash('error', 'Invalid request. Please try again.');
            redirect('/register');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirmation'] ?? '';

        // Validation
        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'Name is required';
        }

        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        } elseif (User::findByEmail($email)) {
            $errors['email'] = 'This email is already registered';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if ($password !== $passwordConfirm) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            $_SESSION['old'] = ['name' => $name, 'email' => $email];
            $_SESSION['errors'] = $errors;
            redirect('/register');
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        Auth::login($user);

        unset($_SESSION['old'], $_SESSION['errors']);
        flash('success', 'Welcome to FastClient! Your account has been created.');
        redirect('/dashboard');
    }

    public function logout(): never
    {
        if (!verify_csrf()) {
            redirect('/dashboard');
        }

        Auth::logout();
        flash('success', 'You have been logged out.');
        redirect('/login');
    }
}
