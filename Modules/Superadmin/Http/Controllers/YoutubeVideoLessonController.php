<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Models\Business;
use App\Models\YoutubeVideoLesson;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class YoutubeVideoLessonController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $youtube_video_lessons = YoutubeVideoLesson::select('id', 'url_from_app', 'url_from_youtube', 'page_name')->get()->toArray();
        if (request()->ajax()) {
            return DataTables::of($youtube_video_lessons)->addColumn('action', function($row) {
                return 
                '<div class="btn-group">
               
                <a href="'.\route('youtube-video-lessons.edit', [ $row['id'] ]).'" class="btn btn-warning btn-sm" style="height: 30px;"><i class="glyphicon glyphicon-edit"></i> Editar</a></li>
                <form id="delete_youtube_video_lesson_'.$row['id'].'" method="POST" action="'. route('youtube-video-lessons.destroy', $row['id']) .'">
                <button type="button" class="btn btn-danger btn-delete btn-sm"><i class="fa fa-trash"></i> Excluir</button>
                '. method_field('DELETE') .'
                '. csrf_field() .'
                </form>
                
                </div>'
                ;
            })->toArray();
        }
        $business_id = request()->session()->get('user.business_id');
        $business = Business::find($business_id);
        return view('superadmin::youtube_video_lessons.index')->with('business', $business);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('superadmin::youtube_video_lessons.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $data = $request->except('_token');
            $new = new YoutubeVideoLesson();
            $new->create($data);
            $output = [
                'success' => 1,
                'msg' => 'Video cadastrado!'
            ];
        }catch(\Exception $e){
            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];
        }
        return redirect('superadmin/youtube-video-lessons')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $edit = YoutubeVideoLesson::findOrFail($id);
        return view('superadmin::youtube_video_lessons.edit')->with('duplicate_youtube_video_lesson', $edit);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {   
        try{
            $data = $request->except(['_token', '_method']);
            $new = new YoutubeVideoLesson();
            $new->where('id', $id)->update($data);
            $output = [
                'success' => 1,
                'msg' => 'Video atualizado!'
            ];
        }catch(\Exception $e){
            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];
        }
        return redirect('superadmin/youtube-video-lessons')->with('status', $output);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {   
        try{
            YoutubeVideoLesson::destroy($id);
            $output = [
                'success' => 1,
                'msg' => 'Video removido!'
            ];
        }catch(\Exception $e){
            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];
        }
        return redirect('superadmin/youtube-video-lessons')->with('status', $output);
    }
}
