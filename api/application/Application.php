<?php

require("user/User.php");
require("db/DB.php");
require("chat/Chat.php");
require("game/Game.php");
require("gamer/Gamer.php");
require("map/Map.php");

class Application {
    function __construct()
    {
        $config = json_decode(file_get_contents('./config/config.json'), true);
        $db = new DB($config["DataBase"]);
        $map = new Map($db);
        $this->user = new User($db);
        $this->chat = new Chat($db);
        $this->game = new Game($db, $map,$config["Game"]);
        $this->gamer = new Gamer($db, $map);
    }

    // Функция проверки типа полученных значений в запросе
    private function checkParamsType($params){
        foreach ($params as $param => $value){
            switch($param){
                case "token":
                    if(is_numeric($value)) return false;
                    break;
                case "method":
                    if(is_numeric($value)) return false;
                    break;
                case "login":
                    if(is_numeric($value)) return false;
                    break;
                case "password":
                    if(is_numeric($value)) return false;
                    break;
                case "name":
                    if(is_numeric($value)) return false;
                    break;
                // TODO
                // ...
                // ...
                case "messageTo":
                    if(!is_numeric($value)) return false;
                    break;
                case "unitType":
                    if(!is_numeric($value)) return false;
                    break;
                case "village":
                    if(!is_numeric($value)) return false;
                    break;
                // TODO
                // ...
            }
        }
        return true;
    }

    ////////////////////////////////////////
    //////////////forUser///////////////////
    ////////////////////////////////////////

    public function login($params)
    {
        if ($this->checkParamsType($params)) {
            if ($params['login'] && $params['password']){
                return $this->user->login($params['login'], $params['password']);
            }
        }
    }

    public function registration($params)
    {
        if ($this->checkParamsType($params)) {
            [
                'login' => $login,
                'password' => $password,
                'name' => $name
            ] = $params;
            if ($login && $password && $name) {
                return $this->user->registration($login, $password, $name);
            }
        }
    }

    public function logout($params) {
        if ($this->checkParamsType($params)) {
            $user = $this->user->getUser($params['token']);
            if ($user) {
                return $this->user->logout($user);
            }
        }
    }

    ////////////////////////////////////////
    //////////////forChat///////////////////
    ////////////////////////////////////////

    public function sendMessage($params, $type)
    {
        if($this->checkParamsType($params)){
            [
                'token' => $token,
                'message' => $message,
                'messageTo' => $messageTo
            ] = $params;
            if ($type === "all") {
                $messageTo = "NULL";
            }
            $user = $this->user->getUser($token);
            if ($user && $message) {
                return $this->chat->sendMessage($user, $message, $messageTo);
            }
        }
    }

    public function getMessages($params)
    {
        if($this->checkParamsType($params)){
            if ($params['hash']) {
                $user = $this->user->getUser($params['token']);
                if ($user) {
                    return $this->chat->getMessages($params['hash'], $user);
                }
            }
        }
    }

    public function getLoggedUsers($params)
    {
        if($this->checkParamsType($params)){
            $user = $this->user->getUser($params['token']);
            if ($user) {
                return $this->chat->getLoggedUsers();
            }
        }
    }

    ////////////////////////////////////////
    //////////////forGame///////////////////
    ////////////////////////////////////////


    public function getMap($params)
    {
        if ($this->checkParamsType($params)) {
            $user = $this->user->getUser($params['token']);
            if ($user) {
                return $this->game->getMap();
            }
        }
    }

    public function getUnitsTypes($params)
    {
        if ($this->checkParamsType($params)) {
            $user = $this->user->getUser($params['token']);
            if ($user) {
                return $this->game->getUnitsTypes();
            }
        }
    }

    public function getScene($params)
    {
        if ($this->checkParamsType($params)) {
            $user = $this->user->getUser($params['token']);
            if ($user) {
                return $this->game->getScene($params['unitsHash'], $params['mapHash']);
            }
        }
    }
    ////////////////////////////////////////
    //////////////forGamer//////////////////
    ////////////////////////////////////////
    public function getCastle($params)
    {
        if ($this->checkParamsType($params)) {
            $user = $this->user->getUser($params['token']);
            if ($user) {
                $gamer = $this->gamer->getGamer($user);
                if (!$gamer) {
                    $this->gamer->addCastle($user);
                    $gamer = $this->gamer->getGamer($user);
                }
                return array(
                    'castle' => $gamer
                );
            }
        }
    }

    public function upgradeCastle($params)
    {
        if ($this->checkParamsType($params)) {
            $user = $this->user->getUser($params['token']);
            if ($user) {
                $gamer = $this->gamer->getGamer($user);
                if ($gamer) {
                    return $this->gamer->upgradeCastle($gamer);
                }
            }
        }
    }

    public function buyUnit($params)
    {
        if ($this->checkParamsType($params)) {
            if ($params['unitType']){
                $user = $this->user->getUser($params['token']);
                if ($user) {
                    $gamer = $this->gamer->getGamer($user);
                    if ($gamer) {
                        return $this->gamer->buyUnit($gamer, $params['unitType']);
                    }
                }
            }
        }
    }

    public function robVillage($params)
    {
        if ($this->checkParamsType($params)) {
            $user = $this->user->getUser($params['token']);
            if ($user) {
                $gamer = $this->gamer->getGamer($user);
                $village = $this->game->getVillage($params['village']);
                if ($gamer && $village) {
                    return $this->gamer->robVillage($gamer, $village);
                }
            }
        }
    }

    public function destroyVillage($params)
    {
        if ($this->checkParamsType($params)) {
            $user = $this->user->getUser($params['token']);
            if ($user) {
                $gamer = $this->gamer->getGamer($user);
                $village = $this->game->getVillage($params['village']);
                if ($gamer && $village) {
                    return $this->gamer->destroyVillage($gamer, $village);
                }
            }
        }
    }

    public function destroyCastle($params)
    {
        if ($this->checkParamsType($params)) {
            $userId = $this->user->getUser($params['token']);
            if ($userId && $params['castle']) {
                $castle = $this->game->getCastle($params['castle']);
                $unitsInCastle = $this->gamer->getUnitsinCastle($params['castle']);
                $gamer = $this->gamer->getGamer($userId);
                if ($gamer && $castle && !$unitsInCastle) {
                    return $this->gamer->destroyCastle($gamer, $castle);
                }
            }
        }
    }

    public function updateUnits($params) {
        if ($this->checkParamsType($params)) {
            $userId = $this->user->getUser($params['token']);  
            if  ($userId){
                $gamer = $this->gamer->getGamer($userId);
                if ($gamer) {
                    $time = $this->gamer->updateUnits($gamer, $params['myUnits'], $params['otherUnits'], $params['villages']);
                    if ($time) {
                        $this->game->updateMap($time);
                    }
                return true;
                }
            }   
        }
    }
}