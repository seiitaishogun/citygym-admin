<?php
/**
 * @author tmtuan
 * created Date: 13-Nov-20
 */

namespace App\Domains\News\Http\Controllers\Backend\Banner;

use App\Domains\News\Http\Controllers\Backend\Banner\BannerController;
use App\Domains\News\Models\Banner;
use App\Domains\News\Models\BannerMeta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EditBannerController extends BannerController {

    /**
     * show edit form page
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id, Request $request){
        $lang = $request->get('lang');
        $lang = $lang ?? app()->getLocale();
        $data = Banner::find($id);

        if ( !empty($data) ) {
            $data->getMeta($lang);
            return view('backend.banner.edit', ['data' => $data, 'lang' => $lang]);
        } else return  redirect()->route('admin.banner.index')->withErrors(__("Invalid Request"));

    }

    /**
     * perform edit new baner action
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function editAction($id, Request $request) {
        $user = Auth::user();
        $postData = $request->post();

        $banner = Banner::find($id);
        if ( empty($banner) ) return  redirect()->route('admin.banner.index')->withErrors(__("Invalid Request"));

        $validator = \Validator::make($postData, [
            'title' => 'required|min:2',
            'link_type' => 'required',
            'lang_code' => 'required',
        ],
            [
                'title.required' => 'Vui lòng nhập Tên',
                'link_type.required' => 'Vui lòng chọn loại banner',
                'lang_code.required' => 'Vui lòng chọn ngôn ngữ',
            ]
        );

        //edit banner data
        $banner->update([
            'status' => $postData['status'],
            'order' => $postData['order'],
        ]);
        $banner->touch();
        $banner->getMeta($postData['lang_code']??'vn');


        //upload banner
        $bannerMeta = new BannerMeta($postData);
        $bannerMeta->banner_id = $banner->banner_id;

        if ( !empty($_FILES['image']['name'])  ) {
            $fileUpload = $this->upload_img($request);
            if (isset($fileUpload['data']) && !empty($fileUpload['data'])) {
                $bannerMeta->image = json_encode([
                    'full_img' => $fileUpload['data']['path'],
                    'thumb' => $fileUpload['data']['thumbPath']
                ]);

                //delete old image
                if ( isset($banner->meta->image->full_img) ) Storage::delete('public'.$banner->meta->image->full_img);
                if ( isset($banner->meta->image->thumb) ) Storage::delete('public'.$banner->meta->image->thumb);

            } else return redirect()->route('admin.banner.add')->withErrors($fileUpload['msg']);
        }

        //update banner meta
        BannerMeta::updateMeta($bannerMeta->toArray());

        return redirect()->route('admin.banner.index')->withFlashSuccess(__('news.edit_banner_success'));
    }

    public function editAction12($id, Request $request) {
        $inputData = $request->all();

        if ( empty( $inputData['vn']['title']) || empty($inputData['en']['title']) ) return redirect('admin/banner/edit/'.$id)
            ->withErrors(__('news.title_empty'))
            ->withInput();

        $banner = Banner::find($id);
        if ( $banner->banner_id ) {
            //edit banner data
            $banner->update([
                'status' => $inputData['status'],
            ]);

            //save banner by lang
            $vnMeta = [
                'title' => $inputData['vn']['title'],
            ];
            //upload banner
            $fileUpload = $this->upload_img('vn', $request, (new Carbon($banner->created_at))->format('Y/m'));
            if ( isset($fileUpload['data']) && !empty($fileUpload['data']) ) {
                $vnMeta['image'] = json_encode([
                    'full_img' => $fileUpload['data']['path'],
                    'thumb' => $fileUpload['data']['thumbPath']
                ]);

                //delete old image
                if ( isset($banner->meta['vn']->image->full_img) ) Storage::delete('public'.$banner->meta['vn']->image->full_img);
                if ( isset($banner->meta['vn']->image->thumb) ) Storage::delete('public'.$banner->meta['vn']->image->thumb);

                BannerMeta::where('banner_id', $banner->banner_id)
                    ->where('lang_code', 'vn')
                    ->update($vnMeta);
                unset($fileUpload);

                $enMeta = [
                    'banner_id' => $banner->banner_id,
                    'title' => $inputData['en']['title'],
                    'lang_code' => 'en'
                ];
                //upload banner
                $fileUpload = $this->upload_img('en', $request, (new Carbon($banner->created_at))->format('Y/m'));
                if ( isset($fileUpload['data']) && !empty($fileUpload['data']) ) {
                    $enMeta['image'] = json_encode([
                        'full_img' => $fileUpload['data']['path'],
                        'thumb' => $fileUpload['data']['thumbPath']
                    ]);

                    //delete old image
                    if ( isset($banner->meta['en']->image->full_img) ) Storage::delete('public'.$banner->meta['en']->image->full_img);
                    if ( isset($banner->meta['en']->image->thumb) ) Storage::delete('public'.$banner->meta['en']->image->thumb);

                    BannerMeta::where('banner_id', $banner->banner_id)
                        ->where('lang_code', 'en')
                        ->update($enMeta);

                    return redirect()->route('admin.banner.index')->withFlashSuccess(__('news.edit_banner_success'));
                } else return redirect()->back()->withErrors($fileUpload['msg']);

            } else return redirect()->back()->withErrors($fileUpload['msg']);


        } else return redirect()->route('admin.banner.index')->withErrors(__('Invalid Request'));

    }
}
