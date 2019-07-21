<?php

namespace App\DataFixtures;

use App\Entity\VideoSource;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class VideoSourceFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $fiftysix56 = $this->createVideoSource("56", false);
        $blip = $this->createVideoSource("Blip", false);
        $dailyMotion = $this->createVideoSource("Daily Motion", false);
        $disclose = $this->createVideoSource("Disclose", false);
        $forumNetwork = $this->createVideoSource("Forum Network", false);
        $krishnatube = $this->createVideoSource("Krishnatube", false);
        $megavideo = $this->createVideoSource("megavideo", false);
        $myspace = $this->createVideoSource("MySpace", false);
        $novamov = $this->createVideoSource("Novamov", false);
        $pbs = $this->createVideoSource("PBS", false);
        $rutube = $this->createVideoSource("Rutube", false);
        $sevenload = $this->createVideoSource("Sevenload", false);
        $snagfilms = $this->createVideoSource("Snagfilms", false);
        $stagevu = $this->createVideoSource("stagevu", false);
        $tudou = $this->createVideoSource("Tudou", false);
        $veoh = $this->createVideoSource("Veoh", false);
        $viddler = $this->createVideoSource("Viddler", false);
        $vimeo = $this->createVideoSource("Vimeo", false);
        $youtubePlaylist = $this->createVideoSource("youtube-playlist", false);
        $youtube = $this->createVideoSource("Youtube", false);
        $zshare = $this->createVideoSource("ZShare", false);

        $manager->persist($fiftysix56);
        $manager->persist($blip);
        $manager->persist($dailyMotion);
        $manager->persist($disclose);
        $manager->persist($forumNetwork);
        $manager->persist($krishnatube);
        $manager->persist($megavideo);
        $manager->persist($myspace);
        $manager->persist($novamov);
        $manager->persist($pbs);
        $manager->persist($rutube);
        $manager->persist($sevenload);
        $manager->persist($snagfilms);
        $manager->persist($stagevu);
        $manager->persist($tudou);
        $manager->persist($veoh);
        $manager->persist($viddler);
        $manager->persist($vimeo);
        $manager->persist($youtubePlaylist);
        $manager->persist($youtube);
        $manager->persist($zshare);
        $manager->flush();

        $this->createReference($youtube);
    }

    private function createVideoSource(string $name, string $embed)
    {
        $videoSource = new VideoSource();
        $videoSource->setName($name);
        $videoSource->setEmbed($embed);
        return $videoSource;
    }

    /**
     * @param VideoSource $videoSource
     */
    private function createReference(VideoSource $videoSource)
    {
        $this->addReference('video-source.'.$videoSource->getName(), $videoSource);
    }
}