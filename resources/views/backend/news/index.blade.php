@extends('backend.layouts.app')

@section('title', __('news.news_title'))

@section('content')
    <div class="card">
        <div class="card-header">
            @lang('news.news_title')

            <div class="card-header-actions">
                <x-utils.link
                    icon="c-icon cil-plus"
                    class="card-header-action"
                    :href="route('admin.news.create')"
                    :text="__('news.add_new')"
                />
            </div>
        </div>
        <div class="card-body">
            <form  method="POST" action="" id="tmtNews" enctype="multipart/form-data">
                <div class="row">
                    @csrf
                        <div class="col-6">
                            <div class="row">
                                <div class="col-4">
                                    <button class="btn btn-sm btn-danger" type="submit" id="dellAllPost" name="action" value="tmtDell" formaction="{{route('admin.news.deleteAll')}}" >Xoá toàn bộ tin</button>
                                    <button class="btn btn-sm btn-warning" type="submit" name="action" value="tmtDell" formaction="{{route('admin.news.unPublished')}}" >Gỡ tin đăng</button>
                                </div>

                                <div class="col-4">
                                    <div class="form-group" id="sltTime" >
                                        <select class="form-control" name="cat" id="tmtChangeCat">
                                            <option value="" >--Chọn Danh Mục--</option>
                                            @foreach($categories as $item)
                                                <option value="{{ $item->cat_id  }}" {{ ( isset($selectedCat) && $selectedCat == $item->cat_id ) ? 'selected' : '' }} > {{ $item->cat_name }} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="input-group mb-3">
                                        <input class="form-control" id="datepicker" type="text" name="date" placeholder="Chọn ngày">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary btn-sm" type="submit" name="action" value="tmtFilterDate" ><span class="fa fa-search"></span> </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-6">

                            <div class="d-inline-flex float-right">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Tìm kiếm..." aria-label="Recipient's username" name="search" aria-describedby="basic-addon2">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary btn-sm" type="submit" name="action" value="tmtSearch" ><span class="fa fa-search"></span> </button>
                                    </div>
                                </div>
                            </div>
                        </div>


                </div>


            <table class="table table-responsive-sm table-striped">
                <thead>
                <tr>
                    <th><input id="selectAll" type="checkbox" ></th>
                    <th>@lang('news.new_id')</th>
                    <th>@lang('news.image')</th>
                    <th>@lang('news.news_title')</th>
                    <th>@lang('news.categories')</th>
                    <th>@lang('news.created_at')</th>
                    <th>@lang('news.updated_at')</th>
                    <th>@lang('news.status')</th>
                    <th>{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @if(count($news) > 0 )
                    @foreach($news as $item)
                        <tr>
                            <td><input id="tmt-select-{{$item->new_id}}" type="checkbox" name="post[]" value="{{ $item->new_id }}"></td>
                            <td>#{{ $item->new_id }}</td>
                            <td>
                                <img src='{{ asset('storage'.(is_object($item->image)?$item->image->thumb:$item->image)) }}' class="img-thumbnail" style="max-width: 75px">
{{--                                <img src="{{ route('image.displayImage',$item->image) }}" alt="" title="" class="img-thumbnail">--}}
                            </td>
                            <td>
                                <a href="{{ route('admin.news.edit', ['id' => $item->new_id]) }}">
                                    {{ $item->title }}
                                </a>
                            </td>
                            <td>
                                @if($item->cat_id > 0 )
                                    {{ $item->category }}
                                @endif
                            </td>
                            <td>
                                {{ $item->created_date }}
                            </td>
                            <td>
                                {{ $item->updated_at }}
                            </td>
                            <td>
                                @lang("news.status_{$item->status}")
                            </td>
                            <td>
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.news.edit', ['id' => $item->new_id]) }}">
                                    <i class="fa fa-edit text-white"></i>
                                </a>
                                <a class="btn btn-sm btn-danger" href="{{ route('admin.news.delete', ['id' => $item->new_id]) }}">
                                    <i class="fa fa-trash-alt text-white"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9">
                            {{ __('No Record Found') }}
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>

            </form>
            {{ $news->links() }}
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(function () {
        $('#tmtChangeCat').on('change', function () {
            $('#tmtNews').submit();
        });

        $('#dellAllPost').on('click', function () {
            var datastring = $("#tmtNews").serialize();
            console.log(datastring);
            {{--$.ajax({--}}
            {{--    type: "POST",--}}
            {{--    url: "{{route('admin.news.deleteAll')}}",--}}
            {{--    data: datastring,--}}
            {{--    success: function(data) {--}}
            {{--        alert('Data send');--}}
            {{--    }--}}
            {{--});--}}
        });

        flatpickr("#datepicker", {
            mode: "range",
            enableTime: false,
            dateFormat: "Y-m-d",
        });

        $('#selectAll').click(function(e){
            var table= $(e.target).closest('table');
            $('td input:checkbox',table).prop('checked',this.checked);
        });



    });

</script>
@endsection
