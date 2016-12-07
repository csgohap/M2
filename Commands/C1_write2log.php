<?php
////======================================================////
////																										  ////
////                    Команда M-пакета						      ////
////																											////
////======================================================////
/**
 *  Что делает
 *  ----------
 *    - Add record to log, bind this record with tags if needed
 *
 *  Какие аргументы принимает
 *  -------------------------
 *
 *    [
 *      "data" => [
 *        msg         // Текстовая строка для записи в лог
 *        tags        // Теги, с которыми надо связать запись в лог
 *      ]
 *    ]
 *
 *  Формат возвращаемого значения
 *  -----------------------------
 *
 *    [
 *      status          // 0 - всё ОК, -1 - нет доступа, -2 - ошибка
 *      data            // результат выполнения команды
 *    ]
 *
 *  Значение data в зависимости от статуса
 *  --------------------------------------
 *
 *    status == 0
 *    -----------
 *      - ""
 *
 *    status == -1
 *    ------------
 *      - Не контролируется в командах. Отслеживается в хелпере runcommand.
 *
 *    status = -2
 *    -----------
 *      - Текст ошибки. Может заменяться на "" в контроллерах (чтобы скрыть от клиента).
 *
 */

//---------------------------//
// Пространство имён команды //
//---------------------------//
// - Пример:  M1\Commands

  namespace M2\Commands;

//---------------------------------//
// Подключение необходимых классов //
//---------------------------------//

  // Базовые классы, необходимые для работы команд вообще
  use App\Jobs\Job,
      Illuminate\Queue\SerializesModels,
      Illuminate\Queue\InteractsWithQueue,
      Illuminate\Contracts\Queue\ShouldQueue;   // добавление в очередь задач

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
      Illuminate\Support\Facades\View;

  // Доп.классы, которые использует эта команда


//---------//
// Команда //
//---------//
class C1_write2log extends Job { // TODO: добавить "implements ShouldQueue" - и команда будет добавляться в очередь задач

  //----------------------------//
  // А. Подключить пару трейтов //
  //----------------------------//
  use InteractsWithQueue, SerializesModels;

  //-------------------------------------//
  // Б. Переменные для приёма аргументов //
  //-------------------------------------//
  // - Которые передаются через конструктор при запуске команды

    // Принять данные
    public $data;

  //------------------------------------------------------//
  // В. Принять аргументы, переданные при запуске команды //
  //------------------------------------------------------//
  public function __construct($data)  // TODO: указать аргументы
  {

    $this->data = $data;

  }

  //----------------//
  // Г. Код команды //
  //----------------//
  public function handle()
  {

    /**
     * Оглавление
     *
     *  1. Получить входящие аргументы
     *  2. Преобразовать $msg в строку
     *  3. Провести валидацию
     *  4. Добавить новую запись в лог
     *  5. Добавить в M2_tags те теги, которых там ещё нет
     *  6. Связать запись $newnote со всеми тегами из $tags
     *  7. Применить заданные в настройках ограничения на объем хранимых в логе данных
     *
     *  N. Вернуть статус 0
     *
     */

    //---------------------------------------------------------//
    // Добавить запись в лог, связать с тегами, если требуется //
    //---------------------------------------------------------//
    $res = call_user_func(function() { try { DB::beginTransaction();

      // 1. Получить входящие аргументы
      $msg  = $this->data['msg'];
      $tags = $this->data['tags'] == 'empty' ? [] : $this->data['tags'];

      // 2. Преобразовать $msg в строку
      switch (gettype($msg)) {
        case 'boolean':       $msg = '(boolean) '.$msg; break;
        case 'integer':       $msg = '(integer) '.$msg; break;
        case 'double':        $msg = '(double) '.$msg; break;
        case 'string':        $msg = '(string) '.$msg; break;
        case 'array':         $msg = '(array) '.json_encode($msg, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); break;
        case 'object':        $msg = '(object) '.json_encode($msg, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); break;
        case 'resource':      $msg = '(resource) '.'write2log не может преобразовать переменную типа resource в строку'; break;
        case 'NULL':          $msg = 'NULL'; break;
        case 'unknown type':  $msg = '(unknown type) '.'write2log не может преобразовать переменную типа unknown type в строку'; break;
        default:              $msg = 'write2log не может преобразовать переменную не опознанного типа в строку'; break;
      }

      // 3. Провести валидацию

        // Сообщение
        //if( !preg_match("/^[0-9а-яА-ЯёЁa-zA-Z-\/\\\\_!№@#$&:=()\[\]{}*%?\"'`.,\r\n +->\"]*$/ui", $msg) )
        //  throw new \Exception('При добавлении записи в лог произошла ошибка, сообщение не прошло влидацию.');

        // Теги
        foreach($tags as $tag) {
          if( !preg_match("/^[0-9а-яА-ЯёЁa-zA-Z-\/\\\\_!№@#$&:=()\[\]{}*%?\"'`.,\r\n +-]*$/ui", $tag) ) {
            throw new \Exception('При добавлении записи в лог произошла ошибка, один из тегов не прошел влидацию.');
          }
        }

      // 4. Добавить новую запись в лог

        // 4.1. Если класса \M2\Models\MD1_log не существует
        // - Сообщить и завершить
        if(!class_exists('\M2\Models\MD1_log'))
          throw new \Exception('Класс "\M2\Models\MD1_log" не существует.');

        // 4.2. Создать новый eloquent-объект
        $newnote = new \M2\Models\MD1_log();

        // 4.3. Наполнить $newnote
        $newnote->message = $msg;

        // 4.4. Сохранить $newnote в БД
        $newnote->save();

        // 4.5. Получить ID новой записи
        $newnote_id = $newnote->id;

      // 5. Добавить в M2_tags те теги, которых там ещё нет
      // - Все теги приводить к нижнему регистру

        // 5.1. Получить коллекцию всех тегов из БД в виде массива
        $tags_in_db = \M2\Models\MD2_tags::all()->toArray();

        // 5.2. Вычислить расхождения массивов $tags с $tags_in_db
        // - Все сравнения проводить в нижнем регистре
        // - В нём будут те теги из $tags, которых в БД ещё нет
        $result_arr = [];
        foreach($tags as $tag) {
          $is = 0;
          foreach($tags_in_db as $tag_in_db) {
            if(mb_strtolower($tag) == mb_strtolower($tag_in_db['tagname'])) { $is = 1; break; }
          }
          if(!$is) array_push($result_arr, $tag);
        }

        // 5.3. Добавить теги из $result_arr в БД, в таблицу тегов
        foreach($result_arr as $newtagname) {

          // 1] Создать новый eloquent-объект настроек
          $newtag = new \M2\Models\MD2_tags();

          // 2] Наполнить $newtag
          $newtag->tagname      = mb_strtolower($newtagname);
          $newtag->description  = '';
          $newtag->color        = '#000';
          $newtag->priority     = 0;

          // 3] Сохранить $newtag в БД
          $newtag->save();

        }

      // 6. Связать запись $newnote со всеми тегами из $tags

        // 6.1. Получить массив ID всех тегов из $tags из БД
        $tags_ids = \M2\Models\MD2_tags::whereIn('tagname', $tags)->pluck('id');

        // 6.1. Связать
        foreach($tags_ids as $tagid) {
          $newnote->tags()->attach($tagid);
        }

      // 7. Применить заданные в настройках ограничения на объем хранимых в логе данных
      runcommand('\M2\Commands\C2_limitator');

    DB::commit(); } catch(\Exception $e) {
        $errortext = 'Invoking of command C1_write2log from M-package M2 have ended with error: '.$e->getMessage();
        DB::rollback();
        Log::info($errortext);
        return [
          "status"  => -2,
          "data"    => $errortext
        ];
    }}); if(!empty($res)) return $res;


    //---------------------//
    // N. Вернуть статус 0 //
    //---------------------//
    return [
      "status"  => 0,
      "data"    => ""
    ];

  }

  //--------------//
  // Д. Заготовки //
  //--------------//
  /**
   *
   * Д1. Провести валидацию
   * Д2. Начать транзакцию
   * Д3. Подтвердить транзакцию
   * Д4. Сохранить данные в БД
   * Д5. Создать элемент
   * Д6. Удалить элемент
   *
   *
   */


    // Д1. Провести валидацию //
    //------------------------//

      //// x. Провести валидацию
      //
      //  // Создать объект-валидатор
      //  $validator = Validator::make($this->data, [
      //    'prop1'               => 'sometimes|rule1',
      //    'prop2'               => 'sometimes|rule2',
      //    'prop3'               => 'sometimes|rule3',
      //  ]);
      //
      //  // Провести валидацию, и если она провалилась...
      //  if ($validator->fails()) {
      //
      //    // Вернуть статус -2 и ошибку
      //    return [
      //      "status"  => -2,
      //      "data"    => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE)
      //    ];
      //
      //  }


    // Д2. Начать транзакцию //
    //-----------------------//

      //// x. Начать транзакцию
      //DB::beginTransaction();


    // Д3. Подтвердить транзакцию //
    //----------------------------//

      //// x. Подтвердить транзакцию
      //DB::commit();


    // Д4. Сохранить данные в БД //
    //---------------------------//

      //// x. Сохранить данные в БД
      //try {
      //
      //  // 4.1. Получить eloquent-объект
      //  $item = \M1\Models\MD1_somemodel::find($this->data['id']);
      //
      //  // 4.2. Сохранить в v присланные данные
      //  foreach($this->data as $key => $value) {
      //
      //    // Если $key == 'id', перейти к следующей итерации
      //    if($key == 'id') continue;
      //
      //    // Если в таблице есть столбец $key
      //    if(lib_hasColumn('m1', 'MD1_somemodel', $key)) {
      //
      //      // Изменить значение столбца $key на $value
      //      $item[$key] = $value;
      //
      //      // Сохранить изменения
      //      $item->save();
      //
      //    }
      //
      //  }
      //
      //} catch(\Exception $e) {
      //
      //  // Откатить транзакцию
      //  DB::rollBack();
      //
      //  // Вернуть статус -2 и ошибку
      //  return [
      //    "status"  => -2,
      //    "data"    => $e->getMessage()
      //  ];
      //
      //}


    // Д5. Создать элемент //
    //---------------------//

      //// x. Создать новый элемент
      //try {
      //
      //  // 1] Попробовать найти удалённый элемент
      //  $item = \M7\Models\MD1_somemodel::onlyTrashed()->where('name','=',$this->data['name'])->first();
      //
      //  // 2] Если удалённый элемент с таким именем не найдн
      //  if(empty($item)) {
      //
      //    // 2.1] Создать новый элемент
      //    $item = new \M1\Models\MD1_somemodel();
      //
      //    // 2.2] Наполнить $new данными
      //    $item->name                        = $this->data['name'];
      //    $item->description                 = $this->data['description'];
      //
      //    // 2.3] Сохранить $new в БД
      //    $item->save();
      //
      //  }
      //
      //  // 3] Если удалённый элемент найден
      //  if(!empty($item)) {
      //
      //    // 3.1] Восстановить его
      //    $item->restore();
      //
      //    // 3.2] Обновить некоторые свойства права
      //    $item->description                 = $data['description'];
      //
      //    // 3.3] Сохранить изменения
      //    $item->save();
      //
      //  }
      //
      //
      //} catch(\Exception $e) {
      //
      //  // Откатить транзакцию
      //  DB::rollBack();
      //
      //  // Вернуть статус -2 и ошибку
      //  return [
      //    "status"  => -2,
      //    "data"    => $e->getMessage()
      //  ];
      //
      //}


    // Д6. Удалить элемент //
    //---------------------//

      //// x. Удалить элемент
      //try {
      //
      //  // 1] Получить eloquent-модель элемента, который требуется удалить
      //  $item = \M1\Models\MD1_somemodel::find($this->data['id']);
      //
      //  // 2] Удалить элемент
      //  $item->delete();
      //
      //} catch(\Exception $e) {
      //
      //  // Откатить транзакцию
      //  DB::rollBack();
      //
      //  // Вернуть статус -2 и ошибку
      //  return [
      //    "status"  => -2,
      //    "data"    => $e->getMessage()
      //  ];
      //
      //}




}

?>

