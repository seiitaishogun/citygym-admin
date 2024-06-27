<?php
/**
 * @author tmtuan
 * created Date: 12-Nov-20
 */


namespace App\Domains\News\Http\Controllers\Backend\News;

use App\Domains\News\Models\Category;
use App\Domains\News\Models\News;
use App\Domains\News\Models\NewsMeta;
use App\Http\Controllers\Controller;
use Image;
use Illuminate\Http\Request;
use Carbon\Carbon;
use function Couchbase\defaultDecoder;

class NewsController extends Controller {

    /**
     * list all new item
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $input = $request->all();
        $postData = $_POST;
        $qr = News::join('news_meta', 'news.new_id', '=', 'news_meta.new_id')
                        ->where('news_meta.lang_code', app()->getLocale());
        if ( isset($input['search']) && !empty($input['search']) ) {
            $s = str_replace('-','%', clean_url($input['search']));

            $qr->where(function ($query) use ($s){
                $query->where('news_meta.title', 'like', "%{$s}%")
                    ->orWhere('news_meta.slug', 'like', "%{$s}%");
            });
        }

        if ( isset($input['cat']) && !empty($input['cat']) ) {
            $qr->where('cat_id', $input['cat']);
            $selectedCat = $input['cat'];
        }


        if ( isset($input['date']) && !empty($input['date']) ) {
            $inputDate = explode('to', $input['date']);
            $from_date = Carbon::createFromFormat('Y-m-d', trim($inputDate[0]))->format('Y-m-d'.' 00:00:01');
            $to_date = Carbon::createFromFormat('Y-m-d', trim($inputDate[1]))->format('Y-m-d'.' 12:59:59');
            $qr->whereBetween('updated_at', [$from_date, $to_date]);
        }

        $newsData = $qr->where('deleted_at', NULL)
                        ->where('news_type', 'news')
                        ->orderBy('news.new_id', 'DESC')
                        ->paginate(10)->withQueryString(); //dd(\DB::getQueryLog());
        $catData = Category::join('category_meta', 'category_meta.cat_id', '=', 'category.cat_id')
            ->where('category_meta.lang_code', app()->getLocale())->get();


        return view('backend.news.index', ['news' => $newsData, 'categories' => $catData??[], 'selectedCat' => $selectedCat ?? null ]);
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
        return redirect()->route('admin.news.index')->withFlashSuccess(__('news.delete_news_success'));
    }

    /**
     * Delete multiple post items
     * @param Request $request
     * @return mixed
     */
    public function deleteAll(Request $request) {
        $postData = $request->post();

        if ( empty($postData['post'])) return redirect()->route('admin.news.index')->withErrors(__('Invalid Request'));

        foreach ($postData['post'] as $id) {
            $postItem = News::find($id);
            if ( isset($postItem->new_id)) $postItem->delete();
        }
        return redirect()->route('admin.news.index')->withFlashSuccess(__('news.delete_news_success'));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function unPublished(Request $request) {
        $postData = $request->post();

        if ( empty($postData['post'])) return redirect()->route('admin.news.index')->withErrors(__('Invalid Request'));

        foreach ($postData['post'] as $id) {
            $postItem = News::find($id);
            if ( isset($postItem->new_id)) {
                $postItem->update(['status' => 'draft']);
                $postItem->touch();
            }
        }
        return redirect()->route('admin.news.index')->withFlashSuccess(__('news.un_publish_success'));
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
                        $image->save($thumbPath);

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


    public function cloneContent(Request $request)
    {
        $input = $request->all();

        if ( !empty($input) ) {

            if(strpos($input['ids'], ';') == TRUE) {
                $data = array_filter(explode(';', $input['ids']));
                foreach ($data as $id) {
                    $meta = NewsMeta::where('new_id', $id)->first();
                    if ( $meta->lang_code == 'vn' ) {
                        $chk = NewsMeta::where('new_id', $id)->where('lang_code', 'en')->first();
                        if ( !isset($chk->meta_id) || empty($chk) ) {
                            $newMetaContent = $meta->replicate();
                            $newMetaContent->lang_code = 'en';
                            $newMetaContent->save();
                        }
                    }
                }
                return redirect()->route('admin.news.cloneContent')->withFlashSuccess(__('Update lang success!'));
            } else {
                $meta = NewsMeta::where('new_id', $input['ids'])->first();
                if ( $meta->lang_code == 'vn' ) {
                    $chk = NewsMeta::where('new_id', $input['ids'])->where('lang_code', 'en')->first();
                    if ( !isset($chk->meta_id) || empty($chk) ) {
                        $newMetaContent = $meta->replicate();
                        $newMetaContent->lang_code = 'en';
                        $newMetaContent->save();
                    }
                }
                return redirect()->route('admin.news.cloneContent')->withFlashSuccess(__('Update lang success!'));
            }
        }

        return view('backend.news.cloneView');
    }

}
