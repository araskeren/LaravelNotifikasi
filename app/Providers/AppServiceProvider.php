<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Observers\PostObserver;

use App\Notifications\NewPost;

use App\Post;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Post::observe(PostObserver::class);
        Post::created(function($post){

          $user = $post->user;
          foreach ($user->followers as $follower) {
              $follower->notify(new NewPost($user, $post));
          }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
