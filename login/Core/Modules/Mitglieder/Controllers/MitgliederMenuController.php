<?php

namespace FSArch\Modules\Mitglieder\Controllers;

use FSArch\Core\Services\Auth;

/**
 * Mitglieder Menu Controller
 */
class MitgliederMenuController
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    public function showMenu(): void
    {
        if (!$this->auth->isLoggedIn()) {
            header('Location: /FHArch-oop/login');
            exit;
        }

        if (!$this->hasPermission('ADM-MI')) {
            echo "Keine Berechtigung für Mitgliederverwaltung";
            return;
        }

        // Render Mitglieder menu
        include __DIR__ . '/../Views/menu.php';
    }

    public function hasPermission(string $module): bool
    {
        $user = $this->auth->getCurrentUser();
        return $user && $this->auth->hasPermission($user, $module);
    }
}