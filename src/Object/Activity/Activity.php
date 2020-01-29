<?php
 namespace App\Object\Activity;

 use App\Object\Activity\Data\Data;

 abstract class Activity
 {
     /**
      * @var Data
      */
     private $data;

     /**
      * @var string
      */
     private $name;

     /**
      * @var string
      */
     private $avatar;

     /**
      * @var string
      */
     private $username;

     /**
      * @return Data
      */
     public function getData(): Data
     {
         return $this->data;
     }

     /**
      * @param Data $data
      */
     public function setData(Data $data): void
     {
         $this->data = $data;
     }

     /**
      * @return string
      */
     public function getName(): string
     {
         return $this->name;
     }

     /**
      * @param string $name
      */
     public function setName(string $name): void
     {
         $this->name = $name;
     }

     /**
      * @return string
      */
     public function getAvatar(): string
     {
         return $this->avatar;
     }

     /**
      * @param string $avatar
      */
     public function setAvatar(string $avatar): void
     {
         $this->avatar = $avatar;
     }

     /**
      * @return string
      */
     public function getUsername()
     {
         return $this->username;
     }

     /**
      * @param string $username
      */
     public function setUsername(string $username)
     {
         $this->username = $username;
     }

     /**
      * @return array
      */
     public function toArray()
     {
 {}        return [
             'data' => $this->data->toArray(),
             'name' => $this->name,
             'avatar' => $this->avatar,
             'username' => $this->username
         ];
     }
 }