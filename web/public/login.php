<?php
// Page de connexion au système de monitoring ECG
session_start();
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../includes/functions.php';

$pageTitle = "Connexion";

// Process login if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données soumises
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validation basique
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Veuillez remplir tous les champs.';
        // Stay on the same page
    } elseif (!verifyCsrfToken($csrf_token)) {
        $_SESSION['error'] = 'Session expirée. Veuillez réessayer.';
        // Stay on the same page
    } else {
        try {
            // Connexion à la base de données
            $db = getDbConnection();
            
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
                    $token = bin2hex(random_bytes(32)); // Generate a secure random token
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 jours
                    
                    // Stocker le token en base de données
                    $query = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        'user_id' => $user['id'],
                        'token' => hashData($token),
                        'expires_at' => date('Y-m-d H:i:s', $expiry)
                    ]);
                    
                    // Créer le cookie
                    setcookie('remember_token', $token, $expiry, '/', '', false, true);
                }
                
                $_SESSION['success'] = 'Connexion réussie.';
                redirect('/public/index.php');
                exit;
            } else {
                $_SESSION['error'] = 'Identifiants incorrects.';
                // Stay on the same page
            }
        } catch (PDOException $e) {
            global $DEBUG;
            $_SESSION['error'] = 'Erreur de connexion: ' . ($DEBUG ? $e->getMessage() : 'Contactez l\'administrateur.');
            // Stay on the same page
        }
    }
}

// Display the login form
include_once '../includes/header.php';
?>

<div class="row mt-5 justify-content-center">
    <div class="col-md-6">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sign-in-alt me-2"></i>Connexion</h3>
            </div>
            <div class="card-body">
                <form method="post">
                    <?php
                    // Génération d'un token CSRF
                    $csrf_token = generateCsrfToken();
                    ?>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Se connecter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?> 