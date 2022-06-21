<?php
require_once('User.php');

class UserMemoryDatabase
{
    private $database;

    public function __construct() {
        $this->database = [];
    }

    public function registerUser($id, $name, $admin) {
        $user = new User($id, $name, $admin);
        $id = $user->getId();
        $this->database[$id] = $user;
    }

    public function deleteUser($user) {
        $id = $user->getId();
        if(array_key_exists($id, $this->database)) {
            unset($this->database[$id]);
        }
    }

    public function checkUserIdExists($id) {
        $userIds = array_keys($this->database);
        return in_array($id, $userIds);
    }

    public function searchUserById($id) {
        $user = false;
        if($this->checkUserIdExists($id)) {
            $user = $this->database[$id];
        }
        return $user;
    }

    public function getAllUsers() {
        return array_values($this->database);
    }

    public function getAllUserNamesAdminStatus() {
        $names = [];
        foreach($this->database as $id => $user) {
            $isAdmin = $user->getAdminStatus();
            $names[] = $id . ' : ' . $user->getName() . ' : Admin - ' . $isAdmin;
        }
        return implode("\n", $names);
    }
}