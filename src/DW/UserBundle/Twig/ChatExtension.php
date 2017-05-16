<?php

namespace DW\UserBundle\Twig;

use DW\UserBundle\Service\SecurityService;
use DW\UserBundle\Service\UserService;
use Iflylabs\iFlyChat;

class ChatExtension extends \Twig_Extension
{
    /**
     * @var SecurityService
     */
    private $securityService;

    /**
     * @var string
     */
    private $appId;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param SecurityService $securityService
     * @param string $appId
     * @param string $apiKey
     */
    public function __construct(SecurityService $securityService, string $appId, string $apiKey)
    {
        $this->securityService = $securityService;
        $this->appId = $appId;
        $this->apiKey = $apiKey;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction("chat", [$this, "chat"])
        ];
    }

    public function chat()
    {
        $iflyChat = new iFlyChat($this->appId, $this->apiKey);

        $loggedInUser = $this->securityService->getLoggedInUser();
        $user = [
            'user_name' => $loggedInUser->getUsername(),
            'user_id' => (string) $loggedInUser->getId()
        ];
        $iflyChat->setUser($user);

        echo $iflyChat->getHtmlCode();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'chat_extension';
    }
}
