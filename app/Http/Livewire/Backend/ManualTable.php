<?php

namespace App\Http\Livewire\Backend;

use App\Domains\Auth\Models\User;
use App\Domains\News\Models\News;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Traits\HtmlComponents;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class UsersTable.
 */
class ManualTable extends TableComponent
{
    use HtmlComponents;

    /**
     * @var string
     */
    public $sortField = 'new_id';

    /**
     * @var string
     */
    public $status;

    /**
     * @var array
     */
    protected $options = [
        'bootstrap.container' => false,
        'bootstrap.classes.table' => 'table table-striped',
    ];

    /**
     * @param  string  $status
     */
    public function mount($status = 'active'): void
    {
        $this->status = $status;
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        $langcode = app()->getLocale();
        $query = News::join('news_meta', 'news_meta.new_id', '=', 'news.new_id')
                ->where('news_meta.lang_code', $langcode)
                ->where('news_type', 'manual')
                ->orderBy('news.new_id', 'DESC');

        if ($this->status === 'deleted') {
            return $query->onlyTrashed();
        }

        return $query;
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('news.new_id'), 'news.new_id' )
                ->sortable()
                ->format(function ( News $model ) {
                    return '#'.$model->new_id;
                }),
            Column::make(__('news.image') )
                ->format(function ( News $model ) {
                    $lang = app()->getLocale();
                    if ( isset($model->meta[$lang]->image) && !empty($model->meta[$lang]->image) ) {
                        $imgData = is_object($model->meta[$lang]->image) ? $model->meta[$lang]->image : json_decode($model->meta[$lang]->image);
                    }
                    else $imgData = false;
//                    print_r($imgData);
                    $img = is_object($imgData)?asset('storage/'.$imgData->thumb):asset('img/no-img.png') ;

                    return $this->html('<img src="'.$img.'" class="img-thumbnail" style="max-width: 75px">');
                }),
            Column::make(__('news.title'), 'title' )
                ->searchable(function ($builder, $term){
                    return $builder->join('news_meta', 'news_meta.new_id', '=', 'news.new_id')
                        ->like('tile', $term)
                        ->orLike('slug', $term);
                })
                ->format(function ( News $model ) {
                    $lang_code = app()->getLocale();
                    return isset($model->meta[$lang_code]->title) ? $model->meta[$lang_code]->title : '';
                }),
            Column::make(__('news.created_at'), 'created_at')
                ->sortable()
                ->format(function ( News $model ) {
                    $myTime = Carbon::parse($model->created_at);
                    return $myTime->format('d/m/Y');
                }),
            Column::make(__('news.status'), 'status')
                ->sortable()
                ->format(function ( News $model ) {
                    return __("news.status_{$model->status}");
                }),
            Column::make(__('Actions'))
                ->format(function (News $model) {
                    return view('backend.manual.includes.actions', ['data' => $model]);
                }),
        ];
    }
}
