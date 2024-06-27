<?php
/**
 * @author tmtuan
 * created Date: 13-Nov-20
 */

namespace App\Domains\News\Http\Controllers\Backend\Category;

use App\Domains\News\Models\Category;
use App\Http\Controllers\Controller;

class CategoryController extends Controller {

    /**
     * list all category and show add new category form
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index() {
        $cat_parent = Category::join('category_meta', 'category.cat_id', '=', 'category_meta.cat_id' )
            ->where('category_meta.lang_code', app()->getLocale())
            ->where('status', 'published')
            ->where('parent_id', 0)
            ->where('deleted_at', NULL)
            ->get();

        $cat_data = Category::join('category_meta', 'category.cat_id', '=', 'category_meta.cat_id' )
                    ->where('category_meta.lang_code', app()->getLocale())
                    ->where('deleted_at', NULL)
                    ->get();

        return view('backend.category.index', ['cat_data' => $cat_data, 'cat_parent' => $cat_parent]);
    }

    /**
     * delete cateogry
     * @param $cat_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($cat_id) {
        $catData = Category::find( $cat_id);

        if ( !empty($catData) ) {
            $child = Category::where('parent_id', $catData->cat_id)->first();
            if ( !empty($child) ) return redirect()->route('admin.category.index')->withErrors(__('news.delete_category_parent'));

            $catData->delete();
            return redirect()->route('admin.category.index')->withFlashSuccess(__('news.delete_category_success'));
        } else return redirect()->route('admin.category.index')->withFlashSuccess(__('Invalid Request'));
    }

}
