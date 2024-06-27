<?php
/**
 * @author tmtuan
 * created Date: 12-Nov-20
 */


namespace App\Domains\News\Http\Controllers\Backend\Manual;

use App\Domains\News\Models\Category;
use App\Domains\News\Models\NewsMeta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Domains\News\Models\News;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;


class EditManualController extends ManualController {

    /**
     * show edit form
     * @param $new_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($new_id) {
        $post = News::find($new_id);

        if( empty($post) ) return redirect()->route('admin.manual.index')->withErrors(__('Invalid Request'));

        return view('backend.manual.edit', ['item' => $post]);
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
                'status' => $inputData['status'],
            ]);

            //save post by lang
            $itemMeta = [
                'title' => $inputData['title'],
                'slug' => clean_url($inputData['title']),
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

            return redirect()->route('admin.manual.index')->withFlashSuccess(__('news.edit_manual_success'));

        } else return redirect()->route('admin.manual.index')->withErrors(__('Invalid Request'));

    }

}
