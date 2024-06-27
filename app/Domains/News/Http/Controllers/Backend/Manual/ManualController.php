<?php
/**
 * @author tmtuan
 * created Date: 12-Nov-20
 */


namespace App\Domains\News\Http\Controllers\Backend\Manual;

use App\Domains\News\Models\News;
use App\Domains\News\Models\NewsMeta;
use App\Http\Controllers\Controller;
use Image;
use Illuminate\Http\Request;

class ManualController extends Controller {
    protected $postType = 'manual';

    /**
     * list all new item
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $input = $request->all();
        $qr = News::join('news_meta', 'news.new_id', '=', 'news_meta.new_id')
            ->where('news_meta.lang_code', app()->getLocale());
        if ( isset($input['search']) && !empty($input['search']) ) {
            $s = str_replace('-','%', clean_url($input['search']));

            $qr->where(function ($query) use ($s){
                $query->where('news_meta.title', 'like', "%{$s}%")
                    ->orWhere('news_meta.slug', 'like', "%{$s}%");
            });
        }
        $newsData = $qr->where('deleted_at', NULL)
            ->where('news_type', 'manual')
            ->orderBy('news.new_id', 'DESC')
            ->paginate(7);

        return view('backend.manual.index', ['news' => $newsData]);
    }

    /**
     * delete new item
     * @param $new_id
     */
    public function delete($id) {
        $post = News::find($id);
        if ( empty($post)) return redirect()->route('admin.news.index')->withErrors(__('Invalid Request'));
//        $postMeta = NewsMeta::where('new_id', $post->id)->get();
//        foreach ($postMeta as $row) $row->delete();
        $post->delete();

        return redirect()->route('admin.manual.index')->withFlashSuccess(__('news.delete_news_success'));
    }

    /**
     * upload new image by lang
     * @param string $lang
     * @param Request $request
     * @param String $date formated as "Y/m"
     * @return array|string|null
     */
    public function upload_img(Request $request, $date = '') { //dd($_FILES);
        $subFolder = (!empty($date)) ? $date : date("Y") . '/' . date("m");

        $postData = $request->only("image");

        $rules = array(
            'image' => 'mimes:jpeg,jpg,png,gif'
        );
        // Now pass the input and rules into the validator
        $validator = \Validator::make($postData, $rules);
        if ($validator->fails())
        {
            return ['error' => 1, 'msg' => __('news.invalid_image')];
        } else {
            if ( $request->hasFile("image")) {
                if ( $request->file("image")->isValid() ) {
                    $img_cf = config('boilerplate.news');
                    $file = $request->file("image");
                    try {
                        $tmpName = explode('.',$file->getClientOriginalName());
                        $fileName = time() . clean_url($tmpName[0]).'.'.$tmpName[1];
                        $filePath = "/news/" . $subFolder . "/" . $fileName;
                        $file->storeAs('/news/'. $subFolder . '/', $fileName, 'public');
                        $file->storeAs('/news/'. $subFolder . '/thumbnails/', $fileName, 'public');

                        //create thumb
                        $thumbPath = public_path('storage/news/'. $subFolder . '/thumbnails/' . $fileName);
                        $image = Image::make($thumbPath, [
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
                                'thumbPath' => "/news/" . $subFolder . "/thumbnails/".$fileName,
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
