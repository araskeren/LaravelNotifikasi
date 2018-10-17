<?php
namespace App\Observers;

use App\Notifications\NewPost;
use App\Post;

class PostObserver
{

    private $post;

    public function __construct(Post $post){
      $this->post = $post;
    }
    /**
     * Called whenever a post is created
     * @param Post $post
     */
    public function created(Post $post)
    {
        dd($post);
        $user = $post->user;
        foreach ($user->followers as $follower) {
            $follower->notify(new NewPost($user, $post));
        }
    }
}
