<?php
namespace App\Libraries;

use DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
class CommonFunctions{

    function logData($filename, $content){
        Log::channel($filename)->info($content);
    }

    public function checkUploadedFileProperties($extension, $fileSize)
    {
        $valid_extension = array("csv"); //Only want csv 
        $maxFileSize = 2097152; // Uploaded file size limit is 2mb
        if (in_array(strtolower($extension), $valid_extension)) {
            if ($fileSize > $maxFileSize) {
                throw new \Exception('No file was uploaded', Response::HTTP_REQUEST_ENTITY_TOO_LARGE); 
            }
        } else {
            throw new \Exception('Invalid file extension', Response::HTTP_UNSUPPORTED_MEDIA_TYPE); 
        }
    }

    function sendRequest( $url = '', $postdata = array(),$basic_auth = false,$username = null,$password = null) {

        $curl = curl_init();
    
    
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $postdata
        ));
    
        if( !is_array($postdata) && !empty(json_decode($postdata)) ){
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        }
        if( $basic_auth ){
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type:application/json","Authorization: Basic ".base64_encode($username.":".$password)]);
        }
        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);   //get status code
        curl_close($curl);
    
        return $response;
    }
}
