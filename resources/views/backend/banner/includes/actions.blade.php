<a class="btn btn-sm btn-primary" href="{{ route('admin.banner.edit', ['id' => $item->banner_id]) }}">
    <i class="fa fa-edit text-white"></i>
</a>
<a class="btn btn-sm btn-danger" href="{{ route('admin.banner.delete', ['id' => $item->banner_id]) }}">
    <i class="fa fa-trash-alt text-white"></i>
</a>
