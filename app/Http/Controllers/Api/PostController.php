<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use App\Http\Resources\PostResource;

use Illuminate\Support\Facades\Validator;

class PostController extends Controller
    {
    /**
    * index
    *
    * @return void
    */
    public function index()
    {
        $posts = Post::latest()->paginate(5);
        return new PostResource(true, 'List Data Posts', $posts);
    }
    /**
    * store
    *
    * @param mixed $request
    * @return void
    */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'image' => 
        'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'title' => 'required',
        'content' => 'required',
        ]);

    
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());
        
            $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);
    
        return new PostResource(true, 'Data Post Berhasil Ditambahkan!', 
        $post);
    }
    /**
     * show
     * 
     * @param mixed $post
     * @return void
     * 
     */
    public function show($id)
    {
        $post = Post::find($id);

        return new PostResource(true, 'Details Date Post!', $post);
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required | min:10',
            'content' => 'required | min:10',
        ]);

        if($validator->fails()){
         return response()->json($validator->errors(), 422);
        }

        $post = Post::find($id);

        if($request->hasfile('image')){
            $image = $request->file('image');
            $image->storeAs('public/posts/', $image->hashName());

            Storage::delete('public/posts/'.basename($post->$image));

            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,
            ]);
        } else {
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }

        return new PostResource(true, 'Data Post Berhasil Berubah!', $post);
    }
    
    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id)
    {
        //find post by ID
        $post = Post::find($id);

        //delete image
        Storage::delete('public/posts/'.basename($post->image));

        //delete post
        $post->delete();

        //return response
        return new PostResource(true, 'Data Post Berhasil Dihapus!', null);
    }

}