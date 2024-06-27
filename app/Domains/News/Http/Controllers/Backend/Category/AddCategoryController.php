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

class AddCategoryController extends Controller {

    /**
     * perform add new category action
     * @param Request $request
     * @return mixed
     */
    public function addAction(Request $request) {
        $user = Auth::user();
        $user_type = get_class($user);
        $inputData = $request->all();

        if ( empty( $inputData['cat_name']) ) return redirect('admin/category')
            ->withErrors(__('news.cat_name_empty'))
            ->withInput();

        $cat = new Category($inputData);
        $cat->author_id = $user->id;
        $cat->author_type = $user_type;
        $cat->save();
        if ( $cat->cat_id ) {
            //save cat by lang
            $catMeta = [
                'cat_id' => $cat->cat_id,
                'cat_name' => $inputData['cat_name'],
                'slug' => clean_url($inputData['cat_name']),
                'lang_code' => app()->getLocale()??'vn'
            ];
            CategoryMeta::create($catMeta);

        }
        return redirect()->route('admin.category.index')->withFlashSuccess(__('news.add_cat_success'));
    }
}
