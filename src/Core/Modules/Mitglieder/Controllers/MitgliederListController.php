<?php

namespace FSArch\Modules\Mitglieder\Controllers;

use FSArch\Core\Services\Auth;
use FSArch\Core\Services\Database;
use PDO;

/**
 * Mitglieder List Controller
 */
class MitgliederListController
{
    private Auth $auth;
    private PDO $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->pdo = Database::getConnection();
    }

    public function showList(): void
    {
        if (!$this->auth->isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        if (!$this->hasPermission('ADM-MI')) {
            echo "Keine Berechtigung für Mitgliederliste";
            return;
        }

        // Get members data
        $members = $this->getMembers();

        // Render list
        include __DIR__ . '/../Views/list.php';
    }

    private function getMembers(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT m.*, d.fd_vname, d.fd_name, d.fd_email
                FROM fv_mitglieder m
                LEFT JOIN fv_ben_dat d ON m.mi_id = d.be_mi_id
                ORDER BY d.fd_name, d.fd_vname
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error fetching members: ' . $e->getMessage());
            return [];
        }
    }

    public function hasPermission(string $module): bool
    {
        $user = $this->auth->getCurrentUser();
        return $user && $this->auth->hasPermission($user, $module);
    }
}