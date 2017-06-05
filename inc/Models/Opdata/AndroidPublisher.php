<?php

date_default_timezone_set('UTC');

include_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Class for android publisher.
 * @see https://developers.google.com/android-publisher/api-ref
 */
class AndroidPublisher
{
    public $errors = [];
    public $packageError = false;

    public static $imageTypeList = [
        'featureGraphic',
        'icon',
        'phoneScreenshots',
        'promoGraphic',
        'sevenInchScreenshots',
        'tenInchScreenshots',
        'tvBanner',
        'tvScreenshots',
        'wearScreenshots',
    ];

    /**
     * construct method of AndroidPublisher
     *
     * @param    string  $credentials_file (require) The file path to credentials file (xxx.json)
     * @param    string  $application_name (optional) The application name
     */
    public function __construct($credentials_file, $application_name = '')
    {
        if (is_string($credentials_file) && file_exists($credentials_file)) {
            $this->credentials_file = $credentials_file;
        } else {
            throw new Exception('The credentials file does not exist.');
        }
        $this->application_name = $application_name;

        $this->buildService();
    }

    private function buildService()
    {
        try {
            $this->scopes = ['https://www.googleapis.com/auth/androidpublisher'];
            $client = new Google_Client();
            $client->setAuthConfig($this->credentials_file);
            $client->setApplicationName($this->application_name);
            $client->setScopes($this->scopes);
            $this->client = $client;
            $this->service = new Google_Service_AndroidPublisher($client);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * Gets the edit identifier.
     *
     * @see https://developers.google.com/android-publisher/api-ref/edits
     */
    public function getEditId($packageName)
    {
        $this->packageError = false;

        try {
            $AppEdit = $this->service->edits->insert($packageName, new Google_Service_AndroidPublisher_AppEdit());
            $editId = $AppEdit->getId();

            return $editId;
        } catch (Google_Service_Exception $e) {
            switch ($e->getErrors()[0]['reason']) {
                case 'projectNotLinked':
                case 'applicationNotFound':
                    $this->packageError = true;
                    break;
                default:
                    break;
            }
            $this->errors[] = $e->getErrors()[0]['message'];
            // throw new Exception($e->getMessage(), $e->getCode(), $e);

        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            // throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        return false;
    }

    /**
     * $Model = [
     *      "packageName" => "packageName",
     *      "language" => $language,
     *      "title" => $title,
     *      "fullDescription" => $fullDescription,
     *      "shortDescription" => $shortDescription,
     *      "video" => $video,
     * ];
     */
    /**
     * updateEditsListing
     *
     * @see https://developers.google.com/android-publisher/api-ref/edits/listings
     * @param      array   $model
     *
     * @return     boolean
     */
    public function updateEditsListing($model)
    {
        $this->packageError = false;
        $packageName = $model['packageName'];
        $language = str_replace('_', '-', $model['language']);
        $title = $model['title'];
        $shortDescription = $model['shortDescription'];
        $fullDescription = $model['fullDescription'];
        $video = $model['video'];

        $is_success = false;
        $editId = $this->getEditId($packageName);

        if (!$editId) {
            return false;
        }

        $body = new Google_Service_AndroidPublisher_Listing();
        $body->language = $language;
        $body->title = $title;
        $body->shortDescription = $shortDescription;
        $body->fullDescription = $fullDescription;
        $body->video = $video;

        try {
            //update($packageName, $editId, $language, Google_Service_AndroidPublisher_Listing $postBody, $optParams = array())
            $edits_listings = $this->service->edits_listings->update($packageName, $editId, $language, $body);

            //function commit($packageName, $editId, $optParams = array());
            $stat = $this->service->edits->commit($packageName, $editId);
            // var_dump($stat);
            if ($stat instanceof Google_Service_AndroidPublisher_AppEdit) {
                $is_success = true;
            } else {
                $is_success = false;
            }
        } catch (Google_Service_Exception $e) {
            switch ($e->getErrors()[0]['reason']) {
                case 'permissionDenied':
                    $this->packageError = true;
                    break;
                default:
                    break;
            }
            $this->errors[] = $e->getErrors()[0]['message'];
            // throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $is_success;
    }


    /**
     * Gets the edits listing.
     *
     * @see https://developers.google.com/android-publisher/api-ref/edits/listings/get
     * @see https://developers.google.com/android-publisher/api-ref/edits/listings/list
     * @return   Resource Google_Service_AndroidPublisher_Listing   The edits listing.
     */
    public function getEditsListing($packageName, $language = '')
    {
        $this->packageError = false;

        $editId = $this->getEditId($packageName);

        if (!$editId) {
            return false;
        }

        try {
            if ($language) {
                $edits_listings = $this->service->edits_listings->get($packageName, $editId, $language);
            } else {
                $edits_listings = $this->service->edits_listings->listEditsListings($packageName, $editId);
            }
        } catch (Google_Service_Exception $e) {
            switch ($e->getErrors()[0]['reason']) {
                case 'permissionDenied':
                    $this->packageError = true;
                    break;
                default:
                    break;
            }
            $this->errors[] = $e->getErrors()[0]['message'];
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        if ($edits_listings instanceof Google_Service_AndroidPublisher_ListingsListResponse) {
            return $edits_listings;
        } else {
            return false;
        }
    }

    public function getLastError()
    {
        $last = count($this->errors) - 1;

        if (count($this->errors) > 0) {
            return $this->errors[$last];
        } else {
            return false;
        }
    }

    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }


    public function updateEditsImages($model)
    {
        $this->packageError = false;
        $packageName = $model['packageName'];
        $language = str_replace('_', '-', $model['language']);
        $imageType = $model['imageType'];
        $image_list = $model['images'];
        $keep_first_image = $model['keep_first_image'];

        if (!is_array($image_list)) {
            $image_list = [$image_list];
        }

        $is_success = false;
        try {
            // get Edits
            $editId = $this->getEditId($packageName);
            if (!$editId) {
                $this->errors[] = '(updateEditsImages)Get Edit Error!';
                return false;
            }

            if ($imageType == 'phoneScreenshots') {
                if ($keep_first_image) {
                    $image_list = array_slice($image_list, 0, 7);

                    // delete all the image but first one.
                    $imagesListResponse = $this->service->edits_images->listEditsImages(
                            $packageName, $editId, $language, $imageType);
                    if ($imagesListResponse instanceof Google_Service_AndroidPublisher_ImagesListResponse) {
                        $img_list = $imagesListResponse->getImages();
                        $count =  sizeof($img_list);
                        for ($i = 1; $i < $count; $i++) {
                            $img = $img_list[$i];
                            $id = $img->getId();
                            $this->service->edits_images->delete($packageName,$editId,$language,
                                $imageType,$id);
                        }
                    } else {
                        $this->errors[] = '(updateEditsImages)Get ImagesListRespons Fail!';
                        return false;
                    }
                } else {
                    $image_list = array_slice($image_list, 0, 8);
                    // delete all;
                    $this->service->edits_images->deleteall($packageName, $editId, $language, $imageType);
                }
            } else {
                $image_list = array_slice($image_list, 0, 1);
            }

            // upload image;
            foreach ($image_list as $image) {
                $mimeType = '';
                if ($this->endsWith($image, 'jpeg') || $this->endsWith($image, 'jpg')) {
                    $mimeType = 'image/jpeg';
                }
                elseif ($this->endsWith($image, 'png')) {
                    $mimeType = 'image/png';
                }
                else {
                    $this->errors[] = '(updateEditsImages)Bad Image_Type!must be jpeg or png!';
                    return false;
                }

                $body = [
                    'data' => file_get_contents($image),
                    'mimeType' => $mimeType,
                    'uploadType' => 'media'
                ];
                $this->service->edits_images->upload($packageName, $editId,
                        $language, $imageType, $body);
            }
            // commit
            $stat = $this->service->edits->commit($packageName, $editId);
            // var_dump($stat);
            if ($stat instanceof Google_Service_AndroidPublisher_AppEdit) {
                $is_success = true;
            } else {
                $is_success = false;
                $this->errors[] = '(updateEditsImages)Commit Error!';
            }
        } catch (Google_Service_Exception $e) {
            switch ($e->getErrors()[0]['reason']) {
                case 'permissionDenied':
                    $this->packageError = true;
                    break;
                default:
                    break;
            }
            $this->errors[] = $e->getErrors()[0]['message'];
            // throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $is_success;
    }

    public function deleteallEditsImages($model, $count=0)
    {

        $this->packageError = false;
        $packageName = $model['packageName'];
        $language = str_replace('_', '-', $model['language']);
        $imageType = $model['imageType'];

        $is_success = false;

        $max_count = 8;
        if ($count>0 && $count <=8) {
            $max_count = $count;
        }

        try {

            $editId = $this->getEditId($packageName);

            if (!$editId) {
                return false;
            }

            $imagesListResponse = $this->service->edits_images->listEditsImages($packageName,$editId,
                $language, $imageType);

            if ($imagesListResponse instanceof Google_Service_AndroidPublisher_ImagesListResponse) {
                $img_list = $imagesListResponse->getImages();
                $max_count =  min($max_count, sizeof($img_list) - 2);

                for ($i = 0; $i < $max_count; $i++) {
                    $img = $img_list[$i];
                    $id = $img->getId();
                    $this->service->edits_images->delete($packageName,$editId,$language,
                        $imageType,$id);
                }
                $stat = $this->service->edits->commit($packageName, $editId);
                if ($stat instanceof Google_Service_AndroidPublisher_AppEdit) {
                    $is_success = true;
                } else {
                    $is_success = false;
                    $this->errors[] = '(deleteallEditsImages)Edit Commit Fail!';
                    return false;
                }
            } else {
                $this->errors[] = '(deleteallEditsImages)Get ImagesListRespons Fail!';
                return false;
            }
        } catch (Google_Service_Exception $e) {
            switch ($e->getErrors()[0]['reason']) {
                case 'permissionDenied':
                    $this->packageError = true;
                    break;
                default:
                    break;
            }
            $this->errors[] = $e->getErrors()[0]['message'];
            // throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $is_success;

    }

    public function getAllListOfEditsImages($model)
    {

        $this->packageError = false;
        $packageName = $model['packageName'];
        $language = str_replace('_', '-', $model['language']);
        $imageType = $model['imageType'];

        $is_success = false;

        $image_list = false;
        try {

            $editId = $this->getEditId($packageName);

            if (!$editId) {
                return false;
            }

            $imagesListResponse = $this->service->edits_images->listEditsImages($packageName,$editId,
                $language, $imageType);

            if ($imagesListResponse instanceof Google_Service_AndroidPublisher_ImagesListResponse) {
                $list = $imagesListResponse->getImages();
                $image_list = [];
                foreach ($list as $key => $img) {
                    $image_list[] = [
                        'id' => $img->getId(),
                        'sha1' => $img->getSha1(),
                        'url' => $img->getUrl(),
                    ];
                }
            }
        } catch (Google_Service_Exception $e) {
            switch ($e->getErrors()[0]['reason']) {
                case 'permissionDenied':
                    $this->packageError = true;
                    break;
                default:
                    break;
            }
            $this->errors[] = $e->getErrors()[0]['message'];
            // throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $image_list;

    }

}
