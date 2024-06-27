<?php
/**
 * @author tmtuan
 * created Date: 13-Nov-20
 */

namespace App\Domains\News\Http\Controllers\Backend\Category;

use App\Domains\News\Models\Category;
use App\Domains\News\Models\CategoryMeta;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EditCategoryController extends Controller {

    /**
     * show edit form
     * @param $new_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id){
        $catData = Category::where('cat_id', $id)
                ->where('deleted_at', NULL)
                ->first();
        if ( !empty($catData) ) {
            $cat_parent = Category::join('category_meta', 'category.cat_id', '=', 'category_meta.cat_id' )
                ->where('category_meta.lang_code', app()->getLocale())
                ->where('status', 'published')
                ->where('parent_id', 0)
                ->where('deleted_at', NULL)
                ->get();
            return view('backend.category.edit', ['cat' => $catData, 'cat_parent' => $cat_parent]);
        } else return redirect()->route('admin.category.index')->withErrors(__('Invalid Request'));

    }

    /**
     * perform edit action
     * @param Request $request
     * @return mixed
     */
    public function editAction($id, Request $request) {
        $inputData = $request->all();

        if ( empty( $inputData['cat_name']) ) return redirect('admin/category/edit/'.$id)
            ->withErrors(__('news.cat_name_empty'))
            ->withInput();

        $catData = Category::find( $id);

        if ( !empty($catData) ) {
            $catData->fill($inputData);
            $catData->save();

            //update meta
            CategoryMeta::where('cat_id', $catData->cat_id)
                        ->where('lang_code', app()->getLocale()??'vn')
                        ->update([
                            'cat_name' => $inputData['cat_name'],
                            'slug' => clean_url($inputData['cat_name'])
                        ]);

            return redirect()->route('admin.category.index')->withFlashSuccess(__('news.edit_cat_success', ));
        } else return redirect()->route('admin.category.index')->withFlashSuccess(__('Invalid Request'));

    }
}
