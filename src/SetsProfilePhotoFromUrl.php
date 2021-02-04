<?php

namespace JoelButcher\Socialstream;

use Illuminate\Http\UploadedFile;

trait SetsProfilePhotoFromUrl
{
    /**
     * Sets the users profile photo from a URL.
     *
     * @param  string  $url
     * @return void
     */
    public function setProfilePhotoFromUrl(string $url)
    {
        $name = pathinfo($url)['basename'];
        file_put_contents($file = '/tmp/'.$name, file_get_contents($url));
        $this->updateProfilePhoto(new UploadedFile($file, $name));
    }
}