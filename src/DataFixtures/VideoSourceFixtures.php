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
        $fiftysix56 = $this->createVideoSource("56", "no", "disabled");
        $blip = $this->createVideoSource("Blip", "no", "disabled");
        $dailyMotion = $this->createVideoSource("Daily Motion", "no", "disabled");
        $disclose = $this->createVideoSource("Disclose", "no", "disabled");
        $forumNetwork = $this->createVideoSource("Forum Network", "no", "disabled");
        $krishnatube = $this->createVideoSource("Krishnatube", "no", "disabled");
        $megavideo = $this->createVideoSource("megavideo", "no", "disabled");
        $myspace = $this->createVideoSource("MySpace", "no", "disabled");
        $novamov = $this->createVideoSource("Novamov", "no", "disabled");
        $pbs = $this->createVideoSource("PBS", "no", "disabled");
        $rutube = $this->createVideoSource("Rutube", "no", "disabled");
        $sevenload = $this->createVideoSource("Sevenload", "no", "disabled");
        $snagfilms = $this->createVideoSource("Snagfilms", "no", "disabled");
        $stagevu = $this->createVideoSource("stagevu", "no", "disabled");
        $tudou = $this->createVideoSource("Tudou", "no", "disabled");
        $veoh = $this->createVideoSource("Veoh", "no", "disabled");
        $viddler = $this->createVideoSource("Viddler", "no", "disabled");
        $vimeo = $this->createVideoSource("Vimeo", "no", "disabled");
        $youtubePlaylist = $this->createVideoSource("youtube-playlist", "no", "disabled");
        $youtube = $this->createVideoSource("Youtube",
            "no",
            "enabled",
            '<iframe 
                width="%WIDTH%" 
                height="%HEIGHT%" 
                src="http://www.youtube.com/embed/%VIDEO_ID%?autoplay=1&cc_load_policy=0&modestbranding=1&iv_load_policy=3"
                 frameborder="0" 
                 allowfullscreen>
             </iframe>');
        $zshare = $this->createVideoSource("ZShare", "no", "disabled");

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
        $this->createReference($vimeo);
    }

    /**
     * @param string $name
     * @param string $embed
     * @param bool $enabled
     * @param string|null $embedCode
     * @return VideoSource
     */
    private function createVideoSource(string $name, string $embed, string $status, string $embedCode = null)
    {
        $videoSource = new VideoSource();
        $videoSource->setName($name);
        $videoSource->setEmbedAllowed($embed);
        $videoSource->setStatus($status);
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