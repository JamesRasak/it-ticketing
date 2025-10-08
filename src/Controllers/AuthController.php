<?php
declare(strict_types=1);

namespace App\Controllers;
use function App\{view,redirect,db,flash,csrf_token,current_user};
use App\Models\User;

class AuthController {
    public function showLogin(): void { if (current_user()) redirect('/'); view('auth/login.php', ['title'=>'Login']); }
    public function login(): void {
        $email=trim($_POST['email']??''); $password=$_POST['password']??''; $user=User::findByEmail($email);
        if(!$user || !password_verify($password,$user['password_hash'])){ flash('danger','Invalid credentials'); redirect('/login'); }
        $_SESSION['user']=['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
        session_regenerate_id(true); db()->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')->execute([$user['id']]);
        redirect('/');
    }
    public function showRegister(): void { if (current_user()) redirect('/'); view('auth/register.php', ['title'=>'Register']); }
    public function register(): void {
        $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $password=$_POST['password']??''; $confirm=$_POST['password_confirmation']??'';
        if(!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password)<8 || $password!==$confirm){ flash('danger','Invalid input (password >= 8 chars, valid email).'); redirect('/register'); }
        if(User::findByEmail($email)){ flash('danger','Email already in use.'); redirect('/register'); }
        User::create($name,$email,$password); flash('success','Account created! Please log in.'); redirect('/login');
    }
    public function logout(): void { $_SESSION=[]; if(ini_get('session.use_cookies')){ $p=session_get_cookie_params(); setcookie(session_name(),' ',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']); } session_destroy(); redirect('/login'); }
}
