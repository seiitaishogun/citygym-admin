<?php
/**
 * @author tmtuan
 * created Date: 13-Nov-20
 */

namespace App\Domains\News\Http\Controllers\Backend\Banner;

use App\Domains\News\Http\Controllers\Backend\Banner\BannerController;
use App\Domains\News\Models\Banner;
use App\Domains\News\Models\BannerMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddBannerController extends BannerController {

    /**
     * show add form page
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function add(Request $request){
        $data = [];
        return view('backend.banner.add', ['data' => $data]);
    }

    /**
     * perform add new baner action
     * @param Request $request
     * @return mixed
     */
    public function addAction(Request $request) {
        $user = Auth::user();
        $postData = $request->post();

        $validator = \Validator::make($postData, [
                'title' => 'required|min:2',
                'link_type' => 'required',
                //'lang_code' => 'required',
            ],
            [
                'title.required' => 'Vui lòng nhập Tên',
                'link_type.required' => 'Vui lòng chọn loại banner',
                //'lang_code.required' => 'Vui lòng chọn ngôn ngữ',
            ]
        );

        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator->messages());
        }
        //add new banner
        $banner = new Banner($postData);
        $banner->user_id = $user->id;
        $banner->order = empty($postData['order']) ?? 0;
        $banner->save();

        $bannerMeta = new BannerMeta($postData);
        $bannerMeta->banner_id = $banner->banner_id;
        $bannerMeta->lang_code = 'vn';
        //upload banner
        if ( !empty($_FILES['image']['name'])  ) {
            $fileUpload = $this->upload_img($request);
            if (isset($fileUpload['data']) && !empty($fileUpload['data'])) {
                $bannerMeta->image = json_encode([
                    'full_img' => $fileUpload['data']['path'],
                    'thumb' => $fileUpload['data']['thumbPath']
                ]);
            } else return redirect()->route('admin.banner.add')->withErrors($fileUpload['msg']);
        }
        $bannerMeta->save();
        return redirect()->route('admin.banner.index')->withFlashSuccess(__('news.add_banner_success'));
    }

    public function addActionBak(Request $request) {
        $user = Auth::user();
        $inputData = $request->all();

        if ( empty( $inputData['vn']['title']) || empty($inputData['en']['title']) ) return redirect('admin/banner/add')
            ->withErrors(__('news.title_empty'))
            ->withInput();

        $banner = new Banner($inputData);
        $banner->user_id = $user->id;
        $banner->save();
        if ( $banner->banner_id ) {
            //save banner by lang
            $vnMeta = [
                'banner_id' => $banner->banner_id,
                'title' => $inputData['vn']['title'],
                'lang_code' => 'vn'
            ];
            if ( $inputData['vn']['banner_type'] == 'content' ) $vnMeta['content'] = $inputData['vn']['content'];
            elseif ( $inputData['vn']['banner_type'] == 'display_url' ) $vnMeta['display_url'] = $inputData['vn']['display_url'];
            //upload banner
            if ( !empty($_FILES['vn']['name']['image'])  ) {
                $fileUpload = $this->upload_img('vn', $request);
                if (isset($fileUpload['data']) && !empty($fileUpload['data'])) {
                    $vnMeta['image'] = json_encode([
                        'full_img' => $fileUpload['data']['path'],
                        'thumb' => $fileUpload['data']['thumbPath']
                    ]);
                } else return redirect()->route('admin.banner.add')->withErrors($fileUpload['msg']);
            }
            BannerMeta::create($vnMeta);

            $enMeta = [
                'banner_id' => $banner->banner_id,
                'title' => $inputData['en']['title'],
                'lang_code' => 'en'
            ];
            if ( $inputData['en']['banner_type'] == 'content' ) $enMeta['content'] = $inputData['en']['content'];
            elseif ( $inputData['en']['banner_type'] == 'display_url' ) $enMeta['display_url'] = $inputData['en']['display_url'];

            if ( !empty($_FILES['en']['name']['image']) ) {
                //upload banner
                $fileUpload = $this->upload_img('en', $request);
                if (isset($fileUpload['data']) && !empty($fileUpload['data'])) {
                    $enMeta['image'] = json_encode([
                        'full_img' => $fileUpload['data']['path'],
                        'thumb' => $fileUpload['data']['thumbPath']
                    ]);
                    return redirect()->route('admin.banner.index')->withFlashSuccess(__('news.add_banner_success'));

                } else return redirect()->route('admin.banner.add')->withErrors($fileUpload['msg']);
            }
            BannerMeta::create($enMeta);

        } else {
            $banner->forceDelete();
            return redirect()->route('admin.news.index')->withFlashSuccess(__('news.add_banner_fail'));
        }

    }
}
