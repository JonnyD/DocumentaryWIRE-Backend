<?php

namespace DW\DocumentaryBundle\Uploader;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class PosterUploader
{
    /**
     * @var string
     */
    private $targetDir;

    /**
     * @param string $targetDir
     */
    public function __construct(string $targetDir)
    {
        $this->targetDir = $targetDir;
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        $file->move($this->targetDir, $fileName);

        return $fileName;
    }
}