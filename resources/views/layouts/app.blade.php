<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="/css/app.css" rel="stylesheet">

    <!-- Scripts -->
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
    <!-- This makes the current user's id available in javascript -->
    @if(!auth()->guest())
        <script>
            window.Laravel.userId = <?php echo auth()->user()->id; ?>
        </script>
    @endif
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    @if(!Auth::guest())
                    <a class="navbar-brand" href="{{ url('/home') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                    @else
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                    @endif
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    @if(!Auth::guest())
                    <ul class="nav navbar-nav">
                        &nbsp;
                        <li><a href="{{ url('/posts/create') }}">New Post</a></li>
                    </ul>
                    <ul class="nav navbar-nav">
                        &nbsp;
                        <li><a href="{{ url('/users') }}">User List</a></li>
                    </ul>
                    @endif
                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ url('/login') }}">Login</a></li>
                            <li><a href="{{ url('/register') }}">Register</a></li>
                        @else
                          <li class="dropdown" id="dropdown-notifikasi">
                              <a class="dropdown-toggle" id="notifications" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                  <span class="glyphicon glyphicon-user"></span>
                              </a>
                              <ul class="dropdown-menu" aria-labelledby="notificationsMenu" id="notificationsMenu">
                                  <li class="dropdown-header">No notifications</li>
                              </ul>
                          </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="{{ url('/logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    @include ('layouts.partials._notifications')
                </div>
            </div>
        </div>

        <div class="container">
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="/js/app.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
      // check if there's a logged in user
      if(Laravel.userId) {
          $.get('/notifications', function (data) {
              addNotifications(data, "#notifications");
          });
      }
      window.setInterval(function(){
        runNotification();
        console.log('Notifikasi Berjalan !');
      }, 5000);
    });

    /*
      Ketika tombol di notifikasi di klik maka akan mengambil data notifikasi dari database lalu menampilkannya.
      Hanya mendapatkan notifikasi yang telah di baca saja.
    */
    $('#notifications').on('click',function(e){
      runNotification();
    })

    function runNotification(){
      $.get('/notifications', function (data) {
        console.log(data);
        $('#notificationsMenu').remove();
        $('#dropdown-notifikasi').append("<ul class='dropdown-menu' aria-labelledby='notificationsMenu' id='notificationsMenu'> <li class='dropdown-header'>No notifications</li></ul>");
        if(data.length>0){
          showNotifications(data, "#notifications");
        }
      });
    }

    function addNotifications(newNotifications, target) {
      notifications = _.concat(notifications, newNotifications);
      // show only last 5 notifications
      notifications.slice(0, 5);
      showNotifications(notifications, target);
    }
    function showNotifications(notifications, target) {
      if(notifications.length) {
          var htmlElements = notifications.map(function (notification) {
              return makeNotification(notification);
          });
          $(target + 'Menu').html(htmlElements.join(''));
          $(target).addClass('has-notifications')
      } else {
          $(target + 'Menu').html('<li class="dropdown-header">No notifications</li>');
          $(target).removeClass('has-notifications');
      }
    }
    // Make a single notification string
    function makeNotification(notification) {
        var to = routeNotification(notification);
        var notificationText = makeNotificationText(notification);
        return '<li><a href="' + to + '">' + notificationText + '</a></li>';
    }

    const NOTIFICATION_TYPES = {
        follow: 'App\\Notifications\\UserFollowed',
        newPost: 'App\\Notifications\\NewPost'
    };

    function routeNotification(notification) {
      var to = `?read=${notification.id}`;
      if(notification.type === NOTIFICATION_TYPES.follow) {
          to = 'users' + to;
      } else if(notification.type === NOTIFICATION_TYPES.newPost) {
          const postId = notification.data.post_id;
          to = `posts/${postId}` + to;
      }
      return '/' + to;
    }

    function makeNotificationText(notification) {
      var text = '';
      if(notification.type === NOTIFICATION_TYPES.follow) {
          const name = notification.data.follower_name;
          text += '<strong>'+name+'</strong> Mengikutimu';
      } else if(notification.type === NOTIFICATION_TYPES.newPost) {
          const name = notification.data.following_name;
          const created=notification.created_at;
          text += '<strong>'+name+'</strong> Membuat Posting Baru '+created;
      }
      return text;
    }
    </script>
</body>
</html>
