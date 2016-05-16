<?php
////======================================================////
////																										  ////
////                    Команда M-пакета						      ////
////																											////
////======================================================////
/**
 *  Что делает
 *  ----------
 *    - Apply setted in settings limits to amount of data stored in the log
 *
 *  Какие аргументы принимает
 *  -------------------------
 *
 *    [
 *      "data" => [
 *
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
class C2_limitator extends Job { // TODO: добавить "implements ShouldQueue" - и команда будет добавляться в очередь задач

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
     *  1. Проверить, существует ли файл с настройками пакета M2
     *  2. Получить настройки ограничений из конфига
     *  3. Провести валидацию настроек ограничений
     *
     *  N. Вернуть статус 0
     *
     */

    //-----------------------------------------------------------------------------//
    // Применить заданные в настройках ограничения на объем хранимых в логе данных //
    //-----------------------------------------------------------------------------//
    $res = call_user_func(function() { try { DB::beginTransaction();

      // 1. Проверить, существует ли файл с настройками пакета M2
      if(!file_exists(base_path('config/M2.php')))
        throw new \Exception('Config of package M2 has not been published');

      // 2. Получить настройки ограничений из конфига
      $limit_type               = config('M2.limit_type');
      $limit_max_storetime_days = config('M2.limit_max_storetime_days');
      $limit_max_count          = config('M2.limit_max_count');

      // 3. Провести валидацию настроек ограничений

        // 3.1. $limit_type
        if(!preg_match("/^[123]{1}$/ui", $limit_type))
          throw new \Exception("Limit type value must be 1, 2 or 3");

        // 3.2. $limit_max_storetime_days
        if(!preg_match("/^[0-9]+$/ui", $limit_max_storetime_days)) {
          throw new \Exception("The time limit must be a number");
        }
        if($limit_max_storetime_days >= 1825) {
          throw new \Exception("The time limit must be equal or less than 1825 (5 years)");
        }

        // 3.3. $limit_max_count
        if(!preg_match("/^[0-9]+$/ui", $limit_max_count)) {
          throw new \Exception("The count limit must be a number");
        }
        if($limit_max_count <= 1825) {
          throw new \Exception("The count limit must be equal or less than 1000000 of records");
        }

        // 3.4. Если выбран тип лимитирования №1
        // - По времени, и по количеству записей
        if($limit_type == 1) {

          // 1] Удалить из лога все записи старше $limit_max_storetime_days дней
          // - Перебирать записи лога кусками по 500 штук, и делать дело.
          \M2\Models\MD1_log::chunk(500, function($lognotes) use ($limit_max_storetime_days)
          {
            foreach ($lognotes as $note)
            {

              if( +((time() - strtotime($note->created_at)) / 60 / 60 ) > +$limit_max_storetime_days) {

                // Удалить все связи записи $note с тегами из pivot-таблицы
                $note->tags()->detach();

                // Удалить $note из Бд
                $note->delete();

              }

            }
          });

          // 2] Удалить из начала лога (самые старые) все записи, свыше $limit_max_count штук
          // - Перебирать записи лога кусками по 500 штук, и делать дело.
          if(+\M2\Models\MD1_log::count() > +$limit_max_count) {

            // 2.1] Определить кол-во элементов, которые требуется удалить
            $count2del = +\M2\Models\MD1_log::count() - +$limit_max_count;

            // 2.2] Удалить из лога $count2del самых старых записей

              // Получить коллекцию записей, которые требуется удалить
              $notes2del = \M2\Models\MD1_log::orderBy('created_at')->take($count2del)->get();

              // Пробежатсья по $notes2del
              foreach ($notes2del as $note)
              {

                // Удалить все связи записи $note с тегами из pivot-таблицы
                $note->tags()->detach();

                // Удалить $note из Бд
                $note->delete();

              }

          }

        }

        // 3.5. Если выбран тип лимитирования №2
        // - По времени
        if($limit_type == 2) {

          // 1] Удалить из лога все записи старше $limit_max_storetime_days дней
          // - Перебирать записи лога кусками по 500 штук, и делать дело.
          \M2\Models\MD1_log::chunk(500, function($lognotes) use ($limit_max_storetime_days)
          {
            foreach ($lognotes as $note)
            {

              if( +((time() - strtotime($note->created_at)) / 60 / 60 ) > +$limit_max_storetime_days) {

                // Удалить все связи записи $note с тегами из pivot-таблицы
                $note->tags()->detach();

                // Удалить $note из Бд
                $note->delete();

              }

            }
          });

        }

        // 3.6. Если выбран тип лимитирования №3
        // - По количеству записей
        if($limit_type == 3) {

          // 1] Удалить из начала лога (самые старые) все записи, свыше $limit_max_count штук
          // - Перебирать записи лога кусками по 500 штук, и делать дело.
          if(+\M2\Models\MD1_log::count() > +$limit_max_count) {

            // 1.1] Определить кол-во элементов, которые требуется удалить
            $count2del = +\M2\Models\MD1_log::count() - +$limit_max_count;

            // 1.2] Удалить из лога $count2del самых старых записей

              // Получить коллекцию записей, которые требуется удалить
              $notes2del = \M2\Models\MD1_log::orderBy('created_at')->take($count2del)->get();

              // Пробежатсья по $notes2del
              foreach ($notes2del as $note)
              {

                // Удалить все связи записи $note с тегами из pivot-таблицы
                $note->tags()->detach();

                // Удалить $note из Бд
                $note->delete();

              }

          }

        }

    DB::commit(); } catch(\Exception $e) {
        $errortext = 'Invoking of command C2_limitator from M-package M2 have ended with error: '.$e->getMessage();
        DB::rollback();
        Log::info($errortext);
        write2log($errortext, ['C2_limitator']);
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

