<?php
/**
 * @author tmtuan
 * created Date: 12-Nov-20
 */


namespace App\Domains\News\Http\Controllers\Backend\News;

use App\Domains\News\Models\Category;
use App\Domains\News\Models\NewsMeta;
use Illuminate\Http\Request;
use App\Domains\News\Models\News;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;


class CreateNewsController extends NewsController {

    public function create() {
        $catData = Category::join('category_meta', 'category.cat_id', '=', 'category_meta.cat_id' )
            ->where('category_meta.lang_code', app()->getLocale())
            ->where('status', 'published')
            ->where('deleted_at', NULL)
            ->get();

        return view('backend.news.create', ['cat_data' => $catData]);
    }

    public function addAction(Request $request) {
        $user = Auth::user();
        $user_type = get_class($user);
        $inputData = $request->all();

        $validator = \Validator::make($inputData, [
                'title' => 'required',
            ],
            [
                'title.required' => __('news.title_empty'),
            ]
        );
        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator->messages())
                ->withInput();
        }

        $post = new News($inputData);
        $post->author_id = $user->id;
        $post->author_type = $user_type;
        $post->save();
        if ( $post->new_id ) {
            //save post by lang
            $itemMeta = [
                'new_id' => $post->new_id,
                'title' => $inputData['title'],
                'slug' => clean_url($inputData['title']),
                'display_url' => $inputData['display_url'],
                'content' => $inputData['content'],
                'lang_code' => 'vn'
            ];
            if ( !empty($_FILES['image']['name'])  ) {
                $fileUpload = $this->upload_img($request);
                if ( isset($fileUpload['data']) && !empty($fileUpload['data']) ) {
                    $itemMeta['image'] = json_encode([
                        'full_img' => $fileUpload['data']['path'],
                        'thumb' => $fileUpload['data']['thumbPath']
                    ]);
                } else {
                    $post->forceDelete();
                    return redirect()->route('admin.news.create')->withErrors($fileUpload['msg']);
                }
            }
            NewsMeta::create($itemMeta);

            return redirect()->route('admin.news.index')->withFlashSuccess(__('news.add_post_success'));
        } else {
            $post->forceDelete();
            return redirect()->route('admin.news.index')->withFlashSuccess(__('news.add_post_fail'));
        }

    }

}
