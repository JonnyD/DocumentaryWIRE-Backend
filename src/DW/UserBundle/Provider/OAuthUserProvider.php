<?php

namespace DW\UserBundle\Provider;

use DW\UserBundle\Entity\Facebook;
use DW\UserBundle\Entity\User;
use DW\UserBundle\Enum\UserStatus;
use DW\UserBundle\Service\FacebookService;
use DW\UserBundle\Service\RoleService;
use DW\UserBundle\Service\UserService;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseOAuthUserProvider;

class OAuthUserProvider extends BaseOAuthUserProvider
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var RoleService
     */
    private $roleService;

    /**
     * @var FacebookService
     */
    private $facebookService;

    /**
     * @var DataManager
     */
    private $dataManager;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @param UserService $userService
     * @param RoleService $roleService
     * @param FacebookService $facebookService
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(
        UserService $userService,
        RoleService $roleService,
        FacebookService $facebookService,
        DataManager $dataManager,
        FilterManager $filterManager,
        EncoderFactoryInterface $encoderFactory)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->facebookService = $facebookService;
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @param UserInterface $user
     * @param UserResponseInterface $response
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $profilePicture = $response->getProfilePicture();
        $accessToken = $response->getAccessToken();
        $facebookId = $response->getResponse()["id"];

        $facebook = $this->facebookService->getFacebookByUser($user);
        if ($facebook == null) {
            $facebook = new Facebook();
        }

        $facebook->setUser($user);
        $facebook->setFacebookId($facebookId);
        $facebook->setAccessToken($accessToken);
        $facebook->setAvatarFull($profilePicture);

        if ($user->getAvatar() == null) {
            $avatar = $this->getImageFromUrl($profilePicture);
            $user->setAvatar($avatar);
        }

        $this->facebookService->save($facebook);
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $profilePicture = $response->getProfilePicture();
        $accessToken = $response->getAccessToken();
        $email = $response->getEmail();
        $facebookId = $response->getResponse()["id"];
        $name = $response->getRealName();

        $facebook = $this->facebookService->getFacebookByFacebookId($facebookId);
        if ($facebook != null) {
            $user = $facebook->getUser();
        } else {
            $user = $this->userService->getUserByEmail($email);
            if ($user != null) {
                $facebook = $user->getFacebook();
                if ($facebook == null) {
                    $facebook = new Facebook();
                }
                $facebook->setUser($user);
                $facebook->setFacebookId($facebookId);
                $facebook->setAccessToken($accessToken);
                $facebook->setAvatarFull($profilePicture);
                $this->facebookService->save($user);
            } else {
                $username = uniqid(mt_rand());
                if ($name != null && $name != "") {
                    $username = str_replace(' ', '', $name);
                    $username = preg_replace('/[^A-Za-z0-9\-]/', '', $username);
                }
                $username = strtolower($username);

                $user = new User();
                $user->setUsername($username);
                $user->setEmail($email);
                $user->setStatus(UserStatus::ACTIVE);

                $user->setSalt(uniqid(mt_rand()));
                $password = sha1(uniqid(mt_rand(), true));
                $encoder = $this->encoderFactory->getEncoder($user);
                $password = $encoder->encodePassword($password, $user->getSalt());
                $user->setPassword($password);

                $facebook = new Facebook();
                $facebook->setUser($user);
                $facebook->setFacebookId($facebookId);
                $facebook->setAccessToken($accessToken);
                $facebook->setAvatarFull($profilePicture);
                $user->setFacebook($facebook);

                $avatar = $this->getImageFromUrl($profilePicture);
                $user->setAvatar($avatar);

                $role = $this->roleService->getRoleByName("user");
                $user->addRole($role);

                $this->userService->confirmUser($user);
            }
        }

        $this->userService->loginUser($user);
        return $user;
    }

    private function getImageFromUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $headers = get_headers($url);
            $responseCode = substr($headers[0], 9, 3);
            if($responseCode != "404") {
                $contents = @file_get_contents($url);
                if ($contents != false) {
                    if (strpos($url, "?")) {
                        $urlArray = explode("?", $url, 2);
                        $url = $urlArray[0];
                    }
                    $filename = sha1(uniqid(mt_rand(), true));
                    $ext = pathinfo($url, PATHINFO_EXTENSION);
                    $tmpImageName = $filename . '.' . $ext;
                    $tmpImagePathRel = 'uploads/tmp/' . $tmpImageName;
                    file_put_contents($tmpImagePathRel, $contents);

                    $processedImage = $this->dataManager->find('avatar200', $tmpImagePathRel);
                    $response = $this->filterManager->applyFilter($processedImage, 'avatar200');
                    $avatar = $response->getContent();
                    unlink($tmpImagePathRel); // eliminate unfiltered temp file.
                    $permanentFolderPath = 'uploads/avatar/';
                    $permanentImagePath = $permanentFolderPath . $tmpImageName;
                    $f = fopen($permanentImagePath, 'w');
                    fwrite($f, $avatar);
                    fclose($f);

                    return $tmpImageName;
                }
            }
        }
    }
}