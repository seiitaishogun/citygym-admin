<?php
/**
 * @author tmtuan
 * created Date: 17-Nov-20
 */

namespace App\Domains\News\Http\Controllers\Api;

use App\Domains\News\Models\Banner;
use App\Domains\News\Models\Category;
use App\Domains\News\Models\News;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class NewsController extends ApiController {

    /**
     * API get all category
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listCategories(Request $request) {
        $queryData = $request->all();
        $lang = $queryData['lang']??'vn';
        $catData = Category::join('category_meta', 'category.cat_id', '=', 'category_meta.cat_id' )
            ->where('category_meta.lang_code', $lang)
            ->where('status', 'published')
            ->where('deleted_at', NULL)
            ->orderBy('thutu', 'ASC')
            ->get();

        if ( empty($catData) ) return response()->json(['status' => 'fail', 'msg' => __('No Record Found')], 404);
        else return response()->json([
            'status' => 'success',
            'data' => $catData,
        ],200);
    }

    public function getCategory(Request $request) {
        $queryData = $request->all();
        $lang = $queryData['lang']??'vn';
        $catItem = Category::join('category_meta', 'category.cat_id', '=', 'category_meta.cat_id' )
            ->where('category_meta.lang_code', $lang)
            ->where('category.cat_id', $queryData['id'])
            ->where('status', 'published')
            ->where('deleted_at', NULL)
            ->first();

        if ( empty($catItem) ) return response()->json(['status' => 'fail', 'msg' => __('No Record Found')], 404);
        else return response()->json([
            'status' => 'success',
            'data' => $catItem,
        ],200);
    }

    /**
     * API get all news or list news by category
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listNews(Request $request)
    {
        $queryData = $request->all();
        $lang = $queryData['lang'] ?? 'vn';

        $conds = [
            ['news_meta.lang_code', $lang],
            ['deleted_at', NULL]
        ];
        if (isset($queryData['cat_id']) && $queryData['cat_id'] > 0) $conds[] = ['cat_id', $queryData['cat_id']];

        $per_page = $queryData['per_page'] ?? 5;
        $newsData = News::join('news_meta', 'news.new_id', '=', 'news_meta.new_id')
            ->where('news_type', 'news')
            ->where($conds)->orderBy('news_meta.new_id', 'DESC')
            ->paginate($per_page);

        if (empty($newsData)) return response()->json(['status' => 'fail', 'msg' => __('No Record Found')], 404);
        else {
            $returnData = [];
            foreach ($newsData as $post) {
                $postItem = $post->toArray();
                $img['full_img'] = URL::to('storage'.$post->image->full_img);
                $img['thumb'] = URL::to('storage'.$post->image->thumb);

                $postItem['image'] = $img;
                $returnData[] = $postItem;
            }
            return response()->json([
                'status' => 'success',
                'data' => $returnData,
            ], 200);
        }
    }

    /**
     * API get new detail
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNew(Request $request) {
        $queryData = $request->all();
        $lang = $queryData['lang']??'vn';

        $post = News::join('news_meta', 'news.new_id', '=', 'news_meta.new_id')
                    ->where('news.new_id', $queryData['id'])
                    ->where('news_meta.lang_code',$lang)
                    ->where('status','published')
                    ->where('deleted_at',NULL)
                    ->first()->toArray();

        $img['full_img'] = URL::to('storage'.$post['image']->full_img);
        $img['thumb'] = URL::to('storage'.$post['image']->thumb);
        $post['image'] = $img;

        if ( empty($post) ) return response()->json(['status' => 'fail', 'msg' => __('No Record Found')], 404);
        else return response()->json([
            'status' => 'success',
            'data' => $post,
        ], 200);
    }

    public function listBanner(Request $request) {
        $queryData = $request->all();
        $lang = $queryData['lang']??'vn';
        $bannerData = Banner::join('banner_meta', 'banner.banner_id', '=', 'banner_meta.banner_id' )
            ->where('banner_meta.lang_code', $lang)
            ->where('status', 'published')
            ->where('deleted_at', NULL)
            ->orderByDesc('updated_at')
            ->get();

        if ( empty($bannerData) ) return response()->json(['status' => 'fail', 'msg' => __('No Record Found')], 404);
        else {
            $returnData = [];
            foreach ($bannerData as $item) {
                $bannerItem = $item->toArray();
                $img['full_img'] = URL::to('storage'.$item->image->full_img);
                $img['thumb'] = URL::to('storage'.$item->image->thumb);

                $bannerItem['image'] = $img;
                $returnData[] = $bannerItem;
            }
            return response()->json([
                'status' => 'success',
                'data' => $returnData,
            ], 200);
        }
    }

}
