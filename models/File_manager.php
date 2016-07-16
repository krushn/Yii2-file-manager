<?php 

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;
use app\components\Image;

class File_manager extends Model
{
    /**
     * @var UploadedFile
     */
    public $images;

    public function rules()
    {
        return [
            [['images'], 'file', 'skipOnEmpty' => false, 'extensions' => 'jpeg,gif,bmp,png,jpg', 'maxFiles' => 50],
        ];
    }
    
    public function upload($directory)
    {
        if ($this->validate()) {
            foreach ($this->images as $file) {
                $file->saveAs($directory . $file->baseName . '.' . $file->extension);
            }
            return true;
        } else {
            print_r($this->getErrors());
            return false;
        }
    }

    public function resize($filename, $width, $height) {
        
        if (!is_file(DIR_IMAGE . $filename)) {
            return;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $image_old = $filename;
        $image_new = 'cache/' . substr($filename, 0, strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

        if (!is_file(DIR_IMAGE . $image_new) || (filectime(DIR_IMAGE . $image_old) > filectime(DIR_IMAGE . $image_new))) {
            list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
                 
            if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) { 
                return DIR_IMAGE . $image_old;
            }
 
            $path = '';
            $directories = explode('/', dirname($image_new));
            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;
                if (!is_dir(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }
            if ($width_orig != $width || $height_orig != $height) {
                $image = new Image(DIR_IMAGE . $image_old);
                $image->resize($width, $height);
                $image->save(DIR_IMAGE . $image_new);
            } else {
                copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
            }
        }

        return HTTP_CATALOG . '../upload/' . $image_new;
    }
}