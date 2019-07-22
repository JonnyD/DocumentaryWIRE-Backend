<?php

namespace App\Service;

class ImageService
{
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

    /**
     * @param string $string
     * @return bool
     */
    function isBase64(string $string)
    {
        if (base64_decode($string, true) === false){
            return true;
        } else {
            return false;
        }
    }
}