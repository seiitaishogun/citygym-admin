<?php
/**
 * @author tmtuan
 * created Date: 19-Nov-20
 */


namespace App\Domains\News\Http\Controllers\Backend\Banner;

use App\Domains\News\Models\Banner;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Image;

class BannerController extends Controller {

    /**
     * show list banner page
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request) {
        //\Artisan::call('view:clear');
        $inputData = $request->all();
        $query = Banner::join('banner_meta', 'banner.banner_id', '=', 'banner_meta.banner_id')
                        ->where('banner_meta.lang_code', app()->getLocale())
                        ->where('deleted_at', NULL);

        if ( isset($inputData['s']) && !empty($inputData['s']) )
        {
            $query->where('banner_meta.title', 'like', "%{$inputData['s']}%");
        }


        if ( isset($inputData['order']) && !empty($inputData['order']) )
        {
            $query->orderBy('banner_meta.title', $inputData['order']);
            $order = $inputData['order'];
        }
        else {
            $query->orderBy('banner.order', 'ASC');
        }

        $data = $query->paginate(10);
        return view('backend.banner.index', ['data' => $data, 'order' => $order??null]);
    }

    /**
     * delete banner item
     * @param $id
     */
    public function delete($id) {
        $banner = Banner::find($id);
        if ( empty($banner)) return redirect()->route('admin.banner.index')->withErrors(__('Invalid Request'));
        $banner->delete();
        return redirect()->route('admin.banner.index')->withFlashSuccess(__('news.delete_banner_success'));
    }


    /**
     * upload banner image by lang
     * @param string $lang
     * @param Request $request
     * @param String $date formated as "Y/m"
     * @return array|string|null
     */
    public function upload_img(Request $request, $date = '') {
        $subFolder = (!empty($date)) ? $date : date("Y") . '/' . date("m");
        $postData = $request->only("image");

        $rules = array(
            'image' => 'required|mimes:jpeg,jpg,png,gif'
        );
        // Now pass the input and rules into the validator
        $validator = \Validator::make($postData, $rules);
        if ($validator->fails())
        {
            return ['error' => 1, 'msg' => __('news.banner_image_invalid')];
        } else {
            $img_cf = config('boilerplate.news');
            $file = $request->file("image");
            try {
                $tmpName = explode('.',$file->getClientOriginalName());
                $fileName = time() . clean_url($tmpName[0]).'.'.$tmpName[1];
                $filePath = "/banner/" . $subFolder . "/" . $fileName;
                $file->storeAs('/banner/'. $subFolder . '/', $fileName, 'public');
                $file->storeAs('/banner/'. $subFolder . '/thumbnails/', $fileName, 'public');

                //create thumb
                $thumbPath = public_path('storage/banner/'. $subFolder . '/thumbnails/' . $fileName);
                $image = Image::make($thumbPath, [
                    'grayscale' => false
                ]);
                $image->resize($img_cf['thumb_width'], $img_cf['thumb_width'], function ($constraint) {
                    $constraint->aspectRatio();
                });
                $image->save($thumbPath);

                return [
                    'error' => 0,
                    'data' => [
                        'file_name' => $fileName,
                        'path' => $filePath,
                        'thumbPath' => "/banner/" . $subFolder . "/thumbnails/".$fileName,
                        'file_extension' => $file->getClientOriginalExtension()
                    ]
                ];
            } catch (Illuminate\Filesystem\FileNotFoundException $e) {
                return ['error' => 1, 'msg' => __('news.file-uploaded-fail')];
            }
        }
    }

    public function upload_img1($lang = 'vn', Request $request, $date = '') { //dd($_FILES);
        $subFolder = (!empty($date)) ? $date : date("Y") . '/' . date("m");
        $postData = $request->only("{$lang}.image");

        $rules = array(
            'image' => 'required|mimes:jpeg,jpg,png,gif'
        );
        // Now pass the input and rules into the validator
        $validator = \Validator::make($postData[$lang], $rules);
        if ($validator->fails())
        {
            return ['error' => 1, 'msg' => __('news.banner_image_invalid')];
        } else {
            if ( $request->hasFile("{$lang}.image")) {
                if ( $request->file("{$lang}.image")->isValid() ) {
                    $img_cf = config('boilerplate.news');
                    $file = $request->file("{$lang}.image");
                    try {
                        $tmpName = explode('.',$file->getClientOriginalName());
                        $fileName = time() . clean_url($tmpName[0]).'.'.$tmpName[1];
                        $filePath = "/banner/" . $subFolder . "/" . $fileName;
                        $file->storeAs('/banner/'. $subFolder . '/', $fileName, 'public');

                        //create thumb
                        $thumbPath = storage_path("app/public/banner/" . $subFolder . "/thumbnails/");
                        //check folder exist
                        if (!file_exists($thumbPath)) {
                            mkdir($thumbPath, 0755, true);
                        }

                        $image = Image::make($file->getRealPath(), [
                            'grayscale' => false
                        ]);
                        $image->resize($img_cf['thumb_width'], $img_cf['thumb_width'], function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $image->save($thumbPath.$fileName);

                        return [
                            'error' => 0,
                            'data' => [
                                'file_name' => $fileName,
                                'path' => $filePath,
                                'thumbPath' => "/banner/" . $subFolder . "/thumbnails/".$fileName,
                                'file_extension' => $file->getClientOriginalExtension()
                            ]
                        ];
                    } catch (Illuminate\Filesystem\FileNotFoundException $e) {
                        return ['error' => 1, 'msg' => __('news.file-uploaded-fail')];
                    }
                } else return ['error' => 1, 'msg' => __('news.file-uploaded-fail')];

            } else {
                return __('news.no-file-selected');
            }
        }

    }

}
