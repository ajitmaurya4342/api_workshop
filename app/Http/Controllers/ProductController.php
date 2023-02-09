<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

//Libraries
use App\Libraries\CommonFunctions;

class ProductController extends Controller
{
    private $CommonFunctions;

    public function __construct()
	{
        $this->CommonFunctions = new CommonFunctions();

    }

    public function uploadProducts(Request $request)
    {
        $file = $request->file('uploaded_file');
        if ($file) {
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension(); //Get extension of uploaded file
            $fileSize = $file->getSize(); //Get size of uploaded file

            //Check for file extension and size
            $this->CommonFunctions->checkUploadedFileProperties($extension, $fileSize);

            $location = 'upload_product'; //Created an "uploads" folder for that
            $file->move($location, $filename);
            $filepath = public_path($location . "/" . $filename);
            // Reading file
            $file = fopen($filepath, "r");

            $importData_arr = array(); 
            $i = 0;
            while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                $num = count($filedata);
                if ($i == 0) {
                    // Skip first row
                    $i++;
                    continue;
                }
                for ($c = 0; $c < $num; $c++) {
                    $importData_arr[$i][] = $filedata[$c];
                }
                $i++;
            }
            fclose($file); 
            //unlink(public_path($location . "/" . $filename)); //Incase to delete file
            $j = 0;
            $LogFilename = "ProductMaster";
            foreach ($importData_arr as $importData) {
                $j++;
                try {
                    DB::beginTransaction();
                    Product::create([
                        'product_name' => $importData[1],
                        'price' => $importData[2],
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ]);
                    DB::commit();
                    $this->CommonFunctions->logData($LogFilename, $importData[1]." Inserted"); 
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->CommonFunctions->logData($LogFilename, $importData[1]." Not Inserted ".$e->getMessage()); 
                }
            }
            return response()->json([
                'message' => "$j products successfully uploaded"
            ]);
        } else {
        //no file was uploaded
            throw new \Exception('No file was uploaded', Response::HTTP_BAD_REQUEST);
        }
    }

    
}
