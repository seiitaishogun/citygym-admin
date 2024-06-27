<?php
/**
 * @author tmtuan
 * created Date: 14-May-21
 */
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Image;

class Attach extends Controller {
    public function store(Request $request) {
        $postData = $request->only("file");
        $file_title = $request->only("file_title")??'';
        $user = auth()->user();

        $rules = array(
            'file' => 'required|mimes:jpeg,jpg,png,gif'
        );
        // Now pass the input and rules into the validator
        $validator = \Validator::make($postData, $rules);
        if ($validator->fails())
        {
            $response = ['error' => 1, 'errMess' => __('Invalid File type')];
        } else {
            $subFolder = date('Y/m') ;
            $img_cf = config('boilerplate.news');
            $file = $request->file("file");
            try {
                $tmpName = explode('.',$file->getClientOriginalName());
                $fileName =  (!empty($file_title) ) ? clean_url($file_title) : clean_url($tmpName[0]).'.'.$tmpName[1];
                $filePath = "/attach/" . $subFolder . "/" . $fileName;
                $file->storeAs('/attach/'. $subFolder . '/', $fileName, 'public');
                $file->storeAs('/attach/'. $subFolder . '/thumbnails/', $fileName, 'public');

                //create thumb
                $thumbPath = public_path('storage/attach/'. $subFolder . '/thumbnails/' . $fileName);
                $image = Image::make($thumbPath, [
                    'grayscale' => false
                ]);
                $image->resize($img_cf['thumb_width'], $img_cf['thumb_width'], function ($constraint) {
                    $constraint->aspectRatio();
                });
                $image->save($thumbPath);

                //insert data image
                $insertData = [
                    'file_name' => $fileName,
                    'file_title' => !empty($file_title)?$file_title:null,
                    'file_type' => $file->getClientOriginalExtension()??null
                ];
                $newFile = new \App\Models\Attach($insertData);
                $newFile->user_id = $user->id;
                $newFile->save();

                $location = asset('storage/'.$filePath);
                $response = [
                    'error' => 0,
                    'location' => $location,
                    'image' => [
                        'file_name' => $fileName,
                        'full_image' => $location,
                        'thumbPath' => asset('storage/attach/'. $subFolder . '/thumbnails/', $fileName),
                    ]
                ];
            } catch (Illuminate\Filesystem\FileNotFoundException $e) {
                $response = ['error' => 1, 'errMess' => __('File upload fail!')];
            }
        }

        return response()->json($response);
    }
}
