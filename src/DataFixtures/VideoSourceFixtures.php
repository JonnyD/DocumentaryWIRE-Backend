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
        $fiftysix56 = $this->createVideoSource("56", false, false);
        $blip = $this->createVideoSource("Blip", false, false);
        $dailyMotion = $this->createVideoSource("Daily Motion", false, false);
        $disclose = $this->createVideoSource("Disclose", false, false);
        $forumNetwork = $this->createVideoSource("Forum Network", false, false);
        $krishnatube = $this->createVideoSource("Krishnatube", false, false);
        $megavideo = $this->createVideoSource("megavideo", false, false);
        $myspace = $this->createVideoSource("MySpace", false, false);
        $novamov = $this->createVideoSource("Novamov", false, false);
        $pbs = $this->createVideoSource("PBS", false, false);
        $rutube = $this->createVideoSource("Rutube", false, false);
        $sevenload = $this->createVideoSource("Sevenload", false, false);
        $snagfilms = $this->createVideoSource("Snagfilms", false, false);
        $stagevu = $this->createVideoSource("stagevu", false, false);
        $tudou = $this->createVideoSource("Tudou", false, false);
        $veoh = $this->createVideoSource("Veoh", false, false);
        $viddler = $this->createVideoSource("Viddler", false, false);
        $vimeo = $this->createVideoSource("Vimeo", false, false);
        $youtubePlaylist = $this->createVideoSource("youtube-playlist", false, false);
        $youtube = $this->createVideoSource("Youtube",
            false,
            true,
            '<iframe 
                width="%WIDTH%" 
                height="%HEIGHT%" 
                src="http://www.youtube.com/embed/%VIDEO_ID%?autoplay=1&cc_load_policy=0&modestbranding=1&iv_load_policy=3"
                 frameborder="0" 
                 allowfullscreen>
             </iframe>');
        $zshare = $this->createVideoSource("ZShare", false, false);

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

    /**
     * @param string $name
     * @param string $embed
     * @param bool $enabled
     * @param string|null $embedCode
     * @return VideoSource
     */
    private function createVideoSource(string $name, string $embed, bool $enabled, string $embedCode = null)
    {
        $videoSource = new VideoSource();
        $videoSource->setName($name);
        $videoSource->setEmbed($embed);
        $videoSource->setEnabled($enabled);
        $videoSource->setEmbedCode($embedCode);
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