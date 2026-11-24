<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Session, Validator, View};
use App\Repositories\UserRepository;
use PDOException;

final class AuthController
{
    /** Display and process registration. */
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->form('register');
            return;
        }
        $data = [
            'full_name' => input('full_name'),
            'email' => input('email'),
            'student_type' => input('student_type'),
            'password' => rawInput('password'),
        ];
        $errors = (new Validator())->register($data);
        if (!$errors && (new UserRepository())->findByEmail($data['email'])) {
            $errors['email'] = 'Bu e-posta kullanılıyor.';
        }
        if ($errors) {
            $this->fail('register', $errors, $data);
        }
        try {
            (new UserRepository())->create($data + ['password_hash' => Auth::hash($data['password'])]);
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            $this->fail('register', ['email' => 'Bu e-posta kullanılıyor.'], $data);
        }
        Session::flash('message', 'Kaydınız oluşturuldu. Giriş yapabilirsiniz.');
        redirect('/giris');
    }

    /** Display and process login. */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->form('login');
            return;
        }
        $data = ['email' => input('email')];
        if (time() < ($_SESSION['locked_until'] ?? 0)) {
            $this->fail('login', ['general' => 'Çok fazla deneme. Lütfen 30 saniye bekleyin.'], $data);
        }
        $user = (new UserRepository())->findByEmail($data['email']);
        if (!$user || !Auth::verify(rawInput('password'), $user['password_hash'])) {
            $_SESSION['attempts'] = ($_SESSION['attempts'] ?? 0) + 1;
            if ($_SESSION['attempts'] >= 5) {
                $_SESSION['locked_until'] = time() + 30;
                $_SESSION['attempts'] = 0;
            }
            $this->fail('login', ['general' => 'E-posta veya parola hatalı'], $data);
        }
        Auth::login($user);
        redirect($user['role'] === 'admin' ? '/yonetim' : '/bugun');
    }

    /** End the current user's session. */
    public function logout(): void
    {
        Auth::logout();
        Session::flash('message', 'Çıkış yaptınız.');
        redirect('/giris');
    }

    private function form(string $name): void
    {
        View::render('auth/' . $name, ['errors' => Session::pull('errors', []), 'old' => Session::pull('old', [])]);
    }

    private function fail(string $form, array $errors, array $old): never
    {
        unset($old['password']);
        Session::flash('errors', $errors);
        Session::flash('old', $old);
        redirect($form === 'login' ? '/giris' : '/kayit');
    }
}
