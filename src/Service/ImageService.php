<?php

namespace App\Service;

use App\Entity\Documentary;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageService
{
    /**
     * @var DataManager
     */
    private $dataManager;

    /**
     * @var FilterManager
     */
    private $filterManager;

    private $params;

    /**
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     * @param ParameterBagInterface $params
     */
    public function __construct(
        DataManager $dataManager,
        FilterManager $filterManager,
        ParameterBagInterface $params)
    {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->params = $params;
    }

    /**
     * @param $base64ImageString
     * @param $outputFileWithoutExtension
     * @param string $pathWithEndSlash
     * @return string
     */
    public function saveBase54Image($base64ImageString, $outputFileWithoutExtension, $pathWithEndSlash = "")
    {
        $splited = explode(',', substr($base64ImageString, 5), 2);
        $mime = $splited[0];
        $data = $splited[1];

        $mimeSplitWithoutBase64 = explode(';', $mime, 2);
        $mimeSplit = explode('/', $mimeSplitWithoutBase64[0], 2);
        if (count($mimeSplit) == 2) {
            $extension = $mimeSplit[1];
            if ($extension == 'jpeg') {
                $extension = 'jpg';
            }
            $outputFileWithoutExtension = $outputFileWithoutExtension . '.' . $extension;
        }

        file_put_contents($pathWithEndSlash . $outputFileWithoutExtension, base64_decode($data));

        return $outputFileWithoutExtension;
    }

    public function saveFromURL($poster, $permanentFolderPath)
    {
        $contents = file_get_contents($poster);
        if ($contents != false) {
            $filename = sha1(uniqid(mt_rand(), true));
            $ext = pathinfo($poster, PATHINFO_EXTENSION);
            $tmpImageName = $filename . '.' . $ext;
            $tmpImagePathRel = 'uploads/tmp/' . $tmpImageName;
            file_put_contents($tmpImagePathRel, $contents);

            $processedImage = $this->dataManager->find('cover160x200', $tmpImagePathRel);
            $response = $this->filterManager->applyFilter($processedImage, 'avatar200');
            $avatar = $response->getContent();

            unlink($tmpImagePathRel); // eliminate unfiltered temp file.
            $permanentImagePath = $permanentFolderPath . $tmpImageName;

            $f = fopen($permanentImagePath, 'w');
            fwrite($f, $avatar);
            fclose($f);
            return $tmpImageName;
        }
    }

    /**
     * @param string $string
     * @return bool
     */
    public function isBase64(string $string)
    {
        $extensions = [];
        array_push($extensions, 'data:image/jpeg;base64');
        array_push($extensions,'data:image/png;base64');

        $exploded = explode(',', $string);
        if (in_array($exploded[0], $extensions)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $string
     * @return bool
     */
    public function isUrl(string $string)
    {
        $exploded = explode('://', $string);
        if ($exploded[0] === 'http' || $exploded[0] === 'https') {
            return true;
        }
        return false;
    }

    /**
     * @param Documentary $documentary
     * @param array $data
     * @return Documentary
     */
    public function mapMovieImages(Documentary $documentary, array $data)
    {
        if ($poster = $data['poster']) {
            $currentPoster = $this->params->get('postersUrl') . $documentary->getPoster();
            if ($poster != $currentPoster) {
                $posterFileName = $this->uploadPoster($poster);
                $documentary->setPoster($posterFileName);
            }
        }

        if ($wideImage = $data['wideImage']) {
            $currentWideImage = $this->params->get('wideImagesUrl') . $documentary->getWideImage();
            if ($wideImage != $currentWideImage) {
                $wideImageFileName = $this->uploadWideImage($wideImage);
                $documentary->setWideImage($wideImageFileName);
            }
        }

        return $documentary;
    }

    /**
     * @param Documentary $documentary
     * @param array $data
     * @return Documentary
     */
    public function mapSeriesImages(Documentary $documentary, array $data)
    {
        $series = $documentary->getSeries();

        $poster = $data['poster'];
        if ($poster) {
            $currentPoster = $documentary->getPoster();
            if ($poster != $currentPoster) {
                $posterFileName = $this->uploadPoster($poster);
                $documentary->setPoster($posterFileName);
            }
        }

        $wideImage = $data['wideImage'];
        if ($wideImage) {
            $currentWideImage = $documentary->getWideImage();
            if ($wideImage != $currentWideImage) {
                $wideImageFileName = $this->uploadWideImage($wideImage);
                $documentary->setWideImage($wideImageFileName);
            }
        }

        $seasons = $data['series']['seasons'];
        foreach ($seasons as $season) {
            $seasonNumber = $season['number'];

            $episodes = $season['episodes'];
            foreach ($episodes as $episode) {
                $episodeNumber = $episode['number'];
                $episodeObject = $this->getEpisodeFromDocumentaryObject($documentary, $seasonNumber, $episodeNumber);
                $currentThumbnail = $episodeObject->getThumbnail();

                $newThumbnail = $episode['thumbnail'];
                if ($currentThumbnail != $newThumbnail) {
                    $thumbnailFileName = $this->uploadThumbnail($newThumbnail);
                    $episodeObject->setThumbnail($thumbnailFileName);
                }
            }
        }

        return $documentary;
    }

    /**
     * @param Documentary $documentary
     * @param int $seasonNumber
     * @param int $episodeNumber
     * @return \App\Entity\Episode|mixed|null
     */
    public function getEpisodeFromDocumentaryObject(Documentary $documentary, int $seasonNumber, int $episodeNumber)
    {
        $currentEpisode = null;

        foreach ($documentary->getSeries()->getSeasons() as $season) {
            if ($season->getNumber() === $seasonNumber) {
                foreach ($season->getEpisodes() as $episode) {
                    if ($episode->getNumber() === $episodeNumber) {
                        $currentEpisode = $episode;
                    }
                }
            }
        }

        return $currentEpisode;
    }

    public function getThumbnailFromDataBySeasonAndEpisode(array $data, int $seasonIndex, int $episodeIndex)
    {
        return $data['series']['seasons'][$seasonIndex]['episodes'][$episodeIndex]['thumbnail'];
    }

    /**
     * @param string $poster
     * @param Documentary $documentary
     * @return string
     */
    public function uploadPoster(string $poster)
    {
        $path = 'uploads/posters/';
        $posterFileName = $this->uploadImage($poster, $path);
        return $posterFileName;
    }

    /**
     * @param string $wideImage
     * @return string
     */
    public function uploadWideImage(string $wideImage)
    {
        $path = 'uploads/wide/';
        $wideImageFileName = $this->uploadImage($wideImage, $path);
        return $wideImageFileName;
    }

    /**
     * @param string $thumbnail
     * @return string
     */
    public function uploadThumbnail(string $thumbnail)
    {
        $path = 'uploads/thumbnail/';
        $thumbnailFileName = $this->uploadImage($thumbnail, $path);
        return $thumbnailFileName;
    }

    /**
     * @param string $image
     * @param string $path
     * @return string
     */
    public function uploadImage(string $image, string $path)
    {
        $imageFileName = '';

        if ($image) {
            $outputFileWithoutExtension = uniqid();
            $isBase64 = $this->isBase64($image);
            $isUrl = $this->isUrl($image);

            if ($isBase64) {
                $imageFileName = $this->saveBase54Image($image, $outputFileWithoutExtension, $path);
            } else if ($isUrl) {
                $imageFileName = $this->saveFromURL($image, $path);
            }
        }

        return $imageFileName;
    }
}