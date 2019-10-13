<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class Poster
{
    /**
     * @Assert\File(
     *     maxSize = "1024k",
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "Please upload a valid image"
     * )
     */
    protected $file;

    /**
     * @param File|null $myFile
     */
    public function setFile(File $myFile = null)
    {
        $this->file = $myFile;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    public function upload(File $file)
    {
        //generate unique filename
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        //Set other entity attribute here

        //move the file
        $file->move('uploads/posters/', $fileName);

        return $fileName;
    }
}