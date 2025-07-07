<?php
// filepath: backend/src/model/user.php

require_once __DIR__ . '/conexion.php';

class UserModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Buscar usuario por nombre
    public function getUserByName($nombre)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE nombre = ?");
        $stmt->execute([$nombre]);
        return $stmt->fetch();
    }
}