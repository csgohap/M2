<?php
////========================================================////
////																											  ////
////               Before-middleware M-пакета					      ////
////																												////
////========================================================////

//-------------------//
// Пространство имён //
//-------------------//

  namespace M2\Middlewares;

//---------------------------------//
// Подключение необходимых классов //
//---------------------------------//

  // Классы, поставляемые Laravel
  use Illuminate\Routing\Controller as BaseController,
      Illuminate\Support\Facades\App,
      Illuminate\Support\Facades\Artisan,
      Illuminate\Support\Facades\Auth,
      Illuminate\Support\Facades\Blade,
      Illuminate\Support\Facades\Bus,
      Illuminate\Support\Facades\Cache,
      Illuminate\Support\Facades\Config,
      Illuminate\Support\Facades\Cookie,
      Illuminate\Support\Facades\Crypt,
      Illuminate\Support\Facades\DB,
      Illuminate\Database\Eloquent\Model,
      Illuminate\Support\Facades\Event,
      Illuminate\Support\Facades\File,
      Illuminate\Support\Facades\Hash,
      Illuminate\Support\Facades\Input,
      Illuminate\Foundation\Inspiring,
      Illuminate\Support\Facades\Lang,
      Illuminate\Support\Facades\Log,
      Illuminate\Support\Facades\Mail,
      Illuminate\Support\Facades\Password,
      Illuminate\Support\Facades\Queue,
      Illuminate\Support\Facades\Redirect,
      Illuminate\Support\Facades\Redis,
      Illuminate\Support\Facades\Request,
      Illuminate\Support\Facades\Response,
      Illuminate\Support\Facades\Route,
      Illuminate\Support\Facades\Schema,
      Illuminate\Support\Facades\Session,
      Illuminate\Support\Facades\Storage,
      Illuminate\Support\Facades\URL,
      Illuminate\Support\Facades\Validator,
      Illuminate\Support\Facades\View,
      Closure;

//------------//
// Middleware //
//------------//
class BeforeMiddleware {

  //----------------------------//
  // Обработать входящий запрос //
  //----------------------------//
  // - Запрос проходит через этот код, перед отправкой ответа пользователю
  public function handle($request, Closure $next)
  {


    // ... код middleware ...


    // n. Передать ответ дальше
    return $next($request);

  }

  //-----------------------//
  // Terminable middleware //
  //-----------------------//
  // - Данный код выполняется уже после отправки ответа пользователю

    // TODO: раскомментировать, в случае реализации middleware интерфейса TerminableMiddleware
//  public function terminate($request, $response)
//  {
//
//      // Например, здесь можно сохранить сессионные данные...
//
//  }




}
