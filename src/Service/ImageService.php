<?php

namespace App\Service;

use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

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

    /**
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     */
    public function __construct(
        DataManager $dataManager,
        FilterManager $filterManager)
    {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
    }

    /**
     * @param $base64ImageString
     * @param $outputFileWithoutExtension
     * @param string $pathWithEndSlash
     * @return string
     */
    public function saveBase54Image($base64ImageString, $outputFileWithoutExtension, $pathWithEndSlash = "" ) {
        $splited = explode(',', substr($base64ImageString , 5 ), 2);
        $mime = $splited[0];
        $data = $splited[1];

        $mimeSplitWithoutBase64 = explode(';', $mime,2);
        $mimeSplit = explode('/', $mimeSplitWithoutBase64[0],2);
        if (count($mimeSplit) == 2)
        {
            $extension = $mimeSplit[1];
            if ($extension == 'jpeg') {
                $extension = 'jpg';
            }
            $outputFileWithoutExtension = $outputFileWithoutExtension.'.'.$extension;
        }

        file_put_contents($pathWithEndSlash . $outputFileWithoutExtension, base64_decode($data) );

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
        $exploded = explode(',', $string);
        if ($exploded[0] === 'data:image/png;base64') {
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
}