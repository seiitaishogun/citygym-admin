<?php
/**
 * @author tmtuan
 * created Date: 12-Nov-20
 */


namespace App\Domains\News\Http\Controllers\Backend\News;

use App\Domains\News\Models\Category;
use App\Domains\News\Models\NewsMeta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Domains\News\Models\News;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;


class EditNewsController extends NewsController {

    /**
     * show edit form
     * @param $new_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($new_id) {
        $catData = Category::join('category_meta', 'category.cat_id', '=', 'category_meta.cat_id' )
            ->where('category_meta.lang_code', app()->getLocale())
            ->where('status', 'published')
            ->where('deleted_at', NULL)
            ->get();
        $post = News::find($new_id); //dd($post->meta->content);

        if( empty($post) ) return redirect()->route('admin.news.index')->withErrors(__('Invalid Request'));
        return view('backend.news.edit', ['cat_data' => $catData, 'item' => $post]);
    }

    /**
     * perform edit action
     * @param Request $request
     * @return mixed
     */
    public function editAction($new_id, Request $request) {
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

        $post = News::find($new_id);
        if ( !empty($post) ) { //dd($post->meta['vn']->image);
            //edit new data
            $post->update([
                'cat_id' => $inputData['cat_id'],
                'status' => $inputData['status'],
                'is_url_display' => (empty($inputData['is_url_display']))?0:$inputData['is_url_display'],
                'updated_at' => Carbon::now()
            ]);
            $post->touch();

            //save post by lang
            $itemMeta = [
                'title' => $inputData['title'],
                'slug' => clean_url($inputData['title']),
                'display_url' => $inputData['display_url'],
                'content' => $inputData['content'],
            ];
            if ( !empty($_FILES['image']['name'])  ) {
                $fileUpload = $this->upload_img($request, (new Carbon($post->created_at))->format('Y/m') );
                if ( isset($fileUpload['data']) && !empty($fileUpload['data']) ) {
                    $itemMeta['image'] = json_encode([
                        'full_img' => $fileUpload['data']['path'],
                        'thumb' => $fileUpload['data']['thumbPath']
                    ]);
                    if ( isset($post->meta->image->full_img) ) Storage::delete('public'.$post->meta->image->full_img);
                    if ( isset($post->meta->image->thumb) ) Storage::delete('public'.$post->meta->image->thumb);

                } else return redirect()->back()->withErrors($fileUpload['msg']);
            }

            NewsMeta::where('new_id', $post->new_id)
                ->where('lang_code', 'vn')
                ->update($itemMeta);

            return redirect()->route('admin.news.index')->withFlashSuccess(__('news.edit_post_success'));

        } else return redirect()->route('admin.news.index')->withErrors(__('Invalid Request'));

    }

}
