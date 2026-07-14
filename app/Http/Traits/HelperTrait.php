<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Config;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Http;

trait HelperTrait
{
    public function uploadFile($dir, $file, $fileName)
    {
        $driver = config('filesystems.default');
        return Storage::disk($driver)->putFileAs($dir, $file, $fileName);
    }

    public function imageUploadToBase64($dir, $file)
    {
        // $file_parts    = explode(";base64,", $file);
        // $file_type_aux = explode("image/", $file_parts[0]);
        // $file_type     = $file_type_aux[1];
        $fileName      = rand(1000, 9999) . time() . '.' . $file_type;
        $url           = $this->uploadFile($dir, $file, $fileName);
        return $fileName;
    }
    // public function imageUpload($dir, $file)
    // {
    //     $fileName      = rand(1000,9999) . time() . '.' . $file->extension();
    //     $url           = $this->uploadFile($dir, $file, $fileName);
    //     return $fileName;
    // }

    public function imageUpload($dir, $file)
    {
        $fileName = rand(1000, 9999) . time() . '.' . $file->extension();

        $url = $this->uploadFile($dir, $file, $fileName);

        return $fileName;
    }

    public function videoUploadToBase64($dir, $file)
    {
        $file_parts    = explode(";base64,", $file);
        $file_type_aux = explode("video/", $file_parts[0]);
        $file_type     = $file_type_aux[1];
        $fileName      = rand(1000, 9999) . time() . '.' . $file_type;
        $url           = $this->uploadFile($dir, $file, $fileName);
        return $fileName;
    }
    public function pdfUploadToBase64($dir, $file)
    {
        $file_parts    = explode(";base64,", $file);
        $file_type_aux = explode("application/", $file_parts[0]);
        $file_type     = $file_type_aux[1];
        $fileName      = rand(1000, 9999) . time() . '.' . $file_type;
        $url           = $this->uploadFile($dir, $file, $fileName);
        return $fileName;
    }
    public function audioUploadToBase64($dir, $file)
    {
        $file_parts    = explode(";base64,", $file);
        $file_type_aux = explode("audio/", $file_parts[0]);
        $file_type     = $file_type_aux[1];
        $fileName      = rand(1000, 9999) . time() . '.' . $file_type;
        $url           = $this->uploadFile($dir, $file, $fileName);
        return $fileName;
    }

    public function filterRequestData($data, $modObj)
    {
        $orderBy    = 'id';
        $order      = 'DESC';
        $returnData = array();
        // Check column is exist
        if (isset($data->sorting['by'])) {
            $columns = $modObj->getFillable();
            if (in_array($data->sorting['by'], $columns)) {
                $orderBy = $data->sorting['by'];
            }
        }
        // check order keywork
        if (isset($data->sorting['order'])) {
            $orderKeywords = ['DESC', 'desc', 'ASC', 'asc'];
            if (in_array($data->sorting['order'], $orderKeywords)) {
                $order = $data->sorting['order'];
            }
        }
        $returnData['showPage']   = isset($data->limit) ? $data->limit : 50;
        $returnData['SortBy']     = $orderBy;
        $returnData['SortOrder']  = $order;
        return $returnData;
    }

    public function requestLimit($data)
    {
        $limit = isset($data->limit) ? $data->limit : 10;
        return $limit;
    }

    public function send_otp_to_phone($receiver, $message)
    {
        try {

            $account_sid    = Config::get('constants.TWILIO_SID');
            $auth_token     = Config::get("constants.TWILIO_TOKEN");
            $twilio_number  = Config::get("constants.TWILIO_FROM");
            $client         = new Client($account_sid, $auth_token);
            // dd($client);
            $client->messages->create($receiver, [
                'from' => $twilio_number,
                'body' => $message
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
