<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PostResource::collection(Post::with('author')->paginate(2));
        }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
   $data= $request->validated();
   $data['author_id'] = 1; // Assuming the author_id is 1 for demonstration purposes
   $post= Post::create($data);
   return new PostResource($post);
    
    
    
    // return response()->json([  
    //         "id" => 1,
    //             "title" => "Post Title",
    //             "content" => "Post Content"
    //         ],200);
    //     // ->setStatusCode(200);
           
    } 

    /**
     * Display the specified resource.
     */
    public function show(Post $post)//id or use route model binding
    {
       // $post = Post::findOrFail($id);
        return 
        response()->json([
            "message" => "Post retrieved successfully",
            "data" => new PostResource($post)
            
            ])->setStatusCode(200);
           
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePostRequest $request, Post $post)
    {
        $data = $request->validated();
        $post->update($data);
        return response()->json([
            "message" => "Post updated successfully",
            "data" => new PostResource($post)     // instead of returning the whole post object, you can return only the updated fields if you prefer
            // [
            //     "id" => $post->id,
            //     "title" => $data['title'],
            //     "content" => $data['content']
            // ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();
        //return response()->noContent();
    }
}
