@extends('layouts.app')
@section('title', 'Videos do Youtube')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 style="margin-bottom: 30px;">Link de videos YouTube
        <small>Gerencie em quais paginas terão link para o youtube</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Videos'])

    <a class="btn btn-primary pull-right" href="{{action('\Modules\Superadmin\Http\Controllers\YoutubeVideoLessonController@create')}}">
        <i class="fa fa-plus"></i> @lang('messages.add')
    </a>

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped w-100" id="youtube_video_lessons_table">
            <thead>
                <tr>
                    <th>URL do App</th>
                    <th>URL do Youtube</th>
                    <th>Nome da Página</th>
                    <th>Ações</th>
                </tr>
            </thead>
        </table>
    </div>
    @endcomponent
</section> 
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready( function(){
        youtube_video_lessons_table = $('#youtube_video_lessons_table').DataTable({
            processing: true,
            serverSide: true,
            "ajax": {
                "url": "/superadmin/youtube-video-lessons",
            },
            "columns": [
            { data: 'url_from_app'},
            { data: 'url_from_youtube' },
            { data: 'page_name' },
            { data: 'action' },
            ],
        });

        function deleteYoutubeVideo() {
            $('#btn_delete_youtube_video_lesson').click(function() {

            })
        }
    });
</script>
@endsection