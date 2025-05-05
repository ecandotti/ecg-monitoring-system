<?php
// Traitement du formulaire de connexion
session_start();
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../includes/functions.php';

// Vérification de la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/public/login.php');
}

// Vérification du token CSRF
if (!validateCSRFToken($_POST['csrf_token'])) {
    setError('Session expirée. Veuillez réessayer.');
    redirect('/public/login.php');
}

// Récupération des données soumises
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validation basique
if (empty($username) || empty($password)) {
    setError('Veuillez remplir tous les champs.');
    redirect('/public/login.php');
}

try {
    // Connexion à la base de données
    $db = connectToDatabase();
    
    // Requête pour vérifier les identifiants
    $query = "SELECT id, username, password, role FROM users WHERE username = :username LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Vérification de l'utilisateur et du mot de passe
    if ($user && verifyPassword($password, $user['password'])) {
        // Création de la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        // Si "Se souvenir de moi" est coché, créer un cookie
        if ($remember) {
            $token = generateRememberToken();
            $expiry = time() + (30 * 24 * 60 * 60); // 30 jours
            
            // Stocker le token en base de données
            $query = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                'user_id' => $user['id'],
                'token' => hashString($token),
                'expires_at' => date('Y-m-d H:i:s', $expiry)
            ]);
            
            // Créer le cookie
            setcookie('remember_token', $token, $expiry, '/', '', false, true);
        }
        
        setSuccess('Connexion réussie.');
        redirect('/public/index.php');
    } else {
        setError('Identifiants incorrects.');
        redirect('/public/login.php');
    }
} catch (PDOException $e) {
    setError('Erreur de connexion: ' . ($DEBUG ? $e->getMessage() : 'Contactez l\'administrateur.'));
    redirect('/public/login.php');
} 