<?php

namespace JoelButcher\Socialstream;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

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
        $photo = Http::get($url);
        if ($photo) {
            file_put_contents($file = sys_get_temp_dir().'/'.Str::uuid()->toString(), $photo);

            $this->updateProfilePhoto(new UploadedFile($file, $name));
        }
    }
}
