<?php

namespace DW\UserBundle\Entity;

class Facebook
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $facebookId;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $refreshToken;

    /**
     * @var string
     */
    private $avatarFull;

    /**
     * @var string
     */
    private $avatarThumb;

    /**
     * @var User
     */
    private $user;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @param string $facebookId
     */
    public function setFacebookId(string $facebookId)
    {
        $this->facebookId = $facebookId;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken(string $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return string
     */
    public function getAvatarFull()
    {
        return $this->avatarFull;
    }

    /**
     * @param string $avatarFull
     */
    public function setAvatarFull(string $avatarFull)
    {
        $this->avatarFull = $avatarFull;
    }

    /**
     * @return string
     */
    public function getAvatarThumb()
    {
        return $this->avatarThumb;
    }

    /**
     * @param string $avatarThumb
     */
    public function setAvatarThumb(string $avatarThumb)
    {
        $this->avatarThumb = $avatarThumb;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }
}